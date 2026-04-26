<?php

namespace App\Filament\Resources\Recursos\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\URL;

class RecursosInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Galería de Archivos')
                    ->schema([
                        RepeatableEntry::make('archivos') // Relación con la tabla hija
                            ->schema([
                                ImageEntry::make('assets_procesados.thumb')
                                    ->label('Vista Previa')
                                    // IMPORTANTE: No usamos ->disk() aquí porque no queremos que Laravel 
                                    // intente buscar la ruta directamente. Queremos que el navegador pida la URL firmada.
                                    ->imageUrl(fn($record) => URL::temporarySignedRoute(
                                        'media.stream',
                                        now()->addMinutes(60),
                                        ['archivo_id' => $record->id, 'tipo' => 'thumb'] // Enviamos el parámetro 'tipo'
                                    ))
                                    ->width(300)
                                    ->height(200)
                                    ->openUrlInNewTab()
                                    ->url(fn($record) => URL::temporarySignedRoute(
                                        'media.stream',
                                        now()->addMinutes(60),
                                        ['archivo_id' => $record->id, 'tipo' => 'main'] // Al hacer clic, ver la grande
                                    )),
                                // Si no se ha procesado, mostramos un placeholder
                                //->defaultImageUrl(url('/images/placeholder-processing.png')),

                                TextEntry::make('nombre_archivo_original')
                                    ->label('Nombre')
                                    ->limit(20),

                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'listo' => 'success',
                                        'en_cola' => 'warning',
                                        'error' => 'danger',
                                        default => 'gray',
                                    }),
                            ])
                            ->grid(3) // Esto crea la apariencia de galería (4 columnas)
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
            ]);
    }
}
