<?php

namespace App\Mail;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InstruccionesBienvenidaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

public function __construct(public User $user)
    {
        $this->user->loadMissing('profile');
    }
    
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cuenta verificada - Instrucciones',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.instrucciones-bienvenida',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdf.instrucciones', ['user' => $this->user]);

        return [
            Attachment::fromData(fn () => $pdf->output(), 'Registro_de_Arreola_Histórica.pdf')
                ->withMime('application/pdf'),
        ];
    }
}