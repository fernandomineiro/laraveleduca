<?php

namespace App\Http\Controllers\Admin;

use App\TipoParceiro;
use App\UsuariosPerfil;
use App\ViewPerfilModulosAcoes;
use App\ViewUsuariosMxA;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use function redirect;

class UsuariosperfilController extends Controller
{

    public function index()
    {
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['perfis'] = UsuariosPerfil::all()->where('status', 1);
        $this->arrayViewData['parceiro'] = TipoParceiro::all()->where('status', 1);
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    public function incluir()
    {
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $this->arrayViewData['lista_parceiro'] = TipoParceiro::all()->where('status', 1)->pluck('descricao', 'id');
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id){
        
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['perfil'] = UsuariosPerfil::findOrFail($id);
        $this->arrayViewData['perfilModulosAcoes'] = ViewPerfilModulosAcoes::all()->where('fk_perfil_id', '=', $id);
        $this->arrayViewData['lista_parceiro'] = TipoParceiro::all()->where('status', 1)->pluck('descricao', 'id');
        
        $fk_modulo_acoes_id = $this->arrayViewData['perfilModulosAcoes']->pluck('fk_modulo_acoes_id')->toArray();
        
        $this->arrayViewData['mxa'] = ViewUsuariosMxA::all()->reject(function ($contact) use ($fk_modulo_acoes_id) {
            return in_array($contact->id, $fk_modulo_acoes_id);
        });
        
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {

        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = new UsuariosPerfil();
        $validator = Validator::make($request->all(), $obj->rules, $obj->messages);

        if (!$validator->fails()) {
            $dadosForm = $request->except('_token');
            $dadosForm = $this->insertAuditData($dadosForm);

            $resultado = $obj->create($dadosForm);

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

        $obj = UsuariosPerfil::findOrFail($id);
        $validator = Validator::make($request->all(), $obj->rules, $obj->messages);

        if (!$validator->fails()) {
            $dadosForm = $request->except('_token');

            $dadosForm = $this->insertAuditData($dadosForm, false);


            $resultado = $obj->update($dadosForm);

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

    public function deletar($id, Request $request)
    {

        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = UsuariosPerfil::findOrFail($id);

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

    //Metodos AJAX para Actions

    public function getpxmxaall()
    {
        $this->validateAccess(Session::get('user.logged'));
        $data = ViewUsuariosMxA::all();
        return json_encode(compact('mxa', $data));
    }

    public function removepxmxa(Request $request)
    {
        $this->validateAccess(Session::get('user.logged'));
        $dataFrom = $request->except('_token');
        $data['status'] = 0;
        $data = $this->insertAuditData($data, false);

        DB::table('usuarios_perfil_x_modulos_acoes')->where('id', '=', $dataFrom['id'])->update($data);

        $data = ViewPerfilModulosAcoes::all()->where('fk_perfil_id', $dataFrom['idPerfil']);


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

    public function addpxmxaOld(Request $request){
     
        $this->validateAccess(Session::get('user.logged'));
        $dataFrom = $request->except('_token');

        $mxa = DB::table('usuarios_perfil_x_modulos_acoes')
            ->where('fk_perfil_id', '=', $dataFrom['idPerfil'])
            ->where('fk_modulo_acoes_id', '=', $dataFrom['idAction'])
            ->get();


        $data = array();
        $data['fk_perfil_id'] = $dataFrom['idPerfil'];
        $data['fk_modulo_acoes_id'] = $dataFrom['idAction'];
        $data['status'] = 1;
        if (count($mxa) > 0) {
            $data = $this->insertAuditData($data, false);
            DB::table('usuarios_perfil_x_modulos_acoes')->where('id', '=', $mxa[0]->id)->update($data);
        } else {
            $data = $this->insertAuditData($data);
            DB::table('usuarios_perfil_x_modulos_acoes')->insert($data);
        }

        $data = ViewPerfilModulosAcoes::all()->where('fk_perfil_id', $dataFrom['idPerfil']);

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
    
    public function addpxmxa(Request $request){
        
        $this->validateAccess(Session::get('user.logged'));
        
        if($request->has('modulesAction') && $request->has('idPerfil')){
            foreach ($request->get('modulesAction') as $idAction){
                
                $mxa = DB::table('usuarios_perfil_x_modulos_acoes')
                                ->where('fk_perfil_id', '=', $request->get('idPerfil'))
                                ->where('fk_modulo_acoes_id', '=', $idAction)
                                ->get();
                
                
                $data = array();
                $data['fk_perfil_id'] = $request->get('idPerfil');
                $data['fk_modulo_acoes_id'] = $idAction;
                $data['status'] = 1;
                
                if (count($mxa) > 0) {
                    $data = $this->insertAuditData($data, false);
                    DB::table('usuarios_perfil_x_modulos_acoes')->where('id', '=', $mxa[0]->id)->update($data);
                } else {
                    $data = $this->insertAuditData($data);
                    DB::table('usuarios_perfil_x_modulos_acoes')->insert($data);
                }
                
            }
            
            Session::flash('mensagem_sucesso', 'Atualizado com sucesso!');
            return Redirect::back();

        }
        
        Session::flash('mensagem_erro', $this->msgUpdateErro);
        return Redirect::back()->withErrors($validator)->withInput();
        
        
    }
    
    //FIM métodos AJAX
}
