<?php

namespace App\Observers;

use App\Models\RecursosArchivos;
use Illuminate\Support\Facades\Storage;

class RecursoArchivoObserver
{
    /**
     * Handle the RecursosArchivos "created" event.
     */
    public function created(RecursosArchivos $recursosArchivos): void
    {
        //
    }

    /**
     * Handle the RecursosArchivos "updated" event.
     */
    public function updated(RecursosArchivos $recursosArchivos): void
    {
        //
    }

    /**
     * Handle the RecursosArchivos "deleted" event.
     */
    public function deleted(RecursosArchivos $recursosArchivos): void
    {
        // 1. Borrar el archivo original (carpeta private)
        if ($recursosArchivos->path_original) {
            Storage::disk('private')->delete($recursosArchivos->path_original);

            // Opcional: Borrar la carpeta del ID del archivo si quedó vacía
            $directorioPadre = dirname($recursosArchivos->path_original);
            if (count(Storage::disk('private')->files($directorioPadre)) === 0) {
                Storage::disk('private')->deleteDirectory($directorioPadre);
            }
        }

        // 2. Borrar los archivos procesados por Go (carpeta public)
        // Estructura: items/slug/id_recurso/id_archivo/
        //dd($recursosArchivos->path_original, $directorioPadre);

        if ($directorioPadre) {


            if (Storage::disk('public')->exists($directorioPadre)) {
                // Borramos la carpeta completa con main.webp y thumb.webp
                Storage::disk('public')->deleteDirectory($directorioPadre);
            }
        }
    }

    /**
     * Handle the RecursosArchivos "restored" event.
     */
    public function restored(RecursosArchivos $recursosArchivos): void
    {
        //
    }

    /**
     * Handle the RecursosArchivos "force deleted" event.
     */
    public function forceDeleted(RecursosArchivos $recursosArchivos): void
    {
        //
    }
}
