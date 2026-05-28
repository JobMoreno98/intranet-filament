<?php

namespace App\Filament\Imports;

use App\Models\Coleccion;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ColeccionImporter extends Importer
{
    protected static ?string $model = Coleccion::class;

    public static function getColumns(): array
    {
        return [

            ImportColumn::make('nombre')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('slug')
                ->rules(['max:255'])
                ->fillRecordUsing(function ($record, $state) {
                    $record->slug = $state ?: str($record->nombre)->slug();
                }),

            ImportColumn::make('descripcion'),

            ImportColumn::make('parent_id')
                ->numeric()
                ->rules(['nullable', 'exists:coleccions,id'])
        ];
    }

    public function resolveRecord(): ?Coleccion
    {
        return Coleccion::firstOrNew([
            'slug' => $this->data['slug']
                ?? str($this->data['nombre'])->slug(),
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'La importación de colecciones terminó correctamente.';

        $successfulRows = $import->successful_rows;

        if ($successfulRows) {
            $body .= " {$successfulRows} filas importadas.";
        }

        return $body;
    }
}
