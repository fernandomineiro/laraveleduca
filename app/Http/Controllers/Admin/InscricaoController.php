<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\CursoTurmaInscricao;

use App\Trilha;
use App\Curso;
use App\Faculdade;
use App\Usuario;

class InscricaoController extends Controller
{

    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['inscricoes'] = CursoTurmaInscricao::select('cursos_turmas_inscricao.*', 'usuarios.nome as nome_usuario', 'cursos.titulo as nome_curso', 'cursos_turmas.nome as nome_turma')
            ->join('cursos_turmas', 'cursos_turmas.id', '=', 'cursos_turmas_inscricao.fk_turma')
            ->join('cursos', 'cursos_turmas.fk_curso', '=', 'cursos.id')        
            ->join('usuarios', 'cursos_turmas_inscricao.fk_usuario', '=', 'usuarios.id')
            ->get();

        $lista_faculdade = Faculdade::all()->pluck('titulo', 'id')->toArray();
        $lista_cursos = Curso::select('titulo', 'id', 'fk_faculdade')->where('status', '=', 1)->get();

        $lista = [];
        foreach ($lista_cursos as $k => $curso) {
            if (isset($lista_faculdade[$curso->fk_faculdade])) {
                $lista[$curso->id] = $lista_faculdade[$curso->fk_faculdade] . ' - ' . $curso->titulo;
            }
        }
        $this->arrayViewData['lista_cursos'] = $lista;

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    public function editar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $this->arrayViewData['inscricao'] = CursoTurmaInscricao::findOrFail($id);

        $lista_faculdade = Faculdade::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $lista_cursos = Curso::select('titulo', 'id', 'fk_faculdade')->where('status', '=', 1)->get();

        $lista = array();
        foreach($lista_cursos as $k => $curso) {
            if(isset($lista_faculdade[$curso->fk_faculdade])) {
                $lista[$curso->id] = $lista_faculdade[$curso->fk_faculdade] . ' - ' . $curso->titulo;
            }
        }
        $this->arrayViewData['lista_cursos'] = $lista;
        $this->arrayViewData['lista_usuarios'] = Usuario::all()->where('status', '=', 1)->pluck('nome', 'id');

        $lista_percentual = ['0' => '0'];
        for ($i = 5; $i <= 100; $i = $i + 5) {
            $lista_percentual[$i] = $i . ' %';
        }
        $this->arrayViewData['lista_percentual'] = $lista_percentual;          

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function atualizar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'),false)) return redirect()->route($this->redirecTo);
        $curso = CursoTurmaInscricao::findOrFail($id);
        $validator = Validator::make($request->all(), $curso->rules, $curso->messages);

        $dadosForm = $request->except('_token');

        if (!$validator->fails()) {
            $resultado = $curso->update($dadosForm);
            if ($resultado) {
                \Session::flash('mensagem_sucesso', $this->msgUpdate);
                return Redirect::back();
            } else {
                \Session::flash('mensagem_erro', $this->msgUpdateErro);
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }


    public function deletar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = CursoTurmaInscricao::findOrFail($id);
        $dadosForm = $request->except('_token');

        $dadosForm = $this->insertAuditData($dadosForm, false);
        $dadosForm['status'] = 0;

        $resultado = $obj->update($dadosForm);

        if ($resultado) {
            \Session::flash('mensagem_sucesso', $this->msgDelete);
            return Redirect::back();
        } else {
            \Session::flash('mensagem_erro', $this->msgDeleteErro);
        }
    }
}
