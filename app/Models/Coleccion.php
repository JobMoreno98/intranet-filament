<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Coleccion extends Model
{

    use Searchable;
    
    protected $casts = [
        'esquema' => 'array',
    ];

    protected $guarded = [];
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Coleccion::class, 'parent_id');
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
    }
}
