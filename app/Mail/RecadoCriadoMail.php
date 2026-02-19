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
    public $emailsInternos;

    /**
     * ✅ FIX: $emailsInternos agora tem default, para não rebentar
     * quando algum sítio fizer new RecadoCriadoMail($recado)
     */
    public function __construct(Recado $recado, ?string $guestUrl = null, $emailsInternos = [])
    {
        $this->recado = $recado;
        $this->emailsInternos = $emailsInternos;
        $this->guestUrl = $guestUrl;
    }


public function envelope(): Envelope
{
    // ✅ tipo formulário (ex: Central / Call Center)
    $tipo = $this->recado->tipoFormulario?->name;

    $subject = 'Recado #' . $this->recado->id;

    if (!empty($tipo)) {
        $subject .= ' | ' . $tipo;
    }

    if (!empty($this->recado->plate)) {
        $subject .= ' | Matrícula: ' . $this->recado->plate;
    }

    // ✅ replyTo precisa de array/Address/Collection; garantimos array aqui
    $replyTo = $this->emailsInternos;

    if ($replyTo instanceof \Illuminate\Support\Collection) {
        $replyTo = $replyTo->values()->all();
    } elseif (is_string($replyTo) && !empty($replyTo)) {
        $replyTo = [$replyTo];
    } elseif (!is_array($replyTo)) {
        $replyTo = [];
    }

    return new Envelope(
        subject: $subject,
        replyTo: $replyTo
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
