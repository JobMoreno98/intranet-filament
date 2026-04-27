<?php

use App\Http\Controllers\RecursosController;
use App\Models\RecursosArchivos;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__ . '/settings.php';


Route::get('/media/stream/{archivo_id}', function ($archivo_id) {
    // 1. Validar la firma
    if (!request()->hasValidSignature()) {
        abort(403, 'Firma inválida o expirada');
    }

    $archivo = RecursosArchivos::findOrFail($archivo_id);

    // Obtenemos el path del JSON (ej: archivo-general/1/1/main.webp)
    $path = $archivo->assets_procesados['main'] ?? null;

    if (!$path) {
        abort(404, 'No hay versión procesada para este archivo');
    }

    // 2. Construir la ruta para Nginx
    // IMPORTANTE: Nginx espera una ruta relativa al bloque 'location'
    $nginxPath = '/protegido/' . $path;

    // 3. Responder a Nginx
    // Usamos binary file response o noContent, pero X-Accel-Redirect es la clave
    return response()->noContent()
        ->header('X-Accel-Redirect', $nginxPath)
        ->header('Content-Type', 'image/webp')
        // 'inline' le dice al navegador que la muestre, NO que la descargue
        ->header('Content-Disposition', 'inline')
        // Seguridad extra para evitar que olfateen el tipo de archivo
        ->header('X-Content-Type-Options', 'nosniff');
        
})->name('media.stream')->middleware('auth');

Route::get('/visor/{id}', [RecursosController::class, 'view'])->middleware('auth');
Route::get('/media/url/{id}', [RecursosController::class, 'signedUrl'])->middleware('auth');
