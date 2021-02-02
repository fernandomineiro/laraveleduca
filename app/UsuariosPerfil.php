<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Usuario;

class UsuariosPerfil extends Model
{
    const PROFESSOR = 1;
    const ADMINISTRADOR = 2;
    const CONTEUDISTA = 4;
    const CURADOR = 5;
    const PRODUTORA = 7;
    const ORIENTADOR = 9;
    const FINANCEIRO_IES = 10;
    const MARKETING_IES = 11;
    const ALUNO = 14;
    const PARCEIRO = 19;
    const DESENVOLVEDOR = 20;
    const GESTOR_IES = 22;
    const DIRETOR = 23;

    protected $table = 'usuarios_perfil';
    protected $fillable = ['titulo', 'status', 'fk_criador_id', 'fk_atualizador_id', 'criacao', 'atualizacao','fk_parceiro_id'];

    public $timestamps = false;

    public $rules = [
        'titulo' => 'required'
    ];

    public $messages = [
        'titulo' => 'Título'
    ];

    public function retornaUsuarioPorPerfil($idPerfil)
    {
        return Usuario::where('fk_perfil', $idPerfil)->get();
    }
}
