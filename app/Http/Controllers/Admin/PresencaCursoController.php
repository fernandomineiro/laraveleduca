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
use App\Usuario;

class PresencaCursoController extends Controller
{
    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['agendas'] = CursoTurmaAgenda::select('cursos_turmas_agenda.*', 'cursos.titulo', 'cursos_turmas.nome as turma')
            ->join('cursos_turmas', 'cursos_turmas_agenda.fk_turma', '=', 'cursos_turmas.id')
            ->join('cursos', 'cursos.id', '=', 'cursos_turmas.fk_curso')
            ->where('cursos_turmas_agenda.status', 1)
            ->get();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);

    }

    public function listapresenca($id_agenda_curso) {

        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $lista_cursos = Curso::all()->pluck('titulo', 'id');

        $this->arrayViewData['id_agenda_curso'] = $id_agenda_curso;
        $this->arrayViewData['agenda_curso'] = CursoTurmaAgenda::all()->where('id', '=', $id_agenda_curso)->first();
        $this->arrayViewData['lista_cursos'] = $lista_cursos;
        $this->arrayViewData['lista_presente'] = array('0' => 'Não', '1' => 'Sim');
        

        $this->arrayViewData['presencas'] = CursoTurmaAgendaPresenca::select('cursos_turmas_agenda_presenca.*', 'cursos.titulo', 'usuarios.nome as nome_aluno', 'cursos_turmas_agenda_presenca.*')
            ->join('cursos_turmas_agenda', 'cursos_turmas_agenda_presenca.fk_agenda', '=', 'cursos_turmas_agenda.id')
            ->join('cursos_turmas', 'cursos_turmas_agenda.fk_turma', '=', 'cursos_turmas.id')
            ->join('cursos', 'cursos_turmas.fk_curso', '=', 'cursos.id')
            ->join('usuarios', 'usuarios.id', '=', 'cursos_turmas_agenda_presenca.fk_usuario')
            ->where('cursos_turmas_agenda.status', 1)
            ->where('cursos_turmas_agenda_presenca.fk_agenda', $id_agenda_curso)
            ->get();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.listapresenca', $this->arrayViewData);
    }

    public function incluir($id_agenda_curso)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $lista_cursos = Curso::all()->pluck('titulo', 'id');

        $agenda_curso = CursoTurmaAgenda::select('cursos_turmas_agenda.*', 'cursos_turmas.fk_curso')
                ->join('cursos_turmas', 'cursos_turmas_agenda.fk_turma', '=', 'cursos_turmas.id')
                ->where('cursos_turmas_agenda.id', '=', $id_agenda_curso)->first();

        $this->arrayViewData['id_agenda_curso'] = $id_agenda_curso;
        $this->arrayViewData['lista_cursos'] = $lista_cursos;
        $this->arrayViewData['agenda_curso'] = $agenda_curso;
        $this->arrayViewData['lista_alunos'] = Usuario::all()->where('status', 1)->pluck('nome', 'id');
        

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $presenca_agenda_curso = CursoTurmaAgendaPresenca::select('cursos_turmas_agenda_presenca.*', 'cursos.titulo as nome_curso')
            ->join('cursos_turmas_agenda', 'cursos_turmas_agenda_presenca.fk_agenda', '=', 'cursos_turmas_agenda.id')
            ->join('cursos_turmas', 'cursos_turmas_agenda.fk_turma', '=', 'cursos_turmas.id')
            ->join('cursos', 'cursos_turmas.fk_curso', '=', 'cursos.id')
            ->join('usuarios', 'usuarios.id', '=', 'cursos_turmas_agenda_presenca.fk_usuario')
            ->where('cursos_turmas_agenda.status', 1)
            ->where('cursos_turmas_agenda_presenca.id', $id)
            ->first();

        $this->arrayViewData['id'] = $id;
        $this->arrayViewData['presenca_agenda_curso'] = $presenca_agenda_curso;
        $this->arrayViewData['lista_alunos'] = Usuario::all()->where('status', 1)->pluck('nome', 'id');

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);

    }

    public function salvar(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $presenca = new CursoTurmaAgendaPresenca();

        $validator = Validator::make($request->all(), $presenca->rules, $presenca->messages);
        if (!$validator->fails()) {

            $dadosForm = $request->except('_token');
            $dadosForm = $this->insertAuditData($dadosForm);

            if ($presenca->create($dadosForm)) {
                \Session::flash('mensagem_sucesso', 'Cadastrado com Sucesso!');
                return Redirect::back();
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

        $presenca = CursoTurmaAgendaPresenca::findOrFail($id);
        $validator = Validator::make($request->all(), $presenca->rules, $presenca->messages);

        if (!$validator->fails()) {

            $dadosForm = $request->except('_token');
            $dadosForm = $this->insertAuditData($dadosForm, false);

            if ($presenca->update($dadosForm)) {
                \Session::flash('mensagem_sucesso', 'Atualizado com Sucesso!');
                return Redirect::back();
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
            return redirect()->route('admin.presenca_curso');
        } else {
            \Session::flash('mensagem_erro', $this->msgDeleteErro);
        }
    }
}
