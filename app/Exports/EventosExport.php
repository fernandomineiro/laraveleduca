<?php
/**
 * Created by PhpStorm.
 * User: Vinicius
 * Date: 10/10/2019
 * Time: 10:25
 */

namespace App\Exports;
use App\Eventos;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;


class EventosExport implements FromArray, WithMapping, WithHeadings, ShouldAutoSize{

    public function __construct(){
    }

    public function array(): array{
        return Eventos::select('faculdades.fantasia as faculdade', 'eventos.*', 'cursos_categoria.titulo as categoria')
            ->join('faculdades', 'faculdades.id', 'eventos.fk_faculdade')
            ->leftjoin('cursos_categoria', 'cursos_categoria.id', '=', 'eventos.fk_categoria')
            ->where('eventos.status', '>',0)
            ->get()->toArray();
    }

    public function headings(): array{

        return [
            'Faculdade',
            'Título',
            'Descrição',
            'Status',
            'Categoria',
        ];


    }

    public function map($evento): array{
        $lista_status = [
            '1' => 'Rascunho',
            '2' => 'Revisar',
            '3' => 'Não Aprovado',
            '4' => 'Aprovado',
            '5' => 'Publicado'
        ];
        return [
            $evento['faculdade'],
            $evento['titulo'],
            $evento['descricao'],
            ($lista_status[$evento['status']]) ?  $lista_status[$evento['status']] : '-',
            $evento['categoria']
        ];
    }
}
