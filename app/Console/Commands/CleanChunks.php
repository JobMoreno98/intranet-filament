<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

#[Signature('app:clean-chunks')]
#[Description('Command description')]
class CleanChunks extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $disk = Storage::disk('local');
        $directory = 'chunks'; // Cambia esto por el nombre de tu carpeta de temporales
        $hours = $this->option('hours');
        $now = Carbon::now();
        $deleted = 0;

        foreach ($disk->directories($directory) as $dir) {
            $lastModified = Carbon::createFromTimestamp($disk->lastModified($dir));

            if ($now->diffInHours($lastModified) >= $hours) {
                $disk->deleteDirectory($dir);
                $deleted++;
            }
        }

        $this->info("Recolector finalizado: Se eliminaron {$deleted} carpetas temporales abandonadas.");
    }
}
