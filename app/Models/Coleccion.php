<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coleccion extends Model
{
    protected $guarded = [];
    protected $casts = [
        'esquema' => 'array',
    ];


    public function sub_colecciones(): HasMany
    {
        return $this->hasMany(SubColeccion::class);
    }
}
