<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;

use App\Curso;
use App\Certificado;
use App\CursoCategoria;
use App\Usuario;
use App\CursoTipo;
use App\CertificadoLayout;
use App\Faculdade;
use App\TipoCertificado;
use App\Admin;
use PDF;
use QrCode;
use App\Helper\CertificadoHelper;

class CertificadosController extends Controller
{
	public function index() {
		if (!$this->validateAccess(\Session::get('user.logged'))) 
			return redirect()->route($this->redirecTo);
		$this->arrayViewData['certificados'] = 
			CertificadoLayout::select(
				'certificado_layout.*', 'faculdades.fantasia as faculdade','usuarios.nome as criador','c2.nome as atualizador'
			)
            ->join('faculdades', 'faculdades.id', '=', 'certificado_layout.fk_faculdade')
            ->join('usuarios', 'usuarios.id', '=', 'certificado_layout.fk_criador_id')
            ->join('usuarios as c2', 'c2.id', '=', 'certificado_layout.fk_atualizador_id')
			->where('certificado_layout.status', '>', 0)
            ->get();
        $this->arrayViewData['lista_status'] = array('1' => 'Publicado', '2' => 'Salvo como Rascunho' );
		return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }
    
    public function incluir() {
		if (!$this->validateAccess(\Session::get('user.logged'))) 
			return redirect()->route($this->redirecTo);
		$this->arrayViewData['faculdades'] 		 = Faculdade::all()->where('status', '=', 1)->pluck('fantasia', 'id');
        $this->arrayViewData['faculdades']->put(0, 'Selecione');
        $this->arrayViewData['faculdades'] = $this->arrayViewData['faculdades']->sortKeys();
		$this->arrayViewData['lista_status'] 	 = array('1' => 'Publicar', '2' => 'Salvar Rascunho' );
		$this->arrayViewData['tipos'] 		     = TipoCertificado::all()->where('status', '=', 1)->pluck('titulo', 'id');
		return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }   
    
    public function salvar(Request $request)  {
		$params 	 = $request->all();
    	$Certificado = new CertificadoLayout( $params );
		if( $request->hasFile( 'layout' ) ){
			$file = $request->file( 'layout' );
			$Certificado->layout = $this->uploadFile($file );
		}
		$Certificado->fk_criador_id 	= Auth()->guard('admin')->user()->id;
		$Certificado->fk_atualizador_id = Auth()->guard('admin')->user()->id;
		$Certificado->data_criacao 		= date('YmdHis');
		$Certificado->data_atualizacao 	= date('YmdHis');

		$params['layout'] = $Certificado->layout;
		$params['fk_criador_id'] = $Certificado->fk_criador_id;
		$params['fk_atualizador_id'] = $Certificado->fk_atualizador_id;
		$params['data_criacao'] = $Certificado->data_criacao;
		$params['data_atualizacao'] = $Certificado->data_atualizacao;

		$validator  = Validator::make( $params, $Certificado->rules, $Certificado->messages);

    	if(!$validator->fails()) {
    		$resultado = $Certificado->save();
    		if($resultado) {
    			\Session::flash('mensagem_sucesso', 'Cadastrado com Sucesso!');
    			return redirect()->route('admin.certificados');
    		} else {
    			\Session::flash('mensagem_erro', 'Não foi possível inserir o registro!');
    			return Redirect::back()->withErrors($validator)->withInput();
    		}
    	} else {
    		return Redirect::back()->withErrors($validator)->withInput();
    	}	
    }
    
    public function editar($id) {
		if (!$this->validateAccess(\Session::get('user.logged'))) 
			return redirect()->route($this->redirecTo);
		
		$this->arrayViewData['certificado'] 	 = CertificadoLayout::findOrFail($id);
		$this->arrayViewData['faculdades'] 		 = Faculdade::all()->where('status', '=', 1)->pluck('fantasia', 'id');
		$this->arrayViewData['lista_status'] 	 = array('1' => 'Publicar', '2' => 'Salvar Rascunho' );
		$this->arrayViewData['tipos'] 		     = TipoCertificado::all()->where('status', '=', 1)->pluck('titulo', 'id');
		return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }
    
