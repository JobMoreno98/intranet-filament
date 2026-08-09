<?php

namespace App\Filament\Resources\Areas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')->label('Nombre')->required(),
                TextInput::make('ubicacion')->label('Ubicacion')->required()
            ]);
    }
}
