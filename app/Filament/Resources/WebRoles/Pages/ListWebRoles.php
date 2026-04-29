<?php

namespace App\Filament\Resources\WebRoles\Pages;

use App\Filament\Resources\WebRoles\WebRoleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWebRoles extends ListRecords
{
    protected static string $resource = WebRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
