<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\Proposta;
use App\PropostaStatus;
use App\PropostaQuestionario;
use App\PropostaQuestionarioOpcao;
use App\Professor;

class PropostaQuestionarioController extends Controller
{

	public function index() {
		$proposta_questionario = PropostaQuestionario::get();
		$lista_proposta = Proposta::where('id', $id_proposta)->pluck('titulo', 'id');
		return view('proposta_questionario.lista', [
			'proposta_questionario' => $proposta_questionario,
			'id_proposta' => NULL
		]);
	}
	
	public function carregar( $id_proposta ) {
		$proposta_questionario = PropostaQuestionario::where('fk_proposta', $id_proposta )->get();
		$lista_proposta = Proposta::where('id', $id_proposta)->pluck('titulo', 'id');
		return view('proposta_questionario.lista', [
			'proposta_questionario' => $proposta_questionario, 
			'lista_proposta' => $lista_proposta,
			'id_proposta' => $id_proposta
		]);
	}
	
	public function vincular( $id_proposta ) {
		$proposta_questionario = PropostaQuestionario::get();
		$tipo_questionario  = $this->carregaComboTipoQuestionario();
		$lista_proposta = Proposta::where('id', $id_proposta)->pluck('titulo', 'id');
    	return view('proposta_questionario.formulario', [
			'lista_proposta' => $lista_proposta,
			'tipo_questionario' => $tipo_questionario,
			'id_proposta' => $id_proposta,
			'proposta_questionario' =>$proposta_questionario,
			'proposta_questionario_opcoes' => NULL
		]);
	}
	
    public function incluir() {
		$tipo_questionario  = $this->carregaComboTipoQuestionario();
    	return view('proposta_questionario.formulario', [ 
			'tipo_questionario' => $tipo_questionario,
			'id_proposta' => NULL
		]);
    }

    public function editar($id) {
        $proposta_questionario = PropostaQuestionario::findOrFail($id);
        $proposta_questionario_opcoes = PropostaQuestionarioOpcao::where('fk_proposta_questionario', $id )->get();
        $tipo_questionario  = $this->carregaComboTipoQuestionario();
        $lista_proposta = Proposta::where('id', $proposta_questionario->fk_proposta)->pluck('titulo', 'id');
        return view('proposta_questionario.formulario', [
            'lista_proposta' => $lista_proposta,
            'tipo_questionario' => $tipo_questionario,
            'id_proposta' => $proposta_questionario->fk_proposta,
            'proposta_questionario' => $proposta_questionario,
            'proposta_questionario_opcoes' => $proposta_questionario_opcoes
        ]);
    }
    
    public function salvar(Request $request)  {
    	$proposta_questionario = new PropostaQuestionario();
        $validator = Validator::make($request->all(), $proposta_questionario->rules, $proposta_questionario->messages);
    	if(!$validator->fails()) {
			$params = $request->all();
			$proposta_questionario->tipo_questionario = json_encode( $params['tipo_questionario'] );
			$proposta_questionario->fk_proposta 	= $params['fk_proposta'];
			$proposta_questionario->questao 		= $params['questao'];
			$proposta_questionario->ordem 			= 1;
			$proposta_questionario->status 			= 1;
			$valida_opcao = true;
			foreach( $params['PropostaQuestionarioOpcao_descricao'] as $opcao ){
				if( $opcao ){
					$valida_opcao = false;
					break;
				}
			}
			if( $valida_opcao ){
				\Session::flash('mensagem_erro', 'Nenhuma oção foi informada!');
    			return Redirect::back()->withErrors($validator)->withInput();
			}
			$resultado = $proposta_questionario->save( );
    		if($resultado) {
				$ordem = 0;
				foreach( $params['PropostaQuestionarioOpcao_descricao'] as $opcao ){
					$ordem ++;
					if( $opcao == NULL ){
						continue;
					}
					$proposta_questionario_opcao = new PropostaQuestionarioOpcao();
					$proposta_questionario_opcao->fk_proposta_questionario = $proposta_questionario->id;
					$proposta_questionario_opcao->descricao = $opcao;
					$proposta_questionario_opcao->ordem = $ordem;
					$proposta_questionario_opcao->status = 1;
					$proposta_questionario_opcao->save();
				}
    			\Session::flash('mensagem_sucesso', 'Cadastrado com Sucesso!');
				// return redirect()->action('Admin\PropostaController@editar', ['id'=> $proposta->id ]);
				return redirect()->action('Admin\PropostaQuestionarioController@carregar', ['id'=> $proposta_questionario->fk_proposta ]);
    		} else {
    			\Session::flash('mensagem_erro', 'Não foi possível inserir o registro!');
    			return Redirect::back()->withErrors($validator)->withInput();
    		}
    	} else {
    		return Redirect::back()->withErrors($validator)->withInput();
    	}	
    }

