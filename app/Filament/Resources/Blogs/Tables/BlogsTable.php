<?php

namespace App\Filament\Resources\Blogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BlogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("nombre")->searchable()->sortable(),
                TextColumn::make('tags')
                    ->badge()
                    ->separator(',')
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
            ]);
    }
}
