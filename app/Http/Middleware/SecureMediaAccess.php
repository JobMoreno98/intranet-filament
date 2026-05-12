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

        if (!$token) {
            abort(403);
        }

        try {

            $data = json_decode(
                decrypt($token),
                true
            );
        } catch (\Exception $e) {

            abort(403);
        }

        // expira token
        if (now()->timestamp > $data['e']) {

            abort(403);
        }

        $session = ReaderSession::where(
            'session_id',
            $data['s']
        )->first();

        if (!$session) {

            abort(403);
        }

        // expira sesión
        if ($session->expires_at->isPast()) {

            $session->delete();

            abort(403);
        }

        // validar IP opcional
        if ($session->ip_address !== $request->ip()) {

            abort(403);
        }

        $request->merge([

            'archivo_id' => $data['a']

        ]);

        return $next($request);
    }
}
