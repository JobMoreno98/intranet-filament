<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LargeDatasetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Desactivar el log de consultas para ahorrar memoria RAM
        DB::connection()->disableQueryLog();

        $totalRecursos = 300000;
        $batchSize = 1000; 

        $fondos = ['Acervo General', 'Manuscritos', 'Mapoteca', 'Fototeca', 'Archivo Histórico'];
        $autores = ['Juan Rulfo', 'Mariano Azuela', 'Agustín Yáñez', 'Anónimo', 'Gobierno de Jalisco'];

        $this->command->getOutput()->progressStart($totalRecursos);

        for ($i = 0; $i < ($totalRecursos / $batchSize); $i++) {
            $recursosBatch = [];

            for ($j = 0; $j < $batchSize; $j++) {
                $titulo = "Registro Histórico " . Str::random(8) . " - " . ($i * $batchSize + $j);

                $recursosBatch[] = [
                    'coleccion_id' => 1, // Asegúrate de que exista este ID en tu tabla colecciones
                    'fondo' => $fondos[array_rand($fondos)],
                    'claveFondo' => rand(100, 999),
                    'tipo_media' => 'imagen',
                    'titulo' => $titulo,
                    'autor' => $autores[array_rand($autores)],
                    'anio' => rand(1850, 1950),
                    'metadata' => json_encode([
                        'descripcion' => 'Documento escaneado de prueba',
                        'ubicación' => 'Estante ' . rand(1, 50),
                        'folios' => rand(1, 200)
                    ]),
                    'status' => 'listo',
                    'hash_archivo' => hash('sha256', $titulo),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insertar bloque de Recursos
            DB::table('recursos')->insert($recursosBatch);

            // Obtener los IDs generados para crear los archivos relacionados
            // (Asumimos IDs autoincrementales recientes)
            $lastIds = DB::table('recursos')
                ->orderBy('id', 'desc')
                ->limit($batchSize)
                ->pluck('id');

            $archivosBatch = [];
            foreach ($lastIds as $recursoId) {
                // Creamos 2 archivos por cada recurso (Total 600k archivos)
                for ($k = 1; $k <= 2; $k++) {
                    $archivosBatch[] = [
                        'recursos_id' => $recursoId,
                        'nombre_archivo_original' => "página_{$k}.jpg",
                        'path_original' => "acervo/recurso_{$recursoId}/archivo_{$k}.jpg",
                        'status' => 'listo',
                        'assets_procesados' => json_encode([
                            'main' => "items/prueba/{$recursoId}/{$k}/main.webp",
                            'thumb' => "items/prueba/{$recursoId}/{$k}/thumb.webp"
                        ]),
                        'orden' => $k,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Insertar bloque de Archivos
            DB::table('recursos_archivos')->insert($archivosBatch);

            $this->command->getOutput()->progressAdvance($batchSize);
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info("Seeder completado: 300k recursos y 600k archivos.");
    }
}
