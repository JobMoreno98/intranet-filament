<?php

namespace App\Filament\Resources\Reportes\Tables;

use App\Models\Recursos;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Reportes;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Support\Facades\DB;

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

                IconColumn::make('status')
                    ->label('Estado')
                    ->state(fn(Reportes $record) => filled($record->resuelto))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('resuelto')
                    ->label('Resolución')
                    ->limit(80)
                    ->tooltip(fn(Reportes $record) => $record->resuelto)
                    ->wrap()
                    ->placeholder('Pendiente')
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('pendientes')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Pendientes')
                    ->falseLabel('Resueltos')
                    ->queries(
                        true: fn(Builder $query) => $query->pendiente(),
                        false: fn(Builder $query) => $query->whereNotNull('resuelto')->where('resuelto', '!=', ''),
                        blank: fn(Builder $query) => $query,
                    )
                    ->default(true),
            ])
            ->recordActions([
                //EditAction::make(),

                Action::make('resolver')
                    ->label('Resolver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Reportes $record) => blank($record->resuelto))
                    ->modalHeading('Resolver reporte')
                    ->modalDescription(fn(Reportes $record) => $record->observaciones)
                    ->modalSubmitActionLabel('Marcar como resuelto')
                    ->schema([
                        Textarea::make('resuelto')
                            ->label('Notas de resolución')
                            ->helperText('Describe qué se hizo para atender el reporte.')
                            ->required()
                            ->minLength(5)
                            ->maxLength(2000)
                            ->rows(5),
                    ])
                    ->action(function (Reportes $record, array $data) {
                        $resuelto = DB::transaction(function () use ($record, $data) {
                            $reporte = Reportes::lockForUpdate()->find($record->id);

                            if (filled($reporte->resuelto)) {
                                return false;
                            }

                            $reporte->update([
                                'resuelto' => trim($data['resuelto']),
                                'admin_id' => filament()->auth()->id(),
                                'status' => 1
                            ]);

                            return true;
                        });

                        $resuelto
                            ? Notification::make()->title('Reporte resuelto')->success()->send()
                            : Notification::make()->title('Este reporte ya fue resuelto por otra persona')->warning()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25, 50])
            ->deferLoading()
            ->striped()
            ->emptyStateHeading('Sin reportes')
            ->emptyStateDescription('Cuando alguien reporte un error en un recurso aparecerá aquí.');
    }
}
