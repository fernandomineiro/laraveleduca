<?php

namespace App\Helper;

use App\Aluno;
use App\Assinatura;
use App\Curso;
use App\CursoModuloAluno;
use App\Http\Controllers\API\CursoController;
use App\Pedido;
use App\PedidoItem;
use App\UsuarioAssinatura;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Faker\Provider\Uuid;
use App\UsuarioAcessos;

class RelatorioAlunosMatriculadosHelper
{
    private $data = [];
    
    private function criaArray($arr)
    {
        $this->data[Uuid::uuid()] = $arr;
    }

    public function lista_alunos_matriculados($request)
    {
        $data_registro_start = null;
        $data_registro_end = null;
        $req = $request;
        
        $query = DB::table('pedidos');
        if(isset($req['data_registro']) && is_array($req['data_registro']) && $req['data_registro'] != 'desativado') {
            $query->whereBetween('pedidos.criacao', $req['data_registro']);
        }

        if(isset($req['data_matricula']) && $req['data_matricula'] != 'desativado' ) {
            $query->where('fk_pedido_status', '=', 2);
            $query->whereBetween('pedidos.atualizacao', $req['data_matricula']);
        }

        $query->join('pedidos_item', 'pedidos.id', 'pedidos_item.fk_pedido')
            ->join('pedidos_historico_status', 'pedidos_historico_status.fk_pedido', 'pedidos.id')
            ->join('pedidos_status', 'pedidos_status.id', 'pedidos_historico_status.fk_pedido_status')
            ->join('cursos', 'cursos.id', 'pedidos_item.fk_curso')
            ->join('cursos_tipo', 'cursos_tipo.id', 'cursos.fk_cursos_tipo')
            ->select(
                'pedidos.*',

                DB::raw("case when pedidos.fk_usuario is not null then 
                    (SELECT id as aluno_id FROM alunos where alunos.fk_usuario_id = pedidos.fk_usuario) else null end as aluno_id"),

                DB::raw("case when pedidos.fk_usuario is not null then 
                    (SELECT nome as aluno_nome FROM alunos where alunos.fk_usuario_id = pedidos.fk_usuario) else null end as aluno_nome"),

                DB::raw("case when pedidos.fk_usuario is not null then 
                    (SELECT sobre_nome as aluno_sobre_nome FROM alunos where alunos.fk_usuario_id = pedidos.fk_usuario) else null end as aluno_sobre_nome"),

                DB::raw("case when pedidos.fk_usuario is not null then 
                    (SELECT email as aluno_email FROM usuarios where usuarios.id = pedidos.fk_usuario) else null end as aluno_email"),

                DB::raw("case when pedidos.fk_usuario is not null then 
                    (SELECT razao_social as ies_razao_social FROM faculdades where faculdades.id = pedidos.fk_faculdade) else null end as ies_razao_social"),

                DB::raw("case when pedidos.fk_usuario is not null then 
                    (SELECT fantasia as ies_fantasia FROM faculdades where faculdades.id = pedidos.fk_faculdade) else null end as ies_fantasia"),

                DB::raw("case when pedidos.fk_usuario is not null then 
                    (SELECT id FROM cursos_concluidos 
                    where (cursos_concluidos.fk_faculdade = pedidos.fk_faculdade)
                    and (cursos_concluidos.fk_usuario = pedidos.fk_usuario)
                    and (cursos_concluidos.fk_curso = cursos.id)) else null end as curso_concluido"),

                DB::raw("case when pedidos.fk_usuario is not null then 
                    (SELECT modulos_usuarios.criacao FROM modulos_usuarios 
                        INNER JOIN cursos_modulos on modulos_usuarios.fk_modulo = cursos_modulos.id
                        WHERE cursos_modulos.fk_curso = cursos.id
                        AND modulos_usuarios.fk_usuario = pedidos.fk_usuario
                        ORDER BY modulos_usuarios.criacao DESC LIMIT 1)
                    else null end as ultimo_acesso"),

                 DB::raw("case when cursos.id is not null then 
                    (SELECT fk_certificado FROM conclusao_cursos_faculdades 
                        where (conclusao_cursos_faculdades.fk_curso = cursos.id) 
                        and (conclusao_cursos_faculdades.fk_faculdade = pedidos.fk_faculdade))
                    else null end as fk_certificado"),
                
                'cursos.titulo as curso_titulo',
                'cursos.id as curso_id',
                'cursos_tipo.titulo as curso_tipo',
                'pedidos_status.titulo as pedido_status_titulo'
            );

        if(isset($req['id']) && $req['id'] != 'undefined' && $req['id'] != null) {
            $aluno_id = DB::table('alunos')->where('id', '=', $req['id'])->first();
            $query->where('pedidos.fk_usuario', '=', $aluno_id->fk_usuario_id);
        }

        if (!empty($req['nome']) && $req['nome'] != 'undefined') {
            $query->where(function ($query) use ($req) {
                $query->whereNotNull('pedidos.fk_usuario')
                    ->whereIn('pedidos.fk_usuario',
                        [DB::raw("(SELECT pedidos.fk_usuario FROM pedidos 
                                                 INNER JOIN alunos ON alunos.fk_usuario_id = pedidos.fk_usuario 
                                                 WHERE alunos.nome LIKE '%".$req['nome']."%')")])
                    ->orWhereIn('pedidos.fk_usuario',
                        [DB::raw("(SELECT pedidos.fk_usuario FROM pedidos 
                                                INNER JOIN alunos ON alunos.fk_usuario_id = pedidos.fk_usuario 
                                                WHERE alunos.sobre_nome LIKE '%".$req['nome']."%')")]);
            });
        }

        if (!empty($req['aluno_email']) && $req['aluno_email'] != 'undefined') {
            $query->where(function ($query) use ($req) {
                $query->whereNotNull('pedidos.fk_usuario')
                    ->whereIn('pedidos.fk_usuario',
                        [DB::raw("(SELECT pedidos.fk_usuario FROM pedidos 
                                                 INNER JOIN usuarios ON usuarios.id = pedidos.fk_usuario 
                                                 WHERE usuarios.email LIKE '%".$req['aluno_email']."%')")]);
            });
        }

        if(isset($req['ies'])) {
            $query->where(function ($query) use ($req) {
                $query->whereNotNull('pedidos.fk_usuario')
                    ->whereIn('pedidos.fk_usuario',
                        [DB::raw("(SELECT pedidos.fk_usuario FROM pedidos 
                                                 INNER JOIN alunos ON alunos.fk_usuario_id = pedidos.fk_usuario 
                                                 WHERE alunos.fk_faculdade_id = ".$req['ies'].")")]);
            });
        }

        if(isset($req['curso_id']) && $req['curso_id'] != 'undefined') {
            $query->where('cursos.id', '=', $req['curso_id']);
        }

        if (isset($req['curso_nome']) && $req['curso_nome'] != 'undefined') {
            $query->where('cursos.titulo', 'like', '%' . $req['curso_nome'] . '%');
        }

        if (isset($req['curso_tipo']) && $req['curso_tipo'] != 'undefined') {
            $query->where('cursos.fk_cursos_tipo', '=', $req['curso_tipo']);
        }

        if (isset($req['status_pagamento'])  && $req['status_pagamento'] != 'undefined') {
            $query->where('pedidos_historico_status.fk_pedido_status', '=', $req['status_pagamento']);
        }

        if (isset($req['status_conclusao']) && ($req['status_conclusao'] == '0' || $req['status_conclusao'] == '1' ) && $req['status_conclusao'] != 'undefined') {
            if($req['status_conclusao'] == 1) {
                $query->whereExists(function ($query) {
                    $query->select("cursos_concluidos.id as curso_concluido")
                        ->from('cursos_concluidos')
                        ->whereRaw('cursos_concluidos.fk_faculdade = pedidos.fk_faculdade')
                        ->whereRaw('cursos_concluidos.fk_usuario = pedidos.fk_usuario')
                        ->whereRaw('cursos_concluidos.fk_curso = cursos.id');
                });
            } else if($req['status_conclusao'] == 0) {
                $query->whereNotExists(function ($query) {
                    $query->select("cursos_concluidos.id as curso_andamento")
                        ->from('cursos_concluidos')
                        ->whereRaw('cursos_concluidos.fk_faculdade = pedidos.fk_faculdade')
                        ->whereRaw('cursos_concluidos.fk_usuario = pedidos.fk_usuario')
                        ->whereRaw('cursos_concluidos.fk_curso = cursos.id');
                });
            }
        }

        if(isset($req['email'])  && $req['email'] != 'undefined') {
            $query->where(function ($query) use ($req) {
                $query->whereNotNull('pedidos.fk_usuario')
                    ->whereIn('pedidos.fk_usuario',
                        [DB::raw("(SELECT pedidos.fk_usuario FROM pedidos 
                            INNER JOIN usuarios ON usuarios.id = pedidos.fk_usuario 
                            WHERE usuarios.email LIKE '%".$req['email']."%')")]);
            });
        }
        
        $query->distinct('pedidos.id');
        return  $query;
    }


    public static function lista_alunos_matriculadosITV($req) {
        $collection = collect();
        $data = [];
        
        $query = DB::table('alunos')
                    ->where('alunos.fk_faculdade_id', 6)
                    ->join('usuarios', 'usuarios.id', 'alunos.fk_usuario_id')
                    ->join('cursos_modulos_alunos', 'cursos_modulos_alunos.fk_aluno_id', 'alunos.fk_usuario_id')
                    ->join('cursos', 'cursos.id', 'cursos_modulos_alunos.fk_curso_id');

        $query->select('alunos.*', 'usuarios.email',
                DB::raw("case when cursos.id is not null then 
                        (SELECT fk_certificado FROM conclusao_cursos_faculdades 
                            where (conclusao_cursos_faculdades.fk_curso = cursos.id) 
                            and (conclusao_cursos_faculdades.fk_faculdade = 6))
                        else null end as fk_certificado"),

                DB::raw("case when cursos.id is not null then 
                        (SELECT modulos_usuarios.criacao FROM modulos_usuarios
                            WHERE modulos_usuarios.fk_modulo = cursos_modulos_alunos.fk_curso_modulo_id
                            AND modulos_usuarios.fk_usuario = alunos.fk_usuario_id
                            ORDER BY modulos_usuarios.criacao DESC LIMIT 1)
                        else null end as ultimo_acesso"),

                DB::raw("case when cursos.id is not null then 
                        (SELECT id FROM cursos_concluidos 
                        where (cursos_concluidos.fk_faculdade = 6)
                        and (cursos_concluidos.fk_usuario = alunos.fk_usuario_id)
                        and (cursos_concluidos.fk_curso = cursos.id)) else null end as curso_concluido"),
                DB::raw("case when cursos.id is not null then 
                        (SELECT titulo as cursos_tipo FROM cursos_tipo where cursos_tipo.id = cursos.fk_cursos_tipo) else null end as cursos_tipo"),
            'cursos.titulo as curso_titulo',
            'cursos.id as curso_id'
        );

        if(isset($req['data_registro']) && is_array($req['data_registro']) && $req['data_registro'] != 'desativado') {
            $query->whereBetween('alunos.data_criacao', $req['data_registro']);
        }

        if(isset($req['id']) && $req['id'] != 'undefined' && $req['id'] != null) {
            $query->where('alunos.id', '=', $req['id']);
        }

        if (!empty($req['nome']) && $req['nome'] != 'undefined') {
            $query->where('alunos.nome', 'Like', '%' . $req['nome']);
            $query->orWhere('alunos.sobre_nome', 'Like', '%' . $req['nome']);
        }

        
        if(isset($req['email'])  && $req['email'] != 'undefined') {
            $query->where('usuarios.email', 'LIKE', '%' .$req['email'].'%');
        }

        if (isset($req['curso_id']) && $req['curso_id'] != 'undefined') {
            $query->where('cursos_modulos_alunos.fk_curso_id', '=',  $req['curso_id']);
        }


        if (isset($req['curso_nome']) && $req['curso_nome'] != 'undefined') {
            $query->where('cursos.titulo', 'like', '%' . $req['curso_nome'] . '%');
        }

        if (isset($req['curso_tipo']) && $req['curso_tipo'] != 'undefined') {
            $query->where('cursos.fk_cursos_tipo', '=', $req['curso_tipo']);
        }

        if (isset($req['status_conclusao']) && ($req['status_conclusao'] == '0' || $req['status_conclusao'] == '1' ) && $req['status_conclusao'] != 'undefined') {
            if($req['status_conclusao'] == 1) {
                $query->whereExists(function ($query) {
                    $query->select("cursos_concluidos.id as curso_concluido")
                        ->from('cursos_concluidos')
                        ->whereRaw('cursos_concluidos.fk_faculdade = 6')
                        ->whereRaw('cursos_concluidos.fk_usuario = alunos.fk_usuario_id')
                        ->whereRaw('cursos_concluidos.fk_curso = cursos.id');
                });
            } else if($req['status_conclusao'] == 0) {
                $query->whereNotExists(function ($query) {
                    $query->select("cursos_concluidos.id as curso_andamento")
                        ->from('cursos_concluidos')
                        ->whereRaw('cursos_concluidos.fk_faculdade = 6')
                        ->whereRaw('cursos_concluidos.fk_usuario = alunos.fk_usuario_id')
                        ->whereRaw('cursos_concluidos.fk_curso = cursos.id');
                });
            }
        }

        $query->groupBy('alunos.id');
        
        return $query;
    }


    public static function graficoRealizadas($data)
    {
        // Adicionar Seleção por curso
        // $query = Pedido::select(DB::raw("COUNT(DISTINCT pedidos.fk_usuario) AS total "));
        $query = Pedido::select(DB::raw("COUNT( pedidos.id) AS total "))
            ->join('pedidos_item', 'pedidos_item.fk_pedido', 'pedidos.id')
            ->join('cursos', 'cursos.id', 'pedidos_item.fk_curso');

        if (isset($data['curso_nome']) && $data['curso_nome'] != 'undefined') {
            $query->where('cursos.titulo', 'like', '%' . $data['curso_nome'] . '%');
        }
        
        $query->where("pedidos.status", "=", 2); # PAGO

        if (!empty($data['fk_faculdade'])){
            $query->where("pedidos.fk_faculdade", "=", $data['fk_faculdade']);
        }
        $query->whereBetween('pedidos.criacao', $data['periodo']);
        
        $query->distinct('pedidos.id');

        switch ($data['agrupar_por']) {
            case 'mes':
                $query->addSelect(DB::raw("MONTH(pedidos.criacao) AS mes"));
                $query->addSelect(DB::raw("YEAR(pedidos.criacao) AS ano"));
                $query->groupBy(DB::raw("YEAR(pedidos.criacao), MONTH(pedidos.criacao)"));
                $query->orderBy(DB::raw("YEAR(pedidos.criacao), MONTH(pedidos.criacao)"));
                break;

            case 'ano':
                $query->addSelect(DB::raw("YEAR(pedidos.criacao) AS ano"));
                $query->groupBy(DB::raw("YEAR(pedidos.criacao)"));
                $query->orderBy(DB::raw("YEAR(pedidos.criacao)"));
                break;

            case 'semana':
                $query->addSelect(DB::raw("WEEK(pedidos.criacao) AS semana"));
                $query->addSelect(DB::raw("YEAR(pedidos.criacao) AS ano"));
                $query->groupBy(DB::raw("YEARWEEK(pedidos.criacao, 2)"));
                $query->orderBy(DB::raw("YEARWEEK(pedidos.criacao, 2)"));
                break;

            default:
                $query->addSelect(DB::raw("DAY(pedidos.criacao) AS dia"));
                $query->addSelect(DB::raw("MONTH(pedidos.criacao) AS mes"));
                $query->addSelect(DB::raw("YEAR(pedidos.criacao) AS ano"));
                $query->groupBy(DB::raw("YEAR(pedidos.criacao), MONTH(pedidos.criacao), DAY(pedidos.criacao)"));
                $query->orderBy(DB::raw("YEAR(pedidos.criacao), MONTH(pedidos.criacao), DAY(pedidos.criacao)"));
                break;
        }

     //   dd($data);

        return $query;
    }
}
