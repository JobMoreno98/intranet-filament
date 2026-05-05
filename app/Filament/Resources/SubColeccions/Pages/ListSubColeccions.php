<?php

namespace App\Filament\Resources\SubColeccions\Pages;

use App\Filament\Resources\SubColeccions\SubColeccionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubColeccions extends ListRecords
{
    protected static string $resource = SubColeccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
