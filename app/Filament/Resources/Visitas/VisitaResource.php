<?php

namespace App\Filament\Resources;

use App\Models\Visita;
use App\Filament\Resources\Visitas\Pages\ManageVisitas;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class VisitaResource extends Resource
{
    protected static ?string $model = Visita::class;

    protected static ?string $navigationNavigation = 'Analíticas';
    protected static ?string $modelLabel = 'Seguimiento de visita';
    protected static ?string $pluralModelLabel = 'Seguimiento de visitas';


    protected static ?string $recordTitleAttribute = 'Seguimiento de visitas';
    protected static ?string $title = 'Seguimiento de visitas';
    protected static ?string $navigationLabel = 'Seguimiento de visitas';

    public static function getNavigationGroup(): ?string
    {
        return 'Analisis';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select(
                DB::raw('MAX(id) as id'),
                'url',
                DB::raw('COUNT(*) as total_visitas'),
                DB::raw('MAX(created_at) as ultima_visita')
            )
            ->groupBy('url');
    }

    /**
     * 2. Configuración de la lista unificada
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Columna para identificar el tipo de contenido de forma visual
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->state(function (Visita $record): string {
                        if (str_contains($record->url, '/recurso/')) return 'Documento ';
                        if (str_contains($record->url, '/coleccion/')) return 'Colección';
                        if ($record->url === '/') return 'Inicio';
                        return 'Otro';
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Documento / Libro' => 'danger',
                        'Colección' => 'warning',
                        'Inicio' => 'success',
                        default => 'gray',
                    }),

                // Mostramos la URL limpia o procesada
                TextColumn::make('url')
                    ->label('Ruta del Contenido')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                // Mostramos el conteo acumulado proveniente del DB::raw
                TextColumn::make('total_visitas')
                    ->label('Visitas')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                // Fecha de la última vez que alguien entró a ese link
                TextColumn::make('ultima_visita')
                    ->label('Último Acceso')
                    ->dateTime('d/m/Y H:i A')
                    ->sortable()
                    ->color('gray'),
            ])
            // Ordenamos por defecto las páginas más populares primero
            ->defaultSort('total_visitas', 'desc')
            ->filters([
                // Aquí puedes agregar filtros por fecha si lo deseas más adelante
            ])
            ->actions([
                // Deshabilitamos edición y solo dejamos un botón para ver detalles si fuera necesario
                ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageVisitas::route('/'),
        ];
    }
}
