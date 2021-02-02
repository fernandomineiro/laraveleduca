<?php

namespace App\Imports;

use App\Aluno;
use App\Escola;
use App\EstruturaCurricularUsuario;
use App\Usuario;
use App\UsuariosPerfil;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AlunoEscolaImport implements ToModel, WithHeadingRow {

    /**
    * @param array $row
    *
    * @return Model|null
    */
    public function model(array $row){


        if(!empty($row['nome']) && !empty($row['email'])) {

            //regional	escola	turma
            $estrutura = Escola::select(
                'estrutura_curricular.id'
            )   ->join('estrutura_curricular', 'estrutura_curricular.fk_escola', 'escolas.id')
                ->join('diretoria_ensino', 'escolas.fk_diretoria_ensino', 'diretoria_ensino.id')
                ->where('estrutura_curricular.titulo', 'like', '%'.trim($row['turma']).'%')
                ->where('escolas.razao_social', 'like', '%'.trim($row['escola']).'%')
                ->where('diretoria_ensino.nome', 'like', '%'.trim($row['regional']).'%')
                ->first();

            $usuario = Usuario::create(
                [
                    'email' => $row['email'],
                    'nome' => $row['nome'],
                    'fk_perfil' => UsuariosPerfil::ALUNO,
                    'status' => 1,
                    'password' => bcrypt(Str::random(8)),
                    'fk_faculdade_id' => 29
                ]
            );

            $nome = explode(' ', $row['nome']);
            $firstName = $nome[0];
            unset($nome[0]);
            $aluno = Aluno::create(
                [
                    'nome' => $firstName,
                    'sobre_nome' => join(' ', $nome),
                    'cpf' => $row['cpf'],
                    'fk_usuario_id' => $usuario->id,
                    'fk_faculdade_id' => 29
                ]
            );

            EstruturaCurricularUsuario::create(
                [
                    'fk_estrutura' => $estrutura->id,
                    'fk_usuario' => $usuario->id
                ]
            );

        }
    }
}
