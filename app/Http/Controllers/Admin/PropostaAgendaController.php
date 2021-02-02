<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\PropostaAgenda;
use App\Proposta;

class PropostaAgendaController extends Controller
{

	public function index() {

		$proposta_agenda = PropostaAgenda::select('proposta_agenda.*', 'propostas.titulo')
									->join('propostas', 'proposta_agenda.fk_proposta', '=', 'propostas.id')
									->get();

		$lista_status = array('0' => 'Inativo', '1' => 'Ativo');
    	
		return view('proposta_agenda.lista', [
			'proposta_agenda' => $proposta_agenda, 
			'lista_status' => $lista_status,
		]);
    }
    
    public function incluir() {
    	$lista_status = array('0' => 'Inativo', '1' => 'Ativo');
		$lista_propostas = Proposta::all()->pluck('titulo', 'id');
		
    	return view('proposta_agenda.formulario', [
			'lista_status' => $lista_status, 
			'lista_propostas' => $lista_propostas
		]);
    }   
    
    public function salvar(Request $request)  {
    	$proposta_agenda = new PropostaAgenda();
        $validator = Validator::make($request->all(), $proposta_agenda->rules, $proposta_agenda->messages);
    	 
    	if(!$validator->fails()) {
    		$resultado = $proposta_agenda->create($request->all());
    			
    		if($resultado) {
    			\Session::flash('mensagem_sucesso', 'Cadastrado com Sucesso!');
    			return redirect()->route('admin.proposta_agenda');
    		} else {
    			\Session::flash('mensagem_erro', 'Não foi possível inserir o registro!');
    			return Redirect::back()->withErrors($validator)->withInput();
    		}
    	} else {
    		return Redirect::back()->withErrors($validator)->withInput();
    	}	
    }
    
    
    public function editar($id) {
    	$proposta_agenda = PropostaAgenda::findOrFail($id);
		$lista_status = array('0' => 'Inativo', '1' => 'Ativo');
		$lista_propostas = Proposta::all()->pluck('titulo', 'id');
    	 
    	return view('proposta_agenda.formulario', [
			'proposta_agenda' => $proposta_agenda, 
			'lista_status' => $lista_status, 
			'lista_propostas' => $lista_propostas
		]);
    }
    
    public function atualizar($id, Request $request) {
    	$proposta_agenda = PropostaAgenda::findOrFail($id);
        	$validator = Validator::make($request->all(), $proposta_agenda->rules, $proposta_agenda->messages);
    	
    	if(!$validator->fails()) {
    		$resultado = $proposta_agenda->update($request->all());
    		 
    		if($resultado) {
    			\Session::flash('mensagem_sucesso', 'Atualizado com Sucesso!');
    			return redirect()->route('admin.proposta_agenda');
    		} else {
    			\Session::flash('mensagem_erro', 'Não foi possível atualizar o registro!');
    			return Redirect::back()->withErrors($validator)->withInput();
    		}
    	} else {
    		return Redirect::back()->withErrors($validator)->withInput();
    	}	
    }
    
    public function deletar($id) {
    	$proposta_agenda = PropostaAgenda::findOrFail($id);
    	$proposta_agenda->delete();
    	
    	\Session::flash('mensagem_sucesso', 'Categoria deletada com Sucesso!');
    	return redirect()->route('admin.proposta_agenda');
    }}
