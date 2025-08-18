<?php

namespace App\Mail;

use App\Models\Recado;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecadoCriadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $recado;
    public $guestUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Recado $recado, $guestUrl = null)
    {
        $this->recado = $recado;
        $this->guestUrl = $guestUrl;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Novo Recado Criado')
                    ->view('emails.recados.create')
                    ->with([
                        'recado' => $this->recado,
                        'guestUrl' => $this->guestUrl,
                    ]);
    }
}
