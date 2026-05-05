<?php

namespace App\Http\Controllers;

use App\Models\Recursos;
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
                // Opcional: si tienes dimensiones guardadas, PhotoSwipe las usa para el placeholder
                'w' => 1200,
                'h' => 1600
            ];
        })->toArray();

        return view('visor', compact('paginas', 'recurso'));
    }

    public function signedUrl($id)
    {
        $payload = [
            'a' => $id,
            'u' => auth()->id(),
            'e' => now()->timestamp + 300
        ];

        $token = encrypt(json_encode($payload));

        return response()->json([
            'url' => route('media.stream', ['token' => $token])
        ]);
    }
}
