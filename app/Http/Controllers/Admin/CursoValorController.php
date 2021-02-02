<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\CursoValor;

class CursoValorController extends Controller
{

	public function index() {
    	$cursos_valor = CursoValor::all();
    	$lista_status = array('0' => 'Inativo', '1' => 'Ativo');
    	
		return view('cursos_valor.lista', ['cursos_valor' => $cursos_valor, 'lista_status' => $lista_status]);
    }
    
    public function incluir() {
    	$lista_status = array('0' => 'Inativo', '1' => 'Ativo');
    	
    	return view('cursos_valor.formulario', ['lista_status' => $lista_status]);
    }   
    
    public function salvar(Request $request)  {
    	$cursos_valor = new CursoValor();
        $validator = Validator::make($request->all(), $cursos_valor->rules, $cursos_valor->messages);
    	 
    	if(!$validator->fails()) {
    		$resultado = $cursos_valor->create($request->all());
    			
    		if($resultado) {
    			\Session::flash('mensagem_sucesso', 'Cadastrado com Sucesso!');
    			return redirect()->route('admin.cursos_valor');
    		} else {
    			\Session::flash('mensagem_erro', 'Não foi possível inserir o registro!');
    			return Redirect::back()->withErrors($validator)->withInput();
    		}
    	} else {
    		return Redirect::back()->withErrors($validator)->withInput();
    	}	
    }
    
    
    public function editar($id) {
    	$cursos_valor = CursoValor::findOrFail($id);
    	$lista_status = array('0' => 'Inativo', '1' => 'Ativo');
    	 
    	return view('cursos_valor.formulario', ['cursos_valor' => $cursos_valor, 'lista_status' => $lista_status]);
    }
    
    public function atualizar($id, Request $request) {
    	$cursos_valor = CursoValor::findOrFail($id);
        	$validator = Validator::make($request->all(), $cursos_valor->rules, $cursos_valor->messages);
    	
    	if(!$validator->fails()) {
    		$resultado = $cursos_valor->update($request->all());
    		 
    		if($resultado) {
    			\Session::flash('mensagem_sucesso', 'Atualizado com Sucesso!');
    			return redirect()->route('admin.cursos_valor');
    		} else {
    			\Session::flash('mensagem_erro', 'Não foi possível atualizar o registro!');
    			return Redirect::back()->withErrors($validator)->withInput();
    		}
    	} else {
    		return Redirect::back()->withErrors($validator)->withInput();
    	}	
    }
    
    public function deletar($id) {
    	$cursos_valor = CursoValor::findOrFail($id);
    	$cursos_valor->delete();
    	
    	\Session::flash('mensagem_sucesso', 'Deletado com Sucesso!');
    	return redirect()->route('admin.cursos_valor');
    }}
