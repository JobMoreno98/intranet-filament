<?php

namespace App\Filament\Resources\Recursos;

use App\Filament\Resources\Recursos\Pages\CreateRecursos;
use App\Filament\Resources\Recursos\Pages\EditRecursos;
use App\Filament\Resources\Recursos\Pages\ListRecursos;
use App\Filament\Resources\Recursos\Pages\ViewRecursos;
use App\Filament\Resources\Recursos\Schemas\RecursosForm;
use App\Filament\Resources\Recursos\Schemas\RecursosInfolist;
use App\Filament\Resources\Recursos\Tables\RecursosTable;
use App\Models\Recursos;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecursosResource extends Resource
{
    protected static ?string $model = Recursos::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Contenidos';
    protected static ?string $title = 'Contenidos';
    protected static ?string $navigationLabel = 'Contenidos';
    protected static ?string $pluralModelLabel = 'Contenidos';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Contenidos';
    }

    public static function form(Schema $schema): Schema
    {
        return RecursosForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RecursosInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecursosTable::configure($table);
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
            'index' => ListRecursos::route('/'),
            'create' => CreateRecursos::route('/create'),
            'view' => ViewRecursos::route('/{record}'),
            'edit' => EditRecursos::route('/{record}/edit'),
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
