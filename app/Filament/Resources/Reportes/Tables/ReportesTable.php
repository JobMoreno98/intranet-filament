<?php

namespace App\Filament\Resources\Reportes\Tables;

use App\Models\Recursos;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReportesTable
{
    public static function configure(Table $table): Table
    {
        return $table->modifyQueryUsing(fn(Builder $query) => $query->with([
            'recurso:id,metadata',
            'user:id,name',
        ]))
            ->columns([
                TextColumn::make('recurso.metadata')
                    ->label('Título del recurso')
                    ->state(fn($record) => $record->recurso?->metadata['titulo'] ?? 'Sin título')
                    ->limit(60)
                    ->tooltip(fn($record) => $record->recurso?->metadata['titulo'] ?? null)
                    ->wrap()
                    ->searchable(query: fn(Builder $query, string $search): Builder => $query->whereHas(
                        'recurso',
                        fn(Builder $q) => $q->where('metadata->titulo', 'like', "%{$search}%")
                    ))
                    ->sortable(query: fn(Builder $query, string $direction): Builder => $query->orderBy(
                        Recursos::select('metadata->titulo')
                            ->whereColumn('recursos.id', 'reportes.recursos_id'), // ajusta al nombre real de tu FK
                        $direction
                    )),

                TextColumn::make('correo')
                    ->label('Correo')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('observaciones') // si la columna aún se llama "obseravciones", usa ese nombre
                    ->label('Observaciones')
                    ->limit(80)
                    ->tooltip(fn($record) => $record->observaciones)
                    ->wrap()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->placeholder('Anónimo')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Reportado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25, 50])
            ->deferLoading()
            ->striped()
            ->emptyStateHeading('Sin reportes')
            ->emptyStateDescription('Cuando alguien reporte un error en un recurso aparecerá aquí.');
    }
}
