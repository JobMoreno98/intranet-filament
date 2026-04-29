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

        $totalRecursos = 100;
        $batchSize = 5;

        $fondos = ['Acervo General', 'Manuscritos', 'Mapoteca', 'Fototeca', 'Archivo Histórico'];
        $autores = ['Juan Rulfo', 'Mariano Azuela', 'Agustín Yáñez', 'Anónimo', 'Gobierno de Jalisco'];

        $this->command->getOutput()->progressStart($totalRecursos);

        for ($i = 0; $i < ($totalRecursos / $batchSize); $i++) {
            $recursosBatch = [];

            for ($j = 0; $j < $batchSize; $j++) {
                $titulo = "Registro Histórico " . Str::random(8) . " - " . ($i * $batchSize + $j);

                $recursosBatch[] = [
                    'coleccion_id' => 1,
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

            DB::table('recursos')->insert($recursosBatch);

            $lastIds = DB::table('recursos')
                ->orderBy('id', 'desc')
                ->limit($batchSize)
                ->pluck('id');

            foreach ($lastIds as $recursoId) {
                // Generamos 3000 archivos por recurso en bloques de 500
                for ($chunkStart = 1; $chunkStart <= 3000; $chunkStart += 500) {
                    $archivosBatch = [];

                    for ($k = $chunkStart; $k < $chunkStart + 500 && $k <= 3000; $k++) {
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

                    DB::table('recursos_archivos')->insert($archivosBatch);
                }
            }

            $this->command->getOutput()->progressAdvance($batchSize);
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info("Seeder completado: {$totalRecursos} recursos y " . ($totalRecursos * 3000) . " archivos.");
    }
}
