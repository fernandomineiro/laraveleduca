<?php

namespace App\Http\Controllers\Auth;

use App\LogAcesso;
use App\Rules\ValidRecaptcha;
use App\UsuarioAcessos;
use App\ViewUsuariosPerfil;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Session;

class AdminLoginController extends Controller
{
	public function __construct() {
		$this->middleware('guest:admin', ['except' => ['logout']]);
	}
	
	public function showLoginForm() {
		return view('auth.admin-login');
	}
	
	public function index() {
		
		if(Auth()->guard('admin')->check()) {
			return redirect()->route('perfil');
		} else {
			return redirect()->route('admin.login');
		}		
	}
	
	public function login(Request $request) {
		$this->validate($request, [
			'email' => 'required|email',
			'password' => 'required|min:5',
            'g-recaptcha-response' => ['required', new ValidRecaptcha()]
		], [
		    'email.required' => 'O email é obrigatório',
		    'password.required' => 'A senha é obrigatória',
		    'g-recaptcha-response.required' => 'O recaptcha é obrigatório',
        ]);

		if(Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password, 'status' => 1, 'fk_faculdade_id' => 7], true)) {
		    
		    $usuario = ViewUsuariosPerfil::where('id',Auth()->guard('admin')->user()->id)->first();

		    $newacesso = new LogAcesso();

		    $newacesso->create([
                'fk_usuario' => $usuario->id,
                'ip_acesso' => request()->ip(),
                'data_acesso' => date("Y-m-d h:i", time()),
                'user_agent_acesso' => $request->header('User-Agent'),
            ]);

            $objLog = new \stdClass();

            $objLog->id = $usuario->id;
            $objLog->nome = $usuario->nome;
            $objLog->email = $usuario->email;
            $objLog->foto = $usuario->foto;
            $objLog->fk_perfil = $usuario->id_perfil;
            $objLog->perfil = $usuario->perfil;
            $objLog->diretorio_imagens = $usuario->diretorio_imagens;

            Session::put('user.logged', $objLog);
            Session::put('user.logged', $objLog);
            
            UsuarioAcessos::create(['usuario_id' => $usuario->id,'ip' => $request->ip(),'origem' => 'BACK']);

			return redirect()->intended(route('admin.dashboard'));
		}
		
		return redirect()->back()->withInput($request->only('email', 'remember'))->with(['erro_acesso' => 'Usuário e/ou senha inválidos!']);
	}
	
	public function dashboard() {
		
		return view('admin.dashboard');
		
	}
	
	public function logout() {
	    
		Auth::guard('admin')->logout();
		return redirect('/admin');
		
	}
}
