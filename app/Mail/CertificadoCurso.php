<?php

namespace App\Mail;

use App\CertificadoLayout;
use App\Curso;
use App\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CertificadoCurso extends Mailable
{
    use Queueable, SerializesModels;

    public $usuario;
    public $certificado;
    public $curso;

    /**
     * CertificadoCurso constructor.
     * @param Usuario $usuario
     * @param Curso $curso
     * @param CertificadoLayout $certificado
     */
    public function __construct(Usuario $usuario, Curso $curso, CertificadoLayout $certificado)
    {
        //
        $this->usuario = $usuario;
        $this->curso = $curso;
        $this->certificado = $certificado;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $file = 'files/certificado/'.$this->certificado->layout;
        return $this->view('emails.certificado')->attach($file, [
            'as' => 'Certificado '.$this->curso->titulo,
            'mime' => 'application/pdf',
        ]);
    }
}
