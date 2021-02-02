<?php

namespace App\Http\Controllers\Admin;

use App\UsuarioPerfilModulosAcoes;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\UsuariosPxMxA;
use App\ViewPerfilModulosAcoes;
use App\ViewUsuariosModulosAcoes;
use App\UsuariosPerfil;

class UsuariospxmxaController extends Controller
{

    public function index()
    {

        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['lstObj'] = ViewPerfilModulosAcoes::get();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }


    public function incluir()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $this->arrayViewData['modulosAcoes'] = ViewUsuariosModulosAcoes::all()->pluck('modulo_acao', 'id');
        $this->arrayViewData['perfil'] = UsuariosPerfil::all()->where('status', '=', 1)->pluck('titulo', 'id');
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['obj'] = UsuarioPerfilModulosAcoes::findOrFail($id);

        $this->arrayViewData['modulosAcoes'] = ViewUsuariosModulosAcoes::all()->pluck('modulo_acao', 'id');
        $this->arrayViewData['perfil'] = UsuariosPerfil::all()->where('status', '=', 1)->pluck('titulo', 'id');

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = new UsuariosPxMxA();
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
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = UsuariosPxMxA::findOrFail($id);
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
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = UsuariosPxMxA::findOrFail($id);

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
