<?php

namespace App\Filament\Resources\Recursos\Pages;

use App\Filament\Resources\Recursos\RecursosResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRecursos extends ListRecords
{
    protected static string $resource = RecursosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
