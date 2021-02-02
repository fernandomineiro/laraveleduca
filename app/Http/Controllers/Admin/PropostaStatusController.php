<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\PropostaStatus;

class PropostaStatusController extends Controller
{

    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['propostas_status'] = PropostaStatus::all()->where('status', '=', 1);
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    public function incluir()
    {

        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['propostas_status'] = PropostaStatus::findOrFail($id);
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }


    public function salvar(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'),false)) return redirect()->route($this->redirecTo);
        $params = $request->all();
        $params = $this->insertAuditData($params);
        $proposta_status = new PropostaStatus($params);
        $validator = Validator::make($request->all(), $proposta_status->rules, $proposta_status->messages);
        if (!$validator->fails()) {
            $resultado = $proposta_status->save();
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
        $proposta_status = PropostaStatus::findOrFail($id);
        $params = $request->all();
        $proposta_status->atualizacao = date('Y-m-d H:i:s');
        $proposta_status->fk_atualizador_id = $this->userLogged->id;
        $proposta_status->status = 1;
        $proposta_status->titulo = $params['titulo'];

        $validator = Validator::make($request->all(), $proposta_status->rules, $proposta_status->messages);
        if (!$validator->fails()) {
            $resultado = $proposta_status->save();
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

        $obj = PropostaStatus::findOrFail($id);

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
