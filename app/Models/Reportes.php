<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Observers\ReportObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([ReportObserver::class])]
class Reportes extends Model
{
    protected $guarded = [];
    
    public function recurso(): BelongsTo
    {
        return $this->belongsTo(Recursos::class, 'recursos_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePendiente(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('resuelto')->orWhere('resuelto', '');
        });
    }
}
