<?php

namespace App\Mail;

use App\Models\Recado;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecadoAvisoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $recado;

    public function __construct(Recado $recado)
    {
        $this->recado = $recado;
    }

    public function build()
    {
        return $this->subject('📢 Aviso do Recado #' . $this->recado->id . ' - ' . $this->recado->plate)
                    ->view('emails.recados.aviso');
    }
}
