<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Recado;

class RecadoAvisoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $recado;
    public $aviso;

    public function __construct(Recado $recado, $aviso)
    {
        $this->recado = $recado;
        $this->aviso = $aviso;
    }

    public function build()
    {
        return $this->subject("Aviso sobre recado #{$this->recado->id}")
                    ->view('emails.recados.recado_aviso'); // cria a view depois
    }
}
