<?php

namespace App\Http\Controllers;

use App\LogAcesso;
use App\Rules\ValidRecaptcha;
use App\UsuarioAcessos;
use App\ViewUsuarios;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class ApiController
 * @package App\Http\Controllers
 *
 * @OA\OpenApi(
 *     @OA\Info(
 *        title="Educaz API",
 *        version="1.0.0",
 *     ),
 *     @OA\Server(
 *         description="Educaz development Api server",
 *         url="http://localhost:8000/api",
 *     ),
 *     @OA\Server(
 *         description="Educaz test Api server",
 *         url="http://localhost:8000/api",
 *     ),
 * ),
 *
 * @OA\Tag(
 *      name="Login",
 *      description="Açoes de login, registrar e logout no sistema",
 * ),
 *
 * @OA\Schema(
 *     schema="Login",
 *     description="Realiza o login no sistema",
 *     type="object",
 *     required={"email", "password"},
 *     @OA\Property(
 *      property="email",
 *      type="string"
 *     ),
 *     @OA\Property(
 *      property="password",
 *      type="string"
 *     ),
 * ),
 *
 * @OA\Schema(
 *     schema="Response",
 *     description="Server response",
 *     type="object",
 *     @OA\Property(
 *          property="success",
 *          type="string",
 *          enum={"true", "false"}
 *     ),
 *     @OA\Property(
 *          property="items",
 *          type="array",
 *          @OA\Items(
 *
 *          ),
 *     ),
 * ),
 */
class ApiAcessoRestritoController extends Controller {

    /**
     * @var bool
     */
    public $loginAfterSignUp = true;

