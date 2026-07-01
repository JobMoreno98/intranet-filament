<?php

namespace App\Filament\Resources\Recursos\Pages;

use App\Filament\Resources\Recursos\Pages\Concerns\EnviaArchivosAGo;
use App\Filament\Resources\Recursos\RecursosResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditRecursos extends EditRecord
{
    use EnviaArchivosAGo;

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
        $rawState = $this->form->getRawState();

        // 1. Extraemos los nuevos archivos subidos masivamente
        // getRawState() nos asegura obtener las rutas temporales
        $archivosParaProcesar = $rawState['archivos_bulk'] ?? [];
        $rutaVideoTemporal = $rawState['video_bulk'] ?? null;

        if (empty($archivosParaProcesar) && empty($rutaVideoTemporal)) {
            return;
        }

        $cantiad = $record->archivos()->count() + 1;

        // 2. Procesamos cada archivo nuevo (imágenes / PDFs)
        foreach (array_values($archivosParaProcesar) as $index => $rutaTemporal) {

            // 1. Creamos el registro primero para tener el ID del archivo
            $nuevoArchivo = $record->archivos()->create([
                'path_original' => $rutaTemporal, // Temporal
                'nombre_archivo_original' => basename($rutaTemporal),
                'status' => 'en_cola',
                'orden' => $cantiad,
            ]);

            // 2. Definimos la estructura: coleccion/id_recurso/id_archivo/
            $extension = pathinfo($rutaTemporal, PATHINFO_EXTENSION);
            $nombreLimpio = Str::slug($record->titulo) . "_" . $cantiad . ".{$extension}";
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

            $cantiad = $cantiad + 1;

            // 5. Mandamos a Go
            $this->enviarAGo($nuevoArchivo, $record);
        }

        // 3. Procesamos el video grande (subido por chunks), si lo hay
        if ($rutaVideoTemporal) {
            $nuevoVideo = $record->archivos()->create([
                'path_original' => $rutaVideoTemporal, // Temporal, en disco 'public'
                'nombre_archivo_original' => basename($rutaVideoTemporal),
                'status' => 'en_cola',
                'orden' => $cantiad,
            ]);

            $extension = pathinfo($rutaVideoTemporal, PATHINFO_EXTENSION);
            $nombreLimpio = Str::slug($record->titulo) . "_" . $cantiad . ".{$extension}";
            $rutaFinal = "{$record->coleccion->slug}/{$record->id}/{$nuevoVideo->id}/{$nombreLimpio}";

            // El chunk uploader deja el archivo ensamblado en el disco 'public',
            // no en 'private' como archivos_bulk, así que cruzamos discos.
            if ($this->moverArchivoCrossDisk('public', $rutaVideoTemporal, 'private', $rutaFinal)) {
                $nuevoVideo->update([
                    'path_original' => $rutaFinal,
                    'nombre_archivo_original' => $nombreLimpio,
                ]);
            }

            $this->enviarAGo($nuevoVideo, $record);
        }
    }
}