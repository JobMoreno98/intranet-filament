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

        $referer = $request->headers->get('referer');

        if (!$referer || !str_contains($referer, config('app.url'))) {
            abort(403);
        }
        
        if (!$token) {
            throw new HttpException(403, 'Token requerido');
        }

        try {
            $data = json_decode(decrypt($token), true);
        } catch (\Exception $e) {
            throw new HttpException(403, 'Token inválido');
        }

        // 1. Verificación de expiración
        if (now()->timestamp > $data['e']) {
            throw new HttpException(403, 'Token expirado');
        }

        // 2. Verificación de usuario (Dueño del token)
        if (!auth()->check() || $data['u'] !== auth()->id()) {
            throw new HttpException(403, 'Usuario no autorizado');
        }

        // 3. Validación de integridad (Crucial sin Blobs)
        // Si pasaste el archivo_id por la URL, verificamos que coincida con el del token.
        if ($request->has('archivo_id') && $request->archivo_id != $data['a']) {
            throw new HttpException(403, 'El token no corresponde a este archivo');
        }

        // 4. Inyectar/Sobrescribir el ID del token para el controlador
        $request->merge([
            'archivo_id' => $data['a']
        ]);

        return $next($request);
    }
}
