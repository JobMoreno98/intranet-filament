<?php

namespace App\Filament\Imports;

use App\Models\Coleccion;
use App\Models\Recursos;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class RecursoImporter extends Importer
{
    protected static ?string $model = Recursos::class;

    public static function getColumns(): array
    {
        return [

            ImportColumn::make('coleccion_id')
                ->requiredMapping()
                ->numeric(),

            ImportColumn::make('titulo')
                ->requiredMapping(),

            ImportColumn::make('autor'),

            ImportColumn::make('anio'),

        ];
    }

    public function resolveRecord(): Recursos
    {
        return new Recursos();
    }

    public function fillRecord(): void
    {
        $data = $this->data;

        $camposBase = [
            'coleccion_id',
            'titulo',
            'autor',
            'anio',
            'archivo',
        ];

        $coleccion = Coleccion::find(
            $data['coleccion_id']
        );

        if (! $coleccion) {
            throw ValidationException::withMessages([
                'coleccion_id' => 'Colección no encontrada.',
            ]);
        }

        // Validar campos dinámicos requeridos
        foreach ($coleccion->esquema ?? [] as $campo) {

            if (
                ($campo['is_required'] ?? false)
                && empty($data[$campo['variable']] ?? null)
            ) {

                throw ValidationException::withMessages([
                    $campo['variable'] =>
                    "El campo {$campo['label']} es obligatorio.",
                ]);
            }
        }

        // Metadata dinámica
        $metadata = [];

        foreach ($data as $key => $value) {

            if (! in_array($key, $camposBase)) {
                $metadata[$key] = $value;
            }
        }

        // Llenar modelo
        $this->record->fill([
            'coleccion_id' => $data['coleccion_id'],
            'titulo' => $data['titulo'],
            'autor' => $data['autor'] ?? null,
            'anio' => $data['anio'] ?? null,
            'archivo_original' => $data['archivo'] ?? null,

            'metadata' => $metadata,
        ]);


        Notification::make()
            ->title('Importación finalizada')
            ->body('Se importó correctamente el recurso.')
            ->success()
            ->send();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'La importación terminó correctamente.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= " {$failedRowsCount} filas fallaron.";
        }

        return $body;
    }
}
