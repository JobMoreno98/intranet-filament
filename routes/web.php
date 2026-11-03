<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;

// Controllers
use App\Http\Controllers\AreaController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BuscadorController;
use App\Http\Controllers\ChunkUploadController;
use App\Http\Controllers\ColeccionesConsultaController;
use App\Http\Controllers\FavoritoController;
use App\Http\Controllers\RecursosController;
use App\Http\Controllers\ReportesController;

// Models
use App\Models\Area;
use App\Models\Blog;
use App\Models\ColeccionesConsulta;
use App\Models\RecursosArchivos;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. RUTAS PÚBLICAS Y VISTAS ESTÁTICAS
// ==========================================
Route::get('/', [ColeccionesConsultaController::class, 'index'])->name('home');

Route::get('/nuestra-historia', function () {
    return view('historia', ['title' => 'Nuestra Historia']);
})->name('nuestra.historia');

Route::get('/fondos-historicos', function () {
    return view('fondos-historicos', ['title' => 'NUESTROS FONDOS HISTÓRICOS']);
})->name('fondos.historicos');

Route::get('/arreola-historica', function () {
    return view('arreola-historica', ['title' => 'ARREOLA HISTÓRICA']);
})->name('arreola.historica');

Route::get('/contacto', function () {
    return view('contacto', ['title' => 'CONTACTO']);
})->name('arreola.contacto');

Route::get('/como-usar', function () {
    return view('como-usar', ['title' => 'COMO USAR']);
})->name('arreola.uso');

Route::get('/solicitud-reproduccion', function () {
    return view('solicitud', ['title' => 'SOLICITUD DE REPRODUCCIONES']);
})->name('arreola.solicitud');

// ==========================================
// 2. BUSCADOR Y RECURSOS PÚBLICOS
// ==========================================
Route::get('/buscador', [ColeccionesConsultaController::class, 'buscador'])->name('buscador');
Route::get('/buscador/recurso/{tipo}/{recurso}', [ColeccionesConsultaController::class, 'showRegistro'])->name('buscador.registro');
Route::get('/busqueda-avanzada', [RecursosController::class, 'avanzada'])->name('busqueda.avanzada');
Route::get('/busqueda-resultado', [ColeccionesConsultaController::class, 'busquedaAvanzada'])->name('busqueda.resultado');

Route::get('/fondo/{coleccion}', [ColeccionesConsultaController::class, 'show'])->name('coleccion.show');
Route::get('/fondo/{tabla}/{id}', [RecursosController::class, 'publico'])->name('coleccion.individual');

// ==========================================
// 3. BLOG
// ==========================================
Route::get('/blog', function () {
    $tagsDisponibles = Blog::pluck('tags')->flatten()->unique()->values();

    $archivos = Blog::selectRaw('YEAR(created_at) as anio, MONTH(created_at) as mes, COUNT(*) as cantidad')
        ->groupBy('anio', 'mes')
        ->orderBy('anio', 'desc')
        ->orderBy('mes', 'desc')
        ->get()
        ->map(function ($item) {
            $item->nombre_mes = Carbon::create()->month($item->mes)->translatedFormat('F');
            return $item;
        });

    return view('blogs.index', [
        'tagsDisponibles' => $tagsDisponibles,
        'recientes' => Blog::latest()->take(4)->get(),
        'archivos' => $archivos,
    ]);
})->name('blog.index');

Route::get('/blog/{blog:slug}', function (Blog $blog) {
    return view('blogs.show', [
        'post' => $blog,
        'tagsDisponibles' => Blog::pluck('tags')->flatten()->unique()->values(),
        'recientes' => Blog::where('id', '!=', $blog->id)->latest()->take(4)->get(),
        'relacionados' => Blog::where('id', '!=', $blog->id)
            ->where(function ($q) use ($blog) {
                foreach ($blog->tags ?? [] as $t) {
                    $q->orWhereJsonContains('tags', $t);
                }
            })
            ->latest()
            ->take(3)
            ->get(),
    ]);
})->name('blog.show');

// ==========================================
// 4. ÁREAS Y FONDOS
// ==========================================
Route::resource('/areas', AreaController::class)->names('areas');

Route::get('/areas/{area:slug}/fondos', function (Area $area) {
    return view('areas.colecciones', ['area' => $area]);
})->name('area.colecciones');

Route::get('/fondos', function () {
    return view('fondos.index', [
        'areasDisponibles' => Area::orderBy('nombre')->get(),
        'title' => 'Fondos',
    ]);
})->name('fondos.index');

// ==========================================
// 5. MEDIA, STREAMING Y VISORES PROTEGIDOS
// ==========================================
Route::get('/media/stream', function (Request $request) {
    $archivo = RecursosArchivos::findOrFail($request->archivo_id);
    $path = $archivo->assets_procesados['main'] ?? null;

    if (!$path) {
        abort(404, 'No hay versión procesada para este archivo');
    }

    $extension = pathinfo($path, PATHINFO_EXTENSION);
    $mimeType = $extension === 'webp' ? 'image/webp' : 'image/jpeg';

    return response('', 200)
        ->header('X-Accel-Redirect', '/protegido/' . $path)
        ->header('Content-Length', Storage::disk('private')->size($path))
        ->header('Content-Type', $mimeType)
        ->header('Content-Disposition', 'inline')
        ->header('Cache-Control', 'private, max-age=86400')
        ->header('X-Content-Type-Options', 'nosniff');
})
    ->name('media.stream')
    ->middleware(['secure.media', 'throttle:media']);

