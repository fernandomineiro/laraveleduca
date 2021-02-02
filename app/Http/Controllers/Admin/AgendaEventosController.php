<?php

namespace App\Http\Controllers\Admin;

use App\Professor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
//
use App\AgendaEventos;
use App\Eventos;

class AgendaEventosController extends Controller
{
    public function index($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['agenda_eventos'] = AgendaEventos::select('agenda_evento.*', 'eventos.titulo')
            ->join('eventos', 'agenda_evento.fk_evento', '=', 'eventos.id')
            ->where('agenda_evento.status', 1)
            ->where('agenda_evento.fk_evento', $id)
            ->get();
        $this->arrayViewData['evento'] = $id;

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    public function incluir($id_evento)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['eventos'] = Eventos::all()->pluck('titulo', 'id');
        $this->arrayViewData['id_evento'] = $id_evento;
        $professores = Professor::select('professor.*')
            ->join('usuarios', 'professor.fk_usuario_id', '=', 'usuarios.id')
            ->where('professor.status', '=', 1)
            ->get();

        $lista_professor = array();
        foreach ($professores as $professor) {
            $lista_professor[$professor->id] = $professor->nome . ' ' . $professor->sobrenome;
        }

        $this->arrayViewData['lista_professor'] = $lista_professor;

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['agenda_eventos'] = AgendaEventos::findOrFail($id);
        $this->arrayViewData['eventos'] = Eventos::all()->pluck('titulo', 'id');

        $professores = Professor::select('professor.*')
            ->join('usuarios', 'professor.fk_usuario_id', '=', 'usuarios.id')
            ->where('professor.status', '=', 1)
            ->get();

        $lista_professor = array();
        foreach ($professores as $professor) {
            $lista_professor[$professor->id] = $professor->nome . ' ' . $professor->sobrenome;
        }

        $this->arrayViewData['lista_professor'] = $lista_professor;

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'),false)) return redirect()->route($this->redirecTo);
        $obj = new AgendaEventos();
        $dadosForm = $request->except('_token');

        $hrInicio = explode(':', $dadosForm['hora_inicio']);
        $hrFim = explode(':', $dadosForm['hora_final']);

        $dadosForm['hora_inicio'] = trim($hrInicio[0]) . ':' . trim($hrInicio[1]);
        $dadosForm['hora_final'] = trim($hrFim[0]) . ':' . trim($hrFim[1]);

        $dadosForm['data_inicio'] = implode('-', array_reverse(explode('/', $dadosForm['data_inicio'])));
        $dadosForm['data_final'] = implode('-', array_reverse(explode('/', $dadosForm['data_final'])));

        $validator = Validator::make($dadosForm, $obj->rules, $obj->messages);

        if (!$validator->fails()) {

            $dadosForm['valor'] = str_replace(',', '.', str_replace('.', '', $dadosForm['valor']));

            $dadosForm = $this->insertAuditData($dadosForm);

            $resultado = $obj->create($dadosForm);

            if ($resultado) {
                \Session::flash('mensagem_sucesso', $this->msgInsert);
                return Redirect::back();
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
        $obj = AgendaEventos::findOrFail($id);
        $dadosForm = $request->except('_token');

        $dadosForm['data_inicio'] = implode('-', array_reverse(explode('/', $dadosForm['data_inicio'])));
        $dadosForm['data_final'] = implode('-', array_reverse(explode('/', $dadosForm['data_final'])));

        $rules = [
            'fk_evento' => 'required',
            'descricao' => 'required',
            'data_inicio' => 'required|date',
            'data_final' => 'sometimes|required|date|after_or_equal:data_inicio',
            'hora_inicio' => 'required',
            'hora_final' => 'required',
        ];

        $validator = Validator::make($dadosForm, $rules, $obj->messages);

        if (!$validator->fails()) {

            $hrInicio = explode(':', $dadosForm['hora_inicio']);
            $hrFim = explode(':', $dadosForm['hora_final']);

            $dadosForm['hora_inicio'] = trim($hrInicio[0]) . ':' . trim($hrInicio[1]);
            $dadosForm['hora_final'] = trim($hrFim[0]) . ':' . trim($hrFim[1]);

            $dadosForm['valor'] = str_replace(',', '.', str_replace('.', '', $dadosForm['valor']));


            $dadosForm = $this->insertAuditData($dadosForm, false);

            $resultado = $obj->update($dadosForm);

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

        $obj = AgendaEventos::findOrFail($id);

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
