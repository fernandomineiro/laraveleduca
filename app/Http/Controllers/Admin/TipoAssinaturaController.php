<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\TipoAssinatura;

class TipoAssinaturaController extends Controller
{

    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['tipo_assinatura'] = TipoAssinatura::all()->where('status','=',1);
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
        $this->arrayViewData['tipo_assinatura'] = TipoAssinatura::findOrFail($id);
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $tipo_assinatura = new TipoAssinatura();
        $validator = Validator::make($request->all(), $tipo_assinatura->rules, $tipo_assinatura->messages);

        if (!$validator->fails()) {
            $dadosForm = $request->except('_token');

            $dadosForm = $this->insertAuditData($dadosForm);
            $resultado = $tipo_assinatura->create($dadosForm);

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
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $tipo_assinatura = TipoAssinatura::findOrFail($id);
        $validator = Validator::make($request->all(), $tipo_assinatura->rules, $tipo_assinatura->messages);

        if (!$validator->fails()) {

            $dadosForm = $request->except('_token');

            $dadosForm = $this->insertAuditData($dadosForm, false);

            $resultado = $tipo_assinatura->update($dadosForm);

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

        $obj = TipoAssinatura::findOrFail($id);

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
