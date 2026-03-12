<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\ConfigTributaria;
use App\Models\ConfigNegocio;
use App\Models\Comprobante;
use App\Models\ComprobanteDetalle;
use App\Models\TipoComprobante;
use App\Models\SerieComprobante;
use Greenter\See;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Model\Sale\Note;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SunatService
{
    protected $see;
    protected $config;
    protected $negocio;

    public function __construct()
    {
        $this->config = ConfigTributaria::first();
        $this->negocio = ConfigNegocio::first();

        $this->see = new See();
        $this->see->setService($this->config->modo == 'produccion' ? SunatEndpoints::FE_PRODUCCION : SunatEndpoints::FE_BETA);

        // Configurar certificado
        if($this->config->certificado_path) {
            if (Storage::exists('certificados/' . $this->config->certificado_path)) {
                $path = Storage::path('certificados/' . $this->config->certificado_path);
            } else {
                $path = storage_path('app/certificados/' . $this->config->certificado_path);
            }

            if(file_exists($path)) {
                $certificadoContent = file_get_contents($path);

                // Si tiene contraseña (archivos .p12 o .pfx)
                if($this->config->certificado_password) {
                    $this->see->setCertificate($certificadoContent, $this->config->certificado_password);
                } else {
                    $this->see->setCertificate($certificadoContent);
                }
            }
        }

        // Corrección de credenciales (Separación RUC/Usuario)
        $ruc = $this->negocio->ruc;
        $usuarioSolCompleto = $this->config->usuario_sol;
        $usuarioSolo = str_replace($ruc, '', $usuarioSolCompleto);

        if(empty($usuarioSolo)) {
             $usuarioSolo = $usuarioSolCompleto;
        }

        // Log para debug de credenciales
        Log::info('Configurando credenciales SUNAT', [
            'modo' => $this->config->modo,
            'ruc' => $ruc,
            'usuario_sol_completo' => $usuarioSolCompleto,
            'usuario_solo' => $usuarioSolo,
            'clave_sol_length' => strlen($this->config->clave_sol ?? ''),
            'tiene_certificado' => !empty($this->config->certificado_path)
        ]);

        $this->see->setClaveSOL($ruc, $usuarioSolo, $this->config->clave_sol);
    }

    public function generarComprobante(Venta $venta)
    {
        Log::info('Iniciando generación de comprobante', [
            'venta_id' => $venta->id,
            'modo' => $this->config->modo,
            'certificado_existe' => !empty($this->config->certificado_path)
        ]);

        // 1. DEFINIR EMPRESA (EMISOR)
        $company = new Company();
        $company->setRuc($this->negocio->ruc)
            ->setRazonSocial($this->negocio->nombre_comercial)
            ->setAddress((new Address())
                ->setUbigueo('130101') // Idealmente parametrizable
                ->setDepartamento('LA LIBERTAD')
                ->setProvincia('TRUJILLO')
                ->setDistrito('TRUJILLO')
                ->setDireccion($this->negocio->direccion));

        // 2. DEFINIR CLIENTE (RECEPTOR)
        $client = new Client();

        $tipoDocCliente = '0'; // Sin RUC
        $numDocCliente = '00000000';
        $razonSocialCliente = 'CLIENTE GENERICO';
        $direccionCliente = '-';

        if ($venta->cliente) {
            $razonSocialCliente = $venta->cliente->nombre . ' ' . $venta->cliente->apellido;
            $numDocCliente = $venta->cliente->numero_documento;
            $direccionCliente = $venta->cliente->direccion ?? '-';

            if (strlen($numDocCliente) == 11) {
                $tipoDocCliente = '6'; // RUC
            } elseif (strlen($numDocCliente) == 8) {
                $tipoDocCliente = '1'; // DNI
            }
        }

        $client->setTipoDoc($tipoDocCliente)
            ->setNumDoc($numDocCliente)
            ->setRznSocial($razonSocialCliente);

        // ============================================================
        // INICIO DE LA TRANSACCIÓN: Protegemos el correlativo
        // ============================================================
        DB::beginTransaction();

        try {
            // 3. DETERMINAR TIPO DE COMPROBANTE Y SERIE
            $tipoComprobante = ($tipoDocCliente == '6') ? '01' : '03';
            $serie = ($tipoComprobante == '01') ? 'F001' : 'B001';

            // Obtener el objeto SerieComprobante
            $serieObj = SerieComprobante::where('serie', $serie)->where('activo', true)->firstOrFail();

            // AQUÍ SUMA +1 TEMPORALMENTE EN LA BASE DE DATOS
            $correlativo = $serieObj->obtenerSiguienteCorrelativo();

            // 4A. CÁLCULOS MATEMÁTICOS
            $total = $venta->total;
            $opGravadas = round($total / 1.18, 2);
            $mtoIgv = round($total - $opGravadas, 2);

            // 4B. CREAR LA VENTA (INVOICE)
            $invoice = new Invoice();
            $invoice->setUblVersion('2.1')
                ->setTipoOperacion('0101')
                ->setTipoDoc($tipoComprobante)
                ->setSerie($serie)
                ->setCorrelativo($correlativo)
                ->setFechaEmision(\DateTime::createFromFormat('Y-m-d H:i:s', $venta->fecha))
                ->setFormaPago(new \Greenter\Model\Sale\FormaPagos\FormaPagoContado())
                ->setTipoMoneda('PEN')
                ->setCompany($company)
                ->setClient($client)
                ->setMtoOperGravadas($opGravadas)
                ->setMtoIGV($mtoIgv)
                ->setTotalImpuestos($mtoIgv)
                ->setValorVenta($opGravadas)
                ->setSubTotal($total)
                ->setMtoImpVenta($total);

            // 5. AGREGAR ÍTEMS
            $items = [];
            foreach ($venta->detalles as $det) {
                $item = new SaleDetail();

                $valorUnitario = round($det->precio_unitario / 1.18, 2);
                $impuestoItem = round(($det->precio_unitario - $valorUnitario) * $det->cantidad, 2);
                $valorVentaItem = round($valorUnitario * $det->cantidad, 2);

                $item->setCodProducto($det->id_producto ?? 'SERV')
                    ->setUnidad('NIU')
                    ->setCantidad($det->cantidad)
                    ->setDescripcion($det->nombre_item)
                    ->setMtoBaseIgv($valorVentaItem)
                    ->setPorcentajeIgv(18.00)
                    ->setIgv($impuestoItem)
                    ->setTipAfeIgv('10')
                    ->setTotalImpuestos($impuestoItem)
                    ->setMtoValorVenta($valorVentaItem)
                    ->setMtoValorUnitario($valorUnitario)
                    ->setMtoPrecioUnitario($det->precio_unitario);

                $items[] = $item;
            }
            $invoice->setDetails($items);

            $legend = new Legend();
            $legend->setCode('1000')
                ->setValue($this->numeroALetras($venta->total));
            $invoice->setLegends([$legend]);

            // 6. ENVIAR A SUNAT
            Log::info('Enviando comprobante a SUNAT', [
                'serie' => $invoice->getSerie(),
                'correlativo' => $invoice->getCorrelativo(),
                'cliente_doc' => $client->getNumDoc()
            ]);

            $result = $this->see->send($invoice);

            // Guardamos el XML base siempre
            $nombreArchivo = $invoice->getName();
            Storage::put('comprobantes/xml/' . $nombreArchivo . '.xml', $this->see->getFactory()->getLastXml());

            // 7. EVALUAR RESPUESTA DE SUNAT
            if ($result->isSuccess()) {
                /** @var \Greenter\Model\Response\BillResult $result */
                $cdr = $result->getCdrResponse();
                $mensaje = $cdr->getDescription();
                $hash = $cdr->getId();

                $nombreCdr = 'R-' . $nombreArchivo . '.zip';
                Storage::put('comprobantes/cdr/' . $nombreCdr, $result->getCdrZip());

                // Generar PDF
                $nombrePdf = $nombreArchivo . '.pdf';
                $pdfContent = $this->generarPDF($invoice, $company, $client, $venta, $hash);
                Storage::put('comprobantes/pdf/' . $nombrePdf, $pdfContent);

                // REGISTRAR EN BD SOLO SI SUNAT ACEPTÓ
                $comprobante = Comprobante::create([
                    'id_venta' => $venta->id,
                    'id_tipo_comprobante' => ($tipoComprobante == '01' ? 1 : 2),
                    'id_serie_comprobante' => $serieObj->id,
                    'serie' => $serie,
                    'correlativo' => $correlativo,
                    'fecha_emision' => Carbon::now(),
                    'receptor_tipo_doc' => $tipoDocCliente,
                    'receptor_numero_doc' => $numDocCliente,
                    'receptor_razon_social' => $razonSocialCliente,
                    'receptor_direccion' => $direccionCliente,
                    'op_gravadas' => $opGravadas,
                    'monto_igv' => $mtoIgv,
                    'total' => $total,
                    'moneda' => 'PEN',
                    'forma_pago' => 'Contado',
                    'leyenda_sunat' => $this->numeroALetras($venta->total),
                    'nombre_xml' => $nombreArchivo,
                    'cdr_xml' => $nombreCdr,
                    'ruta_pdf' => $nombrePdf,
                    'hash_cpe' => $hash,
                    'estado_sunat' => 'aceptado',
                    'mensaje_sunat' => $mensaje,
                    'enviado_sunat' => true
                ]);

                // Guardar detalles
                foreach ($venta->detalles as $det) {
                    $valorUnitario = round($det->precio_unitario / 1.18, 2);
                    $subtotalBase = round($valorUnitario * $det->cantidad, 2);
                    $igvItem = round(($det->precio_unitario - $valorUnitario) * $det->cantidad, 2);
                    $totalItem = $det->subtotal;

                    ComprobanteDetalle::create([
                        'id_comprobante' => $comprobante->id,
                        'tipo_item'      => $det->tipo_item,
                        'descripcion'    => $det->nombre_item,
                        'codigo_unidad'  => ($det->tipo_item == 'servicio' ? 'ZZ' : 'NIU'),
                        'cantidad'       => $det->cantidad,
                        'precio_unitario'=> $det->precio_unitario,
                        'valor_unitario' => $valorUnitario,
                        'subtotal'       => $subtotalBase,
                        'igv_total'      => $igvItem,
                        'total'          => $totalItem
                    ]);
                }

                // TODO OK: Confirmamos la transacción (Se guarda todo definitivamente)
                DB::commit();
                return ['success' => true, 'message' => $mensaje];

            } else {
                // SUNAT RECHAZÓ (Ej. Error 0111)
                $mensaje = $result->getError()->getCode() . ' - ' . $result->getError()->getMessage();

                // DESHACEMOS LA TRANSACCIÓN: El correlativo regresa a su estado anterior y nada se guarda
                DB::rollBack();
                return ['success' => false, 'message' => $mensaje];
            }

        } catch (\Exception $e) {
            // SI FALLA PHP O LA BD, DESHACEMOS TODO
            DB::rollBack();
            Log::error('Error al generar comprobante', [
                'venta_id' => $venta->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
        }
    }

    public function generarNotaCredito(Venta $venta, $motivo = "Anulación de la operación")
    {
        // 1. OBTENER EL COMPROBANTE ORIGINAL QUE VAMOS A ANULAR
        $cpeOriginal = $venta->comprobante;

        if (!$cpeOriginal) {
            return ['success' => false, 'message' => 'No existe comprobante para esta venta.'];
        }

        // 2. CONFIGURAR EMPRESA
        $company = (new Company())
            ->setRuc($this->negocio->ruc)
            ->setRazonSocial($this->negocio->nombre_comercial)
            ->setAddress(
                (new Address())
                    ->setUbigueo('130101')
                    ->setDepartamento('LA LIBERTAD')
                    ->setProvincia('TRUJILLO')
                    ->setDistrito('TRUJILLO')
                    ->setDireccion($this->negocio->direccion));

        // 3. CONFIGURAR CLIENTE
        $client = new Client();
        $tipoDoc = $cpeOriginal->receptor_tipo_doc;
        $numDoc = $cpeOriginal->receptor_numero_doc;
        $rznSocial = $cpeOriginal->receptor_razon_social;
        $direccionCliente = $cpeOriginal->receptor_direccion ?? '-';

        if (empty($numDoc)) {
            if ($venta->cliente) {
                $numDoc = $venta->cliente->numero_documento;
                $rznSocial = $venta->cliente->nombre . ' ' . $venta->cliente->apellido;
                $direccionCliente = $venta->cliente->direccion ?? '-';

                if (strlen($numDoc) == 11) {
                    $tipoDoc = '6'; // RUC
                } elseif (strlen($numDoc) == 8) {
                    $tipoDoc = '1'; // DNI
                } else {
                    $tipoDoc = '-';
                }
            } else {
                $numDoc = '00000000';
                $rznSocial = 'CLIENTES VARIOS';
                $tipoDoc = '0';
            }
        }

        $client->setTipoDoc($tipoDoc)
            ->setNumDoc($numDoc)
            ->setRznSocial($rznSocial);

        // ============================================================
        // INICIO DE LA TRANSACCIÓN: Protegemos el correlativo de la NC
        // ============================================================
        DB::beginTransaction();

        try {
            // 4. DETERMINAR SERIE DE LA NOTA DE CRÉDITO
            $esFactura = ($cpeOriginal->id_tipo_comprobante == 1);
            $serieNota = $esFactura ? 'FC01' : 'BC01';

            $serieObj = SerieComprobante::where('serie', $serieNota)->where('activo', true)->firstOrFail();
            $idSerieNota = $serieObj->id;

            // AQUÍ SUMA +1 TEMPORALMENTE EN LA BD PARA LA NOTA DE CRÉDITO
            $correlativoNota = $serieObj->obtenerSiguienteCorrelativo();

            // 5. CREAR LA NOTA
            /** @var Note $note */
            $note = new Note();

            $note->setUblVersion('2.1')
                ->setTipoDoc('07')
                ->setSerie($serieNota)
                ->setCorrelativo($correlativoNota)
                ->setFechaEmision(new \DateTime())
                ->setTipoMoneda('PEN')
                ->setCompany($company)
                ->setClient($client);

            $note->setTipDocAfectado($esFactura ? '01' : '03')
                ->setNumDocfectado($cpeOriginal->serie . '-' . $cpeOriginal->correlativo)
                ->setCodMotivo('01')
                ->setDesMotivo($motivo);

            $opGravadas = $cpeOriginal->op_gravadas;
            $mtoIgv = $cpeOriginal->monto_igv;
            $total = $cpeOriginal->total;

            $note->setMtoOperGravadas($opGravadas)
                ->setMtoIGV($mtoIgv)
                ->setTotalImpuestos($mtoIgv)
                ->setValorVenta($opGravadas)
                ->setSubTotal($total)
                ->setMtoImpVenta($total);

            // 6. ÍTEMS
            $items = [];
            foreach ($venta->detalles as $det) {
                $item = new SaleDetail();

                $valorUnitario = round($det->precio_unitario / 1.18, 2);
                $impuestoItem = round(($det->precio_unitario - $valorUnitario) * $det->cantidad, 2);
                $valorVentaItem = round($valorUnitario * $det->cantidad, 2);

                $item->setCodProducto('ITEM')
                    ->setUnidad('NIU')
                    ->setCantidad($det->cantidad)
                    ->setDescripcion($det->nombre_item ?? 'Item de venta')
                    ->setMtoBaseIgv($valorVentaItem)
                    ->setPorcentajeIgv(18.00)
                    ->setIgv($impuestoItem)
                    ->setTipAfeIgv('10')
                    ->setTotalImpuestos($impuestoItem)
                    ->setMtoValorVenta($valorVentaItem)
                    ->setMtoValorUnitario($valorUnitario)
                    ->setMtoPrecioUnitario($det->precio_unitario);

                $items[] = $item;
            }

            if (count($items) === 0) {
                 DB::rollBack();
                 return ['success' => false, 'message' => 'Error: La venta no tiene ítems para anular.'];
            }

            $note->setDetails($items);

            $legend = new Legend();
            $legend->setCode('1000')
                ->setValue($this->numeroALetras($cpeOriginal->total));
            $note->setLegends([$legend]);

            // 7. ENVIAR A SUNAT
            Log::info('Enviando Nota de Crédito a SUNAT', [
                'serie' => $note->getSerie(),
                'correlativo' => $note->getCorrelativo()
            ]);

            $result = $this->see->send($note);

            $nombreArchivo = $note->getName();
            Storage::put('comprobantes/xml/' . $nombreArchivo . '.xml', $this->see->getFactory()->getLastXml());

            if ($result->isSuccess()) {
                /** @var \Greenter\Model\Response\BillResult $result */
                $cdr = $result->getCdrResponse();
                $mensaje = $cdr->getDescription();
                $hash = $cdr->getId();

                $nombreCdr = 'R-' . $nombreArchivo . '.zip';
                Storage::put('comprobantes/cdr/' . $nombreCdr, $result->getCdrZip());

                $nombrePdf = $nombreArchivo . '.pdf';
                $pdfContent = $this->generarPDFNotaCredito($note, $company, $client, $venta, $cpeOriginal, $hash);
                Storage::put('comprobantes/pdf/' . $nombrePdf, $pdfContent);

                // 8. GUARDAR EN BD SOLO SI ACEPTÓ
                $tipoNc = TipoComprobante::where('codigo_sunat', '07')->first();
                $tipoNcId = $tipoNc ? $tipoNc->id : 3;

                $nc = Comprobante::create([
                    'id_venta' => $venta->id,
                    'id_tipo_comprobante' => $tipoNcId,
                    'id_serie_comprobante' => $idSerieNota,
                    'serie' => $serieNota,
                    'correlativo' => $correlativoNota,
                    'fecha_emision' => Carbon::now(),
                    'id_comprobante_ref' => $cpeOriginal->id,
                    'cod_motivo_nc' => '01',
                    'descripcion_motivo_nc' => $motivo,
                    'op_gravadas' => $cpeOriginal->op_gravadas,
                    'monto_igv' => $cpeOriginal->monto_igv,
                    'total' => $cpeOriginal->total,
                    'moneda' => 'PEN',
                    'leyenda_sunat' => $this->numeroALetras($cpeOriginal->total),
                    'forma_pago' => 'Contado',
                    'receptor_tipo_doc' => $client->getTipoDoc(),
                    'receptor_numero_doc' => $client->getNumDoc(),
                    'receptor_razon_social' => $client->getRznSocial(),
                    'receptor_direccion' => $direccionCliente,
                    'nombre_xml' => $nombreArchivo,
                    'cdr_xml' => $nombreCdr,
                    'ruta_pdf' => $nombrePdf,
                    'hash_cpe' => $hash,
                    'estado_sunat' => 'aceptado',
                    'mensaje_sunat' => $mensaje,
                    'enviado_sunat' => true,
                ]);

                // Guardar detalles
                foreach ($venta->detalles as $det) {
                     $valorUnitario = round($det->precio_unitario / 1.18, 2);
                     $subtotalBase = round($valorUnitario * $det->cantidad, 2);
                     $igvItem = round(($det->precio_unitario - $valorUnitario) * $det->cantidad, 2);

                     \App\Models\ComprobanteDetalle::create([
                        'id_comprobante' => $nc->id,
                        'tipo_item'      => $det->tipo_item,
                        'descripcion'    => $det->nombre_item,
                        'codigo_unidad'  => ($det->tipo_item == 'servicio' ? 'ZZ' : 'NIU'),
                        'cantidad'       => $det->cantidad,
                        'precio_unitario'=> $det->precio_unitario,
                        'valor_unitario' => $valorUnitario,
                        'subtotal'       => $subtotalBase,
                        'igv_total'      => $igvItem,
                        'total'          => $det->subtotal
                    ]);
                }

                // 10. REGENERAR PDF DEL ORIGINAL (Banner Anulado)
                $cpeOriginal->estado_sunat = 'anulado';
                $cpeOriginal->save();

                $cpeOriginal->refresh();
                $venta->refresh();

                $pdfAnuladoContent = $this->generarPDFAnulado($cpeOriginal, $venta);
                Storage::put('comprobantes/pdf/' . $cpeOriginal->nombre_xml . '.pdf', $pdfAnuladoContent);

                // TODO OK: Confirmamos la transacción
                DB::commit();
                return ['success' => true, 'message' => $mensaje];

            } else {
                // SUNAT RECHAZÓ
                $mensaje = $result->getError()->getCode() . ' - ' . $result->getError()->getMessage();

                // DESHACEMOS LA TRANSACCIÓN
                DB::rollBack();
                return ['success' => false, 'message' => $mensaje];
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al generar Nota de Crédito', [
                'venta_id' => $venta->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
        }
    }

    // ==========================================
    // UTILITARIO: CONVERTIR NÚMERO A LETRAS (PHP PURO)
    // ==========================================
    private function numeroALetras($monto)
    {
        $monto = floatval($monto);
        $entero = floor($monto);
        $centavos = round(($monto - $entero) * 100);

        $texto = $this->convertirEntero($entero);

        // Ajuste final para monedas
        $texto = trim($texto);
        if ($texto == 'UNO') $texto = 'UN'; // Caso "UN SOL"

        return 'SON: ' . $texto . ' CON ' . str_pad($centavos, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
    }

    private function convertirEntero($n)
    {
        $output = '';

        if ($n == 0) return 'CERO';

        if ($n >= 1000000) {
            $n_millon = floor($n / 1000000);
            $n = $n % 1000000;
            if ($n_millon == 1) $output .= 'UN MILLON ';
            else $output .= $this->convertirEntero($n_millon) . ' MILLONES ';
        }

        if ($n >= 1000) {
            $n_miles = floor($n / 1000);
            $n = $n % 1000;
            if ($n_miles == 1) $output .= 'MIL ';
            else $output .= $this->convertirEntero($n_miles) . ' MIL ';
        }

        if ($n >= 100) {
            $n_centenas = floor($n / 100);
            $n = $n % 100;
            switch ($n_centenas) {
                case 1: $output .= ($n == 0) ? 'CIEN ' : 'CIENTO '; break;
                case 2: $output .= 'DOSCIENTOS '; break;
                case 3: $output .= 'TRESCIENTOS '; break;
                case 4: $output .= 'CUATROCIENTOS '; break;
                case 5: $output .= 'QUINIENTOS '; break;
                case 6: $output .= 'SEISCIENTOS '; break;
                case 7: $output .= 'SETECIENTOS '; break;
                case 8: $output .= 'OCHOCIENTOS '; break;
                case 9: $output .= 'NOVECIENTOS '; break;
            }
        }

        if ($n >= 10) {
            if ($n <= 15) {
                switch ($n) {
                    case 10: $output .= 'DIEZ '; break;
                    case 11: $output .= 'ONCE '; break;
                    case 12: $output .= 'DOCE '; break;
                    case 13: $output .= 'TRECE '; break;
                    case 14: $output .= 'CATORCE '; break;
                    case 15: $output .= 'QUINCE '; break;
                }
                $n = 0;
            } else if ($n < 20) {
                $output .= 'DIECI';
                $n -= 10;
            } else if ($n == 20) {
                $output .= 'VEINTE ';
                $n = 0;
            } else if ($n < 30) {
                $output .= 'VEINTI';
                $n -= 20;
            } else {
                $n_decenas = floor($n / 10);
                $n = $n % 10;
                switch ($n_decenas) {
                    case 3: $output .= 'TREINTA '; break;
                    case 4: $output .= 'CUARENTA '; break;
                    case 5: $output .= 'CINCUENTA '; break;
                    case 6: $output .= 'SESENTA '; break;
                    case 7: $output .= 'SETENTA '; break;
                    case 8: $output .= 'OCHENTA '; break;
                    case 9: $output .= 'NOVENTA '; break;
                }
                if ($n > 0) $output .= 'Y ';
            }
        }

        if ($n > 0) {
            switch ($n) {
                case 1: $output .= 'UNO '; break;
                case 2: $output .= 'DOS '; break;
                case 3: $output .= 'TRES '; break;
                case 4: $output .= 'CUATRO '; break;
                case 5: $output .= 'CINCO '; break;
                case 6: $output .= 'SEIS '; break;
                case 7: $output .= 'SIETE '; break;
                case 8: $output .= 'OCHO '; break;
                case 9: $output .= 'NUEVE '; break;
            }
        }

        return $output;
    }

    // ==========================================
    // GENERAR PDF DEL COMPROBANTE
    // ==========================================
    private function generarPDF($invoice, $company, $client, $venta, $hash)
    {
        // Crear objeto temporal para la vista (simulando el modelo Comprobante)
        $cpe = (object) [
            'id_tipo_comprobante' => ($invoice->getTipoDoc() == '01' ? 1 : 2),
            'serie' => $invoice->getSerie(),
            'correlativo' => $invoice->getCorrelativo(),
            'fecha_emision' => $invoice->getFechaEmision(),
            'moneda' => 'PEN',
            'receptor_razon_social' => $client->getRznSocial(),
            'receptor_numero_doc' => $client->getNumDoc(),
            'receptor_direccion' => $venta->cliente->direccion ?? '-',
            'op_gravadas' => $invoice->getMtoOperGravadas(),
            'monto_igv' => $invoice->getMtoIGV(),
            'total' => $invoice->getMtoImpVenta(),
            'leyenda_sunat' => $this->numeroALetras($invoice->getMtoImpVenta()),
            'hash_cpe' => $hash,
            'estado_sunat' => 'aceptado'
        ];

        $qr = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(100)->generate(
            $this->negocio->ruc . '|' . $invoice->getTipoDoc() . '|' . $invoice->getSerie() . '|' .
            $invoice->getCorrelativo() . '|0|' . $invoice->getMtoImpVenta() . '|' .
            $invoice->getFechaEmision()->format('Y-m-d') . '|' . $client->getTipoDoc() . '|' .
            $client->getNumDoc() . '|' . $hash
        ));

        $html = view('admin.pdf.ticket-cpe', [
            'cpe' => $cpe,
            'venta' => $venta,
            'negocio' => $this->negocio,
            'qr' => $qr
        ])->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper([0, 0, 226.77, 841.89], 'portrait');
        return $pdf->output();
    }

    // ==========================================
    // GENERAR PDF DE NOTA DE CRÉDITO
    // ==========================================
    private function generarPDFNotaCredito($note, $company, $client, $venta, $cpeOriginal, $hash)
    {
        $cpe = (object) [
            'id_tipo_comprobante' => 3,
            'serie' => $note->getSerie(),
            'correlativo' => $note->getCorrelativo(),
            'fecha_emision' => $note->getFechaEmision(),
            'moneda' => 'PEN',
            'receptor_razon_social' => $client->getRznSocial(),
            'receptor_numero_doc' => $client->getNumDoc(),
            'receptor_direccion' => $venta->cliente->direccion ?? '-',
            'op_gravadas' => $note->getMtoOperGravadas(),
            'monto_igv' => $note->getMtoIGV(),
            'total' => $note->getMtoImpVenta(),
            'leyenda_sunat' => $this->numeroALetras($note->getMtoImpVenta()),
            'hash_cpe' => $hash,
            'estado_sunat' => 'aceptado',
            'cod_motivo_nc' => $note->getCodMotivo(),
            'descripcion_motivo_nc' => $note->getDesMotivo(),
            'comprobanteReferencia' => $cpeOriginal
        ];

        $qr = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(100)->generate(
            $this->negocio->ruc . '|07|' . $note->getSerie() . '|' .
            $note->getCorrelativo() . '|0|' . $note->getMtoImpVenta() . '|' .
            $note->getFechaEmision()->format('Y-m-d') . '|' . $client->getTipoDoc() . '|' .
            $client->getNumDoc() . '|' . $hash
        ));

        $html = view('admin.pdf.ticket-cpe', [
            'cpe' => $cpe,
            'venta' => $venta,
            'negocio' => $this->negocio,
            'qr' => $qr
        ])->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper([0, 0, 226.77, 841.89], 'portrait');
        return $pdf->output();
    }

    // ==========================================
    // GENERAR PDF DE COMPROBANTE ANULADO
    // ==========================================
    private function generarPDFAnulado($comprobante, $venta)
    {
        // Generar QR
        $qr = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(100)->generate(
            $this->negocio->ruc . '|' . ($comprobante->id_tipo_comprobante == 1 ? '01' : '03') . '|' .
            $comprobante->serie . '|' . $comprobante->correlativo . '|0|' . $comprobante->total . '|' .
            $comprobante->fecha_emision->format('Y-m-d') . '|' . $comprobante->receptor_tipo_doc . '|' .
            $comprobante->receptor_numero_doc . '|' . $comprobante->hash_cpe
        ));

        $html = view('admin.pdf.ticket-cpe', [
            'cpe' => $comprobante,
            'venta' => $venta,
            'negocio' => $this->negocio,
            'qr' => $qr
        ])->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper([0, 0, 226.77, 841.89], 'portrait');
        return $pdf->output();
    }
}
