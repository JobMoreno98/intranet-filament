<?php

namespace App\Filament\Resources\Recursos\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArchivosRelationManager extends RelationManager
{
    protected static string $relationship = 'archivos';

    protected static ?string $title = 'Archivos del Recurso';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nombre_archivo_original')
                ->label('Nombre del archivo')
                ->required()
                ->maxLength(255),
            TextInput::make('status')
                ->label('Estado')
                ->disabled(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->select([
                'id',
                'recursos_id',
                'nombre_archivo_original',
                'status',
                'orden',
            ]))
            ->reorderable('orden')
            ->defaultSort('orden')
            ->defaultPaginationPageOption(12)
            ->contentGrid([
                'sm' => 2,
                'md' => 3,
                'xl' => 4,
            ])
            ->columns([
                Stack::make([
                    ViewColumn::make('id')
                        ->view('filament.forms.components.preview'),
                    TextColumn::make('nombre_archivo_original')
                        ->searchable()
                        ->limit(30)
                        ->weight('bold')
                        ->alignCenter(),
                    TextColumn::make('status')
                        ->badge()
                        ->alignCenter()
                        ->color(fn (string $state): string => match ($state) {
                            'listo', 'completado' => 'success',
                            'procesando', 'pendiente' => 'warning',
                            'error' => 'danger',
                            default => 'gray',
                        }),
                ])->space(2),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Eliminar')
                    ->modalHeading('Eliminar archivo')
                    ->modalDescription('¿Estás seguro de que deseas eliminar este archivo? Esta acción no se puede deshacer.'),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->label('Eliminar seleccionados'),
            ]);
    }
}