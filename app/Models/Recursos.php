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

    public function sub_coleccion(): BelongsTo
    {
        return $this->belongsTo(SubColeccion::class, 'sub_colection_id');
    }

    public function archivos()
    {
        return $this->hasMany(RecursosArchivos::class)->orderBy('orden');
    }
}
