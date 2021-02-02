<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;

use App\Mail\EnviaNovaSenhaMail;
use App\Usuario;

use DB;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use \Illuminate\Foundation\Auth\AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('web', ['except' => ['logout', 'userLogout']]);
    }
    
    public function loginUsingId($id) {
    	exit('teste');
    }
    
    public function login(Request $request) {
    	
    	$this->validate($request, [
    			'email' => 'required|email',
    			'password' => 'required|min:5'
    	]);
    	
    	if(Auth::guard('web')->attempt(['email' => $request->email, 'password' => $request->password, 'status' => '1'], true)) {
    		$usuario = Usuario::where('email', $request->email)->first();
    		return redirect()->back();
    	}
    
    	return redirect()->back()->withInput($request->only('email'));
    }    
    
    public function enviar_senha(Request $request) {
    	$dados_usuario = Usuario::where('email', $request->email)->first();

    	if($dados_usuario) {
    		$usuario = Usuario::findOrFail($dados_usuario->id);
    		 
    		if($usuario) {
    		
    			$senha = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
    			$usuario->password = bcrypt($senha);
    		
    			if( $usuario->save() ) {
    				 
    				$usuario->senha = $senha;
    				Mail::to($usuario->email)->send(new EnviaNovaSenhaMail($usuario));
    				 
    				\Session::flash('mensagem_sucesso', 'Uma nova senha foi enviada para seu e-mail!');
    			}
    		} else {
    			\Session::flash('mensagem_erro', 'E-mail não encontrado no sistema!');
    		}    		
    	} else {
    		\Session::flash('mensagem_erro', 'E-mail não encontrado no sistema!');
    	}
    	
    	return redirect()->route('home.index');
    }
    
    public function userLogout() {
    	
    	#Session()->forget('img_avatar');
    	#Session()->flush();
    	
    	Auth::guard('web')->logout();
    	return redirect('/');
    	
    }    
}
