<?php

namespace App\Filament\Resources\Coleccions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Schema;

class ColeccionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nombre'),
                ViewEntry::make('estructura_hijos')->label('Sub Colecciones')
                    ->label('Estructura Jerárquica de Subcolecciones')
                    ->view('filament.infolists.components.coleccion-tree')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
