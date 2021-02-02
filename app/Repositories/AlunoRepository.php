<?php

namespace App\Repositories;

use App\Aluno;
use App\ViewUsuariosAlunos;

class AlunoRepository extends RepositoryAbstract {
    
    public function __construct(Aluno $aluno) {
        $this->model = $aluno;
    }

    public function listarVwAlunos() {
        return ViewUsuariosAlunos::select(
            ['aluno_id', 'registro', 'nome', 'sobre_nome', 'cpf',
                'registro_ativa', 'usuario_ativo', 'email', 'nome_faculdade', 'fk_usuario_id']);
    }
}
