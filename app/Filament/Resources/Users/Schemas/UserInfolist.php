<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos personales')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('profile.nombres')
                            ->label('Nombre(s)')
                            ->placeholder('—'),

                        TextEntry::make('profile.apellidos')
                            ->label('Apellido(s)')
                            ->placeholder('—'),

                        TextEntry::make('profile.nacionalidad')
                            ->label('Nacionalidad')
                            ->placeholder('—'),

                        TextEntry::make('profile.fecha_nacimiento')
                            ->label('Fecha de nacimiento')
                            ->date('d/m/Y')
                            ->placeholder('—'),

                        TextEntry::make('profile.sexo')
                            ->label('Sexo')
                            ->formatStateUsing(fn(?string $state): string => match ($state) {
                                'hombre' => 'Hombre',
                                'mujer' => 'Mujer',
                                'prefiero_no_decirlo' => 'Prefiero no decirlo',
                                default => 'No registrado',
                            }),

                        TextEntry::make('profile.ciudad_pais_residencia')
                            ->label('Ciudad y país de residencia')
                            ->placeholder('—'),
                    ]),

                Section::make('Perfil académico y motivo de consulta')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('profile.grado_estudios')
                            ->label('Grado de estudios')
                            ->badge()
                            ->formatStateUsing(fn(?string $state): string => match ($state) {
                                'sin_estudios' => 'Sin estudios',
                                'educacion_basica' => 'Educación básica',
                                'bachillerato' => 'Bachillerato',
                                'licenciatura' => 'Licenciatura',
                                'posgrado' => 'Posgrado',
                                default => 'No registrado',
                            }),

                        TextEntry::make('profile.motivo_consulta')
                            ->label('Motivo de consulta')
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn(?string $state): string => match ($state) {
                                'personal' => 'Personal',
                                'escolar_tesis' => 'Escolar o tesis',
                                'investigacion_academica_profesional' => 'Investigación académica o profesional',
                                default => 'No registrado',
                            }),

                        TextEntry::make('profile.institucion_adscripcion')
                            ->label('Institución de adscripción')
                            ->placeholder('No especificada')
                            ->columnSpanFull(),

                        TextEntry::make('profile.tema_investigacion')
                            ->label('Tema de investigación')
                            ->placeholder('No especificado')
                            ->columnSpanFull(),
                    ]),

                Section::make('Datos de la cuenta')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('email')
                            ->label('Correo electrónico')
                            ->icon('heroicon-m-envelope')
                            ->copyable(),

                        IconEntry::make('email_verified_at')
                            ->label('Correo verificado')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),

                        TextEntry::make('created_at')
                            ->label('Fecha de registro')
                            ->dateTime('d/m/Y H:i'),
                    ]),
            ]);
    }
}
