<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\UsuariosModulosAcoes;
use App\UsuariosModulosElementos;

class UsuariosmodulosacoesController extends Controller
{
    public function index()
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado

        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        //Array com dados para a view
        $this->arrayViewData['lstObj'] = UsuariosModulosAcoes::select('usuarios_modulos_acoes.*', 'usuarios_modulos_elementos.descricao AS elemento')
        ->join('usuarios_modulos_elementos', 'usuarios_modulos_acoes.fk_elemento_id', '=', 'usuarios_modulos_elementos.id')
        ->where('usuarios_modulos_acoes.status','=',1)
        ->get();

        //modelo proposto para envio de dado a views
        return view('usuario.modulos.acao.lista', $this->arrayViewData);
    }


    public function incluir()
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $this->arrayViewData['elemento'] = UsuariosModulosElementos::all()->where('status', 1)->pluck('descricao', 'id');
        return view('usuario.modulos.acao.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        //Array com dados para a view
        $this->arrayViewData['obj'] = UsuariosModulosAcoes::findOrFail($id);
        $this->arrayViewData['elemento'] = UsuariosModulosElementos::all()->where('status', 1)->pluck('descricao', 'id');

        return view('usuario.modulos.acao.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota, passa-se false para nao carregar variaveis de views
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = new UsuariosModulosAcoes();
        $validator = Validator::make($request->all(), $obj->rules, $obj->messages);

        if (!$validator->fails()) {

            $dadosForm = $request->except('_token');
            $dadosForm = $this->insertAuditData($dadosForm);

            $resultado = $obj->create($dadosForm);

            if ($resultado) {
                \Session::flash('mensagem_sucesso', $this->msgInsert);
                return redirect()->route('admin.usuariosmodulosacoes');
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
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota, passa-se false para nao carregar variaveis de views
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = UsuariosModulosAcoes::findOrFail($id);
        $validator = Validator::make($request->all(), $obj->rules, $obj->messages);

        if (!$validator->fails()) {

            $dadosForm = $request->except('_token');

            $dadosForm = $this->insertAuditData($dadosForm, false);


            $resultado = $obj->update($dadosForm);
            if ($resultado) {
                \Session::flash('mensagem_sucesso', $this->msgUpdate);
                return redirect()->route('admin.usuariosmodulosacoes');
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
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota, passa-se false para nao carregar variaveis de views
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = UsuariosModulosAcoes::findOrFail($id);

        $dadosForm = $request->except('_token');

        $dadosForm = $this->insertAuditData($dadosForm, false);
        $dadosForm['status'] = 0;

        $resultado = $obj->update($dadosForm);

        if ($resultado) {
            \Session::flash('mensagem_sucesso', $this->msgDelete);
            return redirect()->route('admin.usuariosmodulosacoes');
        } else {
            \Session::flash('mensagem_erro', $this->msgDeleteErro);
        }
    }
}
