<?php

namespace App\Exports;

use App\Faculdade;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UniversidadesExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize{

    public function collection(){

        return Faculdade::withoutGlobalScopes()
            ->select(
                'faculdades.*',
                'usuarios.email as email',
                'usuarios.status as usuario_status'
            )
            ->leftJoin('usuarios', 'faculdades.fk_usuario_id', '=', 'usuarios.id')
            ->get();

    }

    public function headings(): array{

        return [
            'ID',
            'Cadastro',
            'CNPJ',
            'Razão Social',
            'Fantasia',
            'E-mail',
            'Status Projeto',
            'Status Usuario'
        ];


    }

    public function map($dados): array{

        return [
            $dados->id,
            is_null($dados->criacao) ? '' : \Carbon\Carbon::parse($dados->criacao)->format('d/m/Y H:i:s'),
            $dados->cnpj,
            $dados->razao_social,
            $dados->fantasia,
            $dados->email,
            $dados->status == 1 ? 'Ativo' : 'Inativo',
            $dados->usuario_status == 1 ? 'Ativo' : 'Inativo'

        ];


    }

}
