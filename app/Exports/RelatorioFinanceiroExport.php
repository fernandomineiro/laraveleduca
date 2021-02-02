<?php

namespace App\Exports;

use App\Pedido;
use App\TrilhaCurso;
use App\PedidoItem;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Http\Controllers\Admin\RelatorioFinanceiroController;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;

class RelatorioFinanceiroExport implements FromCollection, WithHeadings, WithMapping 
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
            'Nome da Faculdade',
            'Professor',
            'ID do Aluno',
            'Nome do aluno',
            'E-mail do aluno',
            'CPF',
            'Status do pagamento',
            'Forma de Pagamento',
            'Codigo do Cupom',
            'Valor do Cupom',
            'Valor Bruto',
            'Valor Pago',
        ];
    }
    
    public function collection()
    {
        return (new RelatorioFinanceiroController)->listaRelatorioFinanceiro($this->parametros)->get();
    }

    public function map($pedido): array
    {
        $pedido->professor_nome = '';
        $pedido->professor_sobrenome = '';
        
        $model_trilha = new TrilhaCurso();
        $model_pedido_item = new PedidoItem();            
                
        if($pedido->fk_trilha != null) {
            $trilha_dados = $model_trilha->lista($pedido->fk_trilha);
            
            if($trilha_dados) {
                foreach ($trilha_dados as $professor) {
                    if($professor) {
                        if(isset($pedido->professor_nome)) {
                            $pedido->professor_nome .= ' -- ' . $professor->nome . ' ' . $professor->sobrenome;    
                        } else {
                            $pedido->professor_nome = $professor->nome . ' ' . $professor->sobrenome;
                        }
                    }
                }
            }
        } 
        elseif ($pedido->fk_trilha == null) {
            $professor = $model_pedido_item->where('fk_pedido', '=', $pedido->pedido_id)
                ->join('cursos', 'cursos.id', 'pedidos_item.fk_curso')
                ->join('professor', 'professor.id', 'cursos.fk_professor')
                ->select('professor.*')
                ->where('cursos.id', '=', $pedido->fk_curso)
                ->first();
            if($professor) {
                if(isset($pedido->professor_nome)) {
                    $pedido->professor_nome .= ' -- ' . $professor->nome . ' ' . $professor->sobrenome;
                } else {
                    $pedido->professor_nome = $professor->nome . ' ' . $professor->sobrenome;
                }
            } else {
                $pedido->professor_nome = '--';
            }
        } else {
            $pedido->professor_nome = '--';
        }

        if(!isset($pedido->professor_nome)) {
            $pedido->professor_nome = '---';
        }

        return [
            Carbon::parse($pedido->data_venda)->format('d/m/Y'),
            $pedido->pedido_pid,
            $pedido->produto_nome ? $pedido->produto_nome : '---',
            $pedido->faculdade_nome,
            $pedido->professor_nome = substr($pedido->professor_nome,3),
            $pedido->aluno_id,
            $pedido->aluno_nome.' '.$pedido->aluno_sobrenome,
            $pedido->email,
            $pedido->aluno_cpf,
            $pedido->pedido_status,
            $pedido->forma_pagamento ? $pedido->forma_pagamento : '---',
            $pedido->cupom_codigo ? $pedido->cupom_codigo : '---',
            $pedido->cupom_valor ? $pedido->cupom_valor : '---',
            $pedido->valor_bruto,
            $pedido->valor_pago ? $pedido->valor_pago : '---',
        ];
    }
    
}
