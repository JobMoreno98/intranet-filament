<?php

namespace App\Filament\Imports;

use App\Models\Coleccion;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ColeccionImporter extends Importer
{
    protected static ?string $model = Coleccion::class;
    protected static ?string $userModel = \App\Models\Admin::class;

    public static function getColumns(): array
    {
        return [

            ImportColumn::make('nombre')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('descripcion')->label('Descripción'),

            ImportColumn::make('parent_id')->label('Padre')
                ->numeric()
                ->rules(['nullable', 'exists:coleccions,id']),

            ImportColumn::make('areas_id')->label('Área')->numeric()->rules(['nullable', 'exists:areas,id']),
        ];
    }

    public function saveRecord(): void
    {
        Coleccion::withoutSyncingToSearch(fn() => $this->record->save());
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'La importación de fondos  terminó correctamente.';

        $successfulRows = $import->successful_rows;

        if ($successfulRows) {
            $body .= " {$successfulRows} filas importadas.";
        }
        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= " {$failedRowsCount} filas fallaron.";
        }

        return $body;
    }

    public function resolveRecord(): ?Coleccion
    {
        return new Coleccion();
    }
}
