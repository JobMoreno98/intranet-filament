<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;


#[Signature('app:clean-chunks')]
#[Description('Command description')]
class CleanChunks extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Apuntamos al disco privado que me mencionaste
        $disk = Storage::disk('private');
        $directory = 'livewire-tmp';

        $hours = $this->option('hours') ?? 24;
        $now = Carbon::now();
        $deleted = 0;

        // 2. Verificamos que el directorio exista para evitar errores
        if (!$disk->exists($directory)) {
            $this->info("El directorio temporal no existe aún.");
            return;
        }

        // 3. Obtenemos todos los archivos dentro de la carpeta temporal
        $files = $disk->files($directory);

        foreach ($files as $file) {
            $lastModified = Carbon::createFromTimestamp($disk->lastModified($file));

            // Si el archivo supera el límite de horas, lo eliminamos
            if ($now->diffInHours($lastModified) >= $hours) {
                $disk->delete($file);
                $deleted++;
            }
        }

        // Si usas subida por chunks (Resumable.js), a veces Livewire crea subcarpetas.
        // También podemos limpiar carpetas vacías o viejas:
        $directories = $disk->directories($directory);
        foreach ($directories as $dir) {
            $lastModified = Carbon::createFromTimestamp($disk->lastModified($dir));
            if ($now->diffInHours($lastModified) >= $hours) {
                $disk->deleteDirectory($dir);
                $deleted++;
            }
        }

        $this->info("Recolector finalizado: Se eliminaron {$deleted} archivos/carpetas temporales de Livewire.");
    }
}
