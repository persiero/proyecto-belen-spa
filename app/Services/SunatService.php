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
use Illuminate\Support\Facades\Storage;
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

        $this->see->setClaveSOL($ruc, $usuarioSolo, $this->config->clave_sol);
    }

    public function generarComprobante(Venta $venta)
    {
        try {
            \Log::info('Iniciando generación de comprobante', [
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
        $direccionCliente = '-'; // <--- NUEVO: Variable para dirección

        if ($venta->cliente) {
            $razonSocialCliente = $venta->cliente->nombre . ' ' . $venta->cliente->apellido;
            $numDocCliente = $venta->cliente->numero_documento;
            $direccionCliente = $venta->cliente->direccion ?? '-'; // <--- NUEVO: Obtenemos dirección
            
            if (strlen($numDocCliente) == 11) {
                $tipoDocCliente = '6'; // RUC
            } elseif (strlen($numDocCliente) == 8) {
                $tipoDocCliente = '1'; // DNI
            }
        }

        $client->setTipoDoc($tipoDocCliente)
            ->setNumDoc($numDocCliente)
            ->setRznSocial($razonSocialCliente);

        // 3. DETERMINAR TIPO DE COMPROBANTE Y SERIE
        $tipoComprobante = ($tipoDocCliente == '6') ? '01' : '03';
        $serie = ($tipoComprobante == '01') ? 'F001' : 'B001';
        
        // Obtener el objeto SerieComprobante
        $serieObj = SerieComprobante::where('serie', $serie)->where('activo', true)->firstOrFail();
        
        // Obtener y actualizar el correlativo de forma segura
        $correlativo = $serieObj->obtenerSiguienteCorrelativo();

        // ============================================================
        // 4A. CÁLCULOS MATEMÁTICOS (CRÍTICO PARA BD)
        // ============================================================
        // Calculamos aquí para usar las mismas variables en el XML y en la BD
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
            ->setMtoOperGravadas($opGravadas) // <--- Usamos variable
            ->setMtoIGV($mtoIgv)              // <--- Usamos variable
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
        try {
            \Log::info('Enviando comprobante a SUNAT', [
                'serie' => $invoice->getSerie(),
                'correlativo' => $invoice->getCorrelativo(),
                'cliente_doc' => $client->getNumDoc()
            ]);
            
            $result = $this->see->send($invoice);
            
            $nombreArchivo = $invoice->getName();
            Storage::put('comprobantes/xml/' . $nombreArchivo . '.xml', $this->see->getFactory()->getLastXml());

            $estado = 'rechazado';
            $mensaje = '';
            $cdr = null;
            $hash = null;
            $nombreCdr = null;
            $nombrePdf = null;

            if ($result->isSuccess()) {
                /** @var \Greenter\Model\Response\BillResult $result */
                $estado = 'aceptado';
                $cdr = $result->getCdrResponse();
                $mensaje = $cdr->getDescription();
                $hash = $cdr->getId();

                $nombreCdr = 'R-' . $nombreArchivo . '.zip';
                Storage::put('comprobantes/cdr/' . $nombreCdr, $result->getCdrZip());

                // <--- NUEVO: GENERAR Y GUARDAR PDF ---
                $nombrePdf = $nombreArchivo . '.pdf';
                $pdfContent = $this->generarPDF($invoice, $company, $client, $venta, $hash);
                Storage::put('comprobantes/pdf/' . $nombrePdf, $pdfContent);
                // -------------------------------------
            } else {
                $estado = 'rechazado';
                $mensaje = $result->getError()->getCode() . ' - ' . $result->getError()->getMessage();
            }

            // 7. REGISTRAR EN BD (AHORA SÍ GUARDAMOS LOS MONTOS)
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
                'ruta_pdf' => $nombrePdf, // <--- NUEVO: Guardar ruta PDF
                'hash_cpe' => $hash,
                'estado_sunat' => $estado,
                'mensaje_sunat' => $mensaje,
                'enviado_sunat' => true
            ]);

            // 8. ¡AQUÍ ESTÁ EL FIX! LLENAR DETALLES (SNAPSHOT)
            // Recorremos los items de la VENTA y los guardamos en COMPROBANTES_DETALLE
            foreach ($venta->detalles as $det) {
                // Recálculos auxiliares
                $valorUnitario = round($det->precio_unitario / 1.18, 2);
                $subtotalBase = round($valorUnitario * $det->cantidad, 2);
                $igvItem = round(($det->precio_unitario - $valorUnitario) * $det->cantidad, 2);
                $totalItem = $det->subtotal; // Precio * Cantidad

                ComprobanteDetalle::create([
                    'id_comprobante' => $comprobante->id,
                    'tipo_item'      => $det->tipo_item, // servicio/producto
                    'descripcion'    => $det->nombre_item,
                    'codigo_unidad'  => ($det->tipo_item == 'servicio' ? 'ZZ' : 'NIU'),
                    'cantidad'       => $det->cantidad,
                    'precio_unitario'=> $det->precio_unitario, // Con IGV
                    'valor_unitario' => $valorUnitario,        // Sin IGV
                    'subtotal'       => $subtotalBase,
                    'igv_total'      => $igvItem,
                    'total'          => $totalItem
                ]);
            }

            return ['success' => $result->isSuccess(), 'message' => $mensaje];

        } catch (\Exception $e) {
            \Log::error('Error al generar comprobante', [
                'venta_id' => $venta->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
        }
        } catch (\Exception $e) {
            \Log::error('Error general en generarComprobante', [
                'venta_id' => $venta->id,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    {
        try {
            // 1. OBTENER EL COMPROBANTE ORIGINAL QUE VAMOS A ANULAR
            $cpeOriginal = $venta->comprobante; // Relación hasOne en Venta
            
            if (!$cpeOriginal) {
                return ['success' => false, 'message' => 'No existe comprobante para esta venta.'];
            }

            // 2. CONFIGURAR EMPRESA (Igual que antes)
            $company = new Company();
            $company->setRuc($this->negocio->ruc)
                ->setRazonSocial($this->negocio->nombre_comercial)
                ->setAddress((new Address())
                    ->setUbigueo('130101')
                    ->setDepartamento('LA LIBERTAD')
                    ->setProvincia('TRUJILLO')
                    ->setDistrito('TRUJILLO')
                    ->setDireccion($this->negocio->direccion));

            // 3. CONFIGURAR CLIENTE (Igual que el original)
            $client = new Client();
            // A. Intentamos leer del historial guardado (Idealmente)
            $tipoDoc = $cpeOriginal->receptor_tipo_doc;
            $numDoc = $cpeOriginal->receptor_numero_doc;
            $rznSocial = $cpeOriginal->receptor_razon_social;
            // <--- NUEVO: Recuperamos la dirección del comprobante original
            $direccionCliente = $cpeOriginal->receptor_direccion ?? '-';

            // B. Si está vacío (Venta antigua o error de guardado), recalculamos desde la Venta
            if (empty($numDoc)) {
                if ($venta->cliente) {
                    $numDoc = $venta->cliente->numero_documento;
                    $rznSocial = $venta->cliente->nombre . ' ' . $venta->cliente->apellido;
                    $direccionCliente = $venta->cliente->direccion ?? '-'; // <--- NUEVO
                    
                    // Lógica simple para detectar tipo
                    if (strlen($numDoc) == 11) {
                        $tipoDoc = '6'; // RUC
                    } elseif (strlen($numDoc) == 8) {
                        $tipoDoc = '1'; // DNI
                    } else {
                        $tipoDoc = '-'; // Otro
                    }
                } else {
                    // Si no tiene cliente asignado (Público General)
                    $numDoc = '00000000';
                    $rznSocial = 'CLIENTES VARIOS';
                    $tipoDoc = '0'; // Sin Documento
                }
            }

            $client->setTipoDoc($tipoDoc)
                ->setNumDoc($numDoc)
                ->setRznSocial($rznSocial);

            // 4. DETERMINAR SERIE DE LA NOTA DE CRÉDITO
            // Si anulamos F001 -> Usamos FC01. Si anulamos B001 -> Usamos BC01
            $esFactura = ($cpeOriginal->id_tipo_comprobante == 1); 
            $serieNota = $esFactura ? 'FC01' : 'BC01';

            // Obtener el objeto SerieComprobante para la Nota de Crédito
            $serieObj = SerieComprobante::where('serie', $serieNota)->where('activo', true)->firstOrFail();
            $idSerieNota = $serieObj->id;

            // Obtener y actualizar el correlativo de forma segura
            $correlativoNota = $serieObj->obtenerSiguienteCorrelativo();

            // 5. CREAR LA NOTA (OBJETO NOTE)
            /** @var Note $note */ 
            $note = new Note();

            // Configuración básica (Línea por línea)
            $note->setUblVersion('2.1')
                ->setTipoDoc('07') // Código SUNAT para Nota de Crédito
                ->setSerie($serieNota)
                ->setCorrelativo($correlativoNota)
                ->setFechaEmision(new \DateTime())
                ->setTipoMoneda('PEN')
                ->setCompany($company)
                ->setClient($client);

            // Documento Afectado (Aquí es donde marcaba el error)
            // Al estar separado, el editor ya sabe que $note es una Nota
            $note->setTipDocAfectado($esFactura ? '01' : '03') // Qué estamos anulando?
                ->setNumDocfectado($cpeOriginal->serie . '-' . $cpeOriginal->correlativo) // Ej: B001-25
                ->setCodMotivo('01') // 01 = Anulación de la operación
                ->setDesMotivo($motivo);

            // Configuración de Moneda y Entidades
            

            // Montos (Deben ser los mismos del original para anularlo totalmente)
            $opGravadas = $cpeOriginal->op_gravadas;
            $mtoIgv = $cpeOriginal->monto_igv;
            $total = $cpeOriginal->total;

            $note->setMtoOperGravadas($opGravadas)
                ->setMtoIGV($mtoIgv)
                ->setTotalImpuestos($mtoIgv)
                ->setValorVenta($opGravadas)
                ->setSubTotal($total)
                ->setMtoImpVenta($total);

            // 6. ÍTEMS (Replicamos los detalles originales)
            $items = [];
            foreach ($venta->detalles as $det) {
                $item = new SaleDetail();
                // OJO: Usamos los valores guardados en ComprobanteDetalle, no de VentaDetalle actual
                // Asumimos que guardaste los detalles en el modelo ComprobanteDetalle.
                // Si no, usamos los de la venta, pero idealmente es del histórico.
                // Por simplicidad usaremos los de la venta actual (que son los mismos)
                
                // Recálculo rápido inverso
                $valorUnitario = round($det->precio_unitario / 1.18, 2);
                $impuestoItem = round(($det->precio_unitario - $valorUnitario) * $det->cantidad, 2);
                $valorVentaItem = round($valorUnitario * $det->cantidad, 2);

                $item->setCodProducto('ITEM')
                    ->setUnidad('NIU')
                    ->setCantidad($det->cantidad)
                    ->setDescripcion($det->descripcion ?? 'Item de venta') // Ajusta según tu modelo detalle
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

            // Validación de seguridad: Si no hay ítems, fallamos antes de enviar
            if (count($items) === 0) {
                 return ['success' => false, 'message' => 'Error: La venta no tiene ítems para anular.'];
            }

            $note->setDetails($items);

            $legend = new Legend();
            $legend->setCode('1000')
                ->setValue($this->numeroALetras($cpeOriginal->total)); // <--- CAMBIO AQUÍ;
            $note->setLegends([$legend]);

            // 7. ENVIAR A SUNAT
            $result = $this->see->send($note);

            // Guardar XML
            $nombreArchivo = $note->getName();
            Storage::put('comprobantes/xml/' . $nombreArchivo . '.xml', $this->see->getFactory()->getLastXml());

            $estado = 'rechazado';
            $mensaje = '';
            $cdr = null;
            $nombreCdr = null;
            $nombrePdf = null;

            if ($result->isSuccess()) {

                /** @var \Greenter\Model\Response\BillResult $result */

                $estado = 'aceptado';
                $cdr = $result->getCdrResponse();
                $mensaje = $cdr->getDescription();
                $hash = $cdr->getId();
                
                $nombreCdr = 'R-' . $nombreArchivo . '.zip';
                Storage::put('comprobantes/cdr/' . $nombreCdr, $result->getCdrZip());

                // <--- NUEVO: GENERAR Y GUARDAR PDF DE NC ---
                $nombrePdf = $nombreArchivo . '.pdf';
                $pdfContent = $this->generarPDFNotaCredito($note, $company, $client, $venta, $cpeOriginal, $hash);
                Storage::put('comprobantes/pdf/' . $nombrePdf, $pdfContent);
                // -------------------------------------------
            } else {
                $mensaje = $result->getError()->getCode() . ' - ' . $result->getError()->getMessage();
            }

            // 8. GUARDAR EN BD (Creamos un NUEVO comprobante tipo NC)
            // Necesitamos saber el ID del tipo 'Nota de Crédito' (que insertamos en SQL)
            // Asumimos que es el ID 3 o lo buscamos dinámicamente
            $tipoNcId = TipoComprobante::where('codigo_sunat', '07')->first()->id;

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

                'nombre_xml' => $nombreArchivo ?? 'NC_PENDIENTE',
                'cdr_xml' => $nombreCdr,
                'ruta_pdf' => $nombrePdf, // <--- NUEVO: Guardar ruta PDF
                'hash_cpe' => $hash ?? null,
                'estado_sunat' => $estado ?? 'rechazado',
                'mensaje_sunat' => $mensaje ?? 'Error',
                'enviado_sunat' => true,               
                
            ]);

            // 9. GUARDAR LOS DETALLES DE LA NOTA DE CRÉDITO
            // (Deben ser espejo del original)
            // OJO: Si tienes ComprobanteDetalle lleno, úsalo. Si no, usa la venta.
            // Como recién vamos a llenar la tabla, usaremos la venta por ahora.
            foreach ($venta->detalles as $det) {
                 // ... (mismos cálculos que arriba) ...
                 $valorUnitario = round($det->precio_unitario / 1.18, 2);
                 $subtotalBase = round($valorUnitario * $det->cantidad, 2); // Base imponible
                 $igvItem = round(($det->precio_unitario - $valorUnitario) * $det->cantidad, 2); // Monto IGV
                 $totalItem = $det->subtotal; // Precio Venta Total del item

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

            // 10. REGENERAR PDF DEL COMPROBANTE ORIGINAL CON BANNER DE ANULADO
            if ($result->isSuccess()) {
                // Actualizar estado del comprobante original
                $cpeOriginal->estado_sunat = 'anulado';
                $cpeOriginal->save();

                // Regenerar PDF con banner de anulado
                $cpeOriginal->refresh(); // Refrescar para obtener el estado actualizado
                $venta->refresh(); // Refrescar venta también
                
                $pdfAnuladoContent = $this->generarPDFAnulado($cpeOriginal, $venta);
                Storage::put('comprobantes/pdf/' . $cpeOriginal->nombre_xml . '.pdf', $pdfAnuladoContent);
            }

            return ['success' => $result->isSuccess(), 'message' => $mensaje];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
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