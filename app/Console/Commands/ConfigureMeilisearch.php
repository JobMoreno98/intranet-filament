<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Meilisearch\Client;

#[Signature('app:configure-meilisearch')]
#[Description('Command description')]
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
        $client->index('recursos')->updateSettings([
            'searchableAttributes' => ['titulo', 'autor', 'metadata', 'coleccion_nombre', 'parent_names', 'fondo'],
            'filterableAttributes' => ['id', 'coleccion_id', 'tipo_media', 'anio', 'status', 'claveFondo'],
            'sortableAttributes' => ['anio', 'claveFondo', 'titulo']
        ]);

        $this->info('¡Meilisearch se ha configurado correctamente para la intranet!');
    }
}
