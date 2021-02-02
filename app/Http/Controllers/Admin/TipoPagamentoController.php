<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\TipoPagamento;
use App\Wirecard;

class TipoPagamentoController extends Controller
{

    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['tipo_pagamento'] = TipoPagamento::all()->where('status','=',1);
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
        $this->arrayViewData['tipo_pagamento'] = TipoPagamento::findOrFail($id);
        
        $view = $this->arrayViewData['modulo']['moduloDetalhes']->view . '.' . $this->arrayViewData['tipo_pagamento']->codigo;

        return view($view, $this->arrayViewData);
    }

    public function atualizar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $tipo_pagamento = TipoPagamento::findOrFail($id);
        $validator = Validator::make($request->all(), $tipo_pagamento->rules, $tipo_pagamento->messages);

        if (isset($request['url_retorno']) && !empty($request['url_retorno'])){
            Wirecard::createReferenceNotification($request->getSchemeAndHttpHost(), $request['url_retorno']);
        }

        if (!$validator->fails()) {

            $dadosForm = $request->except('_token');

            $dadosForm = $this->insertAuditData($dadosForm, false);
            
            $resultado = $tipo_pagamento->update($dadosForm);

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

        $obj = TipoPagamento::findOrFail($id);

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
