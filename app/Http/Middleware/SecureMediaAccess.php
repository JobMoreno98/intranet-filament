<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SecureMediaAccess
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->query('token');

        if (!$token) {
            throw new HttpException(403, 'Token requerido');
        }

        try {
            $data = json_decode(decrypt($token), true);
        } catch (\Exception $e) {
            throw new HttpException(403, 'Token inválido');
        }

        // expiración
        if (now()->timestamp > $data['e']) {
            throw new HttpException(403, 'Expirado');
        }

        // usuario
        if (!auth()->check() || $data['u'] !== auth()->id()) {
            throw new HttpException(403, 'No autorizado');
        }

        // inyectar datos
        $request->merge([
            'archivo_id' => $data['a']
        ]);

        return $next($request);
    }
}