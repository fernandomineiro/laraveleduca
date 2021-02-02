<?php

namespace App\Http\Controllers\API;

use App\Aluno;
use App\Cidade;
use App\Endereco;
use App\Estado;
use App\EstruturaCurricular;
use App\EstruturaCurricularUsuario;
use App\Services\UsuarioService;
use App\Usuario;
use App\Http\Resources\AlunoResource;
use App\Http\Requests\Api\AlunoRequest;
use App\Helper\EducazMail;
use App\Http\Controllers\Controller;
use App\UsuariosPerfil;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AlunoController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $alunos = Aluno::where('status', 1)->get()->toArray();
            return response()->json(['items' => $alunos]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);

            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu ao retornar alunos. O suporte já foi avisado',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($usarioId) {
        try {
            $aluno = Aluno::perfil($usarioId);
            return response()->json(['data' => $aluno]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);

            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu ao retornar alunos. O suporte já foi avisado',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param lluminate\Http\Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request){

        DB::beginTransaction();
        try {
            $data = $request->all();
            $alunoObjeto = new Aluno($data);

            $validator = Validator::make($data, $alunoObjeto->rules, $alunoObjeto->messages);
            if ($validator->fails()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => 'Erro ao inserir aluno! Campos inválidos',
                    'validator' => $validator->messages()->all()
                ]);
            }

            //o fk_perfil é obrigatório
            $data['fk_perfil'] = UsuariosPerfil::ALUNO;
            $data['fk_faculdade_id'] = $request->header('Faculdade', 7);

            $usuarioObj = new Usuario($data);
            $usuarioObj->nome = $alunoObjeto->getName();
            $validator = $usuarioObj->_validate($data);
            if ($validator->fails()) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'error' => 'Erro ao inserir aluno! Campos inválidos',
                    'validator' => $validator->messages()->all()
                ]);
            }

            $usuarioObj->password = bcrypt(trim($data['senha']));
            if (!$usuarioObj->save()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => 'Não foi possível salvar o aluno. Tente novamente mais tarde.'
                ]);
            }

            $usuarioId = $usuarioObj->id;
            $dataEndereco = [
                'bairro' => !empty($data['bairro']) ? $data['bairro'] : null,
                'cep' => !empty($data['cep']) ? $data['cep'] : null,
                'logradouro' => !empty($data['logradouro']) ? $data['logradouro'] : null,
                'numero' => !empty($data['numero']) ? $data['numero'] : null,
                'fk_estado_id' => !empty($data['fk_estado_id']) ? $data['fk_estado_id'] : null,
                'fk_cidade_id' => !empty($data['fk_cidade_id']) ? $data['fk_cidade_id'] : null,
                'complemento' => !empty($data['complemento']) ? $data['complemento'] : null,
            ];

            $endereco = new Endereco($dataEndereco);
            if (!$endereco->save()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => 'Não foi possível salvar o aluno. Tente novamente mais tarde.'
                ]);
            }

            if ($usuarioId && $usuarioId > 0) {

                $estruturas = EstruturaCurricular::select('estrutura_curricular.*')
                        ->join('estrutura_curricular_faculdades', 'estrutura_curricular.id', 'estrutura_curricular_faculdades.fk_estrutura')
                        ->where('estrutura_curricular.estrutura_livre_cadastro', 1)
                        ->where('estrutura_curricular.status', 1)
                        ->where('estrutura_curricular_faculdades.fk_faculdade', $request->header('Faculdade', 6))
                        ->get();

                foreach ($estruturas as $estrutura) {
                    $estruturaAluno = new EstruturaCurricularUsuario();
                    $estruturaAluno->fill([
                        'fk_estrutura' => $estrutura->id,
                        'fk_usuario' =>$usuarioId
                    ]);
                    $estruturaAluno->save();
                }


                $alunoObjeto->fk_usuario_id = $usuarioId;
                $alunoObjeto->fk_faculdade_id = $data['fk_faculdade_id'];
                $alunoObjeto->status = 1;
                $alunoObjeto->matricula  = isset($data['ra']) ? $data['ra'] : null;
                $alunoObjeto->fk_endereco_id = $endereco->id;


                if (! $alunoObjeto->save()) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error' => 'Não foi possível salvar o aluno. Tente novamente mais tarde.'
                    ]);
                }

                $jwt_token = JWTAuth::fromUser($usuarioObj);
                DB::commit();
                return response()->json([
                    'success' => true,
                    'data' => Aluno::find($alunoObjeto->id)->toArray(),
                    'token' => $jwt_token,
                    'user' => $usuarioObj->toArray(),
                    'membership' => ['type' => null],
                    'faculdade' => $request->header('Faculdade', 7)
                ]);
            }


        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu ao tentar salvar o aluno. O suporte já foi avisado.',
                'exception' => $e->getMessage(),
                'data' => $request->all()
            ]);
        }
    }

    /**
     * Confirma ou remove uma presença
     *
     * @param lluminate\Http\Request
     * @return \Illuminate\Http\JsonResponse
     */

    public function update(Request $request, $id) {
        $aluno = Aluno::where("fk_usuario_id","=",$id)->first();
        $user = Usuario::find($id);
        DB::beginTransaction();

        try {
            $data = $request->all();

            if (empty($data['fk_faculdade_id'])) {
                $data['fk_faculdade_id'] = $request->header('Faculdade', 1);
            }

            if (!empty($data['cnpjcpf'])) {
                $data['cpf'] = $data['cnpjcpf'];
                unset($data['cnpjcpf']);
            }

            if (!empty($data['rg'])) {
                $data['identidade'] = $data['rg'];
                unset($data['rg']);
            }
            if (!empty($data['ra'])) {
                $data['matricula'] = $data['ra'];
                unset($data['ra']);
            }

            if (!empty($data['fk_estado_id'])) {
                $uf = Estado::where('id', $data['fk_estado_id'])->first();
            } else if (!empty($data['estado'])) {
                $uf = Estado::where('uf_estado', $data['estado'])->first();
            }

            if (!empty($data['fk_cidade_id'])) {
                $cidade = Cidade::where('id', $data['fk_cidade_id'])->first();
            } else if (!empty($uf) && !empty($data['cidade'])) {
                $cidade = Cidade::where('fk_estado_id', $uf->id)
                    ->where('descricao_cidade', 'like', $data['cidade'].'%')
                    ->first();
            }

            if (!empty($data['universidade']) && $data['universidade'] == 'outro') {
                $data['curso'] = null;
            } else {
                $data['universidade_outro'] = null;
                $data['curso_outro'] = null;
            }

            $dataEndereco = [
                'bairro' => !empty($data['bairro']) ? $data['bairro'] : null,
                'cep' => !empty($data['cep']) ? $data['cep'] : null,
                'logradouro' => !empty($data['logradouro']) ? $data['logradouro'] : null,
                'numero' => !empty($data['numero']) ? $data['numero'] : null,
                'fk_estado_id' => !empty($uf) ? $uf->id : null,
                'fk_cidade_id' => !empty($cidade) ? $cidade->id : null,
                'complemento' => !empty($data['complemento']) ? $data['complemento'] : null,
            ];

            if (!empty(array_filter($dataEndereco))) {
                $endereco = Endereco::where('id', $aluno->fk_endereco_id)->first();


                if (!$endereco) {
                    $endereco = new Endereco();
                }


                $endereco->fill(array_filter($dataEndereco));
                $endereco->save($dataEndereco);

                $data['fk_endereco_id'] = $endereco->id;
            }

            $aluno->update($data);

            $user->nome = $aluno->getName();
            $user->save();

            DB::commit();


        } catch (\InvalidArgumentException $e) {
            DB::rollBack();

            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);

            return response()->json([
                'success' => false,
                'messages' => $e->getMessage(),
                'data' => $request->all()
            ]);
        }  catch (\Exception $e) {
            DB::rollBack();

            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado',
                'exception' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [$user]
        ]);

    }

    public function saveAddress(Request $request) {
        try {

            $validator = Endereco::validade($request->all());
            if ($validator->fails()) {
                return response()->json(['success' => false, 'messages' => $validator->errors()->toArray()]);
            }

            $endereco = Endereco::updateOrCreate(['id' => $request->get('id')], $request->all());

            if (!empty($request->get('fk_usuario'))) {
                $user  = Aluno::where('fk_usuario_id', $request->get('fk_usuario'))->first();
                $user->fk_endereco_id = $endereco->id;
                $user->save();
            }

            return response()->json(['success' => true, 'data' => $endereco]);

        } catch (\Exception $e) {

            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);

            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function saveCredentials(Request $request, $id) {
        try {

            $user = Usuario::where(['id' => $id])->first();
            if (!empty($request->get('email'))) {
                $user->email = $request->get('email');
            }

            if (!empty($request->get('senha')) && $request->get('senha') == $request->get('senha_confirmar')) {
                $user->password = bcrypt($request->get('senha'));
            }

            $user->save();

            return response()->json(['success' => true, 'data' => [$user]]);

        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);

            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function upload_foto(Request $request, $id){
        try {
            $aluno = Usuario::find($id);

            // dd($request->input('name'));
            $foto = $request->file('imagem');
            // $foto= $request->input('name');
            // $foto= (object) $request->all();
            // $foto= $_FILES['imagem'];
            // $foto= $request->file("imagem");
            $type = $foto->getClientOriginalExtension();

            if (!$foto) return;
            $image_name = time() . rand(1000000, 9999999) . "." . $type;

            $filePath = 'files/usuario/' . $image_name;
            Storage::disk('s3')->put($filePath, file_get_contents($foto), 'public');

            $aluno->foto = $image_name;
            $aluno->save();

            return response()->json([
                'success' => true,
                'fileNo' => $image_name
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);

            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Daddos comuns a INSERT e UPDATE
     * @param Request $request
     * @param $type
     * @param $new
     * @return array|mixed
     */
    private function addValuesData($data, $new, $obj = null)
    {
        if (isset($data['telefone_1']))
            if (trim($data['telefone_1']) != '')
                $data['telefone_1'] = $this->removerMascaraSemUtilizacao($data['telefone_1']);

        if (isset($data['telefone_2']))
            if (trim($data['telefone_2']) != '')
                $data['telefone_2'] = $this->removerMascaraSemUtilizacao($data['telefone_2']);

        if (isset($data['telefone_3']))
            if (trim($data['telefone_3']) != '')
                $data['telefone_3'] = $this->removerMascaraSemUtilizacao($data['telefone_3']);

        if (isset($data['share']))
            if (trim($data['share']) != '')
                $data['share'] = $this->tranformaValorSqlShare($data['share']);

        return $data;
    }

    /**
     * @param lluminate\Http\Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {

    }

    /**
     * Retorna os cursos do aluno
     *
     * @param $alunoId
     * @return \Illuminate\Http\JsonResponse
     */
    public function cursos($id = null)
    {
        try {
            $aluno = Aluno::find($id);
            $cursos = $aluno ? $aluno->cursos : [];
            return response()->json(['items' => $cursos, 'count' => count($cursos)]);
        } catch (\Exception $e) {

            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu ao retornar alunos. O suporte já foi avisado',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function redefinirSenha(Request $request) {
        try {

            /** @var UsuarioService $usuarioService */
            $usuarioService = app()->make(UsuarioService::class);
            
            $usuarioService->setIdFaculade($request->header('Faculdade', 7));
            return response()->json(
                $usuarioService->recuperarSenhaPortal($request->get('email'), UsuariosPerfil::ALUNO)
            );
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function buscarEnderecoCep($cep) {

        try {
            //request url
            $url = 'https://viacep.com.br/ws/' . str_replace('.', '', str_replace('-', '', $cep)) . '/json';

            //create new instance of Client class
            $client = new Client();

            //send get request to fetch data
            $response = $client->request('GET', $url);

            if ($response->getStatusCode() == 200) {
                //header information contains detail information about the response.
                if ($response->hasHeader('Content-Length')) {
                    //get number of bytes received
                    $content_length = $response->getHeader('Content-Length')[0];
                    echo '<p> Download '. $content_length. ' of data </p>';
                }

                //get body content
                $body = $response = json_decode($response->getBody()->getContents());

                if (!empty($body->erro) && $body->erro) {
                    return response()->json(['success' => false, 'message' => 'CEP não encontrado']);
                }

                $estado = Estado::where('uf_estado', '=', $body->uf)->first();
                $body->ufId = $estado->id;

                $cidade = Cidade::where('descricao_cidade', 'like', $body->localidade.'%')
                    ->where('fk_estado_id', '=', $estado->id)->first();
                $body->cidadeId = $cidade->id;

                return response()->json(['items' => $body, 'success' => true]);
            }

            return response()->json(['success' => false, 'message' => 'CEP não encontrado']);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);

            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Verifica se o cadastro está completo para comprar
     * @param $idUsuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkCadastroCompleto($idUsuario) {
        try {
            $aluno = Aluno::where('fk_usuario_id', $idUsuario)->first();
            
            if (!$aluno->nome || !$aluno->sobre_nome || !$aluno->cpf || !$aluno->identidade || !$aluno->data_nascimento 
                || !$aluno->telefone_2 || !$aluno->fk_endereco_id) {
                return response()->json([
                    'success' => false
                ]);
            }
            
            $endereco = Endereco::find($aluno->fk_endereco_id);
            if (!$endereco) {
                return response()->json([
                    'success' => false
                ]);
            }
            
            $valido = true;
            $endereco->each(function ($item, $key) use ($valido) {
                if (!$item) $valido = false;
            });

            return response()->json([
                'success' => $valido
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);

            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado',
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
