<?php

namespace App\Http\Controllers;

use App\Models\Recursos;
use Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class ReportesController extends Controller
{
    public function store(Request $request, Recursos $registro)
    {
        // Honeypot: si viene lleno, es un bot. Respondemos "ok" sin hacer nada.
        if ($request->filled('sitio_web')) {
            return back()->with('success', 'Gracias por tu reporte.');
        }

        // Trampa de tiempo: menos de 3 segundos = bot
        if (now()->timestamp - (int) $request->input('cargado_en') < 3) {
            return back()->with('success', 'Gracias por tu reporte.');
        }

        $datos = $request->validate([
            'observaciones' => ['required', 'string', 'min:10', 'max:1000'],
            'cf-turnstile-response' => ['required'],
        ], [
            'cf-turnstile-response.required' => 'Confirma que no eres un robot.',
        ]);

        $respuesta = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.turnstile.secret_key'),
            'response' => $datos['cf-turnstile-response'],
            'remoteip' => $request->ip(),
        ]);

        if (!$respuesta->json('success')) {
            return back()
                ->withErrors(['cf-turnstile-response' => 'No se pudo verificar el captcha, inténtalo de nuevo.'])
                ->withInput();
        }

        // Guarda el reporte (ajusta a tu modelo/tabla)
        $registro->reportes()->create([
            'correo' => Auth::user()->email,
            'observaciones' => $datos['observaciones'],
            'recursos_id' => $registro->id,
            'user_id'       => Auth::id(),
        ]);

        $mensaje = 'Gracias por tu reporte.';

        return redirect()->route('buscador.registro', ['tipo' => 'documento', 'recurso' => $registro])->with('success', $mensaje);

        return back()->with('success', 'Gracias por tu reporte.');
    }
}
