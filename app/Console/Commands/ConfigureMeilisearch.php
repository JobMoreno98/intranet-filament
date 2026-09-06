<?php

namespace App\Console\Commands;

use App\Models\TipoAcervo;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Meilisearch\Client;

#[Signature('meili:configure')]
#[Description('Configura los atributos indexables, filtrables y ordenables en Meilisearch')]
class ConfigureMeilisearch extends Command
{
    /**
     * Execute the console command.
     */
    protected $signature = 'meili:configure';
    protected $description = 'Configura los atributos indexables, filtrables y ordenables en Meilisearch';

    public function handle()
    {
        $this->info('Conectando con Meilisearch...');

        $client = new Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));

        // Configuración de Colecciones
        $this->line('Configurando índice: colecciones...');
        $client->index('colecciones')->updateSettings([
            'searchableAttributes' => ['nombre', 'parent_names', 'descripcion'],
            'filterableAttributes' => ['id', 'parent_id', 'parent_ids']
        ]);

        // Configuración de Recursos
        $this->line('Configurando índice: recursos...');

        // Los campos de metadata son dinámicos: uno por cada "variable" definida
        // en el esquema de cada TipoAcervo (Recuperable o Adicional).
        $camposMetadata = TipoAcervo::whereNotNull('esquema')
            ->pluck('esquema')
            ->flatMap(function ($esquema) {
                $decodificado = is_string($esquema) ? json_decode($esquema, true) : $esquema;
                return $decodificado ?? [];
            })
            ->filter(fn($campo) => in_array(data_get($campo, 'visible'), ['Recuperable', 'Adicional'])
                && !empty(data_get($campo, 'variable')))
            ->pluck('variable')
            ->unique()
            ->map(fn($variable) => "metadata.{$variable}")
            ->values()
            ->all();

        $this->line('Campos de metadata detectados: ' . implode(', ', $camposMetadata));

        $client->index('recursos')->updateSettings([
            'searchableAttributes' => array_merge(
                ['metadata_text', 'acervo', 'coleccion', 'coleccion_nombre', 'parent_names'],
                $camposMetadata
            ),
            'filterableAttributes' => array_merge(
                ['id', 'coleccion_id', 'acervo_id', 'acervo', 'coleccion', 'status', 'archivos'],
                $camposMetadata
            ),
            'sortableAttributes' => ['coleccion_nombre', 'acervo'],
        ]);

        // Habilita el operador CONTAINS para coincidencias parciales en filtros de metadata
        try {
            $client->updateExperimentalFeatures(['containsFilter' => true]);
            $this->line('containsFilter habilitado.');
        } catch (\Throwable $e) {
            $this->warn('No se pudo habilitar containsFilter automáticamente: ' . $e->getMessage());
            $this->warn('Actívalo manualmente con: PATCH /experimental-features { "containsFilter": true }');
        }

        $this->info('¡Meilisearch se ha configurado correctamente para la intranet!');
        $this->warn('Recuerda reindexar: php artisan scout:flush "App\\Models\\Recursos" && php artisan scout:import "App\\Models\\Recursos"');
    }
}