<?php

namespace App\Console\Commands;

use App\Models\Recursos;
use App\Models\RecursosArchivos;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Escucha la cola de Redis "ocr_reindex_queue" (alimentada por el worker en Go
 * cada vez que termina de escribir el OCR de una página) y reindexa en
 * Meilisearch el recurso (libro) correspondiente.
 *
 * Como el worker de Go escribe directo a MySQL (sin pasar por Eloquent), este
 * comando es el puente que le avisa a Laravel que algo cambió.
 *
 * Hace "debounce": si llegan varias páginas del mismo libro en pocos segundos
 * (caso normal cuando se procesa un libro completo), solo reindexa una vez
 * cada $debounceSegundos, en vez de una vez por página.
 *
 * Se ejecuta como proceso persistente vía Supervisor, igual que tu worker de Go:
 *   php artisan ocr:watch-reindex-queue
 */
class OcrReindexQueueWorker extends Command
{
    protected $signature = 'ocr:watch-reindex-queue';

    protected $description = 'Escucha la cola de Redis del OCR y reindexa en Meilisearch los recursos afectados';

    private const QUEUE_KEY = 'ocr_reindex_queue';

    /**
     * Ventana mínima entre dos reindexados del mismo recurso.
     */
    private const DEBOUNCE_SEGUNDOS = 10;

    public function handle(): int
    {
        $this->info('Escuchando la cola de OCR (con auto-reconexión y debounce)...');

        while (true) {
            try {
                // Asumiendo que ya añadiste el prefijo en tu código de Go
                $item = \Illuminate\Support\Facades\Redis::blpop([self::QUEUE_KEY], 5);

                if (!$item) {
                    continue;
                }

                $recursoId = $item[1] ?? null;

                if (!is_numeric($recursoId)) {
                    continue;
                }

                $recursoId = (int) $recursoId;
                $lockKey = "ocr_reindex_lock:{$recursoId}";

                $lockObtenido = \Illuminate\Support\Facades\Redis::set($lockKey, 1, 'EX', self::DEBOUNCE_SEGUNDOS, 'NX');

                // --- NUEVA LÓGICA DEL CANDADO ---
                if (!$lockObtenido) {
                    $tiempoRestante = \Illuminate\Support\Facades\Redis::ttl($lockKey);

                    // Si llega una imagen mientras Meilisearch está indexando, 
                    // pausamos el worker unos segundos en lugar de descartar la imagen
                    if ($tiempoRestante > 0) {
                        sleep($tiempoRestante);
                    }

                    // Renovamos el candado para nuestra ejecución
                    \Illuminate\Support\Facades\Redis::set($lockKey, 1, 'EX', self::DEBOUNCE_SEGUNDOS);
                }

                // Esta ejecución atrapará TODAS las imágenes de la ráfaga
                $this->reindexar($recursoId);
            } catch (\Throwable $e) {
                // --- EL ESCUDO CONTRA CAÍDAS DE REDIS ---
                // Si Redis se cae o parpadea, atrapamos el error en lugar de crashear el worker
                \Illuminate\Support\Facades\Log::warning("Micro-corte con Redis. Reconectando en 2s... Detalle: " . $e->getMessage());

                // Dormimos 2 segundos para darle tiempo a Redis de reponerse y volvemos al inicio del while
                sleep(2);
                continue;
            }
        }

        return self::SUCCESS;
    }

private function reindexar(int $recursoId): void
    {
        try {
            // Buscamos el documento padre
            $recurso = \App\Models\Recursos::find($recursoId);

            if (!$recurso) {
                \Illuminate\Support\Facades\Log::warning("ocr:watch-reindex-queue: recurso {$recursoId} no existe, se ignora.");
                return;
            }

            // 1. Reindexamos el documento principal (el libro)
            $recurso->searchable();

            // 2. Reindexamos las páginas hijas usando tu modelo correcto
            RecursosArchivos::where('recursos_id', $recursoId)->searchable();

            $this->info("Recurso {$recursoId} y sus páginas fueron reindexados en Meilisearch.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error reindexando recurso tras OCR: ' . $e->getMessage(), [
                'recurso_id' => $recursoId,
            ]);
        }
    }
}
