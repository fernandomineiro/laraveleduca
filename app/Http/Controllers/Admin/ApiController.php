<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

use App\Api;
use App\Faculdade;

class ApiController extends Controller
{
    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['apis'] = Api::select('apis.*')
            ->where('apis.status', 1)
            ->get();

        $this->carregaCombos();
        
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    public function incluir()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->carregaCombos();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->carregaCombos();

        $this->arrayViewData['api'] = Api::findOrFail($id);
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'),false)) return redirect()->route($this->redirecTo);
        $obj = new Api();

        $dadosForm = $request->except('_token');

        $dadosForm['params'] = isset($dadosForm['params']) ? json_encode($dadosForm['params']) : [];
        $validator = Validator::make($dadosForm, $obj->rules, $obj->messages);

        if (!$validator->fails()) {
            
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

        $obj = Api::findOrFail($id);

        $dadosForm = $request->except('_token');
        $dadosForm['params'] =  isset($dadosForm['params']) ? json_encode($dadosForm['params']) : [];
        
        $validator = Validator::make($dadosForm, $obj->rules, $obj->messages);

        if (!$validator->fails()) {
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

        $obj = Api::findOrFail($id);

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

    private function carregaCombos() 
    {
        $lista_faculdade = Faculdade::all()->where('status', '=', 1)->pluck('fantasia', 'id');

        $this->arrayViewData['lista_faculdades'] = $lista_faculdade;
        $this->arrayViewData['lista_tipos'] = [
            'GET' => 'GET',
            'POST' => 'POST',
            'PUT' => 'PUT',
            'PATCH' => 'PATCH',
            'DELETE' => 'DELETE'
        ];        
    }
}
