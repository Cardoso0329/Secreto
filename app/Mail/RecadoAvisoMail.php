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
        return $this->subject('📢 Novo Aviso sobre o Recado #' . $this->recado->id)
                    ->view('emails.recado_aviso');
    }
}
