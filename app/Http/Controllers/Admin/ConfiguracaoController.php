<?php

namespace App\Http\Controllers\Admin;

use App\Configuracao;
use App\Faculdade;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use function redirect;

class ConfiguracaoController extends Controller{

    public function index(){
        
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['configuracoes'] = Configuracao::all()->where('status', '=', 1);
        $this->arrayViewData['lista_faculdades'] = Faculdade::all()->where('status', '=', 1)->pluck('fantasia', 'id');

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    public function incluir(){
        
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        
        $this->arrayViewData['lista_faculdades'] = Faculdade::all()->where('status', '=', 1)->pluck('fantasia', 'id');
        $this->arrayViewData['lista_faculdades']->put('', 'Selecione');
        $this->arrayViewData['lista_faculdades'] = $this->arrayViewData['lista_faculdades']->sortKeys();
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id){
        
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        
        $this->arrayViewData['configuracao'] = Configuracao::findOrFail($id);
        $this->arrayViewData['lista_faculdades'] = Faculdade::all()->where('status', '=', 1)->pluck('fantasia', 'id');

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);

    }

    public function salvar(Request $request){
        
        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);
        
        $params = $request->all();
        $configuracao = new Configuracao($params);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $configuracao->logo = $this->uploadFile('logo', $file);
        }
        if ($request->hasFile('banner_home')) {
            $file = $request->file('banner_home');
            $configuracao->banner_home = $this->uploadFile('banner_home', $file);
        }
        $validator = Validator::make($request->all(), $configuracao->rules, $configuracao->messages);
        if (!$validator->fails()) {

            $configuracao->criacao = date('Y-m-d H:i:s');
            $configuracao->fk_criador_id = $this->userLogged->id;
            $configuracao->status = 1;
            $configuracao->atualizacao = date('Y-m-d H:i:s');
            $configuracao->fk_atualizador_id = $this->userLogged->id;

            $resultado = $configuracao->save();

            if ($resultado) {
                Session::flash('mensagem_sucesso', $this->msgInsert);
                return Redirect::back();
            } else {
                Session::flash('mensagem_erro', $this->msgInsertErro);
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }

    public function atualizar($id, Request $request)
    {
        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);
        $configuracao = Configuracao::findOrFail($id);
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $configuracao->logo = $this->uploadFile('logo', $file);
        } else {
            $configuracao->logo = isset($configuracao->logo) ? $configuracao->logo : '';
        }
        if ($request->hasFile('banner_home')) {
            $file = $request->file('banner_home');
            $configuracao->banner_home = $this->uploadFile('banner_home', $file);
        } else {
            $configuracao->banner_home = isset($configuracao->banner_home) ? $configuracao->banner_home : '';
        }
        $params = $request->all();
        $configuracao->cor_principal = $params['cor_principal'];
        $configuracao->cor_secundaria = $params['cor_secundaria'];
        $configuracao->atualizacao = date('Y-m-d H:i:s');
        $configuracao->fk_atualizador_id = $this->userLogged->id;

        $validator = Validator::make($request->all(), $configuracao->rules, $configuracao->messages);
        if (!$validator->fails()) {
            $resultado = $configuracao->save();
            if ($resultado) {
                Session::flash('mensagem_sucesso', $this->msgUpdate);
                return Redirect::back();
            } else {
                Session::flash('mensagem_erro', $this->msgUpdateErro);
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }

    public function deletar($id, Request $request){
        
        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = Configuracao::findOrFail($id);

        $dadosForm = $request->except('_token');

        $dadosForm = $this->insertAuditData($dadosForm, false);
        $dadosForm['status'] = 0;

        $resultado = $obj->update($dadosForm);

        if ($resultado) {
            Session::flash('mensagem_sucesso', $this->msgDelete);
            return Redirect::back();
        } else {
            Session::flash('mensagem_erro', $this->msgDeleteErro);
        }
    }

    public function uploadFile($input, $file)
    {
        if ($file->isValid()) {
            $fileName = date('YmdHis') . '_' . $file->getClientOriginalName();
            if ($file->move('files/configuracao/' . $input, $fileName)) {
                return $fileName;
            }
        }
        return '';
    }
}
