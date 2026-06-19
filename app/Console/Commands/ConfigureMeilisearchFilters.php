<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Meilisearch\Client as MeilisearchClient;

#[Signature('app:configure-meilisearch-filters')]
#[Description('Command description')]
class ConfigureMeilisearchFilters extends Command
{
    /**
     * Execute the console command.
     */
public function handle()
    {
        $this->info('Conectando con Meilisearch...');
        $meili = new MeilisearchClient(config('scout.meilisearch.host'), config('scout.meilisearch.key'));

        $this->info('Obteniendo variables dinámicas desde los esquemas de TipoAcervo...');
        
        // 1. Recopilamos absolutamente todas las variables de todos los acervos existentes en tu base de datos
        $todosLosCampos = ['coleccion_id', 'acervo_id', 'status'];
        
        $acervos = \App\Models\TipoAcervo::whereNotNull('esquema')->get();
        foreach ($acervos as $acervo) {
            $esquema = is_string($acervo->esquema) ? json_decode($acervo->esquema, true) : (array) $acervo->esquema;
            if (is_array($esquema)) {
                foreach ($esquema as $campo) {
                    if (isset($campo['variable'])) {
                        $todosLosCampos[] = "metadata.{$campo['variable']}";
                    }
                }
            }
        }

        // Eliminamos duplicados por si varios acervos comparten campos (como 'anio' o 'lugar')
        $todosLosCampos = array_values(array_unique($todosLosCampos));

        $this->info('Enviando los siguientes atributos como filtrables a Meilisearch:');
        $this->line(implode(', ', $todosLosCampos));

        // 2. Enviamos la configuración definitiva a Meilisearch
        $meili->index('recursos')->updateFilterableAttributes($todosLosCampos);

        $this->info('¡Operación exitosa! Meilisearch está procesando la indexación de los filtros en segundo plano.');
        return Command::SUCCESS;
    }
}
