<?php

namespace App\Mail;

use App\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ForgotPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $usuario;
    public $senha;

    /**
     * ForgotPassword constructor.
     * @param Usuario $usuario
     */
    public function __construct(Usuario $usuario, $senha)
    {
        //
        $this->senha = $senha;
        $this->usuario = $usuario;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.nova_senha');
    }
}
