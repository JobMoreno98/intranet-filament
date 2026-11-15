<?php

namespace App\Filament\Resources\Coleccions\Schemas;

use App\Models\Coleccion;
use App\Models\SubColeccion;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\ToolbarButtonGroup;
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
                ->afterStateUpdated(function (string $state, $set) {
                    $set('slug', Str::slug($state));
                }),

            Select::make('areas_id')->relationship('area', 'nombre')->required()->label('Área')->preload()->searchable(),

            // Relación de Pertenencia Jerárquica
            Select::make('parent_id')
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
                }),
            FileUpload::make('foto')->disk('colecciones')->acceptedFileTypes(['image/*']),

            RichEditor::make('descripcion')
                ->required()->label('Descripción')
                ->columnSpanFull()
                ->toolbarButtons([
                    // 1. Formato de Texto
                    ToolbarButtonGroup::make('Formato de texto', [
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'subscript',
                        'superscript',
                        'textColor',
                        'highlight',
                        'clearFormatting',
                    ]),

                    // 2. Encabezados y Párrafos
                    ToolbarButtonGroup::make('Encabezados y Estilos', [
                        'paragraph',
                        'lead',
                        'small',
                        'h1',
                        'h2',
                        'h3',
                        'h4',
                        'h5',
                        'h6',
                    ]),

                    // 3. Alineación
                    ToolbarButtonGroup::make('Alineación', [
                        'alignStart',
                        'alignCenter',
                        'alignEnd',
                        'alignJustify',
                    ]),

                    // 4. Listas y Bloques de Estructura
                    ToolbarButtonGroup::make('Listas y Bloques', [
                        'bulletList',
                        'orderedList',
                        'blockquote',
                        'horizontalRule',
                        'details',
                    ]),

                    // 5. Gestión de Tablas
                    ToolbarButtonGroup::make('Tablas', [
                        'table',
                        'tableAddColumnBefore',
                        'tableAddColumnAfter',
                        'tableDeleteColumn',
                        'tableAddRowBefore',
                        'tableAddRowAfter',
                        'tableDeleteRow',
                        'tableMergeCells',
                        'tableSplitCell',
                        'tableToggleHeaderRow',
                        'tableToggleHeaderCell',
                        'tableDelete',
                    ]),

                    // 6. Cuadrículas / Columnas (Grids)
                    ToolbarButtonGroup::make('Cuadrículas', [
                        'grid',
                        'gridAddColumnBefore',
                        'gridAddColumnAfter',
                        'gridDeleteColumn',
                        'gridDelete',
                    ]),

                    // 7. Enlaces y Archivos
                    ToolbarButtonGroup::make('Insertar', [
                        'link',
                        'attachFiles',
                        'customBlocks',
                        'mergeTags',
                    ]),

                    // 8. Historial
                    ToolbarButtonGroup::make('Historial', [
                        'undo',
                        'redo',
                    ]),
                ])
                ->fileAttachmentsDisk('public')
                ->fileAttachmentsDirectory('editor-uploads'),

        ]);
    }
}
