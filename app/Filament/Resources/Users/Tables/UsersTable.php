<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('profile.nombres')
                    ->label('Nombre(s)')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('profile.apellidos')
                    ->label('Apellido(s)')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->icon('heroicon-m-envelope')
                    ->copyable()
                    ->searchable()
                    ->sortable(),

                IconColumn::make('email_verified_at')
                    ->label('Verificado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('profile.grado_estudios')
                    ->label('Grado de estudios')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'sin_estudios' => 'Sin estudios',
                        'educacion_basica' => 'Educación básica',
                        'bachillerato' => 'Bachillerato',
                        'licenciatura' => 'Licenciatura',
                        'posgrado' => 'Posgrado',
                        default => 'No registrado',
                    })
                    ->color(fn(?string $state): string => match ($state) {
                        'posgrado' => 'primary',
                        'licenciatura' => 'info',
                        'bachillerato' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('profile.motivo_consulta')
                    ->label('Motivo de consulta')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'personal' => 'Personal',
                        'escolar_tesis' => 'Escolar o tesis',
                        'investigacion_academica_profesional' => 'Investigación académica / prof.',
                        default => 'No registrado',
                    })
                    ->color('gray'),

                TextColumn::make('profile.institucion_adscripcion')
                    ->label('Institución')
                    ->placeholder('Sin adscripción')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('profile.ciudad_pais_residencia')
                    ->label('Residencia')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('profile.nacionalidad')
                    ->label('Nacionalidad')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Fecha de registro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('email_verified_at')
                    ->label('Correo verificado')
                    ->nullable()
                    ->trueLabel('Verificados')
                    ->falseLabel('Pendientes de verificar'),

                SelectFilter::make('grado_estudios')
                    ->label('Grado de estudios')
                    ->relationship('profile', 'grado_estudios')
                    ->options([
                        'sin_estudios' => 'Sin estudios',
                        'educacion_basica' => 'Educación básica',
                        'bachillerato' => 'Bachillerato',
                        'licenciatura' => 'Licenciatura',
                        'posgrado' => 'Posgrado',
                    ]),

                SelectFilter::make('motivo_consulta')
                    ->label('Motivo de consulta')
                    ->relationship('profile', 'motivo_consulta')
                    ->options([
                        'personal' => 'Personal',
                        'escolar_tesis' => 'Escolar o tesis',
                        'investigacion_academica_profesional' => 'Investigación académica o profesional',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                //EditAction::make(),
            ])
            ->toolbarActions([
                /*
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),*/]);
    }
}
