<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\ConfigTributaria;
use App\Models\ConfigNegocio;
use App\Models\Comprobante; // <--- ERROR 1 CORREGIDO: Importación agregada
use App\Models\TipoComprobante;
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
        
        // --- CAMBIO DE RUTA AQUÍ ---
        // Intentamos buscar primero en la ruta segura (private)
        if (Storage::exists('certificados/' . $this->config->certificado_path)) {
            $path = Storage::path('certificados/' . $this->config->certificado_path);
        } else {
            // Si no está ahí, buscamos en la ruta antigua manual
            $path = storage_path('app/certificados/' . $this->config->certificado_path);
        }
        
        if($this->config->certificado_path && file_exists($path)) {
            $this->see->setCertificate(file_get_contents($path));
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
        
        $ultimoCorrelativo = Comprobante::where('serie', $serie)->max('correlativo');
        $correlativo = $ultimoCorrelativo ? $ultimoCorrelativo + 1 : 1;

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
            ->setValue('SON: ' . $venta->total . ' SOLES'); 
        $invoice->setLegends([$legend]);

        // 6. ENVIAR A SUNAT
        try {
            $result = $this->see->send($invoice);
            
            $nombreArchivo = $invoice->getName();
            Storage::put('comprobantes/xml/' . $nombreArchivo . '.xml', $this->see->getFactory()->getLastXml());

            $estado = 'rechazado';
            $mensaje = '';
            $cdr = null;
            $hash = null;
            $nombreCdr = null; // <--- NUEVO: Variable para el nombre del ZIP

            if ($result->isSuccess()) {
                /** @var \Greenter\Model\Response\BillResult $result */
                $estado = 'aceptado';
                $cdr = $result->getCdrResponse();
                $mensaje = $cdr->getDescription();
                $hash = $cdr->getId();

                // <--- NUEVO: Definimos el nombre y guardamos
                $nombreCdr = 'R-' . $nombreArchivo . '.zip';
                Storage::put('comprobantes/cdr/R-' . $nombreArchivo . '.zip', $result->getCdrZip());
            } else {
                $estado = 'rechazado';
                $mensaje = $result->getError()->getCode() . ' - ' . $result->getError()->getMessage();
            }

            // 7. REGISTRAR EN BD (AHORA SÍ GUARDAMOS LOS MONTOS)
            Comprobante::create([
                'id_venta' => $venta->id,
                'id_tipo_comprobante' => ($tipoComprobante == '01' ? 1 : 2),
                'id_serie_comprobante' => ($serie == 'F001' ? 1 : 2),
                'serie' => $serie,
                'correlativo' => $correlativo,
                'fecha_emision' => Carbon::now(),

                // --- AGREGAR ESTO (Tus datos del receptor) ---
                'receptor_tipo_doc' => $tipoDocCliente,
                'receptor_numero_doc' => $numDocCliente,
                'receptor_razon_social' => $razonSocialCliente,
                'receptor_direccion' => $direccionCliente, // <--- NUEVO: Guardamos la dirección
                // ---------------------------------------------
                
                // DATOS TRIBUTARIOS NUEVOS
                'op_gravadas' => $opGravadas,  // <--- Guardamos en BD
                'monto_igv' => $mtoIgv,          // <--- Guardamos en BD
                'total' => $total,

                // DATOS SUNAT
                'nombre_xml' => $nombreArchivo,
                'cdr_xml' => $nombreCdr, // <--- NUEVO: Guardamos el nombre del archivo ZIP
                'hash_cpe' => $hash,
                'estado_sunat' => $estado,
                'mensaje_sunat' => $mensaje,
                'enviado_sunat' => true
            ]);

            return ['success' => $result->isSuccess(), 'message' => $mensaje];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
        }
    }

    public function generarNotaCredito(Venta $venta, $motivo = "Anulación de la operación")
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

            // --- NUEVO: BUSCAMOS EL ID DE LA SERIE EN LA BASE DE DATOS ---
            $serieObj = \App\Models\SerieComprobante::where('serie', $serieNota)->first();
            $idSerieNota = $serieObj ? $serieObj->id : 3; // Si no encuentra, usa 3 (BC01) por defecto
            // -------------------------------------------------------------

            // Correlativo
            $ultimoCorrelativo = Comprobante::where('serie', $serieNota)->max('correlativo');
            $correlativoNota = $ultimoCorrelativo ? $ultimoCorrelativo + 1 : 1;

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
            $legend->setCode('1000')->setValue('SON: ' . $total . ' SOLES');
            $note->setLegends([$legend]);

            // 7. ENVIAR A SUNAT
            $result = $this->see->send($note);

            // Guardar XML
            $nombreArchivo = $note->getName();
            Storage::put('comprobantes/xml/' . $nombreArchivo . '.xml', $this->see->getFactory()->getLastXml());

            $estado = 'rechazado';
            $mensaje = '';
            $cdr = null;
            $nombreCdr = null; // <--- NUEVO

            if ($result->isSuccess()) {

                /** @var \Greenter\Model\Response\BillResult $result */

                $estado = 'aceptado';
                $cdr = $result->getCdrResponse();
                $mensaje = $cdr->getDescription();
                $hash = $cdr->getId();
                // <--- NUEVO: Guardar CDR
                $nombreCdr = 'R-' . $nombreArchivo . '.zip';
                Storage::put('comprobantes/cdr/R-' . $nombreArchivo . '.zip', $result->getCdrZip());
            } else {
                $mensaje = $result->getError()->getCode() . ' - ' . $result->getError()->getMessage();
            }

            // 8. GUARDAR EN BD (Creamos un NUEVO comprobante tipo NC)
            // Necesitamos saber el ID del tipo 'Nota de Crédito' (que insertamos en SQL)
            // Asumimos que es el ID 3 o lo buscamos dinámicamente
            $tipoNcId = TipoComprobante::where('codigo_sunat', '07')->first()->id;

            Comprobante::create([
                'id_venta' => $venta->id, // Lo vinculamos a la misma venta
                'id_tipo_comprobante' => $tipoNcId,
                'id_serie_comprobante' => $idSerieNota, //Faltaba esto
                'serie' => $serieNota,
                'correlativo' => $correlativoNota,
                'fecha_emision' => Carbon::now(),

                'op_gravadas' => $cpeOriginal->op_gravadas, // Usamos datos del original
                'monto_igv' => $cpeOriginal->monto_igv,     // Usamos datos del original
                'total' => $cpeOriginal->total,             // Usamos datos del original
                
                'nombre_xml' => $nombreArchivo ?? 'NC_PENDIENTE',
                'cdr_xml' => $nombreCdr,
                'hash_cpe' => $hash ?? null,
                'estado_sunat' => $estado ?? 'rechazado',
                'mensaje_sunat' => $mensaje ?? 'Error',
                'enviado_sunat' => true,
                
                // IMPORTANTE: Guardamos datos del receptor nuevamente
                'receptor_tipo_doc' => $client->getTipoDoc(),
                'receptor_numero_doc' => $client->getNumDoc(),
                'receptor_razon_social' => $client->getRznSocial(),
                'receptor_direccion' => $direccionCliente, // <--- NUEVO: Guardar Dirección
            ]);

            return ['success' => $result->isSuccess(), 'message' => $mensaje];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}