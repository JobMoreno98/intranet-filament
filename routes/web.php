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

    return response()->noContent()
        ->header('X-Accel-Redirect', '/protegido/' . $path)
        ->header('Content-Type', 'image/webp')
        ->header('Content-Disposition', 'inline')
        ->header('X-Content-Type-Options', 'nosniff');
})->name('media.stream')
    ->middleware(['auth', 'secure.media', 'throttle:media']);

Route::get('/visor/{id}', [RecursosController::class, 'view'])->middleware('auth');
Route::get('/media/url/{id}', [RecursosController::class, 'signedUrl'])->middleware('auth');
