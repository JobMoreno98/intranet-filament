<?php

namespace App\Listeners;

use App\Mail\InstruccionesBienvenidaMail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Mail;

class SendInstruccionesPdfListener
{
    public function handle(Verified $event): void
    {
        /** @var \App\Models\User $user */
        $user = $event->user;

        Mail::to($user->email)->send(new InstruccionesBienvenidaMail($user));
    }
}