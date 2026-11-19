<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'nombres',
        'apellidos',
        'nacionalidad',
        'fecha_nacimiento',
        'sexo',
        'ciudad_pais_residencia',
        'grado_estudios',
        'institucion_adscripcion',
        'motivo_consulta',
        'tema_investigacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}