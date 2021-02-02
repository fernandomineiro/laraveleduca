<?php

namespace App\Exports;

use App\Usuario;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsuariosExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize{

    public function collection(){

        //alterado para retornar apenas administradores (como na listagem da tela inicial desse módulo)
        return Usuario::withoutGlobalScopes()
            ->select(
                'usuarios.*',
                'usuarios_perfil.titulo as nome_perfil'
            )
            ->leftJoin('usuarios_perfil', 'usuarios.fk_perfil', '=', 'usuarios_perfil.id')
            ->whereIn('usuarios.fk_perfil', [2, 20])
            ->where('usuarios.status', '=', 1)
            ->get();

    }

    public function headings(): array{

        return [
            'ID',
            'Cadastro',
            'Nome',
            'E-mail',
            'Perfil',
            'Status'
        ];


    }

    public function map($dados): array{

        return [
            $dados->id,
            is_null($dados->data_criacao) ? '' : \Carbon\Carbon::parse($dados->data_criacao)->format('d/m/Y H:i:s'),
            $dados->nome,
            $dados->email,
            $dados->nome_perfil,
            $dados->status == 1 ? 'Ativo' : 'Inativo'
        ];


    }

}
