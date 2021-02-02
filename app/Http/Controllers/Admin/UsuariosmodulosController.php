<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\UsuariosModulos;
use App\UsuariosMenus;
use App\UsuariosModulosAcoes;
use App\ViewModuloAcoes;
use App\UsuariosMxA;
use App\ViewUsuariosMxA;

use DB;

class UsuariosmodulosController extends Controller
{

    public function index()
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado

        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        //Array com dados para a view
        $this->arrayViewData['lstObj'] = UsuariosModulos::select('usuarios_modulos.*', 'usuarios_menus.descricao AS menu')
            ->leftJoin('usuarios_menus', 'usuarios_modulos.fk_menu_id', '=', 'usuarios_menus.id')
            ->where('usuarios_modulos.status', '=', 1)
            ->orderBy('usuarios_menus.descricao')
            ->orderBy('usuarios_modulos.descricao')
            ->get();

        //modelo proposto para envio de dado a views
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }


    public function incluir()
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['menus'] = UsuariosMenus::all()->where('status', 1)->pluck('descricao', 'id');
        $this->arrayViewData['moduloMxA'] = [];
        $this->arrayViewData['mxa'] = [];
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        //Array com dados para a view
        $this->arrayViewData['obj'] = UsuariosModulos::findOrFail($id);
        $this->arrayViewData['menus'] = UsuariosMenus::all()->where('status', 1)->pluck('descricao', 'id');
        $this->arrayViewData['mxa'] = ViewModuloAcoes::all();
        $this->arrayViewData['moduloMxA'] = ViewUsuariosMxA::all()->where('fk_modulo_id', $id);

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota, passa-se false para nao carregar variaveis de views
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = new UsuariosModulos();
        $validator = Validator::make($request->all(), $obj->rules, $obj->messages);

        if (!$validator->fails()) {

            $dadosForm = $request->except('_token');
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
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota, passa-se false para nao carregar variaveis de views
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = UsuariosModulos::findOrFail($id);
        $validator = Validator::make($request->all(), $obj->rules, $obj->messages);

        if (!$validator->fails()) {

            $dadosForm = $request->except('_token');

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
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota, passa-se false para nao carregar variaveis de views
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = UsuariosModulos::findOrFail($id);

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

    //Metodos AJAX para Actions

    public function getmxaall()
    {
        $this->validateAccess(\Session::get('user.logged'));
        $data = ViewUsuariosMxA::all();
        return json_encode(compact('mxa', $data));
    }

    public function removemxa(Request $request)
    {
        $this->validateAccess(\Session::get('user.logged'));
        $dataFrom = $request->except('_token');
        $data['status'] = 0;
        $data = $this->insertAuditData($data, false);

        DB::table('usuarios_modulos_x_acoes')->where('id', '=', $dataFrom['id'])->update($data);

        $data = ViewUsuariosMxA::all()->where('fk_modulo_id', $dataFrom['idModulo']);


        $dataView = array();

        foreach ($data as $d) {
            $obj = new \stdClass();
            $obj->id = $d->id;
            $obj->acao = $d->acao;
            $obj->elemento = $d->elemento;

            if ($d->status == 0)
                $obj->status = 'Inativo';
            if ($d->status == 1)
                $obj->status = 'Ativo';

            $dataView[] = $obj;
        }

        return json_encode(compact('dataView', $dataView));
    }

    public function addmxa(Request $request)
    {
        $this->validateAccess(\Session::get('user.logged'));
        $dataFrom = $request->except('_token');

        $mxa = DB::table('usuarios_modulos_x_acoes')
            ->where('fk_modulo_id', '=', $dataFrom['idModulo'])
            ->where('fk_acao_id', '=', $dataFrom['idAction'])
            ->get();

        $data = array();

        $data['fk_modulo_id'] = $dataFrom['idModulo'];
        $data['fk_acao_id'] = $dataFrom['idAction'];
        $data['tipo_rota'] = $dataFrom['methodUse'];

        if ($dataFrom['middlewareName'] > 0)
            $data['middleware'] = $dataFrom['middlewareName'];

        if ($dataFrom['acceptParameter'] > 0)
            $data['parametro'] = $dataFrom['acceptParameter'];

        $data['status'] = 1;


        if (count($mxa) > 0) {
            $data = $this->insertAuditData($data, false);
            DB::table('usuarios_modulos_x_acoes')->where('id', '=', $mxa[0]->id)->update($data);
        } else {
            $data = $this->insertAuditData($data);
            DB::table('usuarios_modulos_x_acoes')->insert($data);
        }

        $data = ViewUsuariosMxA::all()->where('fk_modulo_id', $dataFrom['idModulo']);

        $dataView = array();

        foreach ($data as $d) {
            $obj = new \stdClass();
            $obj->id = $d->id;
            $obj->acao = $d->acao;
            $obj->elemento = $d->elemento;

            if ($d->status == 0)
                $obj->status = 'Inativo';
            if ($d->status == 1)
                $obj->status = 'Ativo';

            if ($d->parametro == 0)
                $obj->parametro = 'Não';
            if ($d->parametro == 1)
                $obj->parametro = 'Sim';

            $dataView[] = $obj;
        }

        return json_encode(compact('dataView', $dataView));
    }
    //FIM métodos AJAX
}
