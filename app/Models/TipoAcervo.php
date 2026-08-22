<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class TipoAcervo extends Model
{
    use Searchable;

    protected $guarded = [];

    protected $casts = [
        'esquema' => 'array',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Recursos::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'areas_id');
    }
}