    public function atualizar($id, Request $request) {
		$Certificado = CertificadoLayout::findOrFail($id);
		$params 	 = $request->all();
		if( $request->hasFile( 'layout' ) ){
			$file = $request->file( 'layout' );
			$Certificado->layout = $this->uploadFile($file );
		}
		$params['layout'] = $Certificado->layout;
		$Certificado->fk_atualizador_id = Auth()->guard('admin')->user()->id;
		$Certificado->data_atualizacao 	= date('YmdHis');
		$Certificado->fk_faculdade 		= $params['fk_faculdade'];
		$Certificado->status 			= $params['status'];
		$Certificado->tipo 				= $params['tipo'];
		$Certificado->titulo 		 = $params['titulo'];
		$params['layout'] 			 = $Certificado->layout;
		$params['fk_criador_id'] 	 = $Certificado->fk_criador_id;
		$params['fk_atualizador_id'] = $Certificado->fk_atualizador_id;
		$params['data_criacao'] 	 = $Certificado->data_criacao;
		$params['data_atualizacao']  = $Certificado->data_atualizacao;
		$validator  				 = Validator::make( $params, $Certificado->rules, $Certificado->messages);

    	if(!$validator->fails()) {
    		$resultado = $Certificado->save();
    		if($resultado) {
    			\Session::flash('mensagem_sucesso', 'Cadastrado com Sucesso!');
    			return redirect()->route('admin.certificados');
    		} else {
    			\Session::flash('mensagem_erro', 'Não foi possível inserir o registro!');
    			return Redirect::back()->withErrors($validator)->withInput();
    		}
    	} else {
    		return Redirect::back()->withErrors($validator)->withInput();
    	}	
    }
    
    public function deletar($id) {
		$Certificado = CertificadoLayout::findOrFail($id);
		$Certificado->fk_atualizador_id = Auth()->guard('admin')->user()->id;
		$Certificado->status = 0;
		$Certificado->save();
    	\Session::flash('mensagem_sucesso', 'Excluído com Sucesso!');
    	return redirect()->route('admin.certificados');
	}

	public function generatepdf($id) {

		if (!$this->validateAccess(\Session::get('user.logged'))) 
		return redirect()->route($this->redirecTo);
		$Certificado = CertificadoLayout::findOrFail($id);
		if($Certificado->fk_curso_id) $curso = Curso::findOrFail( $Certificado->fk_curso_id ); 
		
		$url_qrcode = $this->generateQrcodeUrl($id);
		$code = base64_encode( 'educaz-'.$id.'-layout' );
		$params = [
			'Certificado' => $Certificado,
			'curso' => 'Curso Demonstrativo',			
			'url_qrcode' => $url_qrcode,
			'code' => $code
		];
		$pdf = PDF::setOptions([
			'images' => true,
		])->loadView('certificados.generatepdf', $params)->setPaper('a4', 'landscape');
		$return = $pdf->stream();		
		return $return;		
    }

	public function uploadFile( $file ){
    	if( $file->isValid() ) {
    		$fileName = date('YmdHis') . '_' . $file->getClientOriginalName();
    		if($file->move('files/certificado/', $fileName)) {
				return $fileName;
    		}
		}
		return '';
	}

	public function generateQrcodeUrl($id){		
		$id_encoding = base64_encode( 'educaz-'.$id.'-layout' );		
		$url 		 = url('/admin/certificados/'.$id_encoding.'/valida_certificado');
		return $url;
	}	
		
	public function valida_certificado( $id_codificado ) {		
		$id_arr = explode( '-', base64_decode( $id_codificado ) );
		$certificadoLayout = array();
		$certificado = array();
		$curso = array();
		
		if(isset($id_arr['2']) && ( $id_arr['2'] == 'layout' )){			
			$certificadoLayout = CertificadoLayout::find( $id_arr['1'] ); 			
			if($certificadoLayout){
				$curso = Curso::where('fk_certificado', $certificadoLayout->id)->first();
			}						
		} else {			
			if( isset($id_arr['0']) && ( $id_arr['0'] == 'educaz' ) && isset($id_arr['1']) && is_numeric( $id_arr['1']) ){
				$certificado = Certificado::find( $id_arr['1'] ); 				
				if($certificado){
					$curso = Curso::find($certificado->fk_curso);
					if($curso) {
						$certificadoLayout = CertificadoLayout::find($curso->fk_certificado ); 
					}
				}				
			}			
		}
		$nomeUsuario = 'Nome do aluno';
		if($certificado) {
			$usuario = Usuario::find($certificado->fk_usuario);
			if($usuario) $nomeUsuario = $usuario->nome;
		}
		if(!$curso) $nomeCurso = 'Curso Demonstrativo';
		else $nomeCurso = $curso->titulo;
		
    	return view('certificados.valida_certificado', [
			'certificado' 	=> $certificadoLayout,			
			'curso' 		=> $nomeCurso,
			'nomeUsuario'	=> $nomeUsuario
		]);
	}
		
	public function autentica($codigo){
		$id_decoded = explode( '-', base64_decode( $codigo ) );
		if(count($id_decoded)==2 && $id_decoded[0] == 'educaz'){
			$certificado = Certificado::find($id_decoded[1]);
			if($certificado){
				return view('certificados.autentica', ['certificado_path' => $certificado->downloadPath]);				
			} else {
				return view('certificados.autentica', ['certificado_path' => null]);
			}
		} else {
			return view('certificados.autentica', ['certificado_path' => null]);			
		}
		
	}
}
