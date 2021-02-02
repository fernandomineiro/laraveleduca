<?php

namespace App\Imports;

use App\Imports\Sheets\CursosCategoriaImport;
use App\Imports\Sheets\CursosImport;
use App\Imports\Sheets\CursosModuloImport;
use App\Imports\Sheets\CursosQuizImport;
use App\Imports\Sheets\CursosQuizQuestaoImport;
use App\Imports\Sheets\CursosQuizRespostaImport;
use App\Imports\Sheets\CursosSecaoImport;
use App\Imports\Sheets\CursosTagImport;
use App\Imports\Sheets\CursosValorImport;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CursoImport implements WithMultipleSheets, SkipsUnknownSheets{
    
    /*
     * Definição de quais serão as tabelas/abas importadas 
     */
    public function sheets(): array{
        return [
            'cursos'                => new CursosImport(),
            'cursos_valor'          => new CursosValorImport(),
            'cursos_tag'            => new CursosTagImport(),
            'cursos_categoria'      => new CursosCategoriaImport(),
            'cursos_secao'          => new CursosSecaoImport(),
            'cursos_modulo'         => new CursosModuloImport(),
            'cursos_quiz'           => new CursosQuizImport(),
            'cursos_quiz_questao'   => new CursosQuizQuestaoImport(),
            'cursos_quiz_resposta'  => new CursosQuizRespostaImport(),
        ];
    }
    
    public function onUnknownSheet($sheetName){
        
        info("Sheet {$sheetName} was skipped");
        
    }
    
}