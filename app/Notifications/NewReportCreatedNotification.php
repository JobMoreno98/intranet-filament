<?php

namespace App\Notifications;

use App\Models\Reportes;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action;

class NewReportCreatedNotification extends Notification
{

    use Queueable;

    public function __construct(public Reportes $reportes) {}

    public function via(object $notifiable): array
    {
        return ['mail']; // Solo correo para el usuario
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Hemos recibido tu reporte")
            ->greeting("¡Hola {$notifiable->name}!")
            ->line("Te confirmamos que hemos recibido tu reporte.")
            ->line("Motivo registrado: " . ($this->reportes->observaciones ?? $this->reportes->motivo ?? 'Sin especificar'))
            ->line('Nuestro equipo revisará la situación y te notificaremos por este medio en cuanto haya sido resuelta.')
            ->salutation('Saludos cordiales.');
    }
}
