<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\Proposta;
use App\PropostaModulo;

class PropostaModuloController extends Controller
{

    public function index()
    {

        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['proposta_modulo'] = PropostaModulo::all()->where('status', '=', 1);
        $this->arrayViewData['lista_proposta'] = $this->carregaComboProposta();
        $this->arrayViewData['id_proposta'] = NULL;

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    public function carregar($id_proposta)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['proposta_modulo'] = PropostaModulo::all()->where('status', '=', 1)->get();
        $this->arrayViewData['lista_proposta'] = $this->carregaComboProposta();
        $this->arrayViewData['id_proposta'] = $id_proposta;

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    public function incluir()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['lista_proposta'] = $this->carregaComboProposta();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $proposta_modulo = PropostaModulo::findOrFail($id);
        $this->arrayViewData['proposta_modulo'] = $proposta_modulo;
        $this->arrayViewData['lista_proposta'] = Proposta::where('id', $proposta_modulo->fk_proposta)->pluck('titulo', 'id');;
        $this->arrayViewData['id_proposta'] = $proposta_modulo->fk_proposta;

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function vincular($id_proposta)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $proposta_modulo = PropostaModulo::all()->where('status', '=', 1)->get();
        $this->arrayViewData['proposta_modulo'] = $proposta_modulo;
        $this->arrayViewData['lista_proposta'] = $this->carregaComboProposta();
        $this->arrayViewData['id_proposta'] = $id_proposta;

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'),false)) return redirect()->route($this->redirecTo);
        $params = $request->all();
        $params = $this->insertAuditData($params);
        $proposta_modulo = new PropostaModulo($params);
        if ($request->hasFile('arquivo')) {
            $file = $request->file('arquivo');
            $proposta_modulo->arquivo = $this->uploadFile('arquivo', $file);
        }
        $validator = Validator::make($request->all(), $proposta_modulo->rules, $proposta_modulo->messages);
        if (!$validator->fails()) {
            $resultado = $proposta_modulo->save();

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
        $proposta_modulo = PropostaModulo::findOrFail($id);
        if ($request->hasFile('arquivo')) {
            $file = $request->file('arquivo');
            $proposta_modulo->arquivo = $this->uploadFile('arquivo', $file);
        } else {
            $proposta_modulo->arquivo = isset($proposta_modulo->arquivo) ? $proposta_modulo->arquivo : '';
        }
        $params = $request->all();

        $proposta_modulo->fk_proposta = $params['fk_proposta'];
        $proposta_modulo->ordem_modulo = $params['ordem_modulo'];
        $proposta_modulo->url_video = $params['url_video'];
        $proposta_modulo->duracao = $params['duracao'];

        $proposta_modulo->atualizacao = date('Y-m-d H:i:s');
        $proposta_modulo->fk_atualizador_id = $this->userLogged->id;
        $proposta_modulo->status = 1;

        $validator = Validator::make($request->all(), $proposta_modulo->rules, $proposta_modulo->messages);
        if (!$validator->fails()) {
            $resultado = $proposta_modulo->save();
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
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado

        if (!$this->validateAccess(\Session::get('user.logged'),false)) return redirect()->route($this->redirecTo);

        $obj = PropostaModulo::findOrFail($id);


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

    public function carregaComboProposta()
    {
        return Proposta::all()->pluck('titulo', 'id');
    }


    public function uploadFile($input, $file)
    {
        if ($file->isValid()) {
            $fileName = date('YmdHis') . '_' . $file->getClientOriginalName();
            if ($file->move('files/proposta_modulo/' . $input, $fileName)) {
                return $fileName;
            }
        }
        return '';
    }
}
