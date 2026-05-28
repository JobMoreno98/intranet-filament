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

        if ($request->is('admin*') || $request->is('livewire*')) {
            return $response;
        }

        if ($response->getStatusCode() === 200 && !$request->expectsJson()) {
            try {
                $hoy = now()->format('Y-m-d');
                $userId = auth()->id();

                // 2. Definimos un identificador único por usuario o por IP (visitantes libres)
                if ($userId) {
                    $lockKey = "analytics:lock:user:{$userId}:{$hoy}";
                } else {
                    // Limpiamos la IP de caracteres raros para la llave de Redis
                    $ipLimpia = str_replace([':', '.'], '_', $request->ip());
                    $lockKey = "analytics:lock:ip:{$ipLimpia}:{$hoy}";
                }

                // 3.
                $visitaUnicaDia = Redis::set($lockKey, '1', 'EX', 86400, 'NX');

                // 4. Si Redis dice que la llave ya existía (false), rompemos el flujo. Ya contó hoy.
                
                if (!$visitaUnicaDia) {
                    return $response;
                }

                $payload = json_encode([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->getRequestUri(),
                    'user_id' => $userId,
                    'created_at' => now()->toDateTimeString(),
                ]);

                // Guardamos en la cola para la sincronización masiva a MariaDB/MySQL
                Redis::rpush('analytics:visitas_queue', $payload);

                // Incrementamos el contador rápido del día
                Redis::incr("analytics:paginas_vistas:{$hoy}");
            } catch (\Exception $e) {
                // Silencioso
            }
        }

        return $response;
    }
}
