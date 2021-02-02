<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use App\Imports\UsuarioImport;

class UsuariosImportarController extends Controller{
    
    public function index(){
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }
    
    public function incluir(){
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }
    
    public function salvar(Request $request){
        
        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);
        
        $request->validate([
            'arquivo_excel' => 'required'
        ]);
        
        try {
            
            Excel::import(new UsuarioImport(),$request->file('arquivo_excel'));
            
        } catch (\Maatwebsite\Excel\Exceptions\SheetNotFoundException $e) {

            Session::flash('mensagem_erro', $this->msgInsertErro);
            return Redirect::back()->withErrors($e->getMessage())->withInput();
            
        } catch (Exception $e) {

            Session::flash('mensagem_erro', $this->msgInsertErro);
            return Redirect::back()->withErrors($e->getMessage())->withInput();
            
        }
        
        Session::flash('mensagem_sucesso', $this->msgInsert);
        return redirect()->route('admin.usuario');
 
    }
    
}
