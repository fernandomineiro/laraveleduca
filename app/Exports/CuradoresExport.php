<?php

namespace App\Exports;

use App\Curador;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CuradoresExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize{
    
    public function collection(){
        
        return Curador::withoutGlobalScopes()
            ->select(
                'curadores.*',
                'usuarios.email as email',
                'usuarios.status as usuario_status'
            )
            ->leftJoin('usuarios', 'curadores.fk_usuario_id', '=', 'usuarios.id')
            ->get();
        
    }
    
    public function headings(): array{
        
        return [
            'ID',
            'Cadastro',
            'Nome',
            'CPF',
            'E-mail',
            'Status Curador',
            'Status Usuario'
        ];
        
        
    }
    
    public function map($dados): array{
        
        return [
            $dados->id,
            is_null($dados->data_criacao) ? '' : \Carbon\Carbon::parse($dados->data_criacao)->format('d/m/Y H:i:s'),
            $dados->titular_curador,
            $dados->cpf,
            $dados->email,
            $dados->status == 1 ? 'Ativo' : 'Inativo',
            $dados->usuario_status == 1 ? 'Ativo' : 'Inativo'
            
        ];
        
        
    }
    
}