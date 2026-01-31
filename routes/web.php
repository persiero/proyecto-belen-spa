<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\ComprobanteController;
use App\Http\Controllers\Admin\ReporteCajaController;
use App\Livewire\Admin\Servicios\GestionServicios;
use App\Livewire\Admin\Estilistas\GestionEstilistas;
use App\Livewire\Admin\Clientes\GestionClientes;
use App\Livewire\Admin\Productos\GestionProductos;
use App\Livewire\Admin\Turnos\GestionTurnos;
use App\Livewire\Admin\Pos\GestionPos;
use App\Livewire\Admin\Caja\GestionCaja;
use App\Livewire\Admin\Compras\GestionCompras;
use App\Livewire\Admin\Proveedores\GestionProveedores;
use App\Livewire\Admin\Inventario\GestionInventario;
use App\Livewire\Admin\Reportes\ReporteComisiones;
use App\Livewire\Admin\Ventas\HistorialVentas;
use App\Livewire\Admin\Dashboard;
// Importaciones de Modelos
use App\Models\Venta;
use App\Models\ConfigNegocio;
use Barryvdh\DomPDF\Facade\Pdf;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rutas de Autenticación (Login, Registro, etc.)
Auth::routes([
    'register' => false, 
    'verify' => false,
    'reset' => true // Mantén true si quieres que puedan recuperar contraseña
]);

// Ruta pública (Inicio)
Route::get('/', function () {
    return redirect()->route('login'); // Redirige automáticamente al Login
});

// ==========================================
// GRUPO DE ADMINISTRACIÓN (Con prefijo de nombre 'admin.')
// ==========================================
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // 1. Dashboard
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // 2. Ventas (Controlador normal)
    // Nombres finales: admin.ventas.index, admin.ventas.create, etc.
 
    // 3. Servicios (Componente Livewire)
    Route::get('/servicios', GestionServicios::class)->name('servicios');
    Route::get('/estilistas', GestionEstilistas::class)->name('estilistas');
    Route::get('/clientes', GestionClientes::class)->name('clientes');
    Route::get('/productos', GestionProductos::class)->name('productos');
    Route::get('/turnos', GestionTurnos::class)->name('turnos');
    Route::get('/pos/{turno_id?}', GestionPos::class)->name('pos');
    Route::get('/caja', GestionCaja::class)->name('caja');
    Route::get('/compras', GestionCompras::class)->name('compras');
    Route::get('/proveedores', GestionProveedores::class)->name('proveedores');
    Route::get('/inventario', GestionInventario::class)->name('inventario');
    Route::get('/ventas/historial', HistorialVentas::class)->name('ventas.historial');
    Route::get('/configuracion', \App\Livewire\Admin\Configuracion\GestionConfiguracion::class)->name('configuracion');
    Route::get('/perfil', App\Livewire\Admin\Perfil\MiPerfil::class)->name('perfil');

    // Reportes
    Route::get('/reportes/comisiones', ReporteComisiones::class)->name('reportes.comisiones');

});

// ==========================================
// RUTAS DE IMPRESIÓN / UTILITARIOS (Fuera del grupo de nombres 'admin.')
// ==========================================
// La sacamos del grupo anterior para que el nombre sea exacto "print.ticket"
// y no "admin.print.ticket", pero mantenemos la protección 'auth'.

Route::middleware(['auth'])->group(function () {
    
    Route::get('/imprimir/ticket/{id}', function($id) {
        $venta = Venta::with(['cliente', 'detalles', 'pagos.metodoPago', 'turno.servicios.estilista'])->findOrFail($id);
        
        // Usamos first() porque solo hay una configuración de negocio
        $config = ConfigNegocio::first(); 
        
        // --- AQUÍ OCURRE LA MAGIA DEL CAMBIO A PDF ---
        
        // A. Cargamos la vista en memoria (igual que antes)
        $pdf = Pdf::loadView('admin.pdf.ticket', compact('venta', 'config'));

        // B. Configuramos el tamaño (80mm ancho x largo automático/fijo)
        // 226.77 puntos = 80mm aprox. 800 es el largo (ajustable si sale cortado)
        $pdf->setPaper([0, 0, 226.77, 800], 'portrait');

        // C. En lugar de "view", retornamos el "stream" (visor PDF)
        return $pdf->stream('Ticket-Venta-' . $id . '.pdf');
    })->name('print.ticket'); // <--- AHORA SÍ FUNCIONARÁ


    // TICKET SUNAT (Ponlo aquí para que el nombre coincida con el botón)
    Route::get('/comprobante/ticket/{id}', [ComprobanteController::class, 'imprimirTicket'])->name('comprobante.ticket');

    Route::get('/comprobante/xml/{comprobante}', [ComprobanteController::class, 'descargarXml'])->name('comprobante.xml');
    
    Route::get('/comprobante/cdr/{comprobante}', [ComprobanteController::class, 'descargarCdr'])->name('comprobante.cdr');

    // Ruta para el reporte (fuera de los grupos de Livewire si quieres, o dentro del grupo auth)
    Route::get('/admin/caja/reporte/{caja}', [ReporteCajaController::class, 'imprimir'])
        ->name('caja.reporte');

});
