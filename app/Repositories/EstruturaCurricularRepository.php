<?php

namespace App\Repositories;

use App\EstruturaCurricular;
use App\EstruturaCurricularFaculdade;
use Illuminate\Support\Facades\DB;

class EstruturaCurricularRepository extends RepositoryAbstract {

    public function __construct(EstruturaCurricular $model) {
        $this->model = $model;
    }
    
    public function retornaEstruturasCurricularesUsuario(int $idUsuario) {
        return $this->model->select(
            'estrutura_curricular.id',
            'estrutura_curricular.titulo',
            'estrutura_curricular.tipo_liberacao',
            'estrutura_curricular.fk_certificado_layout'
        )->join(
            'estrutura_curricular_usuario', 
            'estrutura_curricular_usuario.fk_estrutura', 
            'estrutura_curricular.id'
        )->where(
            'estrutura_curricular_usuario.fk_usuario', $idUsuario
        )->get();
    }

    public function retornaProjetosEstruturaCurricular($idEstruturaCurricular) {
        return EstruturaCurricularFaculdade::select('*')
            ->where('fk_estrutura', $idEstruturaCurricular )
            ->get();
    }

    public function retornaIdsProjetosEstruturaCurricular($idEstruturaCurricular) {
        return $this->retornaProjetosEstruturaCurricular($idEstruturaCurricular)
                ->transform(function ($projeto) {
                    return $projeto->fk_faculdade;
                })->all();
    }

    public function listarCursosNaoAdicionadosNaEstruturaCurricular($idEstruturaCurricular) {
        $query = "select distinct
                            cursos.id,
                            cursos.titulo,
                            CONCAT(professor.nome, ' ', professor.sobrenome) as professor_nome,
                            cursos_tipo.titulo as tipo
                        from cursos
                            JOIN professor on professor.id = cursos.fk_professor
                            join cursos_tipo on cursos_tipo.id = cursos.fk_cursos_tipo
                        where cursos.status != 0
                            AND cursos.id not in (
                                select fk_conteudo 
                                    from estrutura_curricular_conteudo 
                                    where fk_estrutura = {$idEstruturaCurricular}
                            )
                        order by cursos.id";

        return DB::select($query);
    }

    public function listarCursosAdicionadosNaEstruturaCurricular($idEstruturaCurricular) {
        $query = "select distinct
                            cursos.id,
                            cursos.titulo,
                            CONCAT(professor.nome, ' ', professor.sobrenome) as professor_nome,
                            cursos_tipo.titulo as tipo,
                            cursos_valor.valor,
                            cursos_valor.valor_de
                        from cursos
                            JOIN professor on professor.id = cursos.fk_professor
                            join cursos_tipo on cursos_tipo.id = cursos.fk_cursos_tipo
                            join cursos_valor on cursos.id = cursos_valor.fk_curso
                        where cursos.status != 0
                            AND cursos.id in (
                                select fk_conteudo 
                                    from estrutura_curricular_conteudo 
                                    where fk_estrutura = {$idEstruturaCurricular}
                            )
                        order by cursos.id";
        
        return DB::select($query);
    }
}
