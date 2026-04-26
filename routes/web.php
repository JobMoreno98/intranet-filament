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
    // 1. Validar la firma (Seguridad)
    //dd($archivo_id);
    if (!request()->hasValidSignature()) {
        abort(403);
    }

    $archivo = RecursosArchivos::findOrFail($archivo_id);
    $path = $archivo->assets_procesados['main']; // ej: "coleccion/1/1/main.webp"

    // 2. Construir la ruta que Nginx entiende
    // Debe coincidir con el nombre del 'location' definido en Nginx
    $nginxPath = '/protegido/' . $path;

    // 3. Responder a Nginx
    return response()->noContent()
        ->header('X-Accel-Redirect', $nginxPath)
        ->header('Content-Type', 'image/webp'); // O detectar el mime dinámicamente
})->name('media.stream')->middleware('auth');


Route::get('/ver-contenido/{recurso}', [RecursosController::class, 'view'])->name('view.data')->middleware('auth');
