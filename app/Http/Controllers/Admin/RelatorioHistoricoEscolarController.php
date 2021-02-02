<?php

namespace App\Http\Controllers\Admin;

use App\Aluno;
use App\CursoModuloAluno;
use App\Http\Controllers\Controller;
use App\Pedido;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Faculdade;
use App\Curso;
use App\Http\Controllers\API\CertificadoController;
use App\CursosConcluidos;

class RelatorioHistoricoEscolarController extends Controller{

    public function index(Request $request){
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $params = $this->processaRequest($request);

        //dd($params);

        $data = array();
        $data['cursos'] = array();
        if (!empty($params['cpf']) || !empty($params['nome'])){
            $data = $this->getData($params);

            if (!empty($data)){
                $data = (array)$data->getData();

                $data['carga_horaria_total'] = 0;
                $data['cursos_online_carga_horaria_total'] = 0;
                $data['cursos_remotos_carga_horaria_total'] = 0;
                $data['cursos_presenciais_carga_horaria_total'] = 0;
                $data['cursos_trilha_do_conhecimento_carga_horaria_total'] = 0;

                if (isset($data['cursos'])){
                    foreach ($data['cursos'] as $key => $curso) {
                        $cursos[$curso->semestre][$this->getType($curso->tipo)][$key] = $curso;

                        switch ($this->getType($curso->tipo)) {
                            case 'online':
                                $data['cursos_online_carga_horaria_total'] = (int) $curso->carga_horaria + (int) $data['cursos_online_carga_horaria_total'];
                                break;

                            case 'remoto':
                                $data['cursos_remotos_carga_horaria_total'] = (int) $curso->carga_horaria + (int) $data['cursos_remotos_carga_horaria_total'];
                                break;

                            case 'presencial':
                                $data['cursos_presenciais_carga_horaria_total'] = (int) $curso->carga_horaria + (int) $data['cursos_presenciais_carga_horaria_total'];
                                break;

                            case 'trilha_do_conhecimento':
                                $data['cursos_trilha_do_conhecimento_carga_horaria_total'] = (int) $curso->carga_horaria + (int) $data['cursos_trilha_do_conhecimento_carga_horaria_total'];
                                break;
                        }

                        if ($curso->carga_horaria > 0){
                            $data['carga_horaria_total'] = $curso->carga_horaria + $data['carga_horaria_total'];
                        }
                    }

                    if (!empty($cursos)){
                        unset($data['cursos']);
                        $data['semestres'] = $cursos;
                    }
                }
            }
        }

        $faculdades = Faculdade::where('status', 1)->orderBy('razao_social')->get();

//        $data['params'] = $params;

        $this->arrayViewData['params'] = $params;
        $this->arrayViewData['faculdades'] = $faculdades;

//        return view('relatorio.historico_escolar.lista', compact('data', 'faculdades'));
        return view('relatorio.historico_escolar.lista', $this->arrayViewData);
    }

    private function getType($str){
        $str = preg_replace('/[áàãâä]/ui', 'a', $str);
        $str = preg_replace('/[éèêë]/ui', 'e', $str);
        $str = preg_replace('/[íìîï]/ui', 'i', $str);
        $str = preg_replace('/[óòõôö]/ui', 'o', $str);
        $str = preg_replace('/[úùûü]/ui', 'u', $str);
        $str = preg_replace('/[ç]/ui', 'c', $str);
        $str = preg_replace('/[^a-z0-9]/i', '_', $str);
        $str = preg_replace('/_+/', '_', $str);

        return strtolower($str);
    }

