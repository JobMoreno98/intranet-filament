<?php

namespace App\Console\Commands;

use App\Models\Recursos;
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
        $this->info('Escuchando ' . self::QUEUE_KEY . '...');

        while (true) {
            // BLPOP espera hasta 5s a que llegue algo; si no llega nada, vuelve a intentar.
            // Esto evita hacer polling agresivo (busy loop) sin dejar el proceso "colgado" para siempre.
            $item = Redis::blpop([self::QUEUE_KEY], 5);

            if (!$item) {
                continue;
            }

            // blpop devuelve [nombre_de_la_lista, valor]
            $recursoId = $item[1] ?? null;

            if (!is_numeric($recursoId)) {
                Log::warning('ocr:watch-reindex-queue recibió un valor inesperado', ['valor' => $recursoId]);
                continue;
            }

            $recursoId = (int) $recursoId;
            $lockKey = "ocr_reindex_lock:{$recursoId}";

            // Debounce: si ya reindexamos este recurso hace poco, lo saltamos.
            // NX = solo pone la llave si no existe; si devuelve false, ya había una reciente.
            $lockObtenido = Redis::set($lockKey, 1, 'EX', self::DEBOUNCE_SEGUNDOS, 'NX');

            if (!$lockObtenido) {
                continue;
            }

            $this->reindexar($recursoId);
        }

        return self::SUCCESS;
    }

    private function reindexar(int $recursoId): void
    {
        try {
            // La cache del visor debe limpiarse siempre que cambie el OCR de una página.
            Cache::forget("recurso_con_relaciones_{$recursoId}");

            $recurso = Recursos::find($recursoId);

            if (!$recurso) {
                Log::warning("ocr:watch-reindex-queue: recurso {$recursoId} no existe, se ignora.");
                return;
            }

            $recurso->searchable();

            $this->info("Recurso {$recursoId} reindexado en Meilisearch.");
        } catch (\Throwable $e) {
            Log::error('Error reindexando recurso tras OCR: ' . $e->getMessage(), [
                'recurso_id' => $recursoId,
            ]);
        }
    }
}