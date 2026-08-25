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
        $this->info('Escuchando ' . self::QUEUE_KEY . ' (ignorando prefijos de Laravel)...');

        // Extraemos el cliente nativo de Redis que usa Laravel por debajo
        $redisClient = Redis::connection()->client();

        // Si Laravel está usando la extensión nativa PhpRedis (el estándar actual)
        // le quitamos el prefijo a la fuerza para que escuche la misma ruta de Go
        if (method_exists($redisClient, 'setOption')) {
            $redisClient->setOption(\Redis::OPT_PREFIX, '');
        }

        while (true) {
            // blPop con "P" mayúscula es la sintaxis nativa de PhpRedis
            // Envolvemos en un try/catch para soportar si usaras Predis
            try {
                $item = $redisClient->blPop([self::QUEUE_KEY], 5);
            } catch (\Throwable $e) {
                $item = $redisClient->blpop([self::QUEUE_KEY], 5);
            }

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

            // El candado (debounce) sí lo podemos seguir guardando con la 
            // fachada normal de Laravel porque es de consumo interno de PHP
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