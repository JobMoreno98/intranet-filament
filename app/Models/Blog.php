<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Illuminate\Support\Str;

class Blog extends Model
{
    use Searchable;

    protected $guarded = [];
    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->slug = static::generateUniqueSlug($model->nombre);
        });

        static::updating(function ($model) {
            if ($model->isDirty('nombre')) {
                $model->slug = static::generateUniqueSlug($model->nombre, $model->id);
            }
        });
    }

    protected static function generateUniqueSlug(string $nombre, $ignoreId = null): string
    {
        $slug = Str::slug($nombre);
        $originalSlug = $slug;
        $count = 1;

        $query = static::where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;

            $query = static::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
        }

        return $slug;
    }
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function toSearchableArray()
    {
        return [
            'nombre' => $this->nombre,
            'contenido' => strip_tags($this->contenido),
            'tags' => $this->tags,
        ];
    }

}
