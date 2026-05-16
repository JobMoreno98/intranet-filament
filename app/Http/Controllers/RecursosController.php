<?php

namespace App\Http\Controllers;

use App\Models\ReaderSession;
use App\Models\Recursos;
use App\Models\RecursosArchivos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecursosController extends Controller
{

    public function view($id)
    {
        // 1. Cacheamos solo el array de datos, no el modelo vivo
        $recursoData = Cache::remember("recurso_view_data_{$id}", 1800, function () use ($id) {
            $recurso = Recursos::with(['archivos' => function ($q) {
                $q->orderBy('orden');
            }])->findOrFail($id);

            // Convertimos a array para evitar problemas de serialización
            return $recurso->toArray();
        });

        // 2. Como ahora $recursoData es un array, accedemos con corchetes []
        $paginas = collect($recursoData['archivos'])->map(function ($archivo) {

            $payload = [
                'a' => $archivo['id'],
                'u' => auth()->id(),
                'e' => now()->timestamp + 3600
            ];

            $token = encrypt(json_encode($payload));

            return [
                'id' => $archivo['id'],
                'url' => route('media.stream', [
                    'token' => $token
                ]),
                'w' => 1200,
                'h' => 1600
            ];
        })->toArray();

        // 3. Pasamos los datos a la vista
        // Nota: En la vista, ahora $recurso será un array, 
        // asegúrate de usar $recurso['titulo'] en lugar de $recurso->titulo
        return view('visor', [
            'paginas' => $paginas,
            'recurso' => $recursoData
        ]);
    }

    public function signedUrl($id)
    {
        $payload = [
            'a' => $id, // ID del archivo
            'u' => auth()->id(),
            'e' => now()->timestamp + 300 // Expiración de 5 minutos
        ];

        $token = encrypt(json_encode($payload));

        return response()->json([
            // Agregamos 'archivo_id' para que match con el findOrFail del stream
            'url' => route('media.stream', [
                'archivo_id' => $id,
                'token' => $token
            ])
        ]);
    }

    public function preview(Request $request)
    {
        // Validar que sea admin (opcional pero recomendado)
        if (!auth()->guard('admin')->check() && !auth()->user() instanceof \App\Models\Admin) {
            abort(403, 'Acceso exclusivo para administradores.');
        }

        $archivo = RecursosArchivos::findOrFail($request->archivo_id);

        // Al admin le mostramos el original si no hay procesado, 
        // o podemos elegir siempre mostrar el 'main' para velocidad.
        $path = $archivo->assets_procesados['main'] ?? $archivo->path_original;

        if (!$path) {
            abort(404);
        }

        // Determinamos el mime type basado en el archivo real
        $mime = str_ends_with($path, '.webp') ? 'image/webp' : 'image/jpeg';

        return response('', 200)
            ->header('X-Accel-Redirect', '/protegido/' . $path)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'inline');
    }

    public function publico(Request $request, int $tabla, int $id)
    {

        $sessionId = Str::uuid();

        $item = DB::connection('mysql2')->table('colecciones')->where('clave', $tabla)->first();

        $recurso = DB::connection('mysql2')->table($item->tabla)->where('idElemento', '=', $id)->first();
        //dd($recurso);
        ReaderSession::create([

            'session_id' => $sessionId,

            'recurso_id' => $recurso->IdElemento,

            'ip_address' => $request->ip(),

            'user_agent' => substr(
                $request->userAgent(),
                0,
                500
            ),

            'expires_at' => now()->addHours(2)

        ]);

        for ($i = 1; $i <= $recurso->numArchivos; $i++) {
            $recurso->archivos[] =
                collect(

                    [
                        'id' => $i,
                        "ruta" => $recurso->carpetaContenido . $i . "." . strtolower($recurso->tipoArchivo),
                        'orden' => $i
                    ]
                );
        }
        $recurso->archivos = collect($recurso->archivos);
        $recurso->titulo = $recurso->asunto;


        $paginas = $recurso->archivos
            ->sortBy('orden')
            ->map(function ($archivo) use ($sessionId) {
                $archivo = collect($archivo);
                $payload = encrypt(json_encode([

                    'a' => $archivo['id'],

                    's' => $sessionId,

                    'e' => now()->timestamp + 7200

                ]));

                return [

                    'id' => $archivo['id'],

                    'url' => route('media.stream', [

                        'token' => $payload

                    ])

                ];
            });
        $recurso = collect($recurso);



        return view('visor', [

            'recurso' => $recurso,

            'paginas' => $paginas

        ]);
    }
}
