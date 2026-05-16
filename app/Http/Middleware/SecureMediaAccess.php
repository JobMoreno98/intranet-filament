<?php

namespace App\Http\Middleware;

use App\Models\ReaderSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SecureMediaAccess
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->query('token');

        // 1. Si no hay token en la URL, se bloquea el acceso
        if (!$token) {
            abort(403, 'Acceso denegado: Token de acceso requerido.');
        }

        try {
            // Desencriptamos el contenido de forma segura
            $data = json_decode(decrypt($token), true);
        } catch (\Exception $e) {
            // Si el token fue alterado, inventado o manipulado, truena aquí
            abort(403, 'Acceso denegado: El token de seguridad es inválido.');
        }

        // 2. ⚡ INVALIDACIÓN POR TIEMPO: Comprobar si los 15 minutos ya pasaron
        if (!isset($data['e']) || now()->timestamp > $data['e']) {
            abort(403, 'Acceso denegado: Este enlace temporal ya ha caducado.');
        }

        // 3. ⚡ INVALIDACIÓN POR USUARIO: Verificar que quien descarga sea el dueño del token
        if (!auth()->check() || auth()->id() !== ($data['u'] ?? null)) {
            abort(403, 'Acceso denegado: No tienes permisos para usar este enlace.');
        }

        // Todo en orden. Inyectamos el archivo_id al request para el controlador del stream
        $request->merge([
            'archivo_id' => $data['a'],
        ]);

        return $next($request);
    }
}
