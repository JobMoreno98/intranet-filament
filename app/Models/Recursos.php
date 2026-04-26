<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recursos extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'assets_procesados' => 'array',
        'path_original' => 'array',

    ];



    public function coleccion(): BelongsTo
    {
        // Un ítem pertenece a una colección
        return $this->belongsTo(Coleccion::class, 'coleccion_id');
    }

    public function archivos()
    {
        return $this->hasMany(RecursosArchivos::class)->orderBy('orden');
    }
}
