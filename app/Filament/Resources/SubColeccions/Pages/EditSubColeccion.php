<?php

namespace App\Filament\Resources\SubColeccions\Pages;

use App\Filament\Resources\SubColeccions\SubColeccionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSubColeccion extends EditRecord
{
    protected static string $resource = SubColeccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
