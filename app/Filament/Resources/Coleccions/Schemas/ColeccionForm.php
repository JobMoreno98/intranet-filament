<?php

namespace App\Filament\Resources\Coleccions\Schemas;

use App\Models\Coleccion;
use App\Models\SubColeccion;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ColeccionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nombre')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                // En v5 usamos las clases Get y Set inyectadas explícitamente
                ->afterStateUpdated(function (string $state, $set) {
                    $set('slug', Str::slug($state));
                }),

            TextInput::make('slug')
                ->disabled()
                ->dehydrated()
                ->required()
                // Asegúrate de apuntar a la tabla correcta de Colecciones
                ->unique(ignoreRecord: true),

            // Relación de Pertenencia Jerárquica
            Select::make('parent_id')
                // Ya no usamos ->relationship(...) directamente para las opciones
                // porque necesitamos formatearlas recursivamente.
                ->relationship(name: 'parent', titleAttribute: 'nombre')
                ->placeholder('Ninguna (Colección Principal)')
                ->label('Pertenece a (Colección Padre)')
                ->searchable() // Sigue siendo buscable
                ->preload()    // Sigue pre-cargando para velocidad

                // Formateamos las opciones manualmente
                ->options(function ($record) {
                    // Obtenemos solo las colecciones raíz (las que no tienen padre)
                    $roots = \App\Models\Coleccion::query()
                        ->whereNull('parent_id')
                        ->orderBy('nombre')
                        ->get();

                    $options = [];

                    // Si estamos editando, guardamos el ID para excluirlo y evitar bucles
                    $currentId = $record ? $record->id : null;

                    // Llamamos a la función recursiva para aplanar el árbol con guiones
                    foreach ($roots as $root) {
                        // Pasamos una función anónima (Closure) para la recursividad
                        $addNodes = function ($node, $depth = 0) use (&$addNodes, &$options, $currentId) {
                            // 1. Evitamos añadir el registro actual o sus descendientes como opciones
                            if ($currentId && $node->id === $currentId) {
                                return;
                            }

                            // 2. Creamos el prefijo de indentación (guiones)
                            $prefix = str_repeat('— ', $depth);

                            // 3. Añadimos la opción al arreglo plano
                            $options[$node->id] = $prefix . $node->nombre;

                            // 4. Procesamos los hijos recursivamente subiendo el nivel de profundidad
                            foreach ($node->children()->orderBy('nombre')->get() as $child) {
                                $addNodes($child, $depth + 1);
                            }
                        };

                        // Iniciamos la recursión con cada raíz
                        $addNodes($root);
                    }

                    return $options;
                })
                ->columnSpanFull(),

            Textarea::make('descripcion')
                ->autosize()->label('Descripción'),
            FileUpload::make('foto')->disk('colecciones'),
            Repeater::make('esquema')->columnSpanFull()
                ->label('Configuración de campos para esta colección')
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
                ])->columns(3),
            
        ]);
    }
}
