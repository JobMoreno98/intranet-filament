<?php

namespace App\Observers;

use App\Models\Recursos;
use Illuminate\Support\Facades\Redis;

class RecursoObserver
{
    /**
     * Handle the Recurso "created" event.
     */
    public function created(Recursos $recurso): void
    {
        //
    }

    /**
     * Handle the Recurso "updated" event.
     */
    public function updated(Recursos $recurso): void
    {
        //
    }

    /**
     * Handle the Recurso "deleted" event.
     */
    public function deleted(Recursos $recurso): void
    {
        $payload = [
            'item_id' => $recurso->id,
            'coleccion_slug' => $recurso->coleccion->slug,
            'action' => 'delete'
        ];

        Redis::lpush('cola_procesamiento', json_encode($payload));
    }

    /**
     * Handle the Recurso "restored" event.
     */
    public function restored(Recursos $recurso): void
    {
        //
    }

    /**
     * Handle the Recurso "force deleted" event.
     */
    public function forceDeleted(Recursos $recurso): void
    {
        //
    }
}
