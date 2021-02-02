<?php

namespace App\Exports;

use App\Pedido;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RelatorioVendasExport implements FromQuery, WithHeadings{

    use Exportable;

    public function __construct(array $parametros){
        $this->parametros = $parametros;
    }

    public function headings(): array{
        return [
            '#',
            'Date',
        ];
    }

    public function query(){
        return Pedido::relatorios_vendas($this->parametros);
    }

}
