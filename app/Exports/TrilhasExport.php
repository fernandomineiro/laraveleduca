<?php
/**
 * Created by PhpStorm.
 * User: Vinicius
 * Date: 10/10/2019
 * Time: 10:25
 */

namespace App\Exports;
use App\Trilha;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;


class TrilhasExport implements FromArray, WithMapping, WithHeadings, ShouldAutoSize{

    public function __construct(array $parametros){
        $this->parametros = $parametros;
    }

    public function array(): array{

        return Trilha::trilhasLista($this->parametros);
    }

    public function headings(): array{

        return [
            'IES',
            'Nome',
            'Carga Horária',
            'Preço',
            'Preço de Venda',
            'Categoria',
            'Nº de Inscritos',
            'Nº de assinaturas',
            'Status',
        ];


    }

    public function map($dados): array{

        return [
            $dados['projetos'],
            $dados['titulo'],
            $dados['duracao_total'],
            $dados['valor'],
            $dados['valor_venda'],
            $dados['categorias'],
            $dados['inscritos'],
            $dados['assinaturas'],
            $dados['status_nome'],
        ];
    }
}