Route::get('/media/ocr', function (Request $request) {
    $archivo = RecursosArchivos::findOrFail($request->archivo_id);
    $pathImagen = $archivo->assets_procesados['main'] ?? null;

    if (!$pathImagen) {
        return response()->json([]);
    }

    $pathJson = str_replace(basename($pathImagen), 'ocr.json', $pathImagen);

    if (!Storage::disk('private')->exists($pathJson)) {
        return response()->json([]);
    }

    return response('', 200)
        ->header('X-Accel-Redirect', '/protegido/' . ltrim($pathJson, '/'))
        ->header('Content-Type', 'application/json')
        ->header('Cache-Control', 'private, max-age=86400')
        ->header('X-Content-Type-Options', 'nosniff');
})
    ->name('visor.ocr')
    ->middleware(['secure.media', 'throttle:media']);

Route::get('/videos/{recursoId}/{filename}', function ($recursoId, $filename) {
    $video = RecursosArchivos::where('recursos_id', $recursoId)->first();

    if (!$video) {
        abort(404);
    }

    $realFilename = str_ends_with($filename, '.m3u8') ? "{$video->id}.m3u8" : $filename;
    $internalPath = "{$video->id}/{$realFilename}";

    $headers = [
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        'Pragma' => 'no-cache',
        'Expires' => '0',
    ];

    if (str_ends_with($filename, '.m3u8')) {
        $headers['Content-Type'] = 'application/vnd.apple.mpegurl';
    } elseif (str_ends_with($filename, '.ts')) {
        $headers['Content-Type'] = 'video/mp2t';
    } elseif (str_ends_with($filename, '.webp')) {
        $headers['Content-Type'] = 'image/webp';
        unset($headers['Pragma'], $headers['Expires']);
        $headers['Cache-Control'] = 'public, max-age=86400';
    }

    return response(
        '',
        200,
        array_merge($headers, [
            'X-Accel-Redirect' => "/hls/{$internalPath}",
        ]),
    );
})->where('filename', '.*');

Route::get('/video/key/{key}', function ($key) {
    abort_unless(request()->hasValidSignature(), 403);

    $fullPath = storage_path("app/keys/{$key}");

    if (!file_exists($fullPath)) {
        return response()->json(
            [
                'error' => 'Archivo no encontrado físicamente',
                'debug_path' => $fullPath,
                'user' => posix_getpwuid(posix_geteuid())['name'],
            ],
            404,
        );
    }

    return response(file_get_contents($fullPath), 200, [
        'Content-Type' => 'application/octet-stream',
        'Access-Control-Allow-Origin' => config('app.url'),
        'Access-Control-Allow-Credentials' => 'true',
    ]);
})
    ->name('video.key')
    ->where('key', '.*');

// ==========================================
// 6. RUTAS AUTENTICADAS Y DE ADMINISTRACIÓN
// ==========================================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/visor/{id}', [RecursosController::class, 'view']);
    Route::get('/media/url/{id}', [RecursosController::class, 'signedUrl']);

    // Favoritos
    Route::get('/mis-favoritos', [FavoritoController::class, 'index'])->name('favoritos.index');
    Route::post('/mis-favoritos/toggle', [FavoritoController::class, 'toggle'])->name('favoritos.toggle');

    // Búsqueda OCR en recursos
    Route::get('/recursos/{recurso}/buscar-ocr', [BuscadorController::class, 'buscarEnRecurso'])
        ->middleware('throttle:60,1')
        ->name('recursos.buscar-ocr');
});

// Visor Público Firmado
Route::get('/viewer/visor/{recurso}', [RecursosController::class, 'publico'])
    ->middleware('signed')
    ->name('visor.publico');

// Admin / Filament Media Load
Route::get('/admin/media/load', function (Request $request) {
    if (!auth()->guard('admin')->check() && !auth()->user() instanceof \App\Models\Admin) {
        abort(403, 'Acceso exclusivo para administradores.');
    }

    $archivo = RecursosArchivos::findOrFail($request->archivo_id);
    $version = $request->query('version', 'thumb');

    $path = $archivo->assets_procesados[$version] ?? ($archivo->assets_procesados['main'] ?? $archivo->path_original);

    if (!$path) {
        abort(404);
    }

    $mime = str_ends_with($path, '.webp') ? 'image/webp' : 'image/jpeg';
    if (str_ends_with($path, '.pdf')) {
        $mime = 'application/pdf';
    }

    return response('', 200)
        ->header('X-Accel-Redirect', '/protegido/' . $path)
        ->header('Content-Type', $mime)
        ->header('X-Content-Type-Options', 'nosniff');
})
    ->name('admin.media.load')
    ->middleware(Filament::getPanel('admin')->getAuthMiddleware());

// ==========================================
// 7. API, CHUNKS Y ACCIONES ESPECIALES
// ==========================================
Route::middleware(['web', Authenticate::class])
    ->prefix('chunks')
    ->group(function () {
        Route::match(['get', 'post'], '/chunks/upload', [ChunkUploadController::class, 'handle'])
            ->name('api.chunks.upload')
            ->middleware(Authenticate::class);
    });

Route::post('/registros/{registro}/reporte', [ReportesController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('reporte.recurso');

// ==========================================
// 8. ARCHIVOS EXTERNOS DE CONFIGURACIÓN
// ==========================================
require __DIR__ . '/settings.php';
