<?php

namespace App\Filament\Resources\Blogs\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BlogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('')
                    ->description('Contenido del blog')
                    ->schema([
                        FileUpload::make('portada')->required()->image()->disk('public')->directory('blogs')->acceptedFileTypes(['image/*']),
                        TextInput::make('nombre')->required(),
                        TagsInput::make('tags'),
                        RichEditor::make('contenido')->required()->columnSpanFull()
                    ]),

            ])->columns(1);
    }
}
