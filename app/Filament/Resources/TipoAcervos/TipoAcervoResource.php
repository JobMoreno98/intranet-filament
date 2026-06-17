<?php

namespace App\Filament\Resources\TipoAcervos;

use App\Filament\Resources\TipoAcervos\Pages\CreateTipoAcervo;
use App\Filament\Resources\TipoAcervos\Pages\EditTipoAcervo;
use App\Filament\Resources\TipoAcervos\Pages\ListTipoAcervos;
use App\Filament\Resources\TipoAcervos\Schemas\TipoAcervoForm;
use App\Filament\Resources\TipoAcervos\Tables\TipoAcervosTable;
use App\Models\TipoAcervo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TipoAcervoResource extends Resource
{
    protected static ?string $model = TipoAcervo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Tipos de Acervos';
    protected static ?string $title = 'Tipos de Acervos';
    protected static ?string $navigationLabel = 'Tipos de Acervos';
    protected static ?string $pluralModelLabel = 'Tipos de Acervos';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Contenidos';
    }

    public static function form(Schema $schema): Schema
    {
        return TipoAcervoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TipoAcervosTable::configure($table);
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
            'index' => ListTipoAcervos::route('/'),
            //'create' => CreateTipoAcervo::route('/create'),
            //'edit' => EditTipoAcervo::route('/{record}/edit'),
        ];
    }
}
