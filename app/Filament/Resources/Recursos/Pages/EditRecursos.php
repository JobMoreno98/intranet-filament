<?php

namespace App\Filament\Resources\Recursos\Pages;

use App\Filament\Resources\Recursos\RecursosResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditRecursos extends EditRecord
{
    protected static string $resource = RecursosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        // 1. Extraemos los nuevos archivos subidos masivamente
        // getRawState() nos asegura obtener las rutas temporales
        $archivosParaProcesar = $this->form->getRawState()['archivos_bulk'] ?? [];

        if (empty($archivosParaProcesar)) {
            return;
        }
        $cantiad =  $record->archivos()->count() + 1;
        // 2. Procesamos cada archivo nuevo
        foreach (array_values($archivosParaProcesar) as $index => $rutaTemporal) {

            // 1. Creamos el registro primero para tener el ID del archivo
            $nuevoArchivo = $record->archivos()->create([
                'path_original' => $rutaTemporal, // Temporal
                'nombre_archivo_original' => basename($rutaTemporal),
                'status' => 'en_cola',
                'orden' => $index,
            ]);

            // 2. Definimos la estructura: coleccion/id_recurso/id_archivo/
            $extension = pathinfo($rutaTemporal, PATHINFO_EXTENSION);
            $nombreLimpio = Str::slug($record->titulo) . "_" . $cantiad . ".{$extension}";
            // RUTA FINAL: coleccion-slug/15/105/titulo.jpg
            $rutaFinal = "{$record->sub_coleccion->slug}/{$record->id}/{$nuevoArchivo->id}/{$nombreLimpio}";

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
            $cantiad = $cantiad + 1;

            // 5. Mandamos a Go
            $this->enviarAGo($nuevoArchivo, $record);
        }
    }

    /**
     * Centralizamos el envío a Redis para mantener el orden
     */
    private function enviarAGo($archivo, $recurso): void
    {
        $payload = [
            'archivo_id'     => $archivo->id,
            'recurso_id'     => $recurso->id,
            'path'           => storage_path('app/private/' . $archivo->path_original),
            'coleccion_slug' => $recurso->sub_coleccion->slug,
            'tipo'           => $recurso->tipo_media ?? 'imagen',
            'action'         => 'update'
        ];

        Redis::lpush('cola_procesamiento', json_encode($payload));
    }
}
