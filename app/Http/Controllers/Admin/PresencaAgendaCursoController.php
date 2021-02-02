<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

use App\CursoTurmaAgendaPresenca;
use App\CursoTurmaAgenda;
use App\Curso;

class PresencaAgendaCursoController extends Controller
{
    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['agendas'] = CursoTurmaAgenda::select('cursos_turmas_agenda.*', 'cursos.titulo')
            ->join('cursos_turmas', 'cursos_turmas_agenda.fk_turma', '=', 'cursos_turmas.id')
            ->join('cursos', 'cursos_turmas.fk_curso', '=', 'cursos.id')
            ->where('cursos_turmas_agenda.status', 1)
            ->get();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);

    }

    public function lista_presenca($id_agenda_curso) {

        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['lista_presenca'] = CursoTurmaAgendaPresenca::select('cursos_turmas_agenda.*', 'cursos.titulo')
            ->join('cursos_turmas', 'cursos_turmas_agenda.fk_turma', '=', 'cursos_turmas.id')
            ->join('cursos', 'cursos_turmas.fk_curso', '=', 'cursos.id')
            ->join('cursos_turmas_agenda_presenca', 'cursos_turmas_agenda_presenca.fk_agenda', '=', 'cursos_turmas_agenda.id')
            ->where('cursos_turmas_agenda.status', 1)
            ->where('cursos_turmas_agenda_presenca.fk_agenda', $id_agenda_curso)
            ->get();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista_presenca', $this->arrayViewData);

    }

    public function incluir($id_agenda_curso)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $trilha = Trilha::select('trilha.titulo as nome_trilha')
            ->where('trilha.id', $id_trilha)
            ->first();

        $lista_cursos = Curso::all()->pluck('titulo', 'id');

        $this->arrayViewData['id_agenda_curso'] = $id_agenda_curso;
        $this->arrayViewData['nome_trilha'] = $trilha->nome_trilha;
        $this->arrayViewData['lista_cursos'] = $lista_cursos;

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $presenca_agenda_curso = CursoTurmaAgendaPresenca::select('cursos_turmas_agenda_presenca.*', 'curso.titulo as nome_curso')
            ->join('cursos_turmas_agenda', 'cursos_turmas_agenda_presenca.fk_agenda', '=', 'cursos_turmas_agenda.id')
            ->join('cursos_turmas', 'cursos_turmas_agenda.fk_turma', '=', 'cursos_turmas.id')
            ->join('cursos', 'cursos_turmas.fk_curso', '=', 'cursos.id')
            ->where('cursos_turmas_agenda_presenca.id', $id)
            ->first();

        $this->arrayViewData['id'] = $id;
        $this->arrayViewData['trilha_curso'] = $presenca_agenda_curso;
        $this->arrayViewData['nome_trilha'] = $trilha_curso->nome_trilha;

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
                return redirect()->route('admin.trilha_curso', $request->input('fk_trilha'));
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
                return redirect()->route('admin.trilha_curso', $trilha->fk_trilha);
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

        $obj = CursoTurmaAgendaPresenca::findOrFail($id);

        $dadosForm = $request->except('_token');

        $dadosForm = $this->insertAuditData($dadosForm, false);
        $dadosForm['status'] = 0;

        $resultado = $obj->update($dadosForm);

        if ($resultado) {
            \Session::flash('mensagem_sucesso', $this->msgDelete);
            return redirect()->route('admin.presenca_agenda_curso');
        } else {
            \Session::flash('mensagem_erro', $this->msgDeleteErro);
        }
    }
}