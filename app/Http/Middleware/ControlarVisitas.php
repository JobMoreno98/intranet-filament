<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Redis;

class ControlarVisitas
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() === 200 && !$request->expectsJson()) {
            try {
                $payload = json_encode([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->getRequestUri(),
                    'user_id' => auth()->id(),
                    'created_at' => now()->toDateTimeString(),
                ]);

                // 1. Guardamos el registro de la visita en una lista de Redis
                Redis::rpush('analytics:visitas_queue', $payload);

                // 2. Incrementamos un contador rápido de páginas vistas hoy
                $hoy = now()->format('Y-m-d');
                Redis::incr("analytics:paginas_vistas:{$hoy}");
            } catch (\Exception $e) {
                // Silencioso para no romper la experiencia del usuario
            }
        }

        return $response;
    }
}
