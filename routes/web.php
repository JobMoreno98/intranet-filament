<?php

use App\Http\Controllers\ColeccionesConsultaController;
use App\Http\Controllers\RecursosController;
use App\Models\ColeccionesConsulta;
use App\Models\RecursosArchivos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;

Route::get('/', [ColeccionesConsultaController::class, 'index'])->name('home');


Route::get('/buscador', [ColeccionesConsultaController::class, 'buscador'])->name('buscador');


Route::get('/buscador/registro/{tabla}/{id}', [ColeccionesConsultaController    ::class, 'showRegistro'])->name('buscador.registro');


Route::get('/coleccion/{id}', [ColeccionesConsultaController::class, 'show'])->name('coleccion.show');


Route::get('/coleccion/{tabla}/{id}', [RecursosController::class, 'publico'])->name('coleccion.individual');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__ . '/settings.php';


Route::get('/media/stream', function (Request $request) {
    $archivo = RecursosArchivos::findOrFail($request->archivo_id);

    // Intentamos sacar la ruta del JSON de assets
    $path = $archivo->assets_procesados['main'] ?? null;

    if (!$path) {
        abort(404, 'No hay versión procesada para este archivo');
    }

    // Identificar la extensión real para el Content-Type
    $extension = pathinfo($path, PATHINFO_EXTENSION);
    $mimeType = ($extension === 'webp') ? 'image/webp' : 'image/jpeg';

    return response('', 200)
        ->header('X-Accel-Redirect', '/protegido/' . $path)
        ->header('Content-Length', Storage::disk('private')->size($path))
        ->header('Content-Type', $mimeType) // Dinámico según el archivo
        ->header('Content-Disposition', 'inline') // Asegura que se vea en el navegador
        ->header('Cache-Control', 'private, max-age=86400') // Permite caché local para no saturar el servidor
        ->header('X-Content-Type-Options', 'nosniff');
})->name('media.stream')
    ->middleware(['secure.media', 'throttle:media']);



Route::get('/admin/media/load', function (Request $request) {
    // Verificación de Admin
    if (!auth()->guard('admin')->check() && !auth()->user() instanceof \App\Models\Admin) {
        abort(403, 'Acceso exclusivo para administradores.');
    }

    $archivo = RecursosArchivos::findOrFail($request->archivo_id);

    $version = $request->query('version', 'thumb');

    $path = $archivo->assets_procesados[$version]
        ?? $archivo->assets_procesados['main']
        ?? $archivo->path_original;

    if (!$path) abort(404);

    $mime = str_ends_with($path, '.webp') ? 'image/webp' : 'image/jpeg';
    if (str_ends_with($path, '.pdf')) $mime = 'application/pdf';

    return response('', 200)
        ->header('X-Accel-Redirect', '/protegido/' . $path)
        ->header('Content-Type', $mime)
        ->header('X-Content-Type-Options', 'nosniff');
})->name('admin.media.load')->middleware(Filament::getPanel('admin')->getAuthMiddleware());


Route::get('/visor/{id}', [RecursosController::class, 'view'])->middleware('auth');
Route::get('/media/url/{id}', [RecursosController::class, 'signedUrl'])->middleware('auth');

// Visor Publico 
Route::get('/viewer/visor/{recurso}', [RecursosController::class, 'publico'])
    ->middleware('signed')
    ->name('visor.publico');
