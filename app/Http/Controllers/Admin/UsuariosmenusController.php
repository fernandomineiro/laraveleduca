<?php
namespace App\Http\Controllers\Admin;

use App\UsuariosMenus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use function redirect;

class UsuariosmenusController extends Controller{

    public function index(){
        
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado

        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        //Array com dados para a view
        $this->arrayViewData['lstObj'] = UsuariosMenus::all()->where('status', 1);

        //modelo proposto para envio de dado a views
        return view('usuario.menus.lista', $this->arrayViewData);
    }


    public function incluir(){
        
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        return view('usuario.menus.formulario', $this->arrayViewData);
    }

    public function editar($id){
        
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        //Array com dados para a view
        $this->arrayViewData['obj'] = UsuariosMenus::findOrFail($id);
        return view('usuario.menus.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request){
        
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota, passa-se false para nao carregar variaveis de views
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = new UsuariosMenus();
        $validator = Validator::make($request->all(), $obj->rules, $obj->messages);

        if (!$validator->fails()) {

            $dadosForm = $request->except('_token');
            $dadosForm = $this->insertAuditData($dadosForm);

            $resultado = $obj->create($dadosForm);

            if ($resultado) {
                Session::flash('mensagem_sucesso', $this->msgInsert);
                return redirect()->route('admin.usuariosmenus');
            } else {
                Session::flash('mensagem_erro', $this->msgInsertErro);
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }

    public function atualizar($id, Request $request){
        
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota, passa-se false para nao carregar variaveis de views
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = UsuariosMenus::findOrFail($id);
        $validator = Validator::make($request->all(), $obj->rules, $obj->messages);

        if (!$validator->fails()) {

            $dadosForm = $request->except('_token');

            $dadosForm = $this->insertAuditData($dadosForm, false);


            $resultado = $obj->update($dadosForm);
            if ($resultado) {
                Session::flash('mensagem_sucesso', $this->msgUpdate);
                return redirect()->route('admin.usuariosmenus');
            } else {
                Session::flash('mensagem_erro', $this->msgUpdateErro);
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }

    public function deletar($id, Request $request){
        
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota, passa-se false para nao carregar variaveis de views
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = UsuariosMenus::findOrFail($id);

        $dadosForm = $request->except('_token');

        $dadosForm = $this->insertAuditData($dadosForm, false);
        $dadosForm['status'] = 0;

        $resultado = $obj->update($dadosForm);

        if ($resultado) {
            Session::flash('mensagem_sucesso', $this->msgDelete);
            return redirect()->route('admin.usuariosmenus');
        } else {
            Session::flash('mensagem_erro', $this->msgDeleteErro);
        }
    }
}
