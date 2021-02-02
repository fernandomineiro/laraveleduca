<?php

namespace App\Services;

use App\Helper\EducazMail;
use App\Repositories\UsuarioRepository;
use App\UsuariosPerfil;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioService extends UsuarioServiceAbstract {
    /** @var UsuarioRepository  */
    protected $usuarioRepository;
    /** @var DatabaseManager  */
    protected $databaseManager;
    
    public function __construct(UsuarioRepository $usuarioRepository, DatabaseManager $databaseManager) {
        $this->usuarioRepository = $usuarioRepository;
        $this->databaseManager = $databaseManager;
    }

    public function atualizar(array $data) {
        $usuario = $this->usuarioRepository->load($data['id']);
        
        if (!empty($data['password'])) {
            $data['password'] = $this->getPasswordHash($data['password']);
        }
        
        if ($this->hasFile()) {
            $data['foto'] = $this->uploadUserPhoto();
            if (!empty($usuario->foto)) {
                $this->deletarFotoUsuario($usuario->foto);    
            }
        }
        
        $usuario = $this->usuarioRepository->save($data);
        if (isset($data['foto']) && !empty($data['foto'])) {
            Auth()->guard('admin')->user()->foto = $data['foto'];
        }
        
        return $usuario;
    }

    public function salvar(array $data) {
        
        
        if ($usuario = $this->usuarioRepository->getUsuarioFaculdadeByEmail($data['email'], $this->idFaculade)) {
            return $usuario;
        }

        $password = trim($data['password']);
        if (empty($password)) {
            $data['password_confirmation'] = $password = $this->gerarSenhaRandomica();
        }

        if ($this->hasFile()) {
            $data['foto'] = $this->uploadUserPhoto();
        }
        
        $data['password'] = $this->getPasswordHash($password);

        $usuario = $this->usuarioRepository->save($data);
        
        $data['senha'] = $password;
        $this->enviarEmailUsuarioBoasVindas($data);

        return $usuario;
    }

    
    public function recuperarSenhaPortal(string $email, $usuarioPerfil = null) {
        
        $usuario = $this->usuarioRepository->getUsuarioFaculdadeByEmail($email, $this->idFaculade, $usuarioPerfil);
        if(empty($usuario)) {
            return [
                'success' => false,
                'error' => 'Erro ao redefinir senha! Não existe nenhum usuário cadastrado com este e-mail.',
            ];
        }
        $this->usuarioRepository->load($usuario->id);
        $novasenha = $this->gerarSenhaRandomica();
        $data['password'] = $this->getPasswordHash($novasenha);
        $data['senha_texto'] = $novasenha;
        
        $usuario = $this->usuarioRepository->save($data);

        $envio = $this->enviarEmailRecuperacaoSenhaPortal($usuario, $novasenha);
        return [
            'success' => true,
            'mensagem' => 'Uma nova senha foi enviada para seu e-mail! Use-a para realizar um novo login.',
            'envio' => $envio
        ];
    }

    /**
     * @param array $data
     */
    private function enviarEmailUsuarioBoasVindas(array $data): void {
        $educazMail = new EducazMail($this->idFaculade);
        $educazMail->emailBoasVindas([
            'messageData' => [
                'nome' => $data['nome'],
                'email' => $data['email'],
                'senha' => $data['senha'],
            ]
        ]);
    }

    /**
     * @return string
     */
    private function gerarSenhaRandomica(): string {
        return Str::random(10);
    }

    /**
     * @param $password
     * @return string
     */
    private function getPasswordHash($password): string
    {
        return bcrypt($password);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model|null $usuario
     * @param string $novasenha
     * @return mixed|string
     * @throws \Exception
     */
    private function enviarEmailRecuperacaoSenhaPortal(?\Illuminate\Database\Eloquent\Model $usuario, string $novasenha)
    {
        $educazMail = new EducazMail($this->idFaculade);
        $envio = $educazMail->portalRecuperarSenha(
            [
                'messageData' => [
                    'nome' => $usuario->nome,
                    'email' => $usuario->email,
                    'nova_senha' => $novasenha,
                ]
            ]
        );
        return $envio;
    }
}
