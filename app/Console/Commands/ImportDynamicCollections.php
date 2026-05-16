<?php

namespace App\Console\Commands;

use App\Models\DynamicCollection;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:import-dynamic-collections')]
#[Description('Command description')]
class ImportDynamicCollections extends Command
{
    //protected $signature = 'scout:import-dynamic';
    protected $description = 'Indexa las tablas configuradas en Meilisearch utilizando un modelo dinámico';

    public function handle()
    {
        // 1. Traer la lista de las 92 tablas únicas
        $tablas = DB::connection('mysql2')->table('colecciones')->whereNotNull('tabla')->distinct()->pluck('tabla');

        $this->info('Iniciando la indexación de ' . $tablas->count() . ' tablas en Meilisearch...');

        foreach ($tablas as $tabla) {
            $this->comment("Indexando tabla: {$tabla}...");

            // Instanciamos el modelo dinámico apuntando a la tabla en cuestión
            $modeloInstance = new DynamicCollection();
            $modeloInstance->setCustomTable($tabla);

            // Verificamos si la tabla tiene registros
            $totalRegistros = DB::connection('mysql2')->table($tabla)->count();
            $this->comment("Indexando : {$totalRegistros}...");
            if ($totalRegistros === 0) {
                $this->warn("La tabla {$tabla} está vacía. Saltando...");
                continue;
            }

            DB::connection('mysql2')
                ->table($tabla)
                ->orderBy('IdElemento') 
                ->lazy(500)
                ->chunk(500)
                ->each(function ($chunk) use ($tabla) {
                    $coleccionModelos = collect();

                    foreach ($chunk as $registro) {
                        $arrayRegistro = (array) $registro;

                        // Mapeamos IdElemento a id para Meilisearch
                        if (isset($arrayRegistro['IdElemento'])) {
                            $arrayRegistro['id'] = $arrayRegistro['IdElemento'];
                        } else {
                            $arrayRegistro['id'] = md5(json_encode($arrayRegistro));
                        }

                        $modelo = new DynamicCollection($arrayRegistro);
                        $modelo->setCustomTable($tabla);
                        $coleccionModelos->push($modelo);
                    }

                    $coleccionModelos->searchable();
                });

            $this->info("¡Tabla {$tabla} indexada con éxito!");
        }

        $this->info('Proceso de indexación global completado.');
    }
}
