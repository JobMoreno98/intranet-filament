<?php

namespace App\Filament\Imports;

use App\Models\Admin;
use App\Models\Coleccion;
use App\Models\Recursos;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;

class RecursoImporter extends Importer
{
    protected static ?string $model = Recursos::class;

    protected static ?string $userModel = \App\Models\Admin::class;

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

            ImportColumn::make('tipo_media'),
        ];
    }

    public function resolveRecord(): Recursos
    {
        return new Recursos();
    }

    public function fillRecord(): void
    {
        // 1. $data tiene SOLO los campos mapeados en getColumns()
        $data = $this->data;

        // 2. 💡 $rawData tiene TODA la fila cruda del Excel (incluyendo campos dinámicos)
        $rawData = $this->originalData;

        // Actualizamos los campos base (Añadí 'tipo_media' que declaraste arriba)
        $camposBase = [
            'coleccion_id',
            'titulo',
            'autor',
            'anio',
            'tipo_media',
        ];

        $idLimpio = (int) $data['coleccion_id'];

        $coleccion = Coleccion::find($idLimpio);

        Log::info('=== DEBUG IMPORTADOR DE COLECCIÓN ===', [
            'id_buscado_original' => $data['coleccion_id'],
            'id_limpio' => $idLimpio,
            'existe' => $coleccion ? 'SÍ' : 'NO',
            'esquema' => $coleccion ? $coleccion->esquema : 'N/A',
        ]);

        if (! $coleccion) {
            throw ValidationException::withMessages([
                'coleccion_id' => 'Colección no encontrada.',
            ]);
        }

        // 3. 💡 VALIDACIÓN ESTRICTA USANDO EL EXCEL ORIGINAL ($rawData)
        foreach ($coleccion->esquema ?? [] as $campo) {
            $variable = $campo['variable']; // El nombre exacto de la cabecera en Excel

            if ($campo['is_required'] ?? false) {
                // Buscamos en $rawData en lugar de $data
                if (empty($rawData[$variable] ?? null)) {
                    throw ValidationException::withMessages([
                        // Este error saldrá exactamente en el CSV de "Filas fallidas"
                        $variable => "El campo dinámico '{$campo['label']}' es obligatorio para esta colección.",
                    ]);
                }
            }
        }

        // 4. 💡 METADATA DINÁMICA USANDO EL EXCEL ORIGINAL
        $metadata = [];
        foreach ($rawData as $key => $value) {
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
            // Guardamos tipo_media ya que lo pusiste en getColumns()
            'tipo_media' => $data['tipo_media'] ?? null,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Requerido por la clase abstracta de Filament.
     */
    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'La importación de Recuros terminó correctamente.';
        
        $successfulRows = $import->successful_rows;
        
        if ($successfulRows) {
            $body .= " {$successfulRows} filas importadas.";
        }
        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= " {$failedRowsCount} filas fallaron.";
        }

        return $body;
    }
}
