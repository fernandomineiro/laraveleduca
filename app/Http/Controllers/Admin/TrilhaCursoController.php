<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

use App\TrilhaCurso;
use App\Trilha;
use App\Curso;
use App\Faculdade;

class TrilhaCursoController extends Controller
{
    public function index($id_trilha)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $trilha_cursos = TrilhaCurso::select('trilha_curso.*', 'cursos.titulo as nome_curso', 'cursos.fk_faculdade')
            ->join('cursos', 'cursos.id', '=', 'trilha_curso.fk_curso')
            ->where('trilha_curso.fk_trilha', $id_trilha)
            ->where('trilha_curso.status', 1)
            ->get();

        $trilha = Trilha::select('titulo')->where('trilha.id', $id_trilha)->first();

        $this->arrayViewData['lista_faculdades'] = Faculdade::all()->where('status', '=', 1)->pluck('fantasia', 'id');
        $this->arrayViewData['trilha_cursos'] = $trilha_cursos;
        $this->arrayViewData['trilha'] = $trilha;

        $this->arrayViewData['nome_trilha'] = $trilha->titulo;
        $this->arrayViewData['id_trilha'] = $id_trilha;

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    public function incluir($id_trilha)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $trilha = Trilha::select('trilha.titulo as nome_trilha')
            ->where('trilha.id', $id_trilha)
            ->first();

        $lista_faculdade = Faculdade::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $lista_cursos = Curso::select('titulo', 'id', 'fk_faculdade')->where('status', '=', 1)->where('fk_cursos_tipo', '=', 1)->get();

        $lista = array();
        foreach($lista_cursos as $k => $curso) {
            $lista[$curso->id] = $lista_faculdade[$curso->fk_faculdade] . ' - ' . $curso->titulo;
        }
        $this->arrayViewData['lista_cursos'] = $lista;

        $this->arrayViewData['id_trilha'] = $id_trilha;
        $this->arrayViewData['nome_trilha'] = $trilha->nome_trilha;

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $trilha_curso = TrilhaCurso::select('trilha_curso.*', 'trilha.titulo as nome_trilha')
            ->join('trilha', 'trilha.id', '=', 'trilha_curso.fk_trilha')
            ->where('trilha_curso.id', $id)
            ->first();

        $lista_cursos = Curso::all()->pluck('titulo', 'id')->toArray();

        $this->arrayViewData['id'] = $id;
        $this->arrayViewData['trilha_curso'] = $trilha_curso;
        $this->arrayViewData['nome_trilha'] = $trilha_curso->nome_trilha;
        $this->arrayViewData['lista_cursos'] = $lista_cursos;

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);

    }

    public function salvar(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $trilha = new TrilhaCurso();

        $validator = Validator::make($request->all(), $trilha->rules, $trilha->messages);
        if (!$validator->fails()) {

            $dadosForm = $request->except('_token');
            $dadosForm = $this->insertAuditData($dadosForm);

            if ($trilha->create($dadosForm)) {
                \Session::flash('mensagem_sucesso', 'Cadastrado com Sucesso!');
                return redirect('/admin/trilha_curso/' . $request->input('fk_trilha') . '/index');
            } else {
                \Session::flash('mensagem_erro', 'Não foi possível inserir o registro!');
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }


    public function atualizar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $trilha = TrilhaCurso::findOrFail($id);
        $validator = Validator::make($request->all(), $trilha->rules, $trilha->messages);

        if (!$validator->fails()) {

            $dadosForm = $request->except('_token');

            $dadosForm = $this->insertAuditData($dadosForm, false);

            if ($trilha->update($dadosForm)) {
                \Session::flash('mensagem_sucesso', 'Atualizado com Sucesso!');
                return redirect('/admin/trilha_curso/' . $trilha->fk_trilha . '/index');
            } else {
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }


    public function deletar($id, Request $request)
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota, passa-se false para nao carregar variaveis de views
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = TrilhaCurso::findOrFail($id);

        $dadosForm = $request->except('_token');

        $dadosForm = $this->insertAuditData($dadosForm, false);
        $dadosForm['status'] = 0;

        $resultado = $obj->update($dadosForm);

        if ($resultado) {
            \Session::flash('mensagem_sucesso', $this->msgDelete);
            return redirect('/admin/trilha_curso/' . $obj->fk_trilha . '/index');
        } else {
            \Session::flash('mensagem_erro', $this->msgDeleteErro);
        }
    }
}