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
}
