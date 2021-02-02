<?php

namespace App\Exports;

use App\Pedido;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Http\Controllers\Admin\RelatorioFinanceiroDetalhadoController;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;

class RelatorioFinanceiroDetalhadoExport implements FromCollection, WithHeadings, WithMapping 
{    
    use Exportable;
    
    public function __construct(array $parametros)
    {
        $this->parametros = $parametros;
    }
    
    public function headings(): array{
        return [
            'Data de Venda',
            'ID do Pedido',
            'Produto Adquirido',
            'NFE',
            'Nome da Faculdade',
            'ID do Aluno',
            'Nome do aluno',
            'CPF',
            'Status do pagamento',
            'Método de Pagamento',
            'Codigo do Cupom',
            'Valor do Cupom',
            'Valor Pago Bruto',
            'Crédito ou Débito [3,99%]',
            'ISS [5%]',
            'PIS/Cofins [3,65%]',
            'IRPJ/CSLL [7,5%]',
            'Tarifa Boleto',
            'Tarifa Processamento',
            'Valor Líquido',
            'Qtd Parcelas',
            'Valor Parcela',
            'Total Líquido Parcelado',
            'Valor Pago',
        ];
    }
    
    public function collection()
    {
        return (new RelatorioFinanceiroDetalhadoController)->listaRelatorioFinanceiro($this->parametros)->get();
        $query = (new RelatorioFinanceiroDetalhadoController)->listaRelatorioFinanceiro($this->parametros);
        
        if ($query->count() > 7000) {
            abort(403, 'Não é possível exportar esse número de registros');
        }

        return $query->get();
    }

    public function map($pedido): array
    {
        $recebido = json_decode($pedido->recebido);
        return [
            Carbon::parse($pedido->data_venda)->format('d/m/Y'),
            $pedido->pedido_pid,
            $pedido->produto_nome ? $pedido->produto_nome : '---',
            isset($recebido->number) ? $recebido->number : '---',
            $pedido->faculdade_nome,
            $pedido->aluno_id,
            $pedido->aluno_nome.' '.$pedido->aluno_sobrenome,
            $pedido->aluno_cpf,
            $pedido->pedido_status,
            $pedido->metodo_pagamento ? $pedido->metodo_pagamento : '---',
            $pedido->cupom_codigo ? $pedido->cupom_codigo : '---',
            $pedido->cupom_valor ? $pedido->cupom_valor : '---',
            $pedido->valor_bruto,
            $pedido->valor_bruto == 'Grátis' ? 'Grátis' : ($pedido->metodo_pagamento == 'Cartão de Crédito' ? $pedido->tarifa_cartao : "---"),
            $pedido->valor_bruto == 'Grátis' ? 'Grátis' : $pedido->valor_iss,
            $pedido->valor_bruto == 'Grátis' ? 'Grátis' : $pedido->valor_pis,
            $pedido->valor_bruto == 'Grátis' ? 'Grátis' : $pedido->valor_irpj,
            $pedido->valor_bruto == 'Grátis' ? 'Grátis' : ($pedido->metodo_pagamento == 'Boleto Bancário' ? $pedido->tarifa_boleto : "---"),
            $pedido->valor_bruto == 'Grátis' ? 'Grátis' : $pedido->tarifa_processamento,
            $pedido->valor_bruto == 'Grátis' ? 'Grátis' : $pedido->valor_liquido,
            $pedido->metodo_pagamento == 'Cartão de Crédito' ? $pedido->parcelas : '---',
            $pedido->metodo_pagamento == 'Cartão de Crédito' ? $pedido->valor_parcela : '---',
            $pedido->metodo_pagamento == 'Cartão de Crédito' ? $pedido->valor_liquido_parcela : '---',
            $pedido->valor_pago ? $pedido->valor_pago : '---',
        ];
    }
    
}
