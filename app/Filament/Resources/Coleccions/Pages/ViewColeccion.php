<?php

namespace App\Filament\Resources\Coleccions\Pages;

use App\Filament\Resources\Coleccions\ColeccionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewColeccion extends ViewRecord
{
    protected static string $resource = ColeccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
    
}
