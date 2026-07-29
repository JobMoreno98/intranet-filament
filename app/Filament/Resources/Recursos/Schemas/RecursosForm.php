<?php

namespace App\Filament\Resources\Recursos\Schemas;

use App\Filament\Forms\Components\ChunkFileUpload;
use App\Models\Coleccion;
use App\Models\TipoAcervo;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Gate;

class RecursosForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make([
                Wizard\Step::make('acervo')->label('Tipo Acervo')->schema([
                    Radio::make('acervo_id')
                        ->label('Tipo Acervo')
                        ->options(
                            TipoAcervo::pluck('nombre', 'id')->toArray()
                        )
                        ->reactive()
                        ->required()
                        ->afterStateUpdated(fn($set) => $set('metadata', []))->columns(3)
                ]),
                Wizard\Step::make('coleccion')->label('Colección')->schema([
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
                ]),

                Wizard\Step::make('datos')->label('Datos')->schema([
                    // 2. DATOS DINÁMICOS (Lo que vive dentro del JSON 'metadata')
                    Select::make('tipo_media')->live()
                        ->options([
                            'pdf' => 'Documento PDF',
                            'video' => 'Archivo de Video',
                            'audio' => 'Grabación de Audio',
                            'imagen' => 'Imagen',
                        ])->required(),
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
                                ->collapsed()->addable(false)
                                ->itemLabel(fn(array $state): ?string => $state['nombre_archivo_original'] ?? 'Sin nombre'),

                            FileUpload::make('archivos_bulk')
                                ->label('Subida de archivos')
                                ->multiple()->visible(fn($get) => in_array($get('tipo_media'), ['imagen', 'pdf']))
                                ->disk('private')
                                ->extraAttributes([
                                    'style' => '--file-upload-grid-column-width: 180px;',
                                ])
                                ->directory(fn($get) => 'colection_' . $get('colection_id'))
                                 // Mantiene el estado vivo en Livewire
                                ->dehydrated(false)
                                ->panelLayout('grid')
                                ->reorderable()
                                ->helperText('Usa este campo solo para añadir nuevos archivos.'),

                            ChunkFileUpload::make('video_bulk')->nullable()
                                ->label('Subir archivos')->acceptedFileTypes([
                                    'video/mp4',
                                    'video/quicktime',
                                    'video/x-matroska',
                                    'video/webm',
                                    'video/x-msvideo',
                                    '.mp4',
                                    '.mov',
                                    '.mkv',
                                    '.webm',
                                    '.avi',
                                    'audio/mpeg',
                                    'audio/wav',
                                    'audio/x-wav',
                                    'audio/mp4',
                                    'audio/ogg',
                                    'audio/flac',
                                    'audio/aac',
                                    '.mp3',
                                    '.wav',
                                    '.m4a',
                                    '.ogg',
                                    '.flac',
                                    '.aac',
                                ])
                                ->dehydrated(false)
                                ->extraAttributes(function ($record) {
                                    // Si el registro no existe (es modo creación), permitimos limpiar el input
                                    if (! $record) {
                                        return ['canDeleteFile' => true];
                                    }

                                    // En modo edición, verificamos la política de Shield para este registro
                                    return [
                                        'canDeleteFile' => Gate::allows('delete', $record)
                                    ];
                                })->visible(fn($get) => in_array($get('tipo_media'), ['video', 'audio'])),

                        ])
                        ->columnSpanFull(),

                ]),
            ])->columnSpanFull(),

        ]);
    }
}
