<?php

namespace App\Notifications;

use App\Models\Reportes;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportResolvedNotification extends Notification implements ShouldQueue
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
        // Si es un usuario registrado toma su nombre; si se envió por Notification::route('mail', $correo), dice 'Hola'
        $saludo = isset($notifiable->name) ? "¡Hola {$notifiable->name}!" : '¡Hola!';
        $tituloRecurso = $this->reportes->recurso?->metadata['titulo'] ?? 'el recurso reportado';

        return (new MailMessage)
            ->subject("Tu reporte ha sido resuelto")
            ->greeting($saludo)
            ->line("Te informamos que hemos atendido y resuelto el reporte que realizaste sobre **{$tituloRecurso}**.")
            ->line("**Tu reporte:** {$this->reportes->observaciones}")
            ->line("**Resolución del equipo:** {$this->reportes->resuelto}")
            ->line('Agradecemos tu colaboración para mejorar la calidad de nuestro acervo.')
            ->salutation('Saludos cordiales.');
    }
}