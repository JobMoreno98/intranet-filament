<?php

namespace App\Filament\Resources\Visitas\Pages;

use App\Filament\Resources\VisitaResource as ResourcesVisitaResource;
use App\Filament\Resources\Visitas\VisitaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageVisitas extends ManageRecords
{
    protected static string $resource = ResourcesVisitaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //CreateAction::make(),
        ];
    }
}
