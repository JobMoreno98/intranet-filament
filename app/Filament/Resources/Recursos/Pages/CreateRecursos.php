<?php

namespace App\Filament\Resources\Recursos\Pages;

use App\Filament\Resources\Recursos\RecursosResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateRecursos extends CreateRecord
{
    protected static string $resource = RecursosResource::class;

    public $archivosParaProcesar = [];

    /**
     * Paso 1: Interceptamos los datos ANTES de que Filament los limpie.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Rescatamos los archivos del estado actual del formulario (incluye los IDs temporales)
        $this->archivosParaProcesar = $this->form->getRawState()['archivos_bulk'] ?? [];

        // Limpiamos $data para evitar errores de columna inexistente en 'recursos'
        unset($data['archivos_bulk']);

        return $data;
    }

    /**
     * Paso 2: Creación atómica con renombrado y encolado.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {

            $record = static::getModel()::create($data);

            foreach (array_values($this->archivosParaProcesar) as $index => $rutaTemporal) {
                $nuevoArchivo = $record->archivos()->create([
                    'path_original' => $rutaTemporal, // Temporal
                    'nombre_archivo_original' => basename($rutaTemporal),
                    'status' => 'en_cola',
                    'orden' => $index,
                ]);

                // 2. Definimos la estructura: coleccion/id_recurso/id_archivo/
                $extension = pathinfo($rutaTemporal, PATHINFO_EXTENSION);
                $nombreLimpio = Str::slug($record->titulo) . "_".$index. ".{$extension}";

                // RUTA FINAL: coleccion-slug/15/105/titulo.jpg
                
                $rutaFinal = "{$record->coleccion->slug}/{$record->id}/{$nuevoArchivo->id}/{$nombreLimpio}";

                // 3. Movemos el archivo a su nueva casa
                if (Storage::disk('private')->exists($rutaTemporal)) {
                    // Creamos el directorio si no existe (Storage::move lo hace automáticamente)
                    Storage::disk('private')->move($rutaTemporal, $rutaFinal);

                    // 4. Actualizamos el registro con la ruta real de Sharding
                    $nuevoArchivo->update([
                        'path_original' => $rutaFinal,
                        'nombre_archivo_original' => $nombreLimpio
                    ]);
                }

                // 5. Mandamos a Go
                $this->enviarAGo($nuevoArchivo, $record);
            }

            return $record;
        });
    }

    private function enviarAGo($archivo, $recurso): void
    {
        $payload = [
            'archivo_id'     => $archivo->id,
            'recurso_id'     => $recurso->id,
            'path'           => storage_path('app/private/' . $archivo->path_original),
            'coleccion_slug' => $recurso->sub_coleccion->slug,
            'tipo'           => $recurso->tipo_media ?? 'imagen',
        ];

        Redis::lpush('cola_procesamiento', json_encode($payload));
    }
}
