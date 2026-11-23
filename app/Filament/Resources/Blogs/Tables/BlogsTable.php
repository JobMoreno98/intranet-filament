<?php

namespace App\Filament\Resources\Blogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class BlogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap()
                    ->lineClamp(2)
                    ->tooltip(fn($record): ?string => strlen($record->nombre) > 60 ? $record->nombre : null),

                TextColumn::make('tags')
                    ->label('Etiquetas')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(fn($record): array => is_array($record->tags) ? array_values($record->tags) : [])
                    ->wrap()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->searchable(),
                    
                ToggleColumn::make('fijado')
                    ->label('Fijado')
                    ->alignCenter(),
            ])
            ->defaultSort('created_at', 'desc')
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
            ]);
    }
}
