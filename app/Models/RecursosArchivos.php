<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class RecursosArchivos extends Model
{
    use Searchable;
    protected $guarded = [];

    protected $casts = [
        'assets_procesados' => 'array',
    ];

    public function recurso()
    {
        return $this->belongsTo(Recursos::class, 'recursos_id');
    }

    public function searchableAs(): string
    {
        return 'paginas_index'; // Nombre limpio para el índice en Meilisearch
    }

    public function toSearchableArray(): array
    {
        // 1. Cargamos el padre para no hacer N+1 queries al heredar datos
        $this->loadMissing(['recurso.coleccion', 'recurso.acervo']);

        $recurso = $this->recurso;

        // 2. Si el padre no existe (caso raro de inconsistencia), devolvemos array vacío
        if (!$recurso) {
            return [];
        }

        // 3. Estructuramos la página
        return [
            'id' => (int) $this->id,
            'recursos_id' => (int) $this->recursos_id,
            'orden' => (int) $this->orden, // Número de página
            'ocr' => $this->ocr,
            'status' => $this->status,
            
            // HERENCIA: Traemos datos vitales del libro para que Meilisearch pueda buscarlos
            'libro_coleccion_id' => (int) $recurso->coleccion_id,
            'libro_acervo_id' => (int) $recurso->acervo_id,
            'libro_status' => $recurso->status,
            
            // Aquí puedes heredar los campos de $recurso->metadata si los necesitas 
            // directamente en el buscador general.
        ];
    }
}