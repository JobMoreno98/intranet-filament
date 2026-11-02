<?php

namespace App\Notifications;

use App\Models\Reportes;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReportCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Reportes $reportes)
    {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $saludo = isset($notifiable->name) ? "¡Hola {$notifiable->name}!" : '¡Hola!';
        $tituloRecurso = $this->reportes->recurso?->metadata['titulo'] ?? 'un recurso de la plataforma';

        return (new MailMessage)
            ->subject("Hemos recibido tu reporte")
            ->greeting($saludo)
            ->line("Confirmamos la recepción de tu reporte referente a **{$tituloRecurso}**.")
            ->line("**Observaciones registradas:** {$this->reportes->observaciones}")
            ->line('Nuestro equipo revisará la información y te notificaremos por este medio en cuanto quede resuelto.')
            ->salutation('Saludos cordiales.');
    }
}