<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        // Obtenemos las reglas base (como 'email') y quitamos 'name' si viene requerido ahí
        $baseRules = collect($this->profileRules())->except('name')->toArray();

        Validator::make($input, [
            ...$baseRules,
            'password' => $this->passwordRules(),

            // Nuevos campos del perfil
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'nacionalidad' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'sexo' => ['required', Rule::in(['hombre', 'mujer', 'prefiero_no_decirlo'])],
            'ciudad_pais_residencia' => ['required', 'string', 'max:255'],
            'grado_estudios' => ['required', Rule::in([
                'sin_estudios',
                'educacion_basica',
                'bachillerato',
                'licenciatura',
                'posgrado',
            ])],
            'institucion_adscripcion' => ['nullable', 'string', 'max:255'],
            'motivo_consulta' => ['required', Rule::in([
                'personal',
                'escolar_tesis',
                'investigacion_academica_profesional',
            ])],
            'tema_investigacion' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => trim($input['nombres'] . ' ' . $input['apellidos']),
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            $user->profile()->create([
                'nombres' => $input['nombres'],
                'apellidos' => $input['apellidos'],
                'nacionalidad' => $input['nacionalidad'],
                'fecha_nacimiento' => $input['fecha_nacimiento'],
                'sexo' => $input['sexo'],
                'ciudad_pais_residencia' => $input['ciudad_pais_residencia'],
                'grado_estudios' => $input['grado_estudios'],
                'institucion_adscripcion' => $input['institucion_adscripcion'] ?? null,
                'motivo_consulta' => $input['motivo_consulta'],
                'tema_investigacion' => $input['tema_investigacion'] ?? null,
            ]);

            return $user;
        });
    }
}