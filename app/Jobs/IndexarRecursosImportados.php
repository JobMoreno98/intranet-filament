<?php

namespace App\Jobs;

use App\Models\Recursos;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class IndexarRecursosImportados implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(public string $desde) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Solo indexa lo importado, no toda la tabla.
        // searchable() hace upsert por ID, así que repetirlo no duplica nada.
        Recursos::query()
            ->where('created_at', '>=', $this->desde)
            ->with(['acervo', 'coleccion', 'archivos'])
            ->searchable(500);
    }

    public function failed(Throwable $e): void
    {
        Log::error('Falló la indexación de recursos importados en Meilisearch', [
            'desde' => $this->desde,
            'error' => $e->getMessage(),
        ]);
    }
}