    private function getData($params) {
        $data['aluno'] = $params['aluno'];
//        dd($params);
        $alunos = (object)$data['aluno'];

        if (isset($alunos->id)){
//            foreach ($alunos as $aluno) {
//                
//            }
            $data['aluno'] = $alunos;

            $cursosModulos = new CertificadoController();

            $data['aluno']->data_nascimento = date('d/m/Y', strtotime($data['aluno']->data_nascimento));

            $cursos_concluidos = $this->getConcluidos($data['aluno']->fk_usuario_id);
//            dd($cursos_concluidos);

//            Consulta original:
//            $cursos_online = Aluno::cursos_online($data['aluno']->fk_usuario_id);
//            $cursos_presenciais = Curso::cursosPresenciaisAluno($data['aluno']->fk_usuario_id);
//            $cursos_trilha = Curso::cursosTrilhaOnlineAluno($data['aluno']->fk_usuario_id);
//            $cursos_remotos = Curso::cursosRemotosAluno($data['aluno']->fk_usuario_id);


            $cursos_online = Aluno::cursos_online($data['aluno']->fk_usuario_id)->when(isset($params['curso']), function ($q) use ($params) {
                return $q->where('titulo', $params['curso']);
            });

            $cursos_presenciais = Curso::cursosPresenciaisAluno($data['aluno']->fk_usuario_id)->when(isset($params['curso']), function ($q) use ($params) {
                return $q->where('titulo', $params['curso']);
            });

            $cursos_trilha = Curso::cursosTrilhaOnlineAluno($data['aluno']->fk_usuario_id)->when(isset($params['curso']), function ($q) use ($params) {
                return $q->where('titulo', $params['curso']);
            });

            $cursos_remotos = Curso::cursosRemotosAluno($data['aluno']->fk_usuario_id)->when(isset($params['curso']), function ($q) use ($params) {
                return $q->where('titulo', $params['curso']);
            });

            $data['cursos'] = array();
            foreach ($cursos_online as $key => $curso) {
                $data['cursos'][$curso->fk_curso]['fk_curso'] = $curso->fk_curso;
                $data['cursos'][$curso->fk_curso]['nome'] = $curso->titulo;
                $data['cursos'][$curso->fk_curso]['professor_nome'] = $curso->professor_nome;
                $data['cursos'][$curso->fk_curso]['data_inicio'] = date('d/m/Y', strtotime($curso->criacao));
                $data['cursos'][$curso->fk_curso]['tipo'] = 'Online';
            }

            foreach ($cursos_presenciais as $key => $curso) {
                $data['cursos'][$curso->id]['fk_curso'] = $curso->id;
                $data['cursos'][$curso->id]['nome'] = $curso->nome_curso;
                $data['cursos'][$curso->id]['professor_nome'] = $curso->nome_professor;
                $data['cursos'][$curso->id]['data_inicio'] = date('d/m/Y', strtotime($curso->criacao));
                $data['cursos'][$curso->id]['tipo'] = 'Presencial';
            }

            foreach ($cursos_trilha as $key => $curso) {
                $data['cursos'][$curso->id]['fk_curso'] = $curso->id;
                $data['cursos'][$curso->id]['nome'] = $curso->nome_curso;
                $data['cursos'][$curso->id]['professor_nome'] = $curso->nome_professor;
                $data['cursos'][$curso->id]['data_inicio'] = date('d/m/Y', strtotime($curso->criacao));
                $data['cursos'][$curso->id]['tipo'] = 'Trilha do conhecimento';
            }

            foreach ($cursos_remotos as $key => $curso) {
                $data['cursos'][$curso->id]['fk_curso'] = $curso->id;
                $data['cursos'][$curso->id]['nome'] = $curso->nome_curso;
                $data['cursos'][$curso->id]['professor_nome'] = $curso->nome_professor;
                $data['cursos'][$curso->id]['data_inicio'] = date('d/m/Y', strtotime($curso->criacao));
                $data['cursos'][$curso->id]['tipo'] = 'Remoto';
            }

            foreach ($data['cursos'] as $key => $curso) {
                if (isset($cursos_concluidos[$curso['fk_curso']]['data_conclusao'])) {
                    $semestre = $this->getSemestre($cursos_concluidos[$curso['fk_curso']]['data_conclusao']);

                    $data['cursos'][$curso['fk_curso']]['data_conclusao'] = date('d/m/Y', strtotime($cursos_concluidos[$curso['fk_curso']]['data_conclusao']));

                    $data['cursos'][$curso['fk_curso']]['media'] = $cursos_concluidos[$curso['fk_curso']]['media'];
                    $data['cursos'][$curso['fk_curso']]['nota_quiz'] = (!empty($cursos_concluidos[$curso['fk_curso']]['nota_quiz'])) ? $cursos_concluidos[$curso['fk_curso']]['nota_quiz'] : '--';
                    $data['cursos'][$curso['fk_curso']]['nota_trabalho'] = (!empty($cursos_concluidos[$curso['fk_curso']]['nota_trabalho'])) ? $cursos_concluidos[$curso['fk_curso']]['nota_trabalho'] : '--';
                    $data['cursos'][$curso['fk_curso']]['carga_horaria'] = (!empty($cursos_concluidos[$curso['fk_curso']]['carga_horaria'])) ? $cursos_concluidos[$curso['fk_curso']]['carga_horaria'] : '--';
                    $data['cursos'][$curso['fk_curso']]['frequencia'] = (!empty($cursos_concluidos[$curso['fk_curso']]['frequencia'])) ? $cursos_concluidos[$curso['fk_curso']]['frequencia'] . '%' : '--';
                    $data['cursos'][$curso['fk_curso']]['semestre'] = $semestre;

                } else {
                    $semestre = $this->getSemestre(date('Y-m-d'));

                    $data['cursos'][$curso['fk_curso']]['data_conclusao'] = '--';
                    $data['cursos'][$curso['fk_curso']]['media'] = '--';
                    $data['cursos'][$curso['fk_curso']]['nota_quiz'] = '--';
                    $data['cursos'][$curso['fk_curso']]['nota_trabalho'] = '--';
                    $data['cursos'][$curso['fk_curso']]['carga_horaria'] = '--';
                    $data['cursos'][$curso['fk_curso']]['frequencia'] = '--';
                    $data['cursos'][$curso['fk_curso']]['semestre'] = $semestre;
                }
            }

            $data['link_pdf'] = url('/admin/relatorios/historico-escolar/get_relatorio/pdf/' . base64_encode($data['aluno']->id . '-' . $data['aluno']->fk_usuario_id));
            $data_array[] = $data;

        } else {
            $data_array = [];
        }

//        dd($data);
//        dd($data_array);
        return $data_array;
    }

