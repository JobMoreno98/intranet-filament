<?php

namespace App\Filament\Resources\Recursos\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RecursosForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información Base')
                    ->columns(3)
                    ->schema([
                        Select::make('coleccion_id')
                            ->label('Colección')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn($set) => $set('metadata', []))
                            ->options(function ($record) {
                                $roots = \App\Models\Coleccion::query()
                                    ->whereNull('parent_id')
                                    ->orderBy('nombre')
                                    ->get();

                                $options = [];
                                $currentId = $record ? $record->id : null;

                                $addNodes = function ($node, $depth = 0) use (&$addNodes, &$options, $currentId) {
                                    if ($currentId && $node->id === $currentId) {
                                        return;
                                    }

                                    $prefix = str_repeat('— ', $depth);
                                    $options[$node->id] = $prefix . $node->nombre;

                                    foreach ($node->children()->orderBy('nombre')->get() as $child) {
                                        $addNodes($child, $depth + 1);
                                    }
                                };

                                foreach ($roots as $root) {
                                    $addNodes($root);
                                }

                                return $options;
                            }),

                        TextInput::make('titulo')->required()->label('Título'),
                        TextInput::make('autor')->label('Autor Principal'),

                        TextInput::make('anio')
                            ->numeric()
                            ->label('Año'),

                        TextInput::make('fondo')
                            ->required()
                            ->label('Fondo'),

                        TextInput::make('claveFondo')
                            ->required()
                            //->unique(ignoreRecord: true)
                            ->integer()
                            ->label('Clave Fondo'),

                        Select::make('tipo_media')
                            ->options([
                                'pdf' => 'Documento PDF',
                                'video' => 'Archivo de Video',
                                'audio' => 'Grabación de Audio',
                                'imagen' => 'Imagen',
                            ])->required(),
                    ])->columnSpanFull(),

                // 2. DATOS DINÁMICOS (Lo que vive dentro del JSON 'metadata')
                Section::make('Metadatos Específicos de la Colección')
                    ->description('Campos adicionales definidos en el diseño de la colección.')
                    ->schema([
                        Group::make()
                            ->schema(function ($get) {
                                $coleccionId = $get('coleccion_id');
                                if (!$coleccionId) return [];

                                $coleccion = \App\Models\Coleccion::find($coleccionId);
                                if (!$coleccion || !$coleccion->esquema) return [];

                                $camposDinamicos = [];

                                foreach ($coleccion->esquema as $campo) {
                                    // IMPORTANTE: Aquí usamos el prefijo 'metadata.' 
                                    // para que Filament sepa que debe guardar dentro del JSON
                                    $nombreVariable = "metadata.{$campo['variable']}";

                                    $componente = match ($campo['type']) {
                                        'text'     => TextInput::make($nombreVariable),
                                        'number'   => TextInput::make($nombreVariable)->numeric(),
                                        'textarea' => Textarea::make($nombreVariable)->autosize(),
                                        'date'     => DatePicker::make($nombreVariable),
                                        'toggle'   => Toggle::make($nombreVariable)->inline(),
                                        'select'   => Select::make($nombreVariable)
                                            ->options(collect($campo['options']['choices'] ?? [])
                                                ->pluck('label', 'value')),
                                        default    => TextInput::make($nombreVariable),
                                    };

                                    $componente->label($campo['label']);

                                    if ($campo['is_required'] ?? false) {
                                        $componente->required();
                                    }

                                    $camposDinamicos[] = $componente;
                                }

                                return $camposDinamicos;
                            })
                            ->columns(1),
                    ])->columnSpanFull(),
                Section::make('Archivos del Recurso')
                    ->schema([
                        // 1. REPEATER para gestionar lo existente
                        Repeater::make('archivos')->visible(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord)
                            ->relationship('archivos') // Este SÍ usa la relación para MOSTRAR
                            ->schema([
                                ViewField::make('id') // Pasamos el ID para generar la ruta firmada
                                    ->label('Vista Previa')
                                    ->view('filament.forms.components.preview')
                                    ->columnSpanFull(),
                                /*
                                FileUpload::make('path_original')
                                    ->image()
                                    ->hiddenLabel()
                                    ->disk('private')
                                    //->visibility('private')
                                    ->disabled(),
*/
                                TextInput::make('status')
                                    ->hiddenLabel()
                                    ->extraAttributes(['class' => 'text-center font-bold'])
                                    ->readOnly(),
                            ])
                            ->grid(4)
                            ->orderable('orden')
                            ->collapsible() // Permite colapsar para ahorrar espacio
                            ->collapsed()
                            ->addActionLabel('Añadir archivo individual')
                            ->itemLabel(fn(array $state): ?string => $state['nombre_archivo_original'] ?? 'Sin nombre'),

                        // 2. CAMPO CIEGO para subidas masivas nuevas
                        FileUpload::make('archivos_bulk')
                            ->label('Subida Masiva')
                            ->multiple()
                            ->disk('private')->extraAttributes([
                                'style' => '--file-upload-grid-column-width: 180px;', // Define el ancho de cada miniatura
                            ])
                            ->directory(fn($get) => 'sub_colection_' . $get('sub_colection_id'))
                            ->live() // Mantiene el estado vivo en Livewire
                            ->dehydrated(false)->panelLayout('grid')->reorderable()
                            ->helperText('Usa este campo solo para añadir archivos nuevos en lote.'),
                    ])->columnSpanFull()
            ]);
    }
}
