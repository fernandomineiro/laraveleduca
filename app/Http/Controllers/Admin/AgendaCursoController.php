<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
//
use App\CursoTurmaAgenda;
use App\Curso;
use App\Faculdade;
use App\CursoTurma;

class AgendaCursoController extends Controller
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

    public function incluir()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $lista_faculdade = Faculdade::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $lista_cursos = CursoTurma::select('cursos.titulo', 'cursos_turmas.id', 'cursos_turmas.nome','cursos.fk_faculdade')
            ->join('cursos', 'cursos_turmas.fk_curso', '=', 'cursos.id')
            ->where('cursos_turmas.status', '=', 1)->get();

        $lista = array();
        foreach($lista_cursos as $k => $curso) {
            $lista[$curso->id] = $lista_faculdade[$curso->fk_faculdade] . 'Curso: ' . $curso->titulo . ' - Turma: ' . $curso->nome;
        }
        $this->arrayViewData['lista_cursos'] = $lista;
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['agenda_curso'] = CursoTurmaAgenda::findOrFail($id);
        $this->arrayViewData['agenda_curso']->data_inicio = implode('/', array_reverse(explode('-', $this->arrayViewData['agenda_curso']->data_inicio)));
        $this->arrayViewData['agenda_curso']->data_final = implode('/', array_reverse(explode('-', $this->arrayViewData['agenda_curso']->data_final)));
        $lista_faculdade = Faculdade::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $lista_cursos = Curso::select('titulo', 'id', 'fk_faculdade')->where('status', '=', 1)->get();

        $lista = array();
        foreach($lista_cursos as $k => $curso) {
            $lista[$curso->id] = $lista_faculdade[$curso->fk_faculdade] . 'Curso: ' . $curso->titulo . ' - Turma: ' . $curso->nome;
        }
        $this->arrayViewData['lista_cursos'] = $lista;

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'),false)) return redirect()->route($this->redirecTo);
        $obj = new CursoTurmaAgenda();
        $dadosForm = $request->except('_token');

        $hrInicio = explode(':', $dadosForm['hora_inicio']);
        $hrFim = explode(':', $dadosForm['hora_final']);

        $dadosForm['hora_inicio'] = trim($hrInicio[0]) . ':' . trim($hrInicio[1]);
        $dadosForm['hora_final'] = trim($hrFim[0]) . ':' . trim($hrFim[1]);

        $dadosForm['data_inicio'] = implode('-', array_reverse(explode('/', $dadosForm['data_inicio'])));
        $dadosForm['data_final'] = implode('-', array_reverse(explode('/', $dadosForm['data_final'])));

        $validator = Validator::make($dadosForm, $obj->rules, $obj->messages);

        if (!$validator->fails()) {

            $dadosForm = $this->insertAuditData2($dadosForm);

            $resultado = $obj->create($dadosForm);

            if ($resultado) {
                \Session::flash('mensagem_sucesso', $this->msgInsert);
                return redirect('admin/agenda_curso/index');
            } else {
                \Session::flash('mensagem_erro', $this->msgInsertErro);
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }


    public function atualizar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'),false)) return redirect()->route($this->redirecTo);
        $obj = CursoTurmaAgenda::findOrFail($id);

        $dadosForm = $request->except('_token');

        $hrInicio = explode(':', $dadosForm['hora_inicio']);
        $hrFim = explode(':', $dadosForm['hora_final']);

        $dadosForm['hora_inicio'] = trim($hrInicio[0]) . ':' . trim($hrInicio[1]);
        $dadosForm['hora_final'] = trim($hrFim[0]) . ':' . trim($hrFim[1]);

        $dadosForm['data_inicio'] = implode('-', array_reverse(explode('/', $dadosForm['data_inicio'])));
        $dadosForm['data_final'] = implode('-', array_reverse(explode('/', $dadosForm['data_final'])));

        $rules = [
            'nome' => 'required',
            'hora_inicio' => 'required',
            'hora_final' => 'required',
            'data_inicio' => 'required|date',
            'data_final' => 'sometimes|required|date|after_or_equal:data_inicio'
        ];
        $validator = Validator::make($dadosForm, $rules, $obj->messages);

        if (!$validator->fails()) {
            //$dadosForm = $this->insertAuditData2($dadosForm, false);

            $dadosForm['atualizacao'] = date('Y-m-d H:i:s');     //Data e Hora de criação sempre se altera com Atualiza/Deletar
            $dadosForm['fk_atualizador'] = $this->userLogged->id;     //Id do usuário que criou, alterou ou excluiu sempre altera Atualiza/Deletar


            $resultado = $obj->update($dadosForm);

            if ($resultado) {
                \Session::flash('mensagem_sucesso', $this->msgUpdate);
                return redirect('admin/agenda_curso/index');
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

        $obj = CursoTurmaAgenda::findOrFail($id);

        $dadosForm = $request->except('_token');

        $dadosForm = $this->insertAuditData2($dadosForm, false);
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
