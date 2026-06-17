<?php

namespace App\Filament\Resources\TipoAcervos\Pages;

use App\Filament\Resources\TipoAcervos\TipoAcervoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTipoAcervo extends EditRecord
{
    protected static string $resource = TipoAcervoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //DeleteAction::make(),
        ];
    }
}
