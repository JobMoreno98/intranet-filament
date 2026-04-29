<?php

namespace App\Filament\Resources\WebRoles\Pages;

use App\Filament\Resources\WebRoles\WebRoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWebRole extends EditRecord
{
    protected static string $resource = WebRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
