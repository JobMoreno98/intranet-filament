<?php

namespace App\Filament\Resources\Coleccions\Schemas;

use App\Models\Coleccion;
use App\Models\SubColeccion;
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
                ->maxLength(255),

            Repeater::make('sub_colecciones')
                ->relationship()
                ->schema([
                    TextInput::make('name')->live(onBlur: true) // Se activa al salir del campo
                        ->afterStateUpdated(fn(string $state, callable $set) => $set('slug', Str::slug($state))),
                    TextInput::make('slug')
                        ->disabled() // Lo dejamos deshabilitado para evitar errores humanos
                        ->dehydrated() // Asegura que se guarde en la BD aunque esté "disabled"
                        ->required()
                        ->unique(SubColeccion::class, 'slug', ignoreRecord: true),
                    Textarea::make('descripcion')->autosize()->columnSpanFull(),

                ])->columns(2)
                ->orderColumn('order')->columnSpanFull(),


        ]);
    }
}
