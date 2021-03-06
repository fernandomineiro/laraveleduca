<?php

namespace App\Exports;

use App\Parceiro;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ParceirosExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize{
    
    public function collection(){
        
        return Parceiro::withoutGlobalScopes()
            ->select(
                'parceiro.*',
                'usuarios.email as email',
                'usuarios.status as usuario_status'
            )
            ->leftJoin('usuarios', 'parceiro.fk_usuario_id', '=', 'usuarios.id')
            ->get();
        
    }
    
    public function headings(): array{
        
        return [
            'ID',
            'Cadastro',
            'CPF',
            'CNPJ',
            'Razão Social',
            'Titular',
            'E-mail',
            'Status Parceiro',
            'Status Usuario'
        ];
        
        
    }
    
    public function map($dados): array{
        
        return [
            $dados->id,
            is_null($dados->criacao) ? '' : \Carbon\Carbon::parse($dados->criacao)->format('d/m/Y H:i:s'),
            $dados->cpf,
            $dados->cnpj,
            $dados->razao_social,
            $dados->responsavel,
            $dados->email,
            $dados->status == 1 ? 'Ativo' : 'Inativo',
            $dados->usuario_status == 1 ? 'Ativo' : 'Inativo'
            
        ];
        
        
    }
    
}