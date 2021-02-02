<?php

namespace App\Exports;

use App\GraficosRelatorios;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RelatorioStatusAssinaturaExport implements FromQuery, WithHeadings{
    
    use Exportable;
    
    public function __construct(array $parametros){
        $this->parametros = $parametros;
    }
    
    public function headings(): array{
        return [
            'Aluno',
            'RG',
            'IES',
            'Adesão',
            'Plano',
            'Valor'
        ];
    }
    
    public function query(){
        return GraficosRelatorios::relatorio_status_assinatura($this->parametros);
    }
    
}