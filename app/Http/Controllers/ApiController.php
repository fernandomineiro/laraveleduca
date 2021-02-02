<?php

namespace App\Http\Controllers;

use App\Aluno;
use App\LogAcesso;
use App\Professor;
use App\Rules\ValidRecaptcha;
use App\Usuario;
use App\UsuarioAcessos;
use App\UsuariosPerfil;
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
class ApiController extends Controller {

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

        if (!empty($request->get('provider', null)) && $request->get('provider', null) == 'GOOGLE') {
            return $this->googleAuth($request);
        }

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

    /**
     *
     */
    public function loginEscolas(Request $request) {

        if (
            !empty($request->get('provider', null)) &&
            $request->get('provider', null) == 'GOOGLE'
        ) {
            return $this->googleAuth($request);
        }

        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar o login, campos inválidos',
                'erros' => $validator->messages()->all()
            ], 401);
        }

        $credentials = array_merge($request->only('email', 'password', 'fk_faculdade_id'), ['fk_perfil' => 14]);
        if (!$jwt_token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email ou Senha inválido.',
            ], 401);
        }

        return $this->authFromUser(JWTAuth::user(), $request);
    }

    public function googleAuth(Request $request) {

        $oUser = Usuario::select(
            'usuarios.id',
            'usuarios.email',
            'usuarios.fk_perfil',
            'usuarios.foto',
            'usuarios.status',
            'usuarios.fk_faculdade_id',
            'usuarios.id_google'
        )
            ->join('alunos', 'usuarios.id', 'alunos.fk_usuario_id')
            ->where('alunos.fk_faculdade_id', $request->header('Faculdade', 7))
            ->where('email', $request->get('email', null))
            ->where('id_google', $request->get('id', null))
            ->first();

        if (empty($oUser)) {

            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'name' => 'required',
                'id' => 'required'
            ], [
                'email.required' => 'O email é obrigatório',
                'name.required' => 'Nome é obrigatório',
                'id.required' => 'Id do provider é obrigatório'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao realizar o login, campos inválidos',
                    'erros' => $validator->messages()->all()
                ], 401);
            }

            $oUser = Usuario::updateOrCreate(
                [
                    'id_google' => $request->get('id', null),
                    'email' => $request->get('email', null)
                ],
                [
                    'nome' => $request->get('name'),
                    'email' => $request->get('email'),
                    'password' => bcrypt($this->gerarSenha(10, true, true, true, true)),
                    'foto' => $request->get('photoUrl'),
                    'fk_perfil' => UsuariosPerfil::ALUNO,
                    'id_google' => $request->get('id'),
                ]
            );

            Aluno::create(
                [
                    'nome' => $request->get('firstName'),
                    'sobre_nome' => $request->get('lastName'),
                    'fk_usuario_id' => $oUser->getId(),
                    'fk_faculdade_id' => $request->header('Faculdade', 7),
                ]
            );

        }

        return $this->authFromUser($oUser, $request);
    }

    public function authFromUser($oUser, Request $request) {

        $oLoggedUser = Usuario::select(
            'usuarios.id',
            \DB::raw("concat(alunos.nome, ' ', COALESCE(alunos.sobre_nome, '')) as nome"),
            'usuarios.email',
            'usuarios.fk_perfil',
            'usuarios.foto',
            'usuarios.status',
            'usuarios.fk_faculdade_id',
            'usuarios.id_google',
            'estrutura_curricular.id as id_turma',
            'estrutura_curricular.titulo as nome_turma',
            'escolas.id as id_escola',
            'escolas.razao_social as nome_escola'
        )->join('alunos', 'usuarios.id', 'alunos.fk_usuario_id')
            ->join('estrutura_curricular_usuario', 'estrutura_curricular_usuario.fk_usuario', 'usuarios.id')
            ->join('estrutura_curricular', 'estrutura_curricular_usuario.fk_estrutura', 'estrutura_curricular.id')
            ->join('escolas', 'estrutura_curricular.fk_escola', 'escolas.id')
//            ->where('alunos.fk_faculdade_id', $request->header('Faculdade', 7))
            ->where('usuarios.id', $oUser->getId())->first();

        if (!$userToken = JWTAuth::fromUser($oLoggedUser)) {
            return response()->json(['error' => 'invalid_credentials'], 401);
        }

        $newacesso = new LogAcesso();
        $newacesso->create([
            'fk_usuario' => $oLoggedUser->id,
            'ip_acesso' => request()->ip(),
            'data_acesso' => date("Y-m-d h:i", time()),
            'user_agent_acesso' => $request->header('User-Agent'),
        ]);

        UsuarioAcessos::create(['usuario_id' => $oLoggedUser->id, 'ip' => $request->ip()]);
        return response()->json([
            'success' => true,
            'token' => $userToken,
            'user' => $oLoggedUser,
            'faculdade' => env('FACULDADE', 1)
        ]);
    }    

    /**
     * Realiza o login do aluno kroton
     * Verificar issue ED2-1184 e ED2-1367 para encontrar o documento específico que explica o procedimento completo
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginKroton(Request $request) {

        $aluno = Aluno::where('cpf', $request->get('cpfaluno'))->first();
        $user = Usuario::find($aluno->fk_usuario_id);

        $newacesso = new LogAcesso();

        $newacesso->create([
            'fk_usuario' => $user->id,
            'ip_acesso' => request()->ip(),
            'data_acesso' => date("Y-m-d h:i", time()),
            'user_agent_acesso' => $request->header('User-Agent'),
        ]);

        UsuarioAcessos::create(['usuario_id' => $user->id,'ip' => $request->ip()]);

        $jwt_token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'token' => $jwt_token,
            'user' => $user,
            'membership' => null,
            'faculdade' => 7
        ]);

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
            'membership' => ['type' => !empty($userMembership) ? $userMembership : null],
            'faculdade' => env('FACULDADE', 1)
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginEscolasAcessoRestrito(Request $request) {

        /*if (
            !empty($request->get('provider', null)) &&
            $request->get('provider', null) == 'GOOGLE'
        ) {
            return $this->googleAuth($request);
        }*/

        $credentials = array_merge($request->only('email', 'password'));
        if (!$jwt_token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email ou Senha inválido.',
            ], 401);
        }

        return $this->authFromUserAcessoRestrito(JWTAuth::user(), $request);
    }

    public function authFromUserAcessoRestrito($oUser, Request $request) {
        if ($oUser->fk_perfil == 1) {
            $oLoggedUser = Usuario::select(
                'usuarios.id',
                'usuarios.email',
                'usuarios.fk_perfil',
                'usuarios.foto',
                'usuarios.status',
                'usuarios.fk_faculdade_id',
                'usuarios.id_google',
                'professor.id as id_professor'
            )->join('professor', 'usuarios.id', 'professor.fk_usuario_id')
                ->join('professor_escola', 'professor.id', 'professor_escola.fk_professor')
                ->where('usuarios.id', $oUser->getId())->first();

            if (!$userToken = JWTAuth::fromUser($oLoggedUser)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }

            /** @var Professor $professor */
            $professor = Professor::with('escolas')->find($oLoggedUser->id_professor);
            $oLoggedUser->escolas = $professor->escolas()->get(['escolas.id', 'razao_social', 'cnpj', 'telefone_1', 'telefone_2', 'fk_endereco_id', 'url']);
        } else {
            $oLoggedUser = $oUser;
            if (!$userToken = JWTAuth::fromUser($oUser)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        }

        $newacesso = new LogAcesso();
        $newacesso->create([
            'fk_usuario' => $oLoggedUser->id,
            'ip_acesso' => request()->ip(),
            'data_acesso' => date("Y-m-d h:i", time()),
            'user_agent_acesso' => $request->header('User-Agent'),
        ]);

        UsuarioAcessos::create(['usuario_id' => $oLoggedUser->id, 'ip' => $request->ip()]);
        return response()->json([
            'success' => true,
            'token' => $userToken,
            'user' => $oLoggedUser,
            'faculdade' => env('FACULDADE', 1)
        ]);
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
