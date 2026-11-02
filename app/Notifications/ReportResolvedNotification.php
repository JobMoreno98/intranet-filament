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
        return (new MailMessage)
            ->subject("Tu reporte #{$this->reportes->id} ha sido resuelto")
            ->greeting("¡Hola {$notifiable->name}!")
            ->line("Te informamos que el reporte #{$this->reportes->id} que realizaste en la plataforma ya fue atendido y resuelto.")
            ->when($this->reportes->admin_notes ?? null, function (MailMessage $mail, string $notas) {
                return $mail->line("Respuesta del administrador: {$notas}");
            })
            ->line('Agradecemos mucho tu colaboración para mejorar la plataforma.')
            ->salutation('Saludos cordiales.');
    }
}