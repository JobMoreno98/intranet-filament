<?php

namespace App\Observers;

use App\Models\RecursosArchivos;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class RecursoArchivoObserver
{


    public function saved(RecursosArchivos $archivo)
    {
        $this->clearCache($archivo);
        $archivo->recurso?->searchable();
    }

    public function deleted(RecursosArchivos $archivo)
    {
        // 1. Limpiar Caché (Indispensable para que el visor se actualice)
        $this->clearCache($archivo);
        $archivo->recurso?->searchable();

        // 2. Borrado del archivo original
        $directorioPadre = dirname($archivo->path_original);

        if ($archivo->path_original) {
            Storage::disk('private')->delete($archivo->path_original);

            if (count(Storage::disk('private')->files($directorioPadre)) === 0) {
                Storage::disk('private')->deleteDirectory($directorioPadre);
            }
        }

        if ($directorioPadre) {
            if (Storage::disk('public')->exists($directorioPadre)) {
                Storage::disk('public')->deleteDirectory($directorioPadre);
            }
        }

        // 3. Borrado del HLS generado (manifiesto .m3u8, segmentos .ts, thumb.webp)
        $directorioHls = "encrypted/{$archivo->id}";

        if (Storage::disk('private')->exists($directorioHls)) {
            Storage::disk('private')->deleteDirectory($directorioHls);
        }
    }

    private function clearCache(RecursosArchivos $archivo)
    {
        // Borra la lista completa del visor (misma key que usa showRegistro() en el controlador)
        Cache::forget("recurso_con_relaciones_{$archivo->recursos_id}");

        // Borra la metadata individual del stream
        Cache::forget("archivo_metadata_{$archivo->id}");
    }
}