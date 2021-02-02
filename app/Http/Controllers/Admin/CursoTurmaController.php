<?php

namespace App\Http\Controllers\Admin;

use App\Curso;
use App\CursoTurma;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CursoTurmaController extends Controller{

    public function index() {
        
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['turmas'] = CursoTurma::select('cursos_turmas.*', 'cursos.titulo', 'faculdades.fantasia as ies')
                                            ->join('cursos', 'cursos_turmas.fk_curso', '=', 'cursos.id')
                                            ->join('faculdades', 'cursos.fk_faculdade', '=', 'faculdades.id')
                                            ->where('cursos_turmas.status', '=', 1)
                                            ->get();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
        
    }
    
    public function incluir() {
        
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        
        $listaCursos = Curso::select('cursos.titulo','faculdades.fantasia','cursos.id')
                            ->join('faculdades', 'cursos.fk_faculdade', '=', 'faculdades.id')
                            ->where('cursos.fk_cursos_tipo', '=', 2)
                            ->where('cursos.status', '>', 0)
                            ->orderby('faculdades.fantasia')
                            ->get();
        $this->arrayViewData['lista_cursos'] = [];
        foreach ($listaCursos as $curso){
            $this->arrayViewData['lista_cursos'][$curso->fantasia][$curso->id] = $curso->titulo;
        }
        
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }
    
    public function editar($id) {
        
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['turma'] = CursoTurma::findOrFail($id);
        $listaCursos = Curso::select('cursos.titulo','faculdades.fantasia','cursos.id')
                            ->join('faculdades', 'cursos.fk_faculdade', '=', 'faculdades.id')
                            ->where('cursos.fk_cursos_tipo', '=', 2)
                            ->where('cursos.status', '>', 0)
                            ->orderby('faculdades.fantasia')
                            ->get();
        
        $this->arrayViewData['lista_cursos'] = [];
        foreach ($listaCursos as $curso){
            $this->arrayViewData['lista_cursos'][$curso->fantasia][$curso->id] = $curso->titulo;
        }
                                                
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
        
    }
    
    public function salvar(Request $request) {
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $turma = new CursoTurma();
        $validator = Validator::make($request->all(), $turma->rules, $turma->messages);
        $dadosForm = $request->except('_token');
        $dadosForm = $this->insertAuditData($dadosForm, true);
        if (!$validator->fails()) {
            $resultado = $turma->create($dadosForm);

            if($resultado) {
                Session::flash('mensagem_sucesso', $this->msgInsert);
                return Redirect::back();
            } else {
                Session::flash('mensagem_erro', 'Não foi possível inserir o registro!');
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }
    public function atualizar($id, Request $request) {
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $turma = CursoTurma::findOrFail($id);
        $validator = Validator::make($request->all(), $turma->rules, $turma->messages);

        $dadosForm = $request->except('_token');
        $dadosForm = $this->insertAuditData($dadosForm, false);
        if (!$validator->fails()) {
            $resultado = $turma->update($dadosForm);
            if ($resultado) {
                Session::flash('mensagem_sucesso', $this->msgUpdate);
                return Redirect::back();
            } else {
                Session::flash('mensagem_erro', $this->msgUpdateErro);
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }
    public function deletar($id) {
        try {
            if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
            $turma = CursoTurma::findOrFail($id);
            $turma->status = 0;
            $turma->save();
            Session::flash('mensagem_sucesso', $this->msgDelete);
        } catch(\Exception $e) {
            Session::flash('mensagem_erro', $this->msgDeleteErro . " " . $e->getMessage());
        }
        return Redirect::back();
    }
}
