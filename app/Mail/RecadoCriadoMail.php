<?php

namespace App\Mail;

use App\Models\Recado;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecadoCriadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public Recado $recado;
    public ?string $guestUrl;

    public function __construct(Recado $recado, ?string $guestUrl = null)
    {
        $this->recado = $recado;
        $this->guestUrl = $guestUrl;
    }

    public function envelope(): Envelope
    {
        $subject = 'Recado #' . $this->recado->id;

        if (!empty($this->recado->plate)) {
            $subject .= ' | Matrícula: ' . $this->recado->plate;
        }

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recados.create',
            with: [
                'recado' => $this->recado,
                'guestUrl' => $this->guestUrl,
            ]
        );
    }
}