    public function atualizar($id, Request $request) {
    	$proposta_questionario = PropostaQuestionario::findOrFail($id);
        $validator = Validator::make($request->all(), $proposta_questionario->rules, $proposta_questionario->messages);
    	if(!$validator->fails()) {
			$params = $request->all();
			$proposta_questionario->tipo_questionario = json_encode( $params['tipo_questionario'] );
			$proposta_questionario->fk_proposta 	= $params['fk_proposta'];
			$proposta_questionario->questao 		= $params['questao'];
			$proposta_questionario->ordem 			= 1;
			$proposta_questionario->status 			= 1;
			$valida_opcao = true;
			foreach( $params['PropostaQuestionarioOpcao_descricao'] as $opcao ){
				if( $opcao ){
					$valida_opcao = false;
					break;
				}
			}

			if( $valida_opcao ){
				\Session::flash('mensagem_erro', 'Nenhuma oção foi informada!');
    			return Redirect::back()->withErrors($validator)->withInput();
			}

    		$resultado = $proposta_questionario->save( );
    		if($resultado) {
				PropostaQuestionarioOpcao::where('fk_proposta_questionario', $proposta_questionario->id )->delete();
				$ordem = 0;
				foreach( $params['PropostaQuestionarioOpcao_descricao'] as $opcao ){
					$ordem ++;
					if( $opcao == NULL ){
						continue;
					}
					$proposta_questionario_opcao = new PropostaQuestionarioOpcao();
					$proposta_questionario_opcao->fk_proposta_questionario = $proposta_questionario->id;
					$proposta_questionario_opcao->descricao = $opcao;
					$proposta_questionario_opcao->ordem = $ordem;
					$proposta_questionario_opcao->status = 1;
					$proposta_questionario_opcao->save();
				}

    			\Session::flash('mensagem_sucesso', 'Atualizado com Sucesso!');
				return redirect()->action('Admin\PropostaQuestionarioController@carregar', ['id'=> $proposta_questionario->fk_proposta ]);
    			// return Redirect::back()->withErrors($validator)->withInput();
    		} else {
    			\Session::flash('mensagem_erro', 'Não foi possível atualizar o registro!');
    			return Redirect::back()->withErrors($validator)->withInput();
    		}
    	} else {
    		return Redirect::back()->withErrors($validator)->withInput();
    	}	
    }
    
//    public function deletar($id) {
//		$proposta_questionario = PropostaQuestionario::findOrFail($id);
//		PropostaQuestionarioOpcao::where('fk_proposta_questionario', $proposta_questionario->id )->delete();
//    	$proposta_questionario->delete();
//
//    	\Session::flash('mensagem_sucesso', 'Categoria deletada com Sucesso!');
//    	return redirect()->action('Admin\PropostaQuestionarioController@carregar', ['id'=> $proposta_questionario->fk_proposta ]);
//	}

    public function deletar($id, Request $request)
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota, passa-se false para nao carregar variaveis de views
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = PropostaQuestionario::findOrFail($id);

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

	public function carregaComboTipoQuestionario(){
		return [1 => 'TESTE 1', 2 => 'TESTE 2', 3 => 'TESTE 3'];
	}
}
