<?php

namespace App\Http\Controllers\EducazEscolas;

use App\Escola;
use App\EstruturaCurricular;
use App\Gestao;
use App\Http\Controllers\Controller;
use App\Professor;
use App\UsuariosPerfil;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class EscolaController extends Controller {

    protected $_query;
    protected $user;

    public function __construct() {
        parent::__construct();

        $this->user = JWTAuth::parseToken()->authenticate();

        $this->_query = Escola::select(
                                    'escolas.id',
                                    'escolas.razao_social',
                                    'escolas.cnpj',
                                    'escolas.slug',
                                    'escolas.telefone_1',
                                    'escolas.telefone_2',
                                    'escolas.fk_faculdade',
                                    'escolas.fk_endereco_id',
                                    'escolas.fk_diretoria_ensino',
                                    'escolas.fk_diretor',
                                    'usuarios.nome as nome_diretor'
                            )->distinct()->leftjoin('usuarios', 'usuarios.id', 'escolas.fk_diretor')
                            ->where('escolas.status', 1)
                            ->with([
                                'endereco' => function($query) {
                                    $query->select('id', 'cep', 'logradouro', 'numero', 'complemento', 'bairro');
                                }])
                            ->with([
                                'diretoriaEnsino' => function($query) {
                                    $query->select('id', 'nome', 'slug');
                                }]);




    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function all(Request $request) {
        try {

            $escolas = $this->_query->where('fk_faculdade', $request->header('Faculdade', 29));

            if ($this->user->fk_perfil == UsuariosPerfil::DIRETOR) {
                $escolas->where('escolas.fk_diretor', $this->user->id);
            }

            if ($this->user->fk_perfil == UsuariosPerfil::PROFESSOR) {
                $escolas->join('professor_escola', 'professor_escola.fk_escola', 'escolas.id')
                        ->where('professor_escola.fk_professor', $this->user->id);
            }

            if ($this->user->fk_perfil == UsuariosPerfil::ORIENTADOR) {
                $escolas->join('estrutura_curricular', 'estrutura_curricular.fk_escola', 'escolas.id')->where('estrutura_curricular.fk_orientador', $this->user->id);
            }

            if ($this->user->fk_perfil == UsuariosPerfil::GESTOR_IES) {

                $gestao = Gestao::where('fk_usuario_id', $this->user->id)->first();
                if (!empty($gestao->fk_diretoria_ensino)) {
                    $escolas->where('escolas.fk_diretoria_ensino', $gestao->fk_diretoria_ensino);
                }
            }

            return response()->json($escolas->get());

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param $idEscola
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($idEscola, Request $request) {
        try {

            $diretorias = $this->_query
                                ->where('fk_faculdade', $request->header('Faculdade', 29))
                                ->where('escolas.id', $idEscola)
                                ->with([
                                    'endereco' => function($query) {
                                        $query->select('id', 'cep', 'logradouro', 'numero', 'complemento', 'bairro');
                                    }])
                                ->with([
                                    'diretoriaEnsino' => function($query) {
                                        $query->select('id', 'nome', 'slug');
                                    }])
                                ->first();

            return response()->json($diretorias);

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param $idEscola
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($idEscola, Request $request) {

        try {

            /** @var Escola $escola */
            $escola = Escola::find($idEscola);
            $escola->update(
                array_merge($request->all(), ['slug' => Str::slug($request->get('razao_social'), '-')])
            );

            $endereco = $escola->endereco()->updateOrCreate(['id' => $request->get('endereco')['id']], $request->get('endereco'));
            $escola->update(['fk_endereco_id' => $endereco->id]);

            return $this->show($idEscola, $request);

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request) {

        try {

            /** @var Escola $escola */
            $escola = Escola::create(
                array_merge($request->all(), [
                    'slug' => Str::slug($request->get('razao_social'), '-'),
                    'fk_faculdade' => $request->header('Faculdade', 29)
                ])
            );

            $endereco = $escola->endereco()->updateOrCreate(['id' => $request->get('endereco')['id']], $request->get('endereco'));
            $escola->update(['fk_endereco_id' => $endereco->id]);

            return $this->show($escola->id, $request);

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ]);
        }

    }

    /**
     * @param Request $request
     * @param $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function escolasDiretoria(Request $request, $slug) {
        try {

            $diretorias = $this->_query->join('diretoria_ensino', 'escolas.fk_diretoria_ensino', 'diretoria_ensino.id')
                ->leftjoin('estrutura_curricular', 'estrutura_curricular.fk_escola', 'escolas.id')
                ->where('escolas.status', 1)
                ->where('escolas.fk_faculdade', $request->header('Faculdade', 29))
                ->with([
                    'endereco' => function($query) {
                        $query->select('id', 'cep', 'logradouro', 'numero', 'complemento', 'bairro');
                    }])
                ->with([
                    'diretoriaEnsino' => function($query) {
                        $query->select('id', 'nome', 'slug');
                    }]);

            if (!empty($slug) && $slug != 'undefined') {
                $diretorias->where('diretoria_ensino.slug', $slug);
            }

            $user = JWTAuth::parseToken()->authenticate();
            if ($user->fk_perfil == UsuariosPerfil::ORIENTADOR) {
                $diretorias->where('estrutura_curricular.fk_orientador', $user->id);
            }

            return response()->json($diretorias->get());

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param $idEscola
     * @param $idUsuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function turmas($idEscola, $idUsuario) {

        try {

            if (empty($idEscola)) {
                throw new \Exception('Escola não informada');
            }

            if (empty($idUsuario)) {
                throw new \Exception('Usuário não informada');
            }

            $data = EstruturaCurricular::select(
                'estrutura_curricular.id',
                'titulo',
                'fk_escola',
                'fk_estrutura',
                'fk_usuario'
            )
                ->join('estrutura_curricular_usuario', 'estrutura_curricular.id', 'estrutura_curricular_usuario.fk_estrutura')
                ->where('fk_escola', $idEscola)
                ->where('estrutura_curricular_usuario.fk_usuario', $idUsuario)
                ->get();


            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function deletar($id) {
        try {

            $escola = Escola::findOrFail($id);
            $escola->delete();

            return response()->json('sucesso');

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
                'trace' => $exception->getMessage(),
            ]);
        }
    }
}