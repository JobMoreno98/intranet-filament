<?php

namespace App\Filament\Resources\Recursos\Tables;

use App\Filament\Imports\RecursoImporter;
use App\Models\Recursos;
use App\Services\OcrReproceso;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Ramsey\Collection\Collection;

class RecursosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->withExists([
                'reportes as tiene_reportes_pendientes' => fn($q) => $q->pendiente(),
            ]))->recordClasses(fn($record) => $record->tiene_reportes_pendientes
                ? 'bg-danger-50 dark:bg-danger-950/20'
                : null)
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
                    ->label('Título')->limit(200)->wrap()
                    ->getStateUsing(fn($record) => $record->metadata['titulo'] ?? 'Sin título')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('metadata->titulo', 'like', "%{$search}%");
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('metadata->titulo', $direction);
                    }),

                TextColumn::make('coleccion.nombre')->label('Fondo')->sortable()->searchable(),
                TextColumn::make('acervo.nombre')->label('Tipo Documental')->sortable()->searchable(),
                ToggleColumn::make('visible')->label('Público'),
                ToggleColumn::make('fijado')->label('Fijado'),
                IconColumn::make('tiene_reportes_pendientes')
                    ->label('Reporte')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->action(
                        Action::make('verReportes')
                            ->modalHeading(fn($record) => 'Reportes del recurso #' . $record->id)
                            ->modalContent(fn($record) => view('filament.modals.reportes-pendientes', [
                                'reportes' => $record->reportes()->pendiente()->with('user:id,name')->latest()->get(),
                            ]))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Cerrar')
                    )
                    ->tooltip(fn($state) => $state ? 'Tiene reportes sin atender' : 'Sin reportes pendientes'),

            ])
            ->filters([
                //TrashedFilter::make(),
                SelectFilter::make('acervo_id')
                    ->relationship('acervo', 'nombre') // 'acervo' es el nombre de tu función en el modelo Recursos
                    ->label('Tipo Documental')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('coleccion_id')
                    ->relationship('coleccion', 'nombre') // 'coleccion' es el nombre de tu función en el modelo Recursos
                    ->label('Fondo')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('reportes_pendientes')
                    ->label('Reportes sin atender')
                    ->placeholder('Todos')
                    ->trueLabel('Con reportes pendientes')
                    ->falseLabel('Sin reportes pendientes')
                    ->queries(
                        true: fn(Builder $query) => $query->conReportesPendientes(),
                        false: fn(Builder $query) => $query->sinReportesPendientes(),
                        blank: fn(Builder $query) => $query,
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('reocr')
                    ->label('Reescanear OCR')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    // Solo aparece en recursos de tipo imagen (sin consultas extra por fila).
                    ->visible(fn(Recursos $record) => $record->tipo_media === OcrReproceso::TIPO_PERMITIDO)
                    ->requiresConfirmation()
                    ->modalHeading('Volver a escanear con OCR')
                    ->modalDescription('Se volverá a ejecutar el OCR de las imágenes de este recurso. El texto actual solo se reemplaza si el nuevo escaneo detecta texto.')
                    ->action(fn(Recursos $record) => static::notificarReocr(
                        OcrReproceso::encolar([$record->id])
                    )),
            ])->headerActions([
                ImportAction::make()
                    ->importer(RecursoImporter::class)->chunkSize(50)
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    BulkAction::make('reocr')
                        ->label('Reescanear OCR')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->modalHeading('Volver a escanear con OCR')
                        ->modalDescription('Solo se reprocesan los recursos de tipo imagen; los demás se omiten.')
                        ->deselectRecordsAfterCompletion()
                        ->action(fn(Collection $records) => static::notificarReocr(
                            OcrReproceso::encolar($records->pluck('id')->all())
                        )),
                ]),
            ])
            ->deferLoading()
            ->emptyStateHeading('Sin recursos')
            ->emptyStateDescription('Aún no hay contenido.');
    }
    protected static function notificarReocr(array $r): void
    {
        if ($r['encolados'] === 0) {
            Notification::make()
                ->title('No se envió nada a OCR')
                ->body($r['omitidos'] > 0
                    ? 'Esas imágenes ya estaban en la cola.'
                    : 'Ninguno de los recursos es de tipo imagen con archivos procesados.')
                ->warning()
                ->send();

            return;
        }

        $detalle = array_filter([
            $r['omitidos'] ? "{$r['omitidos']} ya estaban en la cola." : null,
            $r['recursos_no_imagen'] ? "{$r['recursos_no_imagen']} recurso(s) que no son imagen se omitieron." : null,
        ]);

        Notification::make()
            ->title("{$r['encolados']} imagen(es) enviadas a OCR")
            ->body($detalle ? implode(' ', $detalle) : null)
            ->success()
            ->send();
    }
}
