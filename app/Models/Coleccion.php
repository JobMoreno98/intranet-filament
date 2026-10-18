<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class Coleccion extends Model
{

    use Searchable;

    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Coleccion::class, 'parent_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'areas_id');
    }

    // Relación para subcolecciones (Hacia abajo)
    public function children(): HasMany
    {
        return $this->hasMany(Coleccion::class, 'parent_id');
    }

    public function toSearchableArray(): array
    {
        $array = [
            'id'          => (int) $this->id,
            'nombre'      => $this->nombre,
            'slug'        => $this->slug,
            'descripcion' => $this->descripcion,
            'parent_id'   => $this->parent_id ? (int) $this->parent_id : null,
            'tipo' => 'coleccion'
        ];

        // 2. Construimos el árbol jerárquico hacia arriba (Ancestros)
        $ancestrosNombres = [];
        $ancestrosIds = [];
        $actual = $this->parent;

        while ($actual) {
            $ancestrosNombres[] = $actual->nombre;
            $ancestrosIds[]     = (int) $actual->id;
            $actual             = $actual->parent; // Sube un nivel en el árbol
        }

        $array['parent_names'] = $ancestrosNombres; // Ej: ["Historia", "Fondos Políticos"]
        $array['parent_ids']   = $ancestrosIds;     // Ej: [14, 2]

        return $array;
    }

    protected static function booted()
    {
        // Cada vez que se guarde (cree o edite) una colección
        static::saved(function (Coleccion $coleccion) {
            $coleccion->children->each(function ($child) {
                $child->searchable(); // Esto lo re-indexa en Meilisearch
            });
        });

        static::creating(function ($model) {
            $model->slug = static::generateUniqueSlug($model->nombre);
        });

        static::updating(function ($model) {
            // Solo regenera el slug si el nombre cambió
            if ($model->isDirty('nombre')) {
                $model->slug = static::generateUniqueSlug($model->nombre, $model->id);
            }
        });

        static::saved(fn() => Cache::forget('colecciones.mapa_rutas'));
        static::deleted(fn() => Cache::forget('colecciones.mapa_rutas'));
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

    public function items(): HasMany
    {
        return $this->hasMany(Recursos::class);
    }

    protected static function mapaRutas(): array
    {
        return Cache::remember('colecciones.mapa_rutas', 600, function () {
            return static::query()
                ->get(['id', 'nombre', 'parent_id'])
                ->mapWithKeys(fn($c) => [
                    $c->id => ['nombre' => $c->nombre, 'parent_id' => $c->parent_id],
                ])
                ->all();
        });
    }

    public function getRutaAttribute(): string
    {
        $mapa = static::mapaRutas();
        $partes = [];
        $visitados = [];
        $id = $this->id;
        
        while ($id && isset($mapa[$id]) && !isset($visitados[$id])) {
            $visitados[$id] = true;
            array_unshift($partes, $mapa[$id]['nombre']);
            $id = $mapa[$id]['parent_id'];
        }

        return implode(' > ', $partes);
    }
}
