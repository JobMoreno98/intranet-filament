<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Crypt;

class SecureMediaAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->query('token');

        try {
            $data = json_decode(decrypt($token), true);
        } catch (\Exception $e) {
            abort(403, 'Token inválido');
        }

        // expiración
        if (now()->timestamp > $data['e']) {
            abort(403, 'Expirado');
        }

        // usuario
        if ($data['u'] !== auth()->id()) {
            abort(403, 'No autorizado');
        }

        // inyectar
        $request->merge([
            'archivo_id' => $data['a']
        ]);
    }
}
