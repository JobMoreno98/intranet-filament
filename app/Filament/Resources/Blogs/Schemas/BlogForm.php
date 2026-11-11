<?php

namespace App\Filament\Resources\Blogs\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Components\RichEditor\ToolbarButtonGroup;

class BlogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('')
                    ->description('Contenido del blog')
                    ->schema([
                        FileUpload::make('portada')
                            ->required()->image()->disk('public')
                            ->directory('blogs')->acceptedFileTypes(['image/*'])
                            ->imageEditor()->imageAspectRatio('2:1')
                            ->automaticallyOpenImageEditorForAspectRatio(),
                        TextInput::make('nombre')->required(),
                        TagsInput::make('tags'),
                        RichEditor::make('contenido')
                            ->required()
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
                        Hidden::make('fijado')->default(true),
                    ]),

            ])->columns(1);
    }
}
