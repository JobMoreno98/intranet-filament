<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubColeccion extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'esquema' => 'array',
    ];
    public function items(): HasMany
    {
        return $this->hasMany(Recursos::class);
    }


    public function coleccion(): BelongsTo
    {
        return $this->belongsTo(Coleccion::class);
    }
}
