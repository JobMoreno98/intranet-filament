<?php

namespace App\Filament\Resources\Reportes;

use App\Filament\Resources\Reportes\Pages\CreateReportes;
use App\Filament\Resources\Reportes\Pages\EditReportes;
use App\Filament\Resources\Reportes\Pages\ListReportes;
use App\Filament\Resources\Reportes\Schemas\ReportesForm;
use App\Filament\Resources\Reportes\Tables\ReportesTable;
use App\Models\Reportes;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReportesResource extends Resource
{
    protected static ?string $model = Reportes::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Reportes';
    protected static ?string $title = 'Reportes';
    protected static ?string $navigationLabel = 'Reportes';
    protected static ?string $pluralModelLabel = 'Reportes';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'Contenidos';
    }

    public static function form(Schema $schema): Schema
    {
        return ReportesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportesTable::configure($table);
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
            'index' => ListReportes::route('/'),
            //'create' => CreateReportes::route('/create'),
            'edit' => EditReportes::route('/{record}/edit'),
        ];
    }
}
