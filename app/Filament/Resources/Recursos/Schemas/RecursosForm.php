<?php

namespace App\Filament\Resources\Recursos\Schemas;

use App\Models\Coleccion;
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
        return $schema->components([
            Section::make('Información Base')
                ->columns(2)
                ->schema([
                    Select::make('acervo_id')->label('Tipo Acervo')->relationship('acervo', 'nombre')->reactive()->required()->afterStateUpdated(fn($set) => $set('metadata', [])),
                    Select::make('coleccion_id')
                        ->label('Colección')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->options(function () {
                            $roots = Coleccion::query()->whereNull('parent_id')->orderBy('nombre')->get();

                            $options = [];

                            $addNodes = function ($node, $depth = 0) use (&$addNodes, &$options) {
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
                ])
                ->columnSpanFull(),

            // 2. DATOS DINÁMICOS (Lo que vive dentro del JSON 'metadata')
            Section::make('Metadatos Específicos del Tipo de Acervo')
                ->description('Campos definidos en el diseño del acervo.')
                ->schema([
                    Group::make()->schema(function ($get) {
                        $acervoId = $get('acervo_id');
                        if (!$acervoId) {
                            return [];
                        }

                        $coleccion = \App\Models\TipoAcervo::find($acervoId);
                        if (!$coleccion || !$coleccion->esquema) {
                            return [];
                        }

                        $camposDinamicos = [];

                        foreach ($coleccion->esquema as $campo) {
                            // IMPORTANTE: Aquí usamos el prefijo 'metadata.'
                            // para que Filament sepa que debe guardar dentro del JSON
                            $nombreVariable = "metadata.{$campo['variable']}";

                            $componente = match ($campo['type']) {
                                'text' => TextInput::make($nombreVariable),
                                'number' => TextInput::make($nombreVariable)->numeric(),
                                'textarea' => Textarea::make($nombreVariable)->autosize(),
                                'date' => DatePicker::make($nombreVariable),
                                'toggle' => Toggle::make($nombreVariable)->inline(),
                                'select' => Select::make($nombreVariable)->options(collect($campo['options']['choices'] ?? [])->pluck('label', 'value')),
                                default => TextInput::make($nombreVariable),
                            };

                            $componente->label($campo['label']);

                            if ($campo['is_required'] ?? false) {
                                $componente->required();
                            }

                            $camposDinamicos[] = $componente;
                        }

                        return $camposDinamicos;
                    })->columns(3),
                ])
                ->columnSpanFull(),
            Section::make('Archivos del Recurso')
                ->schema([
                    // 1. REPEATER para gestionar lo existente
                    Repeater::make('archivos')
                        ->visible(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord)
                        ->relationship('archivos') // Este SÍ usa la relación para MOSTRAR
                        ->schema([
                            ViewField::make('id') // Pasamos el ID para generar la ruta firmada
                                ->label('Vista Previa')
                                ->view('filament.forms.components.preview')
                                ->columnSpanFull(),
                            TextInput::make('status')
                                ->hiddenLabel()
                                ->extraAttributes(['class' => 'text-center font-bold'])
                                ->readOnly(),
                        ])
                        ->grid(4)
                        ->orderable('orden')
                        ->collapsible() // Permite colapsar para ahorrar espacio
                        ->collapsed()
                        ->itemLabel(fn(array $state): ?string => $state['nombre_archivo_original'] ?? 'Sin nombre'),

                    FileUpload::make('archivos_bulk')
                        ->label('Subida Masiva')
                        ->multiple()
                        ->disk('private')
                        ->extraAttributes([
                            'style' => '--file-upload-grid-column-width: 180px;', // Define el ancho de cada miniatura
                        ])
                        ->directory(fn($get) => 'sub_colection_' . $get('sub_colection_id'))
                        ->live() // Mantiene el estado vivo en Livewire
                        ->dehydrated(false)
                        ->panelLayout('grid')
                        ->reorderable()
                        ->helperText('Usa este campo solo para añadir archivos nuevos en lote.'),
                ])
                ->columnSpanFull(),
        ]);
    }
}
