<?php

namespace App\Filament\Resources\Recursos\Pages\Concerns;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

trait EnviaArchivosAGo
{
    /**
     * Centralizamos el envío a Redis para mantener el orden.
     * Si el archivo es de tipo "video", primero preparamos la key
     * AES-128 + el .keyinfo (igual que hacía el Job de Laravel),
     * y le pasamos a Go solo la ruta del .keyinfo ya escrito en disco.
     */
    private function enviarAGo($archivo, $recurso, string $action = 'update'): void
    {
        $tipo = $recurso->tipo_media ?? 'imagen';

        $payload = [
            'archivo_id'     => $archivo->id,
            'recurso_id'     => $recurso->id,
            'path'           => storage_path('app/private/'.$archivo->path_original),
            'coleccion_slug' => $recurso->coleccion->slug,
            'tipo'           => $tipo,
            'action'         => $action,
        ];

        if (in_array($tipo, ['video', 'audio'], true)) {
            $payload['output_name']   = (string) $archivo->id;
            $payload['key_info_path'] = $this->generarKeyInfoMedia($archivo);
        }

        Redis::lpush('cola_procesamiento', json_encode($payload));
    }

    /**
     * Genera la key AES-128 y el .keyinfo para HLS cifrado (video o audio),
     * y devuelve la ruta absoluta del .keyinfo. El APP_KEY nunca sale de
     * Laravel: Go solo recibe la ruta, ya resuelta, para pasársela a ffmpeg.
     */
    private function generarKeyInfoMedia($archivo): string
    {
        $outputName = (string) $archivo->id;

        $keysDir = storage_path('app/keys');
        if (! is_dir($keysDir)) {
            mkdir($keysDir, 0755, true);
        }

        $keyPath = "{$keysDir}/{$outputName}.key";
        $keyInfoPath = "{$keysDir}/{$outputName}.keyinfo";

        file_put_contents($keyPath, random_bytes(16));

        // Requiere una ruta con nombre 'video.key' definida en tus rutas,
        // p.ej. Route::get('/video-key/{key}', ...)->name('video.key');
        $keyUrl = URL::signedRoute('video.key', [
            'key' => "{$outputName}.key",
        ]);

        file_put_contents($keyInfoPath, implode("\n", [
            $keyUrl,    // URL pública (firmada) que usará el reproductor
            $keyPath,   // path real que usa ffmpeg para cifrar
        ]));

        return $keyInfoPath;
    }

    /**
     * Mueve un archivo entre dos discos distintos (Storage::move solo
     * funciona dentro del mismo disco). Lo usamos porque el chunk uploader
     * deja el video ensamblado en el disco 'public', y necesitamos llevarlo
     * a 'private' con la misma estructura de carpetas que el resto de archivos.
     */
    private function moverArchivoCrossDisk(string $diskOrigen, string $rutaOrigen, string $diskDestino, string $rutaDestino): bool
    {
        $origen = Storage::disk($diskOrigen);
        $destino = Storage::disk($diskDestino);

        if (! $origen->exists($rutaOrigen)) {
            return false;
        }

        $stream = $origen->readStream($rutaOrigen);

        if ($stream === false || $stream === null) {
            return false;
        }

        $destino->put($rutaDestino, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }

        $origen->delete($rutaOrigen);

        return true;
    }
}