    private function checkCursoConcluido($fk_usuario, $fk_curso){
        $curso_concluido = CursosConcluidos::select('id')->where(['fk_usuario' => $fk_usuario, 'fk_curso' => $fk_curso])->first();

        if (isset($curso_concluido->id)){
            return true;
        } else {
            return false;
        }
    }

    private function getConcluidos($fk_usuario){
        $cursos_concluidos = CursosConcluidos::where('fk_usuario', $fk_usuario)->get();

        $cursos = array();
        foreach ($cursos_concluidos as $key => $curso_concluido) {
            $cursos[$curso_concluido->fk_curso]['data_conclusao'] = $curso_concluido->criacao;

            $cursos[$curso_concluido->fk_curso]['media'] = '--';
            if ($curso_concluido->nota_quiz > 0 && $curso_concluido->nota_trabalho > 0 ){
                $cursos[$curso_concluido->fk_curso]['media'] = ($curso_concluido->nota_quiz + $curso_concluido->nota_trabalho) / 2;
                $cursos[$curso_concluido->fk_curso]['nota_quiz'] = $curso_concluido->nota_quiz;
                $cursos[$curso_concluido->fk_curso]['nota_trabalho'] = $curso_concluido->nota_trabalho;
            } elseif ($curso_concluido->nota_quiz > 0){
                $cursos[$curso_concluido->fk_curso]['nota_quiz'] = $curso_concluido->nota_quiz;
                $cursos[$curso_concluido->fk_curso]['media'] = $curso_concluido->nota_quiz;
            } elseif ($curso_concluido->nota_trabalho > 0){
                $cursos[$curso_concluido->fk_curso]['nota_trabalho'] = $curso_concluido->nota_trabalho;
                $cursos[$curso_concluido->fk_curso]['media'] = $curso_concluido->nota_trabalho;
            }

            if ($curso_concluido->carga_horaria > 0){
                $cursos[$curso_concluido->fk_curso]['carga_horaria'] = $curso_concluido->carga_horaria;
            } else {
                $cursos[$curso_concluido->fk_curso]['carga_horaria'] = '--';
            }

            $cursos[$curso_concluido->fk_curso]['frequencia'] = $curso_concluido->frequencia;
        }

        return $cursos;
    }

    private function processaRequest($request){
        $params = [];
        $params['orderby'] = 'alunos.id';
        $params['sort'] = 'DESC';

        if($request->get('cpf')){
            $params['cpf'] = preg_replace("/[^0-9]/", "", $request->get('cpf'));
            $params['cpf_mask'] = $request->get('cpf');
        }

        if($request->get('nome')){
            $params['nome'] = $request->get('nome');
        }

        if($request->get('curso')){
            $params['curso'] = $request->get('curso');
        }

        return $params;
    }

