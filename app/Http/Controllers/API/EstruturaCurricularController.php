<?php

namespace App\Http\Controllers\API;

use App\Helper\EducazMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class EstruturaCurricularController extends Controller {

    public function __construct() {
        parent::__construct();
    }

    public function cursos($idEstrutura) {
        try {
            $queryAdicionados = "SELECT DISTINCT
                            cursos.id,
                            cursos.titulo,
                            CONCAT(professor.nome, ' ', professor.sobrenome) as professor_nome,
                            cursos_tipo.titulo as curso_tipo,
                            cursos_valor.valor,
                            cursos_valor.valor_de,
                            cursos.duracao_total,
                            cursos_categoria.titulo AS Categoria,
                            cursos_categoria.id AS categoria_id,
                            estrutura_curricular_conteudo.ordem,
                            estrutura_curricular_conteudo.modalidade,
                            estrutura_curricular_conteudo.data_inicio
                        from cursos
                            JOIN professor on professor.id = cursos.fk_professor
                            join cursos_tipo on cursos_tipo.id = cursos.fk_cursos_tipo
                            join cursos_valor on cursos.id = cursos_valor.fk_curso
                            JOIN cursos_categoria_curso ON cursos_categoria_curso.fk_curso = cursos.id
                            JOIN cursos_categoria ON cursos_categoria_curso.fk_curso_categoria = cursos_categoria.id
                            JOIN estrutura_curricular_conteudo ON cursos.id = estrutura_curricular_conteudo.fk_conteudo
                                    AND cursos.fk_cursos_tipo = estrutura_curricular_conteudo.modalidade
                                    AND cursos_categoria.id = estrutura_curricular_conteudo.fk_categoria
                        where cursos.status != 0
                            AND fk_estrutura = {$idEstrutura}
                        order by cursos_categoria.id, estrutura_curricular_conteudo.ordem;";
            $data = DB::select($queryAdicionados);

            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);

        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema' . $e->getMessage()
            ]);
        }
    }

    public function cursosCategoria($idCurso) {
        try {
            $queryAdicionados = "SELECT DISTINCT
                            cursos.id,
                            cursos.titulo,
                            CONCAT(professor.nome, ' ', professor.sobrenome) as professor_nome,
                            cursos_tipo.titulo as curso_tipo,
                            cursos_valor.valor,
                            cursos_valor.valor_de,
                            cursos.duracao_total,
                            cursos_categoria.titulo AS Categoria,
                            cursos_categoria.id AS categoria_id,
                            cursos.fk_cursos_tipo as modalidade
                        from cursos
                            JOIN professor on professor.id = cursos.fk_professor
                            join cursos_tipo on cursos_tipo.id = cursos.fk_cursos_tipo
                            join cursos_valor on cursos.id = cursos_valor.fk_curso
                            JOIN cursos_categoria_curso ON cursos_categoria_curso.fk_curso = cursos.id
                            JOIN cursos_categoria ON cursos_categoria_curso.fk_curso_categoria = cursos_categoria.id
                        where cursos.status != 0
                            AND cursos.id = {$idCurso}
                        order by cursos_tipo.titulo, cursos_categoria.titulo";
            $data = DB::select($queryAdicionados);

            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);

        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema' . $e->getMessage()
            ]);
        }
    }


    public function cursosPorCategoria($idCategoria) {
        try {
            $queryAdicionados = "SELECT DISTINCT
                            cursos.id,
                            cursos.titulo,
                            CONCAT(professor.nome, ' ', professor.sobrenome) as professor_nome,
                            cursos_tipo.titulo as curso_tipo,
                            cursos_valor.valor,
                            cursos_valor.valor_de,
                            cursos.duracao_total,
                            cursos_categoria.titulo AS Categoria,
                            cursos_categoria.id AS categoria_id,
                            cursos.fk_cursos_tipo as modalidade
                        from cursos
                            JOIN professor on professor.id = cursos.fk_professor
                            join cursos_tipo on cursos_tipo.id = cursos.fk_cursos_tipo
                            join cursos_valor on cursos.id = cursos_valor.fk_curso
                            JOIN cursos_categoria_curso ON cursos_categoria_curso.fk_curso = cursos.id
                            JOIN cursos_categoria ON cursos_categoria_curso.fk_curso_categoria = cursos_categoria.id
                        where cursos.status != 0
                            AND cursos_categoria.id = {$idCategoria}
                        order by cursos_tipo.titulo, cursos_categoria.titulo";
            $data = DB::select($queryAdicionados);

            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);

        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema' . $e->getMessage()
            ]);
        }
    }
}
