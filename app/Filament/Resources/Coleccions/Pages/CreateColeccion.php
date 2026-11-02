<?php

namespace App\Filament\Resources\Coleccions\Pages;

use App\Filament\Resources\Coleccions\ColeccionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateColeccion extends CreateRecord
{
    protected static string $resource = ColeccionResource::class;
    public function getTitle(): string|Htmlable
    {
        return 'Crear Fondo';
    }
}
