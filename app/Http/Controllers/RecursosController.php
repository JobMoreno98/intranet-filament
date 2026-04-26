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
        $paginas = $recurso->archivos->map(function ($archivo) {
            return [
                'id' => $archivo->id
            ];
        })->values();

        return view('visor', compact('paginas'));
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
