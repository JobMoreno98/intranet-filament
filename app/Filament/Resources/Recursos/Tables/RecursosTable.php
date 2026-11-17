<?php

namespace App\Filament\Resources\Recursos\Tables;

use App\Filament\Imports\RecursoImporter;
use App\Models\Recursos;
use App\Services\OcrReproceso;
use App\Services\ReprocesoArchivos;
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

                Action::make('reocrFallidos')
                    ->label('Reescanear solo sin OCR')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->visible(fn(Recursos $record) => $record->tipo_media === OcrReproceso::TIPO_PERMITIDO)
                    ->requiresConfirmation()
                    ->modalHeading('Reescanear las imágenes sin OCR')
                    ->modalDescription('Solo se reprocesan las imágenes de este recurso cuyo texto OCR está vacío.')
                    ->action(fn(Recursos $record) => static::notificarReocr(
                        OcrReproceso::encolar([$record->id], soloSinOcr: true)
                    )),

                Action::make('reprocesarErrores')
                    ->label('Reprocesar con error o atascados')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    // PDF queda fuera: reprocesarlo duplicaría páginas (ver ReprocesoArchivos).
                    ->visible(fn(Recursos $record) => in_array($record->tipo_media, ReprocesoArchivos::TIPOS_PERMITIDOS, true))
                    ->requiresConfirmation()
                    ->modalHeading('Reprocesar con error o atascados')
                    ->modalDescription('Se vuelven a mandar al worker (conversión completa y OCR) los archivos con status "error" y, en imágenes, los que llevan más de 2 horas en cola.')
                    ->action(fn(Recursos $record) => static::notificarReproceso(
                        ReprocesoArchivos::reprocesarErrores([$record->id])
                    )),

                ViewAction::make(),
                EditAction::make(),
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

                    BulkAction::make('reocrFallidos')
                        ->label('Reescanear solo sin OCR')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->requiresConfirmation()
                        ->modalHeading('Reescanear las imágenes sin OCR')
                        ->modalDescription('Solo se reprocesan las imágenes de tipo imagen cuyo texto OCR está vacío.')
                        ->deselectRecordsAfterCompletion()
                        ->action(fn(Collection $records) => static::notificarReocr(
                            OcrReproceso::encolar($records->pluck('id')->all(), soloSinOcr: true)
                        )),

                    BulkAction::make('reprocesarErrores')
                        ->label('Reprocesar con error o atascados')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->requiresConfirmation()
                        ->modalHeading('Reprocesar con error o atascados')
                        ->modalDescription('Archivos con error (imagen, video, audio) e imágenes con más de 2 horas en cola. Los recursos PDF se omiten.')
                        ->deselectRecordsAfterCompletion()
                        ->action(fn(Collection $records) => static::notificarReproceso(
                            ReprocesoArchivos::reprocesarErrores($records->pluck('id')->all())
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
                    : 'No hay imágenes procesadas que cumplan (tipo imagen y, si elegiste "solo sin OCR", con OCR vacío).')
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

    protected static function notificarReproceso(array $r): void
    {
        $detalle = array_filter([
            $r['atascadas'] ? "{$r['atascadas']} llevaban mucho tiempo en cola." : null,
            $r['duplicados'] ? "{$r['duplicados']} ya estaban en la cola." : null,
            $r['sin_original'] ? "{$r['sin_original']} no tienen el archivo original en disco." : null,
            $r['recursos_omitidos'] ? "{$r['recursos_omitidos']} recurso(s) omitidos por tipo (PDF)." : null,
        ]);

        if ($r['encolados'] === 0) {
            Notification::make()
                ->title('No se reprocesó nada')
                ->body($detalle ? implode(' ', $detalle) : 'No hay archivos con error ni imágenes atascadas en los recursos seleccionados.')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title("{$r['encolados']} archivo(s) enviados a reprocesar")
            ->body($detalle ? implode(' ', $detalle) : null)
            ->success()
            ->send();
    }
}
