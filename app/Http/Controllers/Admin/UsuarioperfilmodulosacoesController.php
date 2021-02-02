<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\UsuariosModulosAcoes;
use App\UsuariosPerfil;
use App\UsuarioPerfilModulosAcoes;

class UsuarioperfilmodulosacoesController extends Controller
{

    public function index()
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado

        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        //Array com dados para a view
        $this->arrayViewData['lstObj'] = UsuarioPerfilModulosAcoes::select('usuarios_modulos_x_acoes.id', 'usuarios_modulos_x_acoes.status', 'usuarios_modulos.descricao AS modulo', 'usuarios_modulos_acoes.descricao AS acao')
            ->join('usuarios_modulos', 'usuarios_modulos_x_acoes.fk_modulo_id', '=', 'usuarios_modulos.id')
            ->join('usuarios_modulos_acoes', 'usuarios_modulos_x_acoes.fk_acao_id', '=', 'usuarios_modulos_acoes.id')
            ->where('usuarios_modulos_x_acoes.status','=',1)
            ->get();

        //modelo proposto para envio de dado a views
        return view('usuario.perfil.pxmxa.lista', $this->arrayViewData);
    }

    public function incluir()
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['modulosAcoes'] = UsuariosModulos::all()->where('status', 1)->pluck('descricao', 'id');
        $this->arrayViewData['perfil'] =  UsuariosPerfil::all()->where('status', 1)->pluck('titulo', 'id');


        return view('usuario.perfil.pxmxa.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        //Array com dados para a view
        $this->arrayViewData['obj'] = UsuariosMxA::findOrFail($id);
        $this->arrayViewData['modulos'] = UsuariosModulos::all()->where('status', 1)->pluck('descricao', 'id');
        $this->arrayViewData['acao'] =  UsuariosModulosAcoes::all()->where('status', 1)->pluck('descricao', 'id');

        return view('usuario.perfil.pxmxa.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota, passa-se false para nao carregar variaveis de views
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = new UsuariosMxA();
        $validator = Validator::make($request->all(), $obj->rules, $obj->messages);

        if (!$validator->fails()) {

            $dadosForm = $request->except('_token');
            $dadosForm = $this->insertAuditData($dadosForm);

            $resultado = $obj->create($dadosForm);

            if ($resultado) {
                \Session::flash('mensagem_sucesso', $this->msgInsert);
                return redirect()->route('admin.usuariospxmxa');
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

        $obj = UsuariosMxA::findOrFail($id);
        $validator = Validator::make($request->all(), $obj->rules, $obj->messages);

        if (!$validator->fails()) {

            $dadosForm = $request->except('_token');

            $dadosForm = $this->insertAuditData($dadosForm, false);


            $resultado = $obj->update($dadosForm);
            if ($resultado) {
                \Session::flash('mensagem_sucesso', $this->msgUpdate);
                return redirect()->route('admin.usuariospxmxa');
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

        $obj = UsuariosMxA::findOrFail($id);

        $dadosForm = $request->except('_token');

        $dadosForm = $this->insertAuditData($dadosForm, false);
        $dadosForm['status'] = 0;

        $resultado = $obj->update($dadosForm);

        if ($resultado) {
            \Session::flash('mensagem_sucesso', $this->msgDelete);
            return redirect()->route('admin.usuariospxmxa');
        } else {
            \Session::flash('mensagem_erro', $this->msgDeleteErro);
        }
    }
}
