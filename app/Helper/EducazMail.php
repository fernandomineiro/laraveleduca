<?php

namespace App\Helper;

use App\Email;
use App\TipoEmail;
use Illuminate\Support\Facades\Log;
use Mail;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class EducazMail  {

    private $contaEnvio;
    private $templatePath = 'views/emails/';
    private $sistemaURL = 'http://3.81.68.4/admin';
    private $sistmaNome = 'PLATAFORMA EDUCAZ';
    private $idFaculdade;
    private $printHtml = false;

    public function __construct($idFaculdade = 7, $printHtml = null) {
        $this->contaEnvio = 'donotreply@educaz.com.br';
        $this->idFaculdade = $idFaculdade;

        if (!empty($printHtml)) {
            $this->printHtml = $printHtml;
        }
    }

    /**
     * @param $template
     * @param $data
     * @param $messageInfo
     * @return mixed
     * @throws \Exception
     */
    protected function send($data) {

        try {
            $renderedHtml = $this->getEmailContent($data);
            $data['messageFrom'] = $this->contaEnvio;

            $result = \Illuminate\Support\Facades\Mail::send(
                [], [],
                function ($message) use ($renderedHtml, $data) {
                    /** @var \Illuminate\Mail\Message $message */
                    $message = $message;
                    $message->setBody($renderedHtml, 'text/html');

                    $message->from($data['messageFrom']);
                    $message->to($data['messageTo']);
                    $message->subject($data['messageSubject']);
                    $message->addBcc($this->contaEnvio);

                    if (!empty($data['attached'])) {
                        $message->attach($data['attached']);
                    }
                });

            return $result;
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * @param $data
     * @return string
     */
    protected function getEmailContent($data) {
        $mustache = new \Mustache_Engine();
        return  $mustache->render(
            File::get(resource_path($this->templatePath."templates/{$this->idFaculdade}/".$data['messageTemplate'])),
            $data['messageData']
        );
    }

    /**
     * @param $data
     * @return mixed|string
     */
    public function recuperarSenha(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::ESQUECI_SENHA);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);

        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }
    /**
     * @param $data
     * @return mixed|string
     */
    public function portalRecuperarSenha(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::PORTAL_ESQUECI_SENHA);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);

        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    public function emailBoasVindas(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::BOAS_VINDAS);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);

        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param $data
     * @param $tipoEmail
     */
    protected function _prepareData(&$data, $tipoEmail) {
        $email = Email::where('fk_faculdade_id', $this->idFaculdade)
            ->where('fk_tipo_email', $tipoEmail)
            ->first();

        $this->contaEnvio = $this->contaEnvio;

        $data['messageTo'] = $data['messageData']['email'];
        $data['messageSubject'] = $email->assunto;

        $data['messageTemplate'] = $this->getTemplate($email->fk_tipo_email);
    }

    /**
     * @param array $data
     * @param bool $printHtml
     * @return mixed|string
     */
    public function confirmacaoPedido(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::CONFIRMACAO_COMPRA);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }


    /**
     * @param array $data
     * @param bool $printHtml
     * @return mixed|string
     */
    public function confirmacaoPedidoNoBoleto(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::CONFIRMACAO_COMPRA_BOLETO);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }


    /**
     * @param array $data
     * @param bool $printHtml
     * @return mixed|string
     */
    public function confirmacaoPedidoAssinatura(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::CONFIRMACAO_COMPRA_ASSINATURA);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param $idTipoEmail
     * @return string
     */
    protected function getTemplate($idTipoEmail) {
        $tipoEmail = TipoEmail::find($idTipoEmail);
        return (Str::slug($tipoEmail->titulo, '_')) . '.blade.php';
    }

    /**
     * @param array $data
     * @param bool $printHtml
     * @return mixed|string
     */
    public function renovarAssinatura(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::RENOVAR_ASSINATURA);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    public function certificado(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::CERTIFICADOS);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    public function promocoes(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::PROMOCOES);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }


    /**
     * @param array $data
     * @return mixed|string
     */
    public function alunoAvisoMensagem(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::ALUNO_AVISO_MENSAGEM);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    public function avisoTrabalhoProfessor(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::PROFESSOR_AVISO_TRABALHO);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }


    /**
     * @param array $data
     * @return mixed|string
     */
    public function avisoCursoAprovado(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::AVISO_CURSO_APROVADO);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    public function avisoCursoAvaliar(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::AVISO_CURSO_AVALIAR);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    public function avisoTrabalhoEnviado(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::AVISO_TRABALHO_ENVIADO);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    public function professorAvisoMensagem(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::PROFESSOR_AVISO_MENSAGEM);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    public function avisoPagamento(array $data)
    {
        try {
            $this->_prepareData($data, TipoEmail::AVISO_PAGAMENTO);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    public function opiniaoProfessor(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::OPINIAO_PROFESSOR);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    public function reenviarBoleto(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::REENVIO_BOLETO);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    public function comprovantePagamento(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::COMPROVANTE_PAGAMENTO);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    public function cadastroDeProfessorEmAnalise(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::CADASTRO_PROFESSOR_EM_ANALISE);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    public function notificarAprovacaoDeProfessor(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::NOTIFICAR_APROVACAO_PROFESSOR);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    public function avisoNovoCadastroProfessor(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::AVISO_NOVO_CADASTRO_PROFESSOR);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * Envia email com as exceções ocorridas no sistema
     * @param \Exception $exception
     * @return mixed
     */
    public function emailException(\Exception $exception) {
        try {

            return false;
            $e = FlattenException::create($exception);

            $handler = new SymfonyExceptionHandler();

            $html = $handler->getHtml($e);

            $data['messageTo'] = 'bugzilla@educaz.com.br';
            $data['messageSubject'] = 'Ocorreu um erro no sistema';
            $data['messageData'] = $html;
            $data['messageTemplate'] = 'exception.blade.php';
            $data['messageFrom'] = $this->contaEnvio;

            return Mail::send(
                [], [],
                function ($message) use ($data) {

                    $message->setBody($data['messageData'], 'text/html');

                    $message->from($data['messageFrom']);
                    $message->to($data['messageTo']);
                    $message->subject($data['messageSubject']);

                    if (!empty($data['attached'])) {
                        $message->attach($data['attached']);
                    }
                });

        } catch (\Exception $ex) {
            Log::info('Erro ao enviar email de exceção: ' . $ex->getMessage());
        }
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    public function avisoNovasTurmas(array $data) {
        try {
            $data['messageTemplate'] = 'avisa-novas-turmas.blade.php';
            $data['messageFrom'] = $this->contaEnvio;

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    public function cancelamentoAssinatura(array $data) {
        try {
            $this->_prepareData($data, TipoEmail::CANCELAMENTO_ASSINATURA);

            if ($this->printHtml) {
                return $this->getEmailContent($data);
            }

            return $this->send($data);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }
}
