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

        // Solo mandamos IDs, NO URLs (más seguro)
        $paginasCargadas = $recurso->archivos->map(function ($archivo) {
            return [
                'id' => $archivo->id,
                'url' => URL::temporarySignedRoute('media.stream', now()->addMinutes(60), [
                    'archivo_id' => $archivo->id,
                    'tipo' => 'main' // Usamos la imagen grande para el libro
                ]),
            ];
        });
        return view('visor', compact('recurso', 'paginasCargadas'));
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
