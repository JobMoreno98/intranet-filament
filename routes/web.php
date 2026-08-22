<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ChunkUploadController;
use App\Http\Controllers\ColeccionesConsultaController;
use App\Http\Controllers\RecursosController;
use App\Models\ColeccionesConsulta;
use App\Models\RecursosArchivos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Storage;

Route::get('/', [ColeccionesConsultaController::class, 'index'])->name('home');


Route::get('/buscador', [ColeccionesConsultaController::class, 'buscador'])->name('buscador');


Route::get('/buscador/recurso/{tipo}/{id}', [ColeccionesConsultaController::class, 'showRegistro'])->name('buscador.registro');


Route::get('/coleccion/{coleccion}', [ColeccionesConsultaController::class, 'show'])->name('coleccion.show');


Route::get('/coleccion/{tabla}/{id}', [RecursosController::class, 'publico'])->name('coleccion.individual');



Route::get('/blog', function () {
    $tagsDisponibles = \App\Models\Blog::pluck('tags')
        ->flatten()
        ->unique()
        ->values();
        
    return view('blogs.index', [
        'tagsDisponibles' => $tagsDisponibles,
        'recientes'       => \App\Models\Blog::latest()->take(4)->get(),
    ]);
})->name('blog.index');

Route::get('/blog/{blog:slug}', function (\App\Models\Blog $blog) {
    $tags = \App\Models\Blog::pluck('tags')->flatten()->unique()->values();
    return view('blogs.show', [
        'post'            => $blog,
        'tagsDisponibles' => \App\Models\Blog::pluck('tags')->flatten()->unique()->values(),
        'recientes'       => \App\Models\Blog::where('id', '!=', $blog->id)->latest()->take(4)->get(),
        'relacionados'    => \App\Models\Blog::where('id', '!=', $blog->id)
            ->where(function ($q) use ($blog) {
                foreach ($blog->tags ?? [] as $t) {
                    $q->orWhereJsonContains('tags', $t);
                }
            })
            ->latest()->take(3)->get(),
    ]);
})->name('blog.show');



Route::resource('/areas', AreaController::class)->names('area');

// routes/web.php
Route::get('/areas/{area:slug}/colecciones', function (\App\Models\Area $area) {
    return view('areas.colecciones', ['area' => $area]);
})->name('area.colecciones');


Route::get('/fondos', function () {
    return view('fondos.index', [
        'areasDisponibles' => \App\Models\Area::orderBy('nombre')->get(),
        'title' => 'Colecciones (Fondos)',
    ]);
})->name('fondos.index');

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



Route::get('/media/ocr', function (Request $request) {

    // Asumiendo que secure.media ya inyectó $request->archivo_id desde el token
    $archivo = \App\Models\RecursosArchivos::findOrFail($request->archivo_id);

    // Intentamos sacar la ruta base del JSON de assets
    $pathImagen = $archivo->assets_procesados['main'] ?? null;

    if (!$pathImagen) {
        return response()->json([]);
    }

    // Reemplazamos el nombre de la imagen (ej. main.webp) por ocr.json
    $pathJson = str_replace(basename($pathImagen), 'ocr.json', $pathImagen);

    // Verificamos si el worker de Go generó el OCR
    if (!Storage::disk('private')->exists($pathJson)) {
        return response()->json([]);
    }

    // Al ser un JSON muy ligero, retornarlo directo funciona perfecto, pero 
    // mantenemos tu estándar de X-Accel-Redirect para que Nginx lo sirva
    return response('', 200)
        ->header('X-Accel-Redirect', '/protegido/' . ltrim($pathJson, '/'))
        ->header('Content-Type', 'application/json')
        ->header('Cache-Control', 'private, max-age=86400')
        ->header('X-Content-Type-Options', 'nosniff');
})->name('visor.ocr')
    ->middleware(['secure.media', 'throttle:media']); // ¡Protegida exactamente igual que tus imágenes!

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

    if (!$path)
        abort(404);

    $mime = str_ends_with($path, '.webp') ? 'image/webp' : 'image/jpeg';
    if (str_ends_with($path, '.pdf'))
        $mime = 'application/pdf';

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



Route::get('/video/key/{key}', function ($key) {
    abort_unless(request()->hasValidSignature(), 403);

    $fullPath = storage_path("app/keys/{$key}");

    if (!file_exists($fullPath)) {
        return response()->json([
            'error' => 'Archivo no encontrado físicamente',
            'debug_path' => $fullPath,
            'user' => posix_getpwuid(posix_geteuid())['name'],
        ], 404);
    }

    return response(file_get_contents($fullPath), 200, [
        'Content-Type' => 'application/octet-stream',
        'Access-Control-Allow-Origin' => config('app.url'),
        'Access-Control-Allow-Credentials' => 'true',
    ]);
})->name('video.key')->where('key', '.*');


Route::get('/videos/{recursoId}/{filename}', function ($recursoId, $filename) {

    $video = RecursosArchivos::where('recursos_id', $recursoId)->first();

    if (!$video) {
        abort(404);
    }

    // El manifiesto en disco se llama "{id}.m3u8", pero los segmentos
    // conservan su nombre real (segment_000.ts, etc.)
    $realFilename = str_ends_with($filename, '.m3u8')
        ? "{$video->id}.m3u8"
        : $filename;

    $internalPath = "{$video->id}/{$realFilename}";

    $headers = [
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        'Pragma' => 'no-cache',
        'Expires' => '0',
    ];

    if (str_ends_with($filename, '.m3u8')) {
        $headers['Content-Type'] = 'application/vnd.apple.mpegurl';
    }

    if (str_ends_with($filename, '.ts')) {
        $headers['Content-Type'] = 'video/mp2t';
    }

    return response('', 200, array_merge($headers, [
        'X-Accel-Redirect' => "/hls/{$internalPath}",
    ]));
})->where('filename', '.*');

// routes/web.php
Route::middleware(['web', Authenticate::class])
    ->prefix('chunks')
    ->group(function () {
        // routes/web.php
        Route::match(['get', 'post'], '/chunks/upload', [ChunkUploadController::class, 'handle'])
            ->name('api.chunks.upload')
            ->middleware(Authenticate::class);
    });

