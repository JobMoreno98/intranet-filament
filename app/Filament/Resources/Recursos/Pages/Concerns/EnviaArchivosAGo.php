<?php

namespace App\Filament\Resources\Recursos\Pages\Concerns;

use App\Services\ProcesamientoGo;
use Illuminate\Support\Facades\Storage;

trait EnviaArchivosAGo
{
    /**
     * Centralizamos el envío a Redis para mantener el orden. La lógica
     * (payload, key AES-128 y .keyinfo para video/audio) vive ahora en
     * App\Services\ProcesamientoGo para poder reutilizarla desde la tabla.
     */
    private function enviarAGo($archivo, $recurso, string $action = 'update'): void
    {
        ProcesamientoGo::enviar($archivo, $recurso, $action);
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