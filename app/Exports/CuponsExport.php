<?php


namespace App\Exports;


use App\Cupom;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CuponsExport  implements FromArray, WithMapping, WithHeadings, ShouldAutoSize{

    private $cupons = [];

    public function __construct(array $lista_cupons){
        $this->cupons = $lista_cupons;
    }

    public function array(): array{

        return $this->cupons;
    }

    public function headings(): array{

        return [
            'ID',
            'Cupom',
            'Descrição',
            'Status',
            'Código',
            'Número Máximo de usos',
            'Número Máximo de produtos',
            'Tipo',
            'Valor',
            'Cursos',
            'Trilhas',
            'Categorias',
            'Data Inicial',
            'Data Final',
            'Data Criação'
        ];


    }

    public function map($dados): array{
        $tipo_cupom = ['1' => 'Percentual (%)', '2' => 'Espécie (R$)'];

        return [
            $dados['id'],
            $dados['titulo'],
            $dados['descricao'],
            ($dados['status'] == 1 ? "Ativo" : "Inativo"),
            $dados['codigo_cupom'],
            $dados['numero_maximo_usos'],
            $dados['numero_maximo_produtos'],
            $tipo_cupom[$dados['tipo_cupom_desconto']],
            $dados['valor'],
            $dados['cursos'],
            $dados['trilhas'],
            $dados['categorias'],
            ($dados['data_validade_inicial']) ? date('d/m/Y', strtotime($dados['data_validade_inicial'])) : '-',
            ($dados['data_validade_final']) ? date('d/m/Y', strtotime($dados['data_validade_final'])) : '-',
            ($dados['data_cadastro']) ? date('d/m/Y', strtotime($dados['data_cadastro'])) : '-',
        ];
    }
}
