<?php

namespace App\Exports;

use App\Aluno;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AlunosExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize{

    public function collection(){


        return Aluno::select('alunos.*',
                'usuarios.email as email',
                'usuarios.status as usuario_status',
                'faculdades.razao_social as ies'
            )
            ->leftJoin('usuarios', 'alunos.fk_usuario_id', '=', 'usuarios.id')
            ->leftJoin('faculdades', 'alunos.fk_faculdade_id', '=', 'faculdades.id')
            ->get();

    }

    public function headings(): array{

        return [
            'ID',
            'Cadastro',
            'Nome',
            'CPF',
            'IDENTIDADE',
            'DATA DE NASCIMENTO',
            'FACULDADE',
            'TELEFONE',
            'TELEFONE 2',
            'MATRÍCULA',
            'SEMESTRE',
            'CURSO SUPERIOR',
            'UNIVERSIDADE',
            'CURSO',
            'ENDEREÇO',
            'E-mail',
            'IES',
            'Status',

        ];


    }
    public function map($aluno): array{

        return [
            $aluno->id,
            is_null($aluno->data_criacao) ? '' : \Carbon\Carbon::parse($aluno->data_criacao)->format('d/m/Y H:i:s'),
            $aluno->nome . ' ' .$aluno->sobre_nome,
            $aluno->cpf,
            $aluno->identidade,
            $aluno->data_nascimento,
            $aluno->fk_faculdade_id,
            $aluno->telefone_1,
            $aluno->telefone_2,
            $aluno->matricula,
            $aluno->semestre,
            $aluno->curso_superior,
            $aluno->universidade,
            $aluno->curso,
            $aluno->fk_endereco_id,
            $aluno->email,
            $aluno->ies,
            $aluno->usuario_status == 1 ? 'Ativo' : 'Inativo'

        ];


    }



}
