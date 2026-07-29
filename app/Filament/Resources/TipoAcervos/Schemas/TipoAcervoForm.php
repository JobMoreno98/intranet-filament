<?php

namespace App\Filament\Resources\TipoAcervos\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;


class TipoAcervoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre'),
                Repeater::make('esquema')->columnSpanFull()
                    ->label('Configuración de campos para esta colección')
                    ->itemLabel(fn(array $state): ?string => $state['label'] ?? 'Nuevo Campo')
                    ->collapsible()->collapsed()
                    ->schema([
                        Grid::make(1)->schema([
                            TextInput::make('label')->required()->label('Nombre del Campo (Label)'),
                            TextInput::make('variable')->required()->label('ID Interno (Variable)'),
                        ]),

                        Select::make('type')->label('Tipo de Dato')
                            ->options([
                                'text' => 'Texto Corto',
                                'textarea' => 'Texto Largo',
                                'number' => 'Número / Año',
                                'select' => 'Lista Desplegable',
                                'file' => 'Archivo Adjunto Extra',
                                'date' => 'Fecha Histórica',
                                'toggle' => 'Interruptor (Si/No)',
                            ])
                            ->live()
                            ->required(),

                        // Configuración de Opciones para Selects (Tus 'choices')
                        Repeater::make('options.choices')
                            ->label('Opciones del Menú')
                            ->visible(fn($get) => $get('type') === 'select')
                            ->schema([
                                TextInput::make('value')->required()->label('Valor'),
                                TextInput::make('label')->required()->label('Texto'),
                            ])->columns(2)->columnSpanFull(),

                        // Configuración para Archivos (Extensiones)
                        TextInput::make('options.allowed_formats')
                            ->label('Formatos permitidos')
                            ->placeholder('ej: pdf, jpg, png')
                            ->visible(fn($get) => $get('type') === 'file'),
                        Grid::make(2)->schema([
                             Select::make('visible')->options([
                                'Recuperable' => 'Recuperable',
                                'Adicional' => 'Adicional',
                                'Interno' => 'Interno'
                            ]),
                            Toggle::make('is_required')->label('¿Es obligatorio?')
                           
                        ])->columns(1)
                    ])->columns(3)->grid(2),
            ]);
    }
}
