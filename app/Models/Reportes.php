<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reportes extends Model
{
    protected $guarded = [];
    public function recurso(): BelongsTo
    {
        return $this->belongsTo(Recursos::class, 'recursos_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
