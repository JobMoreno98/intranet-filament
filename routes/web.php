<?php

use App\Http\Controllers\RecursosController;
use App\Models\RecursosArchivos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__ . '/settings.php';


Route::get('/media/stream', function (Request $request) {

    $archivo = RecursosArchivos::findOrFail($request->archivo_id);

    $path = $archivo->assets_procesados['main'] ?? null;

    if (!$path) {
        abort(404, 'No hay versión procesada para este archivo');
    }

    return response('', 200)
        ->header('X-Accel-Redirect', '/protegido/' . $path)
        ->header('Content-Type', 'image/webp')
        ->header('Content-Disposition', 'inline')
        ->header('X-Content-Type-Options', 'nosniff');
})->name('media.stream')
    ->middleware(['auth', 'secure.media', 'throttle:media']);

Route::get('/admin/media/thumbnail', function (Request $request) {
    // Solo permitimos el acceso si es administrador autenticado
    if (!auth()->user()?->hasRole('admin')) { abort(403); }

    $archivo = RecursosArchivos::findOrFail($request->archivo_id);

    // Prioridad: 1. Miniatura (thumb), 2. Principal (main), 3. Original
    $path = $archivo->assets_procesados['thumb'] 
            ?? $archivo->assets_procesados['main'] 
            ?? $archivo->path_original;

    if (!$path) abort(404);

    // Entregamos vía Nginx para máxima velocidad
    return response('', 200)
        ->header('X-Accel-Redirect', '/protegido/' . $path)
        ->header('Content-Type', 'image/webp')
        ->header('X-Content-Type-Options', 'nosniff');
})->name('admin.media.thumbnail');

Route::get('/visor/{id}', [RecursosController::class, 'view'])->middleware('auth');
Route::get('/media/url/{id}', [RecursosController::class, 'signedUrl'])->middleware('auth');