    private function getSemestre($data_conclusao){
        $data = explode("-", $data_conclusao);

        $primeiro_semestre = [1, 2, 3, 4, 5, 6];
        $segundo_semestre = [7, 8, 9, 10, 11, 12];

        if (!empty($data[1]) && !empty($data[0])){
            if (in_array($data[1], $primeiro_semestre)){
                return '01/' . $data[0];
            } else {
                return '02/' . $data[0];
            }
        } else {
            return $data[2];
        }
    }

    public function getAlunos(Request $request)
    {
        $alunos = [];
        $req = $request->only('nome', 'type', 'curso', 'cpf');
        try {
            $params = [
                'orderby' => 'alunos.id',
                'sort' => 'DESC',
                'nome' => $request->nome,
                'curso' => $request->curso,
                'ies' => $request->ies,
                'cpf' => $request->cpf
            ];

            $query = Aluno::select('alunos.*', 'faculdades.razao_social as faculdade_instituicao', 'faculdades.fantasia as faculdade_fantasia')
                ->leftJoin('endereco', 'alunos.fk_endereco_id', '=', 'endereco.id')
                ->leftJoin('cidades', 'endereco.fk_cidade_id', '=', 'cidades.id')
                ->leftJoin('estados', 'cidades.fk_estado_id', '=', 'estados.id')
                ->join('faculdades', 'faculdades.id', 'alunos.fk_faculdade_id');

            if (!empty($params['nome'])) {
                $query->where(DB::raw('CONCAT(nome, " ", sobre_nome)'), 'like', '%' . $params['nome'] . '%');
            }
            if (!empty($params['cpf'])) {
                $query->where('alunos.cpf', 'like', '%' . $params['cpf'] . '%');
            }
            
            if (!empty($params['id'])) {
                $query->where('alunos.id', $params['id']);
            }

            if (!empty($params['ies'])) {
                $query->where('alunos.fk_faculdade_id', $params['ies']);
            }
            
            if($params['curso']) {
                $curso_selecionado = Curso::where('cursos.titulo', 'like', '%' . $params['curso'] . '%')
                    ->join('pedidos_item', 'pedidos_item.fk_curso', 'cursos.id')
                    ->join('pedidos', 'pedidos.id', 'pedidos_item.fk_pedido')
                    ->distinct('pedidos.fk_usuario')
                    ->select('pedidos.*')
                    ->get();
                $unique_user = $curso_selecionado->unique('fk_usuario');
                $filter_id = $unique_user->pluck('fk_usuario');

                $query->whereIn('alunos.fk_usuario_id', $filter_id);
            }

            $alunos = $query->get();
            
            if(count($alunos) > 0) {
                $collection = collect($alunos);
                $alunos = $collection->unique('id');
            } else {
                $collection = null;
            }

            return response()->json($alunos, 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Erro ao buscar os dados solicitados'], 401);
        }
    }

    public function getRelatorio(Request $request)
    {
        $req = $request->only('fk_usuario_id', 'cpf', 'curso');
        $params = [];
        $params['orderby'] = 'alunos.id';
        $params['sort'] = 'DESC';
        

        $query = Aluno::dados_aluno($params)
            ->select('alunos.*', 'faculdades.razao_social as faculdade_instituicao',
                'cidades.descricao_cidade', 'estados.descricao_estado', 'estados.uf_estado');
        
        if(isset($req['fk_usuario_id'])) {
            $query->where('alunos.fk_usuario_id', $req['fk_usuario_id']);
        }
        
        $data['aluno'] = $query->first();
        
        // acrescentar a faculdade do aluno aqui
        $data = $this->getRelatorioData($data);

        if (!isset($data['aluno'])){
            return response()->json(['success' => false, 'message' => 'Aluno não encontrado.']);
        }

        $data['link_pdf'] = url('/admin/relatorios/historico-escolar/get_relatorio/pdf/'. base64_encode($data['aluno']->id . '-' . $data['aluno']->fk_usuario_id));

        return response()->json($data);
    }

    public function gerarPDF($hash){
        $decode =  base64_decode($hash);
        $data = explode("-", $decode);
        $aluno_id = $data[0];

        $data['aluno'] = Aluno::dados_aluno(['id' => $aluno_id])
            ->select('alunos.*', 'faculdades.razao_social as faculdade_instituicao',
                'cidades.descricao_cidade', 'estados.descricao_estado', 'estados.uf_estado')
            ->first();
        
        if ($data['aluno']->fk_usuario_id == $data[1]){
            $historico = $this->getRelatorioData($data, 'pdf');
//            dd($historico);
            return PDF::loadView('relatorio.historico_escolar.template_pdf', $historico)
                ->setPaper('A4', 'landscape')->stream();
        }
    }

    private function getRelatorioData($data, $type = false){
        $cursos = [];
        if (!empty($data['aluno'])){
            $cursosModulos = new CertificadoController();

            $lista_cursos = CursoModuloAluno::where('fk_aluno_id', $data['aluno']->id )
                ->join('cursos', 'cursos.id', 'cursos_modulos_alunos.fk_curso_id')
                ->select('cursos.*')
                ->distinct('cursos.id')
                ->get();

            $data['aluno']->data_nascimento = $data['aluno']->data_nascimento ?
                date('d/m/Y', strtotime($data['aluno']->data_nascimento)) : null;

            $cursos_concluidos = $this->getConcluidos($data['aluno']->fk_usuario_id);

            $cursos_online = Aluno::cursos_online($data['aluno']->fk_usuario_id);
            $cursos_presenciais = Curso::cursosPresenciaisAluno($data['aluno']->fk_usuario_id);
            $cursos_trilha = Curso::cursosTrilhaOnlineAluno($data['aluno']->fk_usuario_id);
            $cursos_remotos = Curso::cursosRemotosAluno($data['aluno']->fk_usuario_id);

            $data['cursos'] = array();
            foreach ($cursos_online as $key => $curso) {
                $data['cursos'][$curso->fk_curso]['fk_curso'] = $curso->fk_curso;
                $data['cursos'][$curso->fk_curso]['nome'] = $curso->titulo;
                $data['cursos'][$curso->fk_curso]['professor_nome'] = $curso->professor_nome;
                $data['cursos'][$curso->fk_curso]['data_inicio'] = date('d/m/Y', strtotime($curso->criacao));
                $data['cursos'][$curso->fk_curso]['tipo'] = 'Online';
            }

            foreach ($cursos_presenciais as $key => $curso) {
                $data['cursos'][$curso->id]['fk_curso'] = $curso->id;
                $data['cursos'][$curso->id]['nome'] = $curso->nome_curso;
                $data['cursos'][$curso->id]['professor_nome'] = $curso->nome_professor;
                $data['cursos'][$curso->id]['data_inicio'] = date('d/m/Y', strtotime($curso->criacao));
                $data['cursos'][$curso->id]['tipo'] = 'Presencial';
            }

            foreach ($cursos_trilha as $key => $curso) {
                $data['cursos'][$curso->id]['fk_curso'] = $curso->id;
                $data['cursos'][$curso->id]['nome'] = $curso->nome_curso;
                $data['cursos'][$curso->id]['professor_nome'] = $curso->nome_professor;
                $data['cursos'][$curso->id]['data_inicio'] = date('d/m/Y', strtotime($curso->criacao));
                $data['cursos'][$curso->id]['tipo'] = 'Trilha do conhecimento';
            }

            foreach ($cursos_remotos as $key => $curso) {
                $data['cursos'][$curso->id]['fk_curso'] = $curso->id;
                $data['cursos'][$curso->id]['nome'] = $curso->nome_curso;
                $data['cursos'][$curso->id]['professor_nome'] = $curso->nome_professor;
                $data['cursos'][$curso->id]['data_inicio'] = date('d/m/Y', strtotime($curso->criacao));
                $data['cursos'][$curso->id]['tipo'] = 'Remoto';
            }

            foreach ($data['cursos'] as $key => $curso) {
                if (isset($cursos_concluidos[$curso['fk_curso']]['data_conclusao'])){
                    $semestre = $this->getSemestre($cursos_concluidos[$curso['fk_curso']]['data_conclusao']);

                    $data['cursos'][$curso['fk_curso']]['data_conclusao'] = date('d/m/Y', strtotime($cursos_concluidos[$curso['fk_curso']]['data_conclusao']));

                    $data['cursos'][$curso['fk_curso']]['media'] = $cursos_concluidos[$curso['fk_curso']]['media'];
                    $data['cursos'][$curso['fk_curso']]['nota_quiz'] = (!empty($cursos_concluidos[$curso['fk_curso']]['nota_quiz'])) ? $cursos_concluidos[$curso['fk_curso']]['nota_quiz'] : '--';
                    $data['cursos'][$curso['fk_curso']]['nota_trabalho'] = (!empty($cursos_concluidos[$curso['fk_curso']]['nota_trabalho'])) ? $cursos_concluidos[$curso['fk_curso']]['nota_trabalho'] : '--';
                    $data['cursos'][$curso['fk_curso']]['carga_horaria'] = (!empty($cursos_concluidos[$curso['fk_curso']]['carga_horaria'])) ? $cursos_concluidos[$curso['fk_curso']]['carga_horaria'] : '--';
                    $data['cursos'][$curso['fk_curso']]['frequencia'] = (!empty($cursos_concluidos[$curso['fk_curso']]['frequencia'])) ? $cursos_concluidos[$curso['fk_curso']]['frequencia'] . '%' : '--';
                    $data['cursos'][$curso['fk_curso']]['semestre'] = $semestre;

                } else {
                    $semestre = $this->getSemestre(date('Y-m-d'));

                    $data['cursos'][$curso['fk_curso']]['data_conclusao'] = '--';
                    $data['cursos'][$curso['fk_curso']]['media'] = '--';
                    $data['cursos'][$curso['fk_curso']]['nota_quiz'] = '--';
                    $data['cursos'][$curso['fk_curso']]['nota_trabalho'] = '--';
                    $data['cursos'][$curso['fk_curso']]['carga_horaria'] = '--';
                    $data['cursos'][$curso['fk_curso']]['frequencia'] = '--';
                    $data['cursos'][$curso['fk_curso']]['semestre'] = $semestre;
                }
            }
        } else {
            $data = [];
        }


        if (!empty($data)){
            $data['carga_horaria_total'] = 0;
            $data['cursos_online_carga_horaria_total'] = 0;
            $data['cursos_remotos_carga_horaria_total'] = 0;
            $data['cursos_presenciais_carga_horaria_total'] = 0;
            $data['cursos_trilha_do_conhecimento_carga_horaria_total'] = 0;

            if (isset($data['cursos'])){
                foreach ($data['cursos'] as $key => $curso) {
                    $cursos[$curso['semestre']][$this->getType($curso['tipo'])][$key] = $curso;

                    switch ($this->getType($curso['tipo'])) {
                        case 'online':
                            $data['cursos_online_carga_horaria_total'] = (int) $curso['carga_horaria'] + (int) $data['cursos_online_carga_horaria_total'];
                            break;

                        case 'remoto':
                            $data['cursos_remotos_carga_horaria_total'] = (int) $curso['carga_horaria'] + (int) $data['cursos_remotos_carga_horaria_total'];
                            break;

                        case 'presencial':
                            $data['cursos_presenciais_carga_horaria_total'] = (int) $curso['carga_horaria'] + (int) $data['cursos_presenciais_carga_horaria_total'];
                            break;

                        case 'trilha_do_conhecimento':
                            $data['cursos_trilha_do_conhecimento_carga_horaria_total'] = (int) $curso['carga_horaria'] + (int) $data['cursos_trilha_do_conhecimento_carga_horaria_total'];
                            break;
                    }

                    if ($curso['carga_horaria'] > 0){
                        $data['carga_horaria_total'] = $curso['carga_horaria'] + $data['carga_horaria_total'];
                    }
                }

                if (!empty($cursos)){
//                    unset($data['cursos']);
                    $data['semestres'] = $cursos;
                }
            }
        }
        
        if(($lista_cursos && count($lista_cursos)>0) && ($cursos && count($cursos) > 0)) {
            foreach ($lista_cursos as $k=>$l_curso) {
                foreach ($data['cursos'] as $curso) {
                    if($l_curso->id == $curso['fk_curso']) {
                        unset($lista_cursos[$k]);
                        continue;
                    }
                }
            }
        }
        
        
        $data['cursos_nao_iniciados'] = $lista_cursos;

        return $data;
    }
}