    /**
     * Cria novo usuário pela api
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @OA\Post(
     *     path="/register",
     *     description="Cria um novo usuário pela API",
     *     tags={"Login"},
     *     @OA\RequestBody(
     *      required=true,
     *      description="Criar um novo usuário pela API",
     *      @OA\JsonContent(
     *          @OA\Schema(
     *              schema="userRegisterParams",
     *              description="Parametros necessários para criar um novo usuário",
     *              @OA\Property(
     *                  property="name",
     *                  required=true,
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="email",
     *                  required=true,
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  required=true,
     *                  type="string"
     *              )
     *          ),
     *      ),
     *     ),
     *     @OA\Response(
     *      response="200",
     *      description="Usuário criado com sucesso",
     *      @OA\JsonContent(
     *          @OA\Schema(
     *              schema="userLogoutSuccess",
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="user",
     *                  type="object",
     *              ),
     *          ),
     *      ),
     *    ),
     *    @OA\Response(
     *     response="500",
     *     description="internal server error",
     *     @OA\JsonContent(
     *          @OA\Schema(
     *              schema="userLogoutError",
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *              ),
     *          ),
     *      ),
     *    )
     * )
     */
    public function register(Request $request) {

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

        if ($this->loginAfterSignUp) {
            return $this->login($request);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ], 200);
    }

    /**
     * Efetua login via API e retorna token
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/login",
     *     tags={"Login"},
     *     @OA\RequestBody(
     *      required=true,
     *      description="Realiza o login do usuário no sistema",
     *      @OA\JsonContent(ref="#/components/schemas/Login"),
     *     ),
     *     @OA\Response(
     *      response="200",
     *      description="Usuário logado com sucesso",
     *      @OA\JsonContent(
     *          @OA\Schema(
     *              schema="userLogin",
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="token",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="user",
     *                  type="object",
     *              ),
     *              @OA\Property(
     *                  property="faculdade",
     *                  type="integer",
     *              ),
     *          ),
     *          example="{
                           'success': true,
                           'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTU2NTYwMjI2MCwiZXhwIjoxNTY1NjQ1NDYwLCJuYmYiOjE1NjU2MDIyNjAsImp0aSI6IlNKQTJxQ1hSb0NYOHlha3QiLCJzdWIiOjM4LCJwcnYiOiIwYjBjZjUwYWYxMjNkODUwNmUxNmViYTdjYjY3NjI5NzRkYTNhYzNhIn0.vj4aRwWc18loTLzupc3pBcUBJb1JcwQVjEX5atZu410'
                           'user': '{name: Jhoe Down, email: jhoedown@example.com}',
                           'faculdade': 1
                         }"
     *      ),
     *     ),
     *     @OA\Response(
     *      response="401",
     *      description="Usuário ou senha inválidos",
     *      @OA\JsonContent(
     *          @OA\Schema(
     *              schema="userLoginDenied",
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *              ),
     *          ),
     *          example="{
                           'success': false,
                           'message': 'Email ou Senha inválido.'
                        }"
     *      ),
     *     ),
     *     @OA\Response(response="500", description="internal server error")
     * )
     */
    public function login(Request $request) {

        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar o login, campos inválidos',
                'erros' => $validator->messages()->all()
            ], 401);
        }

        $credentials = array_merge($request->only('email', 'password', 'fk_faculdade_id'), ['fk_perfil' => 14]);
        return $this->auth($credentials, $request);

    }

    public function validator($data) {

        return Validator::make($data, [
            'email' => 'required|email',
            'password' => 'required|min:5'
        ], [
            'email.required' => 'O email é obrigatório',
            'password.required' => 'A senha é obrigatória'
        ]);
    }

    public function auth($credentials, Request $request) {
        $jwt_token = null;

        if (!$jwt_token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email ou Senha inválido.',
            ], 401);
        }

        $user = JWTAuth::user();

        $newacesso = new LogAcesso();

        $newacesso->create([
            'fk_usuario' => $user->id,
            'ip_acesso' => request()->ip(),
            'data_acesso' => date("Y-m-d h:i", time()),
            'user_agent_acesso' => $request->header('User-Agent'),
        ]);

        UsuarioAcessos::create(['usuario_id' => $user->id,'ip' => $request->ip()]);

        $userMembership = $user->membership();
        return response()->json([
            'success' => true,
            'token' => $jwt_token,
            'user' => $user,
            'membership' => ['type' => !empty($userMembership[0]) ? $userMembership[0]->tipo_assinatura_id : null],
            'faculdade' => env('FACULDADE', 1)
        ]);
    }

    public function __construct() {

        \Config::set('jwt.user', 'App\ViewUsuarios');
        \Config::set('auth.providers.users.model', ViewUsuarios::class);
    }

    public function loginAcessoRestrito(Request $request) {
//        Config::set('jwt.user', ViewUsuarios::class);
//        Config::set('auth.providers', ['users' => [
//            'driver' => 'eloquent',
//            'model' => ViewUsuarios::class,
//        ]]);
//        \Config::set('jwt.user', 'App\ViewUsuarios');
        \Config::set('auth.providers.users.model', ViewUsuarios::class);

        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar o login, campos inválidos',
                'erros' => $validator->messages()->all()
            ], 401);
        }

        $credentials = array_merge($request->only('email', 'password'), ['status' => 1]);

        $jwt_token = null;

        if (!$jwt_token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email ou Senha inválido.',
            ], 401);
        }

        $user = JWTAuth::user();

        if (
            ($user->status != '1')
            && ($user->fk_perfil == 1 || ($user->fk_faculdade_id != $request->input('fk_faculdade_id')))) {
            return response()->json([
                'success' => false,
                'message' => 'Email ou Senha inválido.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'token' => $jwt_token,
            'user' => $user,
            'membership' => ['type' => !empty($userMembership[0]) ? $userMembership[0]->tipo_assinatura_id : null],
            'faculdade' => env('FACULDADE', 1)
        ]);

        return $this->auth($credentials, $request);
    }

    /**
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/info/{token}",
     *     description="Retorno as informações do usuário do token informado",
     *     tags={"Login"},
     *     @OA\Parameter(
     *      @OA\Schema(
     *          type="string"
     *      ),
     *      name="token",
     *      in="path",
     *      description="Token do usuário logado.",
     *      required=true,
     *     ),
     *     @OA\Response(response="default", description="objeto usuário")
     * )
     */
    public function info($token) {

        $user = JWTAuth::toUser($token);
        return response()->json([
            'user' => $user
        ]);
    }

    /**
     * Efetua logout revoga o token
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     *
     * @OA\Post(
     *     path="/logout",
     *     description="Desloga o usuário do sistema",
     *     tags={"Login"},
     *     @OA\RequestBody(
     *      required=true,
     *      description="Token do usuário logado no sistema",
     *      @OA\JsonContent(
     *          @OA\Schema(
     *              schema="userLogoutParams",
     *              description="Parametros necessários para realizar o logout",
     *              @OA\Property(
     *                  property="token",
     *                  required=true,
     *                  type="string"
     *              )
     *          ),
     *      ),
     *     ),
     *     @OA\Response(
     *      response="200",
     *      description="Usuário logado com sucesso",
     *      @OA\JsonContent(
     *          @OA\Schema(
     *              schema="userLogoutSuccess",
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *              ),
     *          ),
     *          example="{
                            'success': true,
                            'message': 'Usuário deslogado com sucesso!'
                        }"
     *      ),
     *    ),
     *    @OA\Response(
     *     response="500",
     *     description="internal server error",
     *     @OA\JsonContent(
     *          @OA\Schema(
     *              schema="userLogoutError",
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *              ),
     *          ),
     *          example="{
                    'success': false,
                    'message': 'Houve um erro ao tentar efetuar logout!'
                }"
     *      ),
     *    )
     * )
     */
    public function logout(Request $request) {
        $this->validate($request, [
            'token' => 'required'
        ]);

        try {
            JWTAuth::invalidate($request->token);

            return response()->json([
                'success' => true,
                'message' => 'Usuário deslogado com sucesso!'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Houve um erro ao tentar efetuar logout.'
            ], 500);
        }
    }
}
