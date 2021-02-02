<?php
/**
 * Created by PhpStorm.
 * User: Vinicius
 * Date: 10/10/2019
 * Time: 10:25
 */

namespace App\Exports;
use App\Curso;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;


class CursosExport implements FromArray, WithMapping, WithHeadings, ShouldAutoSize{

    public function __construct(){
    }

    public function array(): array{
        return Curso::select('cursos.id', 'cursos.*', 'cursos_valor.valor', 'cursos_valor.valor_de',
            'professor.nome as nome_professor', 'professor.sobrenome as sobrenome_professor', 'curadores.razao_social as nome_curador',
            'produtora.razao_social as nome_produtora', 'faculdades.razao_social as nome_faculdade')
            ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
            ->leftJoin('professor', 'cursos.fk_professor', '=', 'professor.id')
            ->leftJoin('curadores', 'cursos.fk_curador', '=', 'curadores.id')
            ->leftJoin('produtora', 'cursos.fk_produtora', '=', 'produtora.id')
			->leftJoin('faculdades', 'cursos.fk_faculdade', '=', 'faculdades.id')
            ->where('cursos_valor.data_validade', null)
            ->where('cursos.status', '>', 0)
            ->get()->toArray();
    }

    public function headings(): array{

        return [
            'ID',
            'Nome do Curso',
            'Status',
            'Preço',
            'Preço de Venda',
            'Professor',
            'Curador',
            'Produtora',
			'Instituição',
        ];


    }

    public function map($curso): array{
        $lista_status = [
            '1' => 'Rascunho',
            '2' => 'Revisar',
            '3' => 'Não Aprovado',
            '4' => 'Aprovado',
            '5' => 'Publicado'
        ];
        return [
            $curso['id'],
            $curso['titulo'],
            ($lista_status[$curso['status']]) ?  $lista_status[$curso['status']] : '-',
            'R$ ' . number_format( $curso['valor_de'] , 2, ',', '.'),
            'R$ ' . number_format( $curso['valor'] , 2, ',', '.'),
            $curso['nome_professor'] .' '. $curso['sobrenome_professor'],
            $curso['nome_curador'],
            $curso['nome_produtora'],
			$curso['nome_faculdade'],
        ];
    }
}
