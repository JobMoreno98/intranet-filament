<?php

use App\Http\Controllers\RecursosController;
use App\Models\RecursosArchivos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Filament\Facades\Filament;

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

Route::get('/admin/media/load', function (Request $request) {
    // Verificación de Admin
    if (!auth()->guard('admin')->check() && !auth()->user() instanceof \App\Models\Admin) {
        abort(403, 'Acceso exclusivo para administradores.');
    }

    $archivo = RecursosArchivos::findOrFail($request->archivo_id);

    // Obtenemos la versión solicitada (por defecto 'thumb')
    $version = $request->query('version', 'thumb');

    // Buscamos la versión específica, si no existe, caemos en cascada
    $path = $archivo->assets_procesados[$version]
        ?? $archivo->assets_procesados['main']
        ?? $archivo->path_original;

    if (!$path) abort(404);

    // Mime type dinámico (opcional, pero útil si cargamos PDFs u originales)
    $mime = str_ends_with($path, '.webp') ? 'image/webp' : 'image/jpeg';
    if (str_ends_with($path, '.pdf')) $mime = 'application/pdf';

    return response('', 200)
        ->header('X-Accel-Redirect', '/protegido/' . $path)
        ->header('Content-Type', $mime)
        ->header('X-Content-Type-Options', 'nosniff');
})->name('admin.media.load')->middleware(Filament::getPanel('admin')->getAuthMiddleware());

Route::get('/visor/{id}', [RecursosController::class, 'view'])->middleware('auth');
Route::get('/media/url/{id}', [RecursosController::class, 'signedUrl'])->middleware('auth');
