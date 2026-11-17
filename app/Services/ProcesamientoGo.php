<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\URL;

/**
 * Arma el payload y lo manda a la cola del worker Go. Es la misma lógica que
 * antes vivía en el trait EnviaArchivosAGo, sacada a un servicio para poder
 * usarla también desde acciones de la tabla (reprocesar archivos con error).
 */
class ProcesamientoGo
{
    /** Llave SIN prefijo: Laravel le antepone el prefijo de Redis (el worker lee bpej_cola_procesamiento). */
    public const COLA = 'cola_procesamiento';

    /**
     * @param  bool  $alFinal  false (lpush) = se procesa antes, como las subidas normales.
     *                         true  (rpush) = se forma al final de la cola (reprocesos).
     *                         El worker saca con BLPOP, o sea por la izquierda.
     */
    public static function enviar($archivo, $recurso, string $action = 'update', bool $alFinal = false): void
    {
        $tipo = $recurso->tipo_media ?? 'imagen';

        $payload = [
            'archivo_id'     => $archivo->id,
            'recurso_id'     => $recurso->id,
            'path'           => storage_path('app/private/' . $archivo->path_original),
            'coleccion_slug' => $recurso->coleccion->slug,
            'tipo'           => $tipo,
            'action'         => $action,
        ];

        if (in_array($tipo, ['video', 'audio'], true)) {
            $payload['output_name']   = (string) $archivo->id;
            $payload['key_info_path'] = self::generarKeyInfoMedia($archivo);
        }

        $json = json_encode($payload);

        $alFinal
            ? Redis::rpush(self::COLA, $json)
            : Redis::lpush(self::COLA, $json);
    }

    /**
     * Genera la key AES-128 y el .keyinfo para HLS cifrado (video o audio),
     * y devuelve la ruta absoluta del .keyinfo. El APP_KEY nunca sale de
     * Laravel: Go solo recibe la ruta ya resuelta para pasársela a ffmpeg.
     */
    public static function generarKeyInfoMedia($archivo): string
    {
        $outputName = (string) $archivo->id;

        $keysDir = storage_path('app/keys');
        if (! is_dir($keysDir)) {
            mkdir($keysDir, 0755, true);
        }

        $keyPath = "{$keysDir}/{$outputName}.key";
        $keyInfoPath = "{$keysDir}/{$outputName}.keyinfo";

        file_put_contents($keyPath, random_bytes(16));

        $keyUrl = URL::signedRoute('video.key', [
            'key' => "{$outputName}.key",
        ]);

        file_put_contents($keyInfoPath, implode("\n", [
            $keyUrl,    // URL pública (firmada) que usará el reproductor
            $keyPath,   // path real que usa ffmpeg para cifrar
        ]));

        return $keyInfoPath;
    }
}