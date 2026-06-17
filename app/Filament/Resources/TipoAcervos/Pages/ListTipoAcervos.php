<?php

namespace App\Filament\Resources\TipoAcervos\Pages;

use App\Filament\Resources\TipoAcervos\TipoAcervoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTipoAcervos extends ListRecords
{
    protected static string $resource = TipoAcervoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //CreateAction::make(),
        ];
    }
}
