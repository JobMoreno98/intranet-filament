<?php

namespace App\Filament\Resources\SubColeccions\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubColeccionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nombre'),
                Placeholder::make('coleccion.nombre')->disabled(),
                Textarea::make('descripcion'),
                Repeater::make('esquema')->columnSpanFull()
                    ->label('Configuración de Campos para esta Colección')
                    ->itemLabel(fn(array $state): ?string => $state['label'] ?? 'Nuevo Campo')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)->schema([
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
                            ])->columns(2),

                        // Configuración para Archivos (Extensiones)
                        TextInput::make('options.allowed_formats')
                            ->label('Formatos permitidos')
                            ->placeholder('ej: pdf, jpg, png')
                            ->visible(fn($get) => $get('type') === 'file'),

                        Section::make('Validación')->schema([
                            Toggle::make('is_required')->label('¿Es obligatorio?')->inline(),
                        ])->compact(),
                    ])->columns(3)
                    ->orderable('sort_order')
            ]);
    }
}
