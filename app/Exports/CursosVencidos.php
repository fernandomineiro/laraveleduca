<?php
/**
 * Created by PhpStorm.
 * User: Vinicius
 * Date: 10/10/2019
 * Time: 10:25
 */

namespace App\Exports;
use App\AvisarNovasTurmas;
use App\Trilha;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;


class CursosVencidos implements FromArray, WithMapping, WithHeadings, ShouldAutoSize{

    public function __construct(){
    }

    public function array(): array{

        return AvisarNovasTurmas::select('cursos.id',
            'cursos.titulo',
            'cursos.fk_cursos_tipo',
            'avisar_novas_turmas.nome_aluno',
            'avisar_novas_turmas.data_atualizacao',
            'avisar_novas_turmas.data_criacao',
            'cursos_tipo.titulo as curso_tipo',
            'faculdades.fantasia as faculdade',
            'avisar_novas_turmas.email_aluno')
            ->join('cursos', 'avisar_novas_turmas.fk_curso', '=', 'cursos.id')
            ->join('cursos_tipo', 'cursos_tipo.id', '=', 'cursos.fk_cursos_tipo')
            ->join('faculdades', 'faculdades.id', '=', 'avisar_novas_turmas.fk_faculdade')
            ->get()->toArray();
    }

    public function headings(): array{

        return [
            'ID Curso',
            'Nome do Curso',
            'Tipo',
            'Faculdade',
            'Data Interesse',
            'Nome Interessado',
            'Email Interessado',
        ];


    }

    public function map($dados): array{

        return [
            $dados['id'],
            $dados['titulo'],
            isset($dados['curso_tipo']) ?  $dados['curso_tipo'] : '-',
            $dados['faculdade'],
            ($dados['data_criacao']) ? date('d/m/Y', strtotime($dados['data_criacao'])) : '-',
            $dados['nome_aluno'],
            $dados['email_aluno']
        ];
    }
}
