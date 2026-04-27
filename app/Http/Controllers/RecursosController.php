<?php

namespace App\Http\Controllers;

use App\Models\Recursos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

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
                // Opcional: si tienes dimensiones guardadas, PhotoSwipe las usa para el placeholder
                'w' => 1200,
                'h' => 1600
            ];
        })->toArray();

        return view('visor', compact('paginas', 'recurso'));
    }

    // Endpoint para generar URLs firmadas dinámicamente (lazy loading)
    public function signedUrl($id)
    {
        return response()->json([
            'url' => URL::temporarySignedRoute(
                'media.stream',
                now()->addMinutes(5),
                ['archivo_id' => $id]
            )
        ]);
    }
}
