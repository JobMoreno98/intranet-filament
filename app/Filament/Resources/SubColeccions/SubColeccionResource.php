<?php

namespace App\Filament\Resources\SubColeccions;

use App\Filament\Resources\SubColeccions\Pages\CreateSubColeccion;
use App\Filament\Resources\SubColeccions\Pages\EditSubColeccion;
use App\Filament\Resources\SubColeccions\Pages\ListSubColeccions;
use App\Filament\Resources\SubColeccions\Schemas\SubColeccionForm;
use App\Filament\Resources\SubColeccions\Tables\SubColeccionsTable;
use App\Models\SubColeccion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class SubColeccionResource extends Resource
{
    protected static ?string $model = SubColeccion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'SubColecciones';

    public static function form(Schema $schema): Schema
    {
        return SubColeccionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubColeccionsTable::configure($table);
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
            'index' => ListSubColeccions::route('/'),
            'create' => CreateSubColeccion::route('/create'),
            'edit' => EditSubColeccion::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
