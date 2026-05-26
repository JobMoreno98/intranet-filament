<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Recursos extends Model
{
    use SoftDeletes;
    use Searchable;

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'assets_procesados' => 'array',
        'path_original' => 'array',

    ];

    public function coleccion(): BelongsTo
    {
        return $this->belongsTo(Coleccion::class, 'coleccion_id');
    }

    public function archivos()
    {
        return $this->hasMany(RecursosArchivos::class)->orderBy('orden');
    }

    public function toSearchableArray(): array
    {
        // 1. Cargamos datos básicos planos y limpios
        $array = [
            'id'          => (int) $this->id,
            'titulo'      => $this->titulo,
            'autor'       => $this->autor,
            'fondo'       => $this->fondo,
            'claveFondo'  => (int) $this->claveFondo,
            'tipo_media'  => $this->tipo_media,
            'anio'        => $this->anio ? (int) $this->anio : null,
            'status'      => $this->status,
        ];

        // 2. Herencia de búsqueda: Indexamos datos de la Colección a la que pertenece
        if ($this->coleccion) {
            $array['coleccion_id']     = (int) $this->coleccion_id;
            $array['coleccion_nombre'] = $this->coleccion->nombre;

            // Si recuerdas la escalera de la consulta anterior, puedes heredar los padres:
            // Esto permite que si buscan "Historia", aparezcan los libros dentro de sus subcolecciones
            $array['parent_names']     = $this->coleccion->toSearchableArray()['parent_names'] ?? [];
        } else {
            $array['coleccion_id']     = null;
            $array['coleccion_nombre'] = null;
            $array['parent_names']     = [];
        }

        // 3. INDEXADO DINÁMICO DEL JSON DE METADATOS
        // Si el CMS guarda ['editorial' => 'Editorial UdeG', 'paginas' => 350], Meilisearch lo mapeará de inmediato
        if (!empty($this->metadata) && is_array($this->metadata)) {
            $array['metadata'] = $this->metadata;
        } else {
            $array['metadata'] = [];
        }

        return $array;
    }
}
