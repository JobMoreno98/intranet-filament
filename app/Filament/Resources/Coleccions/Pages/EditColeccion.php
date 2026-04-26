<?php

namespace App\Filament\Resources\Coleccions\Pages;

use App\Filament\Resources\Coleccions\ColeccionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditColeccion extends EditRecord
{
    protected static string $resource = ColeccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
