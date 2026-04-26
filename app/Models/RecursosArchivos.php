<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecursosArchivos extends Model
{
        protected $guarded = [];
        protected $casts = [
                'assets_procesados' => 'array',
        ];
        public function recurso()
        {
                return $this->belongsTo(Recursos::class, 'recursos_id');
        }
}
