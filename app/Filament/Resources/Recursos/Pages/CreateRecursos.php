<?php

namespace App\Filament\Resources\Recursos\Pages;

use App\Filament\Resources\Recursos\Pages\Concerns\EnviaArchivosAGo;
use App\Filament\Resources\Recursos\RecursosResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateRecursos extends CreateRecord
{
    use EnviaArchivosAGo;

    protected static string $resource = RecursosResource::class;

    public $archivosParaProcesar = [];

    public $videoParaProcesar = null;

    /**
     * Paso 1: Interceptamos los datos ANTES de que Filament los limpie.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Rescatamos los archivos del estado actual del formulario (incluye los IDs temporales)
        $this->archivosParaProcesar = $this->form->getRawState()['archivos_bulk'] ?? [];
        $this->videoParaProcesar = $this->form->getRawState()['video_bulk'] ?? null;

        // Limpiamos $data para evitar errores de columna inexistente en 'recursos'
        unset($data['archivos_bulk'], $data['video_bulk']);

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
                $this->enviarAGo($nuevoArchivo, $record, 'create');
            }

            // Procesamos el video grande (subido por chunks), si lo hay
            if ($this->videoParaProcesar) {
                $rutaVideoTemporal = $this->videoParaProcesar;
                $orden = count($this->archivosParaProcesar);

                $nuevoVideo = $record->archivos()->create([
                    'path_original' => $rutaVideoTemporal, // Temporal, en disco 'public'
                    'nombre_archivo_original' => basename($rutaVideoTemporal),
                    'status' => 'en_cola',
                    'orden' => $orden,
                ]);

                $extension = pathinfo($rutaVideoTemporal, PATHINFO_EXTENSION);
                $nombreLimpio = Str::slug($record->titulo) . "_" . $orden . ".{$extension}";
                $rutaFinal = "{$record->coleccion->slug}/{$record->id}/{$nuevoVideo->id}/{$nombreLimpio}";

                // El chunk uploader deja el archivo ensamblado en el disco 'public',
                // no en 'private' como archivos_bulk, así que cruzamos discos.
                if ($this->moverArchivoCrossDisk('public', $rutaVideoTemporal, 'private', $rutaFinal)) {
                    $nuevoVideo->update([
                        'path_original' => $rutaFinal,
                        'nombre_archivo_original' => $nombreLimpio,
                    ]);
                }

                $this->enviarAGo($nuevoVideo, $record, 'create');
            }

            return $record;
        });
    }
}