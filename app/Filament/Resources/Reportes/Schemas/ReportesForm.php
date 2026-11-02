<?php

namespace App\Filament\Resources\Reportes\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\Model;

class ReportesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('titulo_recurso')
                    ->label('Título del Recurso')
                    ->content(fn(?Model $record): string => $record?->recurso?->metadata['titulo'] ?? 'Sin título'),

                Placeholder::make('usuario_nombre')
                    ->label('Usuario')
                    ->content(fn(?Model $record): string => $record?->user?->name ?? 'Usuario no encontrado'),

                TextInput::make('correo')
                    ->disabled(),

                Textarea::make('observaciones')
                    ->disabled(),


                DateTimePicker::make('Reportado')
                    ->disabled(),

                DateTimePicker::make('Cerrado')
                    ->disabled(),
                    
                Textarea::make('resuelto')->required(),

                Toggle::make('status')
                    ->label('Atender reporte')
                    ->live()
                    ->disabled(fn(?Model $record) => $record && $record->admin_id !== null)
                    ->afterStateUpdated(function ($set, $state) {
                        $set('admin_id', $state ? filament()->auth()->id() : null);
                    }),

                Hidden::make('admin_id'),
            ]);
    }
}
