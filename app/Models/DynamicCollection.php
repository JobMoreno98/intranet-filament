<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Searchable;

class DynamicCollection extends Model
{
    use Searchable;

    protected $connection = 'mysql2';

    public $timestamps = false;
    protected $primaryKey = 'IdElemento';
    protected $keyType = 'int';
    protected $guarded = [];

    public function setCustomTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Nombre del índice en Meilisearch (será el nombre de la tabla)
     */
    public function searchableAs()
    {
        return $this->getTable();
    }

    /**
     * Definir qué datos se van a subir a Meilisearch.
     * Solo subiremos los campos configurados para esta tabla para no saturar el motor.
     */
    public function toSearchableArray()
    {
        // 1. Obtener los campos permitidos desde la configuración
        $camposPermitidos = DB::connection('mysql2')
            ->table('colecciones')
            ->where('tabla', $this->getTable())
            ->pluck('campo')
            ->toArray();

        // Aseguramos que jale IdElemento de la base de datos
        if (!in_array('IdElemento', $camposPermitidos)) {
            $camposPermitidos[] = 'IdElemento';
        }

        $arrayTotal = $this->toArray();
        $datosFiltrados = array_intersect_key($arrayTotal, array_flip($camposPermitidos));

        // 2. TRUCO: Creamos una copia de 'IdElemento' llamada 'id' exclusivamente para Meilisearch
        if (isset($datosFiltrados['IdElemento'])) {
            $datosFiltrados['id'] = $datosFiltrados['IdElemento'];
        }

        return $datosFiltrados;
    }
}
