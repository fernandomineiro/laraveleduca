<?php

namespace App\Exports;

use App\Professor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProfessoresExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize{
    
    public function collection(){
        
        return Professor::withoutGlobalScopes()
            ->select(
                'professor.*',
                'usuarios.email as email',
                'usuarios.status as usuario_status'
            )
            ->leftJoin('usuarios', 'professor.fk_usuario_id', '=', 'usuarios.id')
            ->get();
        
    }
    
    public function headings(): array{
        
        return [
            'ID',
            'Cadastro',
            'CPF',
            'Nome',
            'E-mail',
            'Status Professor',
            'Status Usuario'
        ];
        
        
    }
    
    public function map($dados): array{
        
        return [
            $dados->id,
            is_null($dados->data_criacao) ? '' : \Carbon\Carbon::parse($dados->data_criacao)->format('d/m/Y H:i:s'),
            $dados->cpf,
            $dados->nome . $dados->sobrenome,
            $dados->email,
            $dados->status == 1 ? 'Ativo' : 'Inativo',
            $dados->usuario_status == 1 ? 'Ativo' : 'Em Avaliação'
            
        ];
        
        
    }
    
}