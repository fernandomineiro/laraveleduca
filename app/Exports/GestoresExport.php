<?php

namespace App\Exports;

use App\Aluno;
use App\Gestao;
use App\UsuariosPerfil;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GestoresExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize{

    public function collection(){

        return Gestao::select('usuarios.*', 'gestao_ies.*', 'usuarios.status AS usuario_ativo', 'usuarios_perfil.titulo as nome_perfil')
            ->join('usuarios', 'gestao_ies.fk_usuario_id', '=', 'usuarios.id')
            ->leftJoin('usuarios_perfil', 'usuarios.fk_perfil', '=', 'usuarios_perfil.id')
            ->get();

    }

    public function headings(): array{

        return [
            'ID',
            'Perfil',
            'Registro em',
            'Nome',
            'CPF',
            'E-mail',
            'Projeto',
            'Usuário Ativo?',

        ];


    }
    public function map($gestor): array{

        return [
            $gestor->id,
            $gestor->nome_perfil,
            is_null($gestor->data_criacao) ? '' : \Carbon\Carbon::parse($gestor->data_criacao)->format('d/m/Y H:i:s'),
            $gestor->nome . ' ' .$gestor->sobre_nome,
            $gestor->cpf,
            $gestor->email,
            $gestor->fk_faculdade_id,
            $gestor->usuario_ativo == 1 ? 'Ativo' : 'Inativo'

        ];


    }



}
