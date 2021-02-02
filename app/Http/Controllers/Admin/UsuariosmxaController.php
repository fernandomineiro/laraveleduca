<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\UsuariosMxA;
use App\UsuariosModulos;
use App\UsuariosModulosAcoes;
use App\ViewUsuariosMxA;

use DB;

class UsuariosmxaController extends Controller
{
    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $this->arrayViewData['lstObj'] = ViewUsuariosMxA::all();

        return view('usuario.modulos.mxa.lista', $this->arrayViewData);
    }

    public function incluir()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $this->arrayViewData['modulos'] = UsuariosModulos::all()->where('status', 1)->pluck('descricao', 'id');
        $this->arrayViewData['acao'] = UsuariosModulosAcoes::all()->where('status', 1);

        return view('usuario.modulos.mxa.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        //Array com dados para a view
        $this->arrayViewData['objSelect'] = UsuariosMxA::all()->where('fk_modulo_id', $id);
        $this->arrayViewData['obj'] = UsuariosMxA::findOrFail($id);


        $this->arrayViewData['modulos'] = UsuariosModulos::all()->where('status', 1);
        $this->arrayViewData['acao'] = UsuariosModulosAcoes::select('usuarios_modulos_acoes.*', 'usuarios_modulos_elementos.descricao AS elemento')
            ->join('usuarios_modulos_elementos', 'usuarios_modulos_acoes.fk_elemento_id', '=', 'usuarios_modulos_elementos.id')
            ->where('usuarios_modulos_acoes.status', '=', 1)
            ->get();

        return view('usuario.modulos.mxa.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $dadosForm = $request->except('_token');
        $lstAcoes = explode(',', $dadosForm["fk_acao_id"]);
        $modulo = $dadosForm["fk_modulo_id"];

        foreach ($lstAcoes as $acoe) {

            $data = array();
            $data['fk_modulo_id'] = $modulo;
            $data['fk_acao_id'] = $acoe;
            $data['status'] = 1;
            $data = $this->insertAuditData($data);
            DB::table('usuarios_modulos_x_acoes')->insert($data);

        }

        \Session::flash('mensagem_sucesso', $this->msgUpdate);
        return redirect()->route('admin.usuariosmxa');
    }

    public function atualizar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $dadosForm = $request->except('_token');
        $lstAcoes = explode(',', $dadosForm["fk_acao_id"]);
        $modulo = $dadosForm["fk_modulo_id"];

        $data = array();
        $data['status'] = 0;
        $data = $this->insertAuditData($data, false);
        DB::table('usuarios_modulos_x_acoes')->where('fk_modulo_id', '=', $modulo)->update($data);

        foreach ($lstAcoes as $acoe) {
            $mxa = DB::table('usuarios_modulos_x_acoes')
                ->where('fk_modulo_id', '=', $modulo)
                ->where('fk_acao_id', '=', $acoe)
                ->get();

            $data = array();
            if (count($mxa) > 0) {
                $data['fk_modulo_id'] = $modulo;
                $data['fk_acao_id'] = $acoe;
                $data['status'] = 1;
                $data = $this->insertAuditData($data, false);
                DB::table('usuarios_modulos_x_acoes')->where('id', '=', $mxa[0]->id)->update($data);
            } else {
                $data = $this->insertAuditData($data);
                DB::table('usuarios_modulos_x_acoes')->insert($data);
            }
        }

        \Session::flash('mensagem_sucesso', $this->msgUpdate);
        return redirect()->route('admin.usuariosmxa');

    }

    public function deletar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $data = array();
        $data['status'] = 0;
        $data = $this->insertAuditData($data, false);
        DB::table('usuarios_modulos_x_acoes')->where('fk_modulo_id', '=', $id)->update($data);

        \Session::flash('mensagem_sucesso', $this->msgDelete);
    }
}
