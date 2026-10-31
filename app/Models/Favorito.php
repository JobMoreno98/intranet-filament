<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorito extends Model
{
    protected $fillable = ['user_id', 'recursos_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recurso()
    {
        return $this->belongsTo(Recursos::class, 'recursos_id');
    }
}
