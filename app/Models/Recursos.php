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

    public function acervo(): BelongsTo
    {
        return $this->belongsTo(TipoAcervo::class, 'acervo_id');
    }


    public function archivos()
    {
        return $this->hasMany(RecursosArchivos::class, 'recursos_id', 'id')->orderBy('orden');
    }

    public function toSearchableArray(): array
    {
        // 1. Cargamos datos básicos planos y limpios
        $array = [
            'id'          => (int) $this->id,
            'acervo'      => $this->acervo->nombre,
            'acervo_id'      => $this->acervo_id,
            'coleccion'       => $this->coleccion->nombre,
            'status'      => $this->status,
            'archivos' =>  $this->archivos()->exists(),
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
        $metadata = $this->metadata;

        $variables = collect($this->acervo->esquema)
            ->whereIn('visible', ['Recuperable','Adicional'])
            ->pluck('variable')
            ->values()
            ->all();

            
        $filtros = array_intersect_key($metadata, array_flip($variables));

        if (!empty($filtros) && is_array($filtros)) {
            // Concatenamos valores clave => valor en un string
            //$array['metadata'] = $metadata; // JSON original
            $flatMetadata = collect($filtros)
                ->map(fn($valor, $clave) => $clave . ': ' . $valor)
                ->implode(' | ');

                //dd($flatMetadata);

            $array['metadata_text'] = $flatMetadata;
        } else {
            $array['metadata'] = '';
            $array['metadata_text'] = '';
        }

        return $array;
    }
}
