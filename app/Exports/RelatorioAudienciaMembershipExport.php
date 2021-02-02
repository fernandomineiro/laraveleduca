<?php

namespace App\Exports;

use App\GraficosRelatorios;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RelatorioAudienciaMembershipExport implements FromQuery, WithHeadings{
    
    use Exportable;
    
    public function __construct(array $parametros){
        $this->parametros = $parametros;
    }
    
    public function headings(): array{
        return [
            'Aluno',
            'IES',
            'Plano',
            'Último acesso',
            'Acessos últimos 30 dias'
        ];
    }
    
    public function query(){
        return GraficosRelatorios::relatorio_audiencia_membership($this->parametros);
    }
    
}