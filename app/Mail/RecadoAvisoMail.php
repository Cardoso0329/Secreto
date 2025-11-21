<?php

namespace App\Mail;

<<<<<<< HEAD
use App\Models\Recado;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
=======
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Recado;
>>>>>>> main

class RecadoAvisoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $recado;
<<<<<<< HEAD

    public function __construct(Recado $recado)
    {
        $this->recado = $recado;
=======
    public $aviso;

    public function __construct(Recado $recado, $aviso)
    {
        $this->recado = $recado;
        $this->aviso = $aviso;
>>>>>>> main
    }

    public function build()
    {
<<<<<<< HEAD
        return $this->subject('📢 Aviso do Recado #' . $this->recado->id . ' - ' . $this->recado->plate)
                    ->view('emails.recados.aviso');
=======
        return $this->subject("Aviso sobre recado #{$this->recado->id}")
                    ->view('emails.recados.recado_aviso'); // cria a view depois
>>>>>>> main
    }
}
