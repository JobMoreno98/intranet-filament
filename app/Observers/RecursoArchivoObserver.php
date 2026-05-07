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
    }

    public function deleted(RecursosArchivos $archivo)
    {
        // 1. Limpiar Caché (Indispensable para que el visor se actualice)
        $this->clearCache($archivo);

        // 2. Lógica de borrado de archivos físicos que ya tenías
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
    }
    
    private function clearCache(RecursosArchivos $archivo)
    {
        // Borra la lista completa del visor
        Cache::forget("recurso_view_data_{$archivo->recursos_id}");

        // Borra la metadata individual del stream
        Cache::forget("archivo_metadata_{$archivo->id}");
    }
}
