<?php

namespace App\Imports;

use App\Aluno;
use App\EstruturaCurricular;
use App\EstruturaCurricularUsuario;
use App\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AlunoImport implements ToModel, WithHeadingRow {

    /**
    * @param array $row
    *
    * @return Model|null
    */
    public function model(array $row){

        if(!empty($row['nome']) && !empty($row['email'])) {

            $usuario = Usuario::updateOrCreate(
                [
                    'email' => $row['email'],
                    'status' => 1,
                    'fk_perfil'  => 14,
                ],
                [
                    'status'  => 1,
                    'nome'  => $row['nome'],
                    'email'  => $row['email'],
                    'password'  => bcrypt($row['email']),
                    'fk_perfil'  => 14,
                    'fk_faculdade_id' => !empty($row['idfaculdade']) ? $row['idfaculdade'] : 7
                ]
            );

            $nomeParts = explode(' ', $row['nome']);
            $firstName = $nomeParts[0];
            unset($nomeParts[0]);

            $aluno = Aluno::updateOrCreate(
                ['fk_usuario_id' => $usuario->id],
                [
                    'fk_usuario_id' => $usuario->id,
                    'nome'  => $firstName,
                    'sobre_nome'  => join(' ', $nomeParts),
                    'cpf'  => $row['cpf'],
                    'status'  => 1,
                    'fk_faculdade_id' => !empty($row['idfaculdade']) ? $row['idfaculdade'] : 7
                ]
            );

            $aProdutos = explode('|', $row['produto']);
            foreach ($aProdutos as $prod) {
                $estruturas = EstruturaCurricular::where('titulo', 'like', '%'.$prod.'%')->get();
                foreach ($estruturas as $estrutura) {
                    EstruturaCurricularUsuario::updateOrCreate(
                        [ 'fk_estrutura' => $estrutura->id, 'fk_usuario' => $usuario->id],
                        [ 'fk_estrutura' => $estrutura->id, 'fk_usuario' => $usuario->id]

                    );
                }
            }
        }
    }
}
