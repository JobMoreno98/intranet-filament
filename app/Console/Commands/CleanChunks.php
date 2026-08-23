<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

#[Signature('app:clean-chunks {--hours=24 : Horas de antigüedad para borrar}')]
#[Description('Comando para limpiar los archivos temporales de Livewire')]
class CleanChunks extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $disk = Storage::disk('local'); // O 'private', el que funcionó en tu debug
        $directory = 'livewire-tmp';

        // Forzamos a que sea un número flotante, así acepta 0 o 0.5 horas
        $hours = (float) ($this->option('hours') ?? 24);
        $now = time(); // Timestamp actual nativo (segundos desde 1970)
        $deleted = 0;

        if (!$disk->exists($directory)) {
            $this->info("El directorio temporal no existe aún.");
            return;
        }

        $files = $disk->files($directory);
        dd($files);

        foreach ($files as $file) {
            $fileTime = $disk->lastModified($file);
            $ageInHours = ($now - $fileTime) / 3600; // Convertimos los segundos a horas

            if ($ageInHours >= $hours) {
                $disk->delete($file);
                $deleted++;
            }
        }

        $directories = $disk->directories($directory);

        foreach ($directories as $dir) {
            $dirTime = $disk->lastModified($dir);
            $ageInHours = ($now - $dirTime) / 3600;

            if ($ageInHours >= $hours) {
                $disk->deleteDirectory($dir);
                $deleted++;
            }
        }

        $this->info("Recolector finalizado: Se eliminaron {$deleted} archivos/carpetas temporales de Livewire.");
    }
}