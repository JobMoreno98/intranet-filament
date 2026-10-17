<?php

namespace App\Filament\Resources\Recursos\Tables;

use App\Filament\Imports\RecursoImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RecursosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("id")->sortable(),
                TextColumn::make('metadata_identificador')
                    ->label('Identificador')
                    ->getStateUsing(fn($record) => $record->metadata['identificador'] ?? 'Sin identificador')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('metadata->identificador', 'like', "%{$search}%");
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('metadata->identificador', $direction);
                    }),

                TextColumn::make('metadata_titulo') // Nombre inventado para la columna
                    ->label('Título')
                    ->getStateUsing(fn($record) => $record->metadata['titulo'] ?? 'Sin título')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('metadata->titulo', 'like', "%{$search}%");
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('metadata->titulo', $direction);
                    }),

                TextColumn::make('coleccion.nombre')->label('Fondo')->sortable()->searchable(),
                TextColumn::make('acervo.nombre')->label('Tipo Acervo')->sortable()->searchable(),
                ToggleColumn::make('visible')->label('Público'),
                ToggleColumn::make('fijado')->label('Fijado')

            ])
            ->filters([
                //TrashedFilter::make(),
                SelectFilter::make('acervo_id')
                    ->relationship('acervo', 'nombre') // 'acervo' es el nombre de tu función en el modelo Recursos
                    ->label('Tipo de Acervo')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('coleccion_id')
                    ->relationship('coleccion', 'nombre') // 'coleccion' es el nombre de tu función en el modelo Recursos
                    ->label('Fondo')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])->headerActions([
                ImportAction::make()
                    ->importer(RecursoImporter::class)
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
