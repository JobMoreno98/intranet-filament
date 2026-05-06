<?php

namespace App\Http\Controllers;

use App\Models\Recursos;
use App\Models\RecursosArchivos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class RecursosController extends Controller
{


    public function view($id)
    {
        $recurso = Recursos::with('archivos')->findOrFail($id);

        $paginas = $recurso->archivos->map(function ($archivo) {
            return [
                'id' => $archivo->id,
                'url' => URL::temporarySignedRoute('media.stream', now()->addMinutes(60), [
                    'archivo_id' => $archivo->id,
                ]),
                'w' => 1200,
                'h' => 1600
            ];
        })->toArray();

        return view('visor', compact('paginas', 'recurso'));
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
}
