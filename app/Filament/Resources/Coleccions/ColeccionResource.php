<?php

namespace App\Filament\Resources\Coleccions;

use App\Filament\Resources\Coleccions\Pages\CreateColeccion;
use App\Filament\Resources\Coleccions\Pages\EditColeccion;
use App\Filament\Resources\Coleccions\Pages\ListColeccions;
use App\Filament\Resources\Coleccions\Pages\ViewColeccion;
use App\Filament\Resources\Coleccions\Schemas\ColeccionForm;
use App\Filament\Resources\Coleccions\Schemas\ColeccionInfolist;
use App\Filament\Resources\Coleccions\Tables\ColeccionsTable;
use App\Models\Coleccion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ColeccionResource extends Resource
{
    protected static ?string $model = Coleccion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Coleccion';

    public static function form(Schema $schema): Schema
    {
        return ColeccionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ColeccionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ColeccionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListColeccions::route('/'),
            'create' => CreateColeccion::route('/create'),
            'view' => ViewColeccion::route('/{record}'),
            'edit' => EditColeccion::route('/{record}/edit'),
        ];
    }
}
