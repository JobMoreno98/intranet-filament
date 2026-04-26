<?php

namespace App\Filament\Resources\Coleccions\Pages;

use App\Filament\Resources\Coleccions\ColeccionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListColeccions extends ListRecords
{
    protected static string $resource = ColeccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
