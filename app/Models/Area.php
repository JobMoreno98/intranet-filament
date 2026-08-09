<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Area extends Model
{
    use Searchable;
    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->slug = Str::slug($model->nombre);
        });

        static::updating(function ($model) {
            $model->slug = Str::slug($model->nombre);
        });
    }
    public function getRouteKeyName()
    {
        return 'slug';
    }
    public function toSearchableArray(): array
    {
        $array = [
            'id'          => (int) $this->id,
            'nombre'      => $this->nombre,
            'slug'        => $this->slug,
        ];

        return $array;
    }
}
