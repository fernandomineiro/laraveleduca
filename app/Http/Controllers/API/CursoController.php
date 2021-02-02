<?php

namespace App\Http\Controllers\API;

use App\AgendaCursos;
use App\AvisarNovasTurmas;
use App\ConfiguracoesTiposCursosAtivos;
use App\CursosFaculdades;
use App\CursosTrabalhos;
use App\CursoTipo;
use App\EstruturaCurricular;
use App\Services\ItvService;
use App\TipoAssinatura;
use App\ViewUsuarioCompleto;
use App\ViewUsuarios;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Helper\EducazMail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

use App\Curso;
use App\Faculdade;
use App\ConclusaoCursosFaculdades;
use App\CursoCategoria;
use App\CursoCategoriaCurso;
use App\CursoTurmaAgenda;
use App\CursoSecao;
use App\CursoModulo;
use App\CursoTag;
use App\CursoAvaliacao;
use App\CursoFavorito;
use App\Quiz;
use App\QuizQuestao;
use App\QuizResposta;
use App\CursoValor;
use App\Professor;
use App\Usuario;
use App\Pergunta;
use App\PerguntaResposta;
use App\ModuloUsuario;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Helper\CertificadoHelper;

class CursoController extends Controller {

    public function __construct() {
        \Config::set('jwt.user', 'App\ViewUsuarios');
        \Config::set('auth.providers.users.model', ViewUsuarios::class);

        parent::__construct();
    }

    /**
     * @param bool $idCurso
     * @param bool $idTipo
     * @param bool $idCategoria
     * @param int $idFaculdade
     * @return JsonResponse
     */
    public function index($idCurso = false, $idTipo = false, $idCategoria = false, $idFaculdade = 1)
    {
        try {
            if ($idCurso) {
                $data = Curso::obter($idCurso, $idFaculdade);

                if(isset($data)){

                    $data['indisponivel_venda'] = CursosFaculdades::where('fk_curso', $idCurso)
                    ->where('fk_faculdade', $idFaculdade)
                    ->pluck('indisponivel_venda')
                    ->first();

                    if($data['tipo'] == '4'){
                        $parte_presencial = CursoTurmaAgenda::select('cursos_turmas_agenda.*')
                            ->join('cursos_turmas', 'cursos_turmas_agenda.fk_turma', '=', 'cursos_turmas.id')
                            ->where('cursos_turmas.fk_curso', '=', $idCurso)->get();
                        $presencial_count = count($parte_presencial);
                        $parte_online = CursoSecao::modulosCurso($idCurso);
                        $online_count = count($parte_online);
                        $total = $online_count + $presencial_count;
                        if($total != 0){
                            $data['percentual_online'] = round(($online_count/$total) * 100) . '%';
                            $data['percentual_presencial'] = round(($presencial_count/$total) * 100) . '%';
                        } else {
                            $data['percentual_online'] = 0;
                            $data['percentual_presencial'] = 0;
                        }
                    }

                    $criterios = ConclusaoCursosFaculdades::
                        where('fk_curso', '=', $idCurso)
                        ->where('fk_faculdade', '=', $idFaculdade)->first();
                    if($criterios){
                        //quando trabalho for null, não existe trabalho
                        //quando trabalho for >= 0, existe trabalho e essa é a nota exigida
                        $data['trabalho'] = isset($criterios['nota_trabalho'])? 1: 0;
                        //quando certificado for null, não emite certificado
                        //quando certificado for 0, emite certificado padrão
                        //quando certificado for > 0, este é o id do certificado layout a ser usado para emissão
                        $data['emite_certificado'] = isset($criterios['fk_certificado'])? 1: 0;
                    }

                    $data['gratis'] = isset($data['gratis']) ? (int) $data['gratis'] : 0;
                }

                return response()->json(['data' => $data]);
            } else {
                $data = Curso::lista($idTipo, $idCategoria, 5, $idFaculdade);
                foreach ($data as $presencial) {
                    $presencial['indisponivel_venda'] = CursosFaculdades::where('fk_curso', $presencial['id'])
                        ->where('fk_faculdade', $idFaculdade)
                        ->pluck('indisponivel_venda')
                        ->first();
                    if (isset($idTipo) && $idTipo == 2) {
                        $presencial['data'] = CursoTurmaAgenda::select('cursos_turmas_agenda.*')
                            ->join('cursos_turmas', 'cursos_turmas_agenda.fk_turma', '=', 'cursos_turmas.id')
                            ->where('cursos_turmas.fk_curso', '=', $presencial['id'])
                            ->orderBy('data_inicio', 'asc')
                            ->pluck('data_inicio')
                            ->first(); // essa query pega a menor data inicial disponível entre os dias da agenda e trata como data inicial do curso
                    }
                }

                if (isset($idTipo) && $idTipo == 4) {
                    foreach ($data as $remoto) {
                        $parte_presencial = CursoTurmaAgenda::select('cursos_turmas_agenda.*')
                            ->join('cursos_turmas', 'cursos_turmas_agenda.fk_turma', '=', 'cursos_turmas.id')
                            ->where('cursos_turmas.fk_curso', '=', $remoto['id'])->get();
                        $presencial_count = count($parte_presencial);
                        $parte_online = CursoSecao::modulosCurso($remoto['id']);
                        $online_count = count($parte_online);
                        $total = $online_count + $presencial_count;
                        if($total != 0){
                            $remoto['percentual_online'] = round(($online_count/$total) * 100) . '%';
                            $remoto['percentual_presencial'] = round(($presencial_count/$total) * 100) . '%';
                        } else {
                            $remoto['percentual_online'] = 0;
                            $remoto['percentual_presencial'] = 0;
                        }

                        //obter primeiro e ultimo modulo do curso
                        $primeira_ultima_data = CursoTurmaAgenda::select('cursos_turmas_agenda.*')
                            ->join('cursos_turmas', 'cursos_turmas_agenda.fk_turma', '=', 'cursos_turmas.id')
                            ->where('cursos_turmas.fk_curso', '=', $remoto['id'])
                            ->orderBy('data_inicio', 'asc')
                            ->pluck('data_inicio');

                        $remoto['primeira_data'] = $primeira_ultima_data
                            ->first();
                        $remoto['ultima_data'] = $primeira_ultima_data
                            ->last();
                    }
                }
                return response()->json([
                    'items' => $data,
                    'count' => count($data)
                ]);
            }
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Show
     * @param $id
     * @return JsonResponse
     */
    public function show(Request $request, $id)
    {
        try {
            $retorno['curso'] = Curso::findOrFail($id);

            if ($retorno['curso']->fk_cursos_tipo === 5) {
                $curso = Curso::getCursoMentoria($id);
                $permission = false;
                if ($request->has('usuario_id')) {
                    $usuario = Usuario::find($request->usuario_id);

                    $permission = $curso['id_professor'] === $usuario->id ? true : $usuario->has_mentoria;
                }

                return response()->json(array_merge($curso, ['permission' => $permission]));
            }

            $retorno['agendas_cadastradas']  = CursoTurmaAgenda::where('cursos_turmas_agenda.fk_curso', '=', $id)->get();

            $retorno['modulos_cadastrados']  = CursoModulo::where('fk_curso', '=', $id)->get();
            $retorno['tags_cadastradas']  = CursoTag::where('fk_curso', '=', $id)->get();

            $lista_faculdades = CursosFaculdades::select('cursos_faculdades.*')
                ->join('faculdades', 'faculdades.id', '=', 'cursos_faculdades.fk_faculdade')
                ->where('cursos_faculdades.fk_curso', $id)
                ->get()->toArray();

            $secoes_cadastradas  = CursoSecao::select(
                'cursos_secao.id as secao_id',
                'cursos_secao.titulo as secao_titulo',
                'cursos_secao.ordem as secao_ordem',
                'cursos_modulos.id as modulo_id',
                'cursos_modulos.titulo as modulo_titulo',
                'cursos_modulos.carga_horaria as modulo_carga_horaria',
                'cursos_modulos.url_arquivo as modulo_url_arquivo',
                'cursos_modulos.url_video as modulo_url_video',
                'cursos_modulos.ordem as modulo_ordem'
            )->join('cursos_modulos', 'cursos_modulos.fk_curso_secao', '=', 'cursos_secao.id')
                ->where('cursos_secao.fk_curso', '=', $id)
                ->orderBy('cursos_secao.id')
                ->orderBy('cursos_modulos.ordem')
                ->distinct()
                ->get();

            $lista_secoes = [];
            foreach($secoes_cadastradas as $key => $secao) {
                $lista_secoes[$secao['secao_id']]['id'] = $secao['secao_id'];
                $lista_secoes[$secao['secao_id']]['titulo'] = $secao['secao_titulo'];
                $lista_secoes[$secao['secao_id']]['ordem'] = $secao['secao_ordem'];
                $lista_secoes[$secao['secao_id']]['modulos'] = [];
                array_push($lista_secoes[$secao['secao_id']]['modulos'], [
                    'id' => $secao['modulo_id'],
                    'titulo' => $secao['modulo_titulo'],
                    'carga_horaria' => $secao['modulo_carga_horaria'],
                    'url_arquivo' => $secao['modulo_url_arquivo'],
                    'url_video' => $secao['modulo_url_video'],
                    'ordem' => $secao['modulo_ordem']
                ]);
            }
            $lista_secoes = array_values($lista_secoes);

            $quiz = Quiz::select('quiz.*')->where('quiz.fk_curso', '=', $id)->first();

            if($quiz) {
                $lista_questao = array();
                $lista_resposta = array();

                $quiz_questao = QuizQuestao::select('*')->where('quiz_questao.fk_quiz', '=', $quiz->id)->get();
                if(count($quiz_questao)) {

                    foreach($quiz_questao as $key => $questao) {
                        $lista_questao[$questao->id] = $questao;

                        $quiz_resposta = QuizResposta::select('*')->where('quiz_resposta.fk_quiz_questao', '=', $questao->id)->get();
                        if(count($quiz_resposta)) {
                            foreach($quiz_resposta as $k_resposta => $resposta) {
                                $lista_resposta[$questao->id][] = $resposta;
                            }
                        }
                    }
                }

                $retorno['quiz'] = $quiz;
                $retorno['quiz_questao'] = $quiz_questao;
                $retorno['quiz_resposta'] = $lista_resposta;
            }
            $retorno['lista_categorias'] = CursoCategoriaCurso::where('fk_curso', '=', $id)->get();

            $retorno['secoes_cadastradas'] = $lista_secoes;
            $retorno['faculdades_cadastradas'] = $lista_faculdades;
            $retorno['dados_valor'] = CursoValor::where('fk_curso', $id)->where('data_validade', null)->first();

            $lista_conclusao_faculdades = array();
            foreach ($lista_faculdades as $faculdade) {
                if ($id != null) {
                    $conclusao = ConclusaoCursosFaculdades::all()->where('fk_curso', '=', $id)->where('fk_faculdade', '=',$faculdade['fk_faculdade'])->first();
                    if (isset($conclusao->id)) {
                        $x['id_conclusao'] = $conclusao->id;
                        $x['fk_faculdade'] = $faculdade['fk_faculdade'];
                        $x['fk_certificado'] = $conclusao->fk_certificado;
                        $x['nota_questionario'] = $conclusao->nota_quiz;
                        $x['nota_trabalho'] = $conclusao->nota_trabalho;
                        $x['frequencia_minima'] = $conclusao->freq_minima;
                        array_push($lista_conclusao_faculdades, $x);
                    }
                }
            }

            $retorno['lista_conclusao'] = $lista_conclusao_faculdades;

            return response()->json([
                'data' => $retorno
            ]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Cria Curso
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)  {
        $dadosForm = $request->except('_token');
        $loggedUser = Usuario::find($dadosForm['fk_criador_id']);

        if ($loggedUser && $loggedUser->fk_perfil == 2) {
            $dadosForm['fk_faculdade'] = $loggedUser->fk_faculdade_id;
        }
        $dadosForm['duracao_total'] = isset($dadosForm['duracao_total']) ? $dadosForm['duracao_total'] . ':00' : '00:00';
        $dadosForm['slug_curso'] = Curso::configurarSlugCurso($dadosForm['titulo']);
        
        try {
            DB::beginTransaction();

            if ($request->hasFile('imagem')) {
                $dadosForm['imagem'] = $this->uploadFile($request);
            }
            
            $curso = new Curso();
            
            $validator = $curso->_validate($dadosForm);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Não foi possível inserir o registro! Campos inválidos',
                    'errors' => $validator->messages()
                ]);
            }

            if ($request->get('fk_cursos_tipo') !== 5) {
                $dadosForm['objetivo_descricao'] = $dadosForm['objetivo_descricao'] ?? '';
                $dadosForm['publico_alvo'] = $dadosForm['publico_alvo'] ?? '';
            }
            /** @var Curso $resultado */
            $resultado = $curso->create($dadosForm);

            if ($resultado) {
                if (!empty($dadosForm['trabalho'])) {
                    $resultado->criarTrabalho();
                }

                if (isset($dadosForm['fk_cursos_categoria']) && count($dadosForm['fk_cursos_categoria'])) {
                    foreach ($dadosForm['fk_cursos_categoria'] as $categoria) {
                        $array = [
                            'fk_curso' => $resultado->id,
                            'fk_curso_categoria' => $categoria['fk_categoria']
                        ];

                        $cursoCategoriaCurso = new CursoCategoriaCurso();
                        
                        $cursoCategoriaCurso->create($array);
                        unset($cursoCategoriaCurso);
                    }
                }

                if (isset($dadosForm['faculdade'])) {
                    $faculdade = $dadosForm['faculdade'];
                    $array = [
                        'fk_curso' => $resultado->id,
                        'fk_faculdade' => $faculdade['fk_faculdade'],
                        'duracao_dias' => (int)isset($faculdade['duracao_dias']),
                        'disponibilidade_dias' => (int)isset($faculdade['disponibilidade_dias']),
                        'curso_gratis' => (isset($dadosForm['curso_gratis']) && $dadosForm['curso_gratis']) ? 1 : 0,
                    ];
                    // inserir aqui model nova após criação da tabela no banco
                    $cursoFaculdade = new CursosFaculdades();
                    $cursoFaculdade->create($array);
                    if($request->get('fk_cursos_tipo') !== 5){
                        $array = [
                            'fk_curso' => $resultado->id,
                            'fk_faculdade' => $faculdade['fk_faculdade'],
                            'fk_certificado' => $faculdade['fk_certificado'],
                            'nota_trabalho' => (int)$dadosForm['nota_trabalho'],
                            'nota_quiz' => (int)$dadosForm['nota_questionario'],
                            'freq_minima' => ($faculdade['frequencia_minima']) ? (int)$faculdade['frequencia_minima'] : 0,
                        ];

                        $conclusaoFaculdade = new ConclusaoCursosFaculdades();
                        $conclusaoFaculdade->create($array);
                    }

                }

                if ($request->get('fk_cursos_tipo') === 5) {
                    ### TAGS ###
                    if (isset($dadosForm['tags']) && count($dadosForm['tags'])) {
                        foreach ($dadosForm['tags'] as $key => $valor) {
                            if (!empty($valor['nome'])) {
                                $array = [
                                    'fk_curso' => $resultado->id,
                                    'tag' => $valor['nome']
                                ];

                                $cursoTag = new CursoTag();
                                $cursoTag->create($array);
                                unset($cursoTag);
                            }
                        }
                    }
                }

                if (isset($dadosForm['modulos']) && !empty($dadosForm['modulos'])) {

                    ### MODULOS ###
                    foreach ($dadosForm['modulos'] as $key_secao => $secao) {
                        if ($secao['titulo'] != '') {
                            $curso_secao = new CursoSecao();
                            $curso_secao->titulo = $secao['titulo'];
                            $curso_secao->ordem = $secao['ordem'];
                            $curso_secao->status = 1;
                            $curso_secao->fk_curso = $resultado->id;
                            $curso_secao->save();

                            foreach ($secao['subModulos'] as $key => $item) {

                                if (($key !== '__X__') && $item['titulo'] != '') {
                                    $cursos_modulo = new CursoModulo();
                                    $cursos_modulo->aula_ao_vivo = $item['aula_ao_vivo'];
                                    $cursos_modulo->data_aula_ao_vivo = isset($item['data_aula_ao_vivo']) ? implode('-',
                                        array_reverse(explode('/', $item['data_aula_ao_vivo']))) : '';
                                    $cursos_modulo->hora_aula_ao_vivo = $item['hora_aula_ao_vivo'];
                                    $cursos_modulo->link_aula_ao_vivo = $item['link_aula_ao_vivo'];
                                    $cursos_modulo->data_fim_aula_ao_vivo = isset($item['data_fim_aula_ao_vivo']) ? implode('-',
                                        array_reverse(explode('/', $item['data_fim_aula_ao_vivo']))) : '';
                                    $cursos_modulo->hora_fim_aula_ao_vivo = $item['hora_fim_aula_ao_vivo'];
                                    $cursos_modulo->titulo = $item['titulo'];
                                    $cursos_modulo->tipo_modulo = !empty($files_modulos[$key_secao][$key]['name']) ? 1 : 2;
                                    $cursos_modulo->url_video = $item['url_video'];
                                    $cursos_modulo->carga_horaria = str_replace(' ', '', $item['carga_horaria']);
                                    $cursos_modulo->url_arquivo = isset($item['arquivo']) ? $item['arquivo'] : '';
                                    $cursos_modulo->fk_curso = $resultado->id;
                                    $cursos_modulo->fk_curso_secao = $curso_secao->id;
                                    $cursos_modulo->ordem = $key;

                                    $cursos_modulo->status = 1;
                                    $cursos_modulo->save();
                                }
                            }
                        }
                    }

                    ### AGENDAS ###
                    $agendas = $dadosForm['agenda'] ?? null;

                    if (isset($agendas) && count($agendas)) {
                        foreach ($agendas as $key => $item) {
                            if ($item['descricao_agenda'] != '' && !empty($item['data_inicio'] && !empty($item['descricao_agenda']))) {
                                $agenda = new CursoTurmaAgenda();
                                $agenda->nome = !empty($item['descricao_agenda']) ? $item['descricao_agenda'] : 'Agenda ' . $key;
                                $agenda->duracao_aula = $item['duracao_aula'];
                                $agenda->data_inicio = $item['data_inicio'];
                                $agenda->data_final = $item['data_inicio'];
                                $agenda->hora_inicio = str_replace(' ', '', $item['hora_inicio']);
                                $agenda->hora_final = str_replace(' ', '', $item['hora_fim']);
                                $agenda->fk_curso = $resultado->id;
                                $agenda->fk_turma = 0;
                                $agenda->save();
                            }
                        }
                    }

                    ### TAGS ###
                    if (isset($dadosForm['tags']) && count($dadosForm['tags'])) {
                        foreach ($dadosForm['tags'] as $key => $valor) {
                            if (!empty($valor['nome'])) {
                                $array = [
                                    'fk_curso' => $resultado->id,
                                    'tag' => $valor['nome']
                                ];

                                $cursoTag = new CursoTag();
                                $cursoTag->create($array);
                                unset($cursoTag);
                            }
                        }
                    }

                    ### QUIZ ###
                    if (isset($dadosForm['quiz']) && count($dadosForm['quiz'])) {
                        $quiz = new Quiz();
                        $array_insert_quiz = [
                            'fk_curso' => $resultado->id,
                            'percentual_acerto' => 0
                        ];

                        $resultado_quiz = $quiz->create($array_insert_quiz);

                        if ($resultado_quiz) {
                            foreach ($dadosForm['quiz']['questao'] as $key => $item) {
                                if (!empty($item['titulo'])) {
                                    $array_insert_quiz_questao = [
                                        'fk_quiz' => $resultado_quiz->id,
                                        'titulo' => $item['titulo'],
                                        'resposta_correta' => $item['resposta_correta'],
                                        'status' => '1',
                                    ];

                                    $quiz_questao = new QuizQuestao();
                                    $resultado_quiz_questao = $quiz_questao->create($array_insert_quiz_questao);

                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($item['op']['descricao' . $i] != '') {
                                            $array_insert_quiz_resposta = [
                                                'label' => $i,
                                                'descricao' => $item['op']['descricao' . $i],
                                                'fk_quiz_questao' => $resultado_quiz_questao->id
                                            ];

                                            $quiz_resposta = new QuizResposta();
                                            $resultado_quiz_resposta = $quiz_resposta->create($array_insert_quiz_resposta);
                                        }
                                    }

                                }
                            }
                        }
                    }
                    ### fim QUIZ ###

                    if (!empty($request->input('valor_de'))) {
                        $preco_cursos_valor = [
                            'fk_curso' => $resultado->id,
                            'valor' => null,
                            'valor_de' => $request->input('valor_de'),
                            'data_inicio' => date('Y-m-d')
                        ];

                        $cursoValor = new CursoValor();
                        $validatorCursoValor = Validator::make($preco_cursos_valor, $cursoValor->rules, $cursoValor->messages);

                        $preco_cursos_valor = $this->insertAuditDataApi($preco_cursos_valor);
                        if (!$validatorCursoValor->fails()) {
                            CursoValor::updateOrCreate([
                                'fk_curso' => $resultado->id,
                            ], $preco_cursos_valor);
                        }
                    }
                } else {
                    if ($request->get('fk_cursos_tipo') !== 5) {
                        \DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'error' => 'Não foi possível inserir o registro! Tente novamente mais tarde'
                        ]);
                    }
                }
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Curso cadastrado com sucesso'
            ]);
        } catch(\Exception $e) {

            DB::rollback();
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Lista de Categorias
     * @param int $idFaculdade
     * @return JsonResponse
     */
    public function categorias($idFaculdade = null)
    {
        try {
            $categorias = CursoCategoria::select(
                'cursos_categoria.id',
                'cursos_categoria.titulo',
                'cursos_categoria.slug_categoria',
                'cursos_categoria.icone'
            )->where('cursos_categoria.status', '=', 1);

            $categoriasTrilhas = CursoCategoria::select(
                'cursos_categoria.id',
                'cursos_categoria.titulo',
                'cursos_categoria.slug_categoria',
                'cursos_categoria.icone'
            )->where('cursos_categoria.status', '=', 1);

            if ($idFaculdade) {
                $categorias->join('cursos_categoria_curso', 'cursos_categoria_curso.fk_curso_categoria', '=', 'cursos_categoria.id')
                    ->join('cursos', 'cursos_categoria_curso.fk_curso', '=', 'cursos.id')
                    ->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id')
                    ->where('cursos.status', '=', 5)
                    ->where('cursos_faculdades.fk_faculdade', '=', $idFaculdade)
                    ->distinct();

                $categoriasTrilhas->join('trilhas_categoria', 'trilhas_categoria.fk_categoria', '=', 'cursos_categoria.id')
                    ->join('trilha', 'trilhas_categoria.fk_trilha', '=', 'trilha.id')
                    ->join('trilhas_faculdades', 'trilhas_faculdades.fk_trilha', '=', 'trilha.id')
                    ->where('trilha.status', '=', 5)
                    ->where('trilhas_faculdades.fk_faculdade', '=', $idFaculdade);
            }

            if (!empty($oConfigFaculdade) && $oConfigFaculdade->ativar_trilha_conhecimento) {
                $categorias->union($categoriasTrilhas);
            }

            $data = $categorias->orderBy('cursos_categoria.titulo', 'ASC')->get();
            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);

        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function atualizar(Request $request) {
        try {
            \DB::beginTransaction();

            $dadosForm = $request->all();

            $curso = Curso::findOrFail($dadosForm['id']);
            $dadosForm['duracao_total'] = $dadosForm['duracao_total'] . ':00';
            $validator = $curso->_validate($request->all());

            $dadosForm['slug_curso'] = Curso::configurarSlugCurso($dadosForm['titulo']);

            $id = $dadosForm['id'];

            if (!empty($request->input('valor_de'))) {
                CursoValor::updateOrCreate([
                    'fk_curso' => $id,
                ], $this->insertAuditDataApi([
                    'fk_curso' => $id,
                    'valor' => null,
                    'valor_de' => $request->input('valor_de'),
                    'data_inicio' => date('Y-m-d')
                ]));
            }

            if ($request->hasFile('imagem')) {
                $file = $request->file('imagem');
                $dadosForm['imagem'] = $this->uploadFile('imagem', $file);
            } else {
                // $dadosForm['imagem'] = isset($dadosForm['imagem']) ? $dadosForm['imagem'] : '';
            }

            if (!empty($dadosForm['trabalho']) && $dadosForm['trabalho']) {
                $dataTrabalho = [
                    'status' => 1,
                    'titulo' => 'TCC',
                    'fk_cursos' => $id
                ];

                CursosTrabalhos::updateOrCreate(
                    [
                        'fk_cursos' => $id
                    ],
                    $dataTrabalho
                );
            }

            // deleta categorias para criar novamente (padrão do sistema)
            $lista_cursos_selecionados = CursoCategoriaCurso::all()->where('fk_curso', '=', $id);
            foreach ($lista_cursos_selecionados as $item) {
                CursoCategoriaCurso::where('id', $item['id'])->delete();
            }

            if (isset($dadosForm['fk_cursos_categoria']) && count($dadosForm['fk_cursos_categoria'])) {
                foreach ($dadosForm['fk_cursos_categoria'] as $categoria) {
                    $array = [
                        'fk_curso' => $id,
                        'fk_curso_categoria' => $categoria['fk_categoria']
                    ];

                    $array = $this->insertAuditDataApi($array, false);

                    $cursoCategoriaCurso = new CursoCategoriaCurso();
                    $cursoCategoriaCurso->create($array);
                    unset($cursoCategoriaCurso);
                }
            }

            if (isset($dadosForm['faculdade'])) {
                $faculdade = $dadosForm['faculdade'];
                $cursoFaculdade = CursosFaculdades::find($faculdade['id']);
                if ($cursoFaculdade) {
                    $cursoFaculdade->duracao_dias = (int)$faculdade['duracao_dias'];
                    $cursoFaculdade->disponibilidade_dias = (int)$faculdade['disponibilidade_dias'];
                    $cursoFaculdade->curso_gratis = (isset($dadosForm['curso_gratis']) && $dadosForm['curso_gratis']) ? 1 : 0;
                    $cursoFaculdade->save();
                } else {
                    $array = [
                        'fk_curso' => $id,
                        'fk_faculdade' => $faculdade['fk_faculdade'],
                        'duracao_dias' => (int)$faculdade['duracao_dias'],
                        'disponibilidade_dias' => (int)$faculdade['disponibilidade_dias'],
                        'curso_gratis' => (isset($dadosForm['curso_gratis']) && $dadosForm['curso_gratis']) ? 1 : 0,
                    ];
                    // inserir aqui model nova após criação da tabela no banco
                    $cursoFaculdade = new CursosFaculdades();
                    $cursoFaculdade->create($array);
                }

                $conclusaoFaculdade = ConclusaoCursosFaculdades::find($faculdade['id_conclusao']);
                if ($conclusaoFaculdade) {
                    $conclusaoFaculdade->nota_quiz = (int)$dadosForm['nota_questionario'];
                    $conclusaoFaculdade->nota_trabalho = (int)$dadosForm['nota_trabalho'];
                    $conclusaoFaculdade->freq_minima = (int)$faculdade['frequencia_minima'];
                    $conclusaoFaculdade->fk_certificado = (int)$faculdade['fk_certificado'];
                    $conclusaoFaculdade->save();
                } else {
                    $array = [
                        'fk_curso' => $id,
                        'fk_faculdade' => $faculdade['fk_faculdade'],
                        'fk_certificado' => $faculdade['fk_certificado'],
                        'nota_trabalho' => (int)$dadosForm['nota_trabalho'],
                        'nota_quiz' => (int)$dadosForm['nota_questionario'],
                        'freq_minima' => ($faculdade['frequencia_minima']) ? (int)$faculdade['frequencia_minima'] : 0,
                    ];


                    $conclusaoFaculdade = new ConclusaoCursosFaculdades();
                    $conclusaoFaculdade->create($array);
                }
            }

            if (isset($dadosForm['modulos']) && isset($dadosForm['modulos'])) {
                $secoesids = collect($dadosForm['modulos'])->map(function ($item, $key) {
                    return $item['id'];
                });
                $secoesdelete = CursoSecao::where('fk_curso', $id)->whereNotIn('id', $secoesids)->get();
                foreach ($secoesdelete as $scd) {
                    $scd->delete();
                }
                ### MODULOS ###
                foreach ($dadosForm['modulos'] as $key_secao => $secao) {
                    if (isset($secao['id'])) {
                        $curso_secao = CursoSecao::find($secao['id']);
                        if ($curso_secao) $curso_secao->update($secao);

                    } else {
                        if (($key_secao !== '__COUNT__') && $secao['titulo'] != '') {
                            $curso_secao = new CursoSecao();
                            $curso_secao->titulo = $secao['titulo'];
                            $curso_secao->ordem = $secao['ordem'];
                            $curso_secao->status = 1;
                            $curso_secao->fk_curso = $id;
                            $curso_secao->save();
                        }
                    }
                    $modulosids = collect($secao['subModulos'])->map(function ($item, $key) {
                        return $item['id'];
                    });
                    $modulosdelete = CursoModulo::where('fk_curso', $id)->where('fk_curso_secao', $secao['id'])->whereNotIn('id', $modulosids)->get();
                    foreach ($modulosdelete as $scd) {
                        $scd->delete();
                    }
                    foreach ($secao['subModulos'] as $key => $item) {
                        if (isset($item['id'])) {
                            $cursos_modulo = CursoModulo::find($item['id']);
                            if ($cursos_modulo) {
                                $cursos_modulo->titulo = $item['titulo'];
                                $cursos_modulo->tipo_modulo = !empty($files_modulos[$key_secao][$key]['name']) ? 1 : 2;
                                $cursos_modulo->url_video = $item['url_video'];
                                $cursos_modulo->carga_horaria = str_replace(' ', '', $item['carga_horaria']);
                                $cursos_modulo->url_arquivo = isset($item['arquivo']) ? $item['arquivo'] : '';
                                $cursos_modulo->fk_curso = $id;
                                $cursos_modulo->fk_curso_secao = $curso_secao->id;
                                $cursos_modulo->aula_ao_vivo = $item['aula_ao_vivo'];
                                $cursos_modulo->data_aula_ao_vivo = isset($item['data_aula_ao_vivo']) ? implode('-',
                                    array_reverse(explode('/', $item['data_aula_ao_vivo']))) : '';
                                $cursos_modulo->hora_aula_ao_vivo = $item['hora_aula_ao_vivo'];
                                $cursos_modulo->link_aula_ao_vivo = $item['link_aula_ao_vivo'];
                                $cursos_modulo->data_fim_aula_ao_vivo = isset($item['data_fim_aula_ao_vivo']) ? implode('-',
                                    array_reverse(explode('/', $item['data_fim_aula_ao_vivo']))) : '';
                                $cursos_modulo->hora_fim_aula_ao_vivo = $item['hora_fim_aula_ao_vivo'];
                                $cursos_modulo->ordem = $key;

                                $cursos_modulo->status = 1;
                                $cursos_modulo->save();
                            }
                        } else {
                            if (!empty($item['titulo']) && $item['titulo'] != '') {
                                $cursos_modulo = new CursoModulo();
                                $cursos_modulo->titulo = $item['titulo'];
                                $cursos_modulo->aula_ao_vivo = $item['aula_ao_vivo'];
                                $cursos_modulo->data_aula_ao_vivo = isset($item['data_aula_ao_vivo']) ? implode('-',
                                    array_reverse(explode('/', $item['data_aula_ao_vivo']))) : '';
                                $cursos_modulo->hora_aula_ao_vivo = $item['hora_aula_ao_vivo'];

                                $cursos_modulo->link_aula_ao_vivo = $item['link_aula_ao_vivo'];

                                $cursos_modulo->data_fim_aula_ao_vivo = isset($item['data_fim_aula_ao_vivo']) ? implode('-',
                                    array_reverse(explode('/', $item['data_fim_aula_ao_vivo']))) : '';
                                $cursos_modulo->hora_fim_aula_ao_vivo = $item['hora_fim_aula_ao_vivo'];
                                $cursos_modulo->tipo_modulo = !empty($files_modulos[$key_secao][$key]['name']) ? 1 : 2;
                                $cursos_modulo->url_video = $item['url_video'];
                                $cursos_modulo->carga_horaria = str_replace(' ', '', $item['carga_horaria']);
                                $cursos_modulo->url_arquivo = isset($item['arquivo']) ? $item['arquivo'] : '';
                                $cursos_modulo->fk_curso = $id;
                                $cursos_modulo->fk_curso_secao = $curso_secao->id;
                                $cursos_modulo->ordem = $key;

                                $cursos_modulo->status = 1;
                                $cursos_modulo->save();
                            }
                        }
                    }
                }
            }
            if(isset($dadosForm['agenda'])){
                $agendas = $dadosForm['agenda'];


                $agendasids = collect($agendas)->map(function ($item, $key) {
                    return $item['id'];
                });
                $agendasdelete = CursoTurmaAgenda::where('fk_curso', $id)->whereNotIn('id', $agendasids)->get();

                foreach ($agendasdelete as $agd) {
                    $agd->delete();
                }
            }


            if(isset($agendas) && count($agendas)) {
                foreach($agendas as $key => $item) {
                    if ($item['id']) {
                        $agenda = CursoTurmaAgenda::find($item['id']);
                        $agenda->nome = !empty($item['descricao_agenda']) ? $item['descricao_agenda'] : 'Agenda ' . $key;
                        $agenda->duracao_aula = $item['duracao_aula'];
                        $agenda->data_inicio = $item['data_inicio'];
                        $agenda->data_final = $item['data_inicio'];
                        $agenda->hora_inicio = str_replace(' ', '', $item['hora_inicio']);
                        $agenda->hora_final = str_replace(' ', '', $item['hora_fim']);
                        $agenda->fk_curso = $dadosForm['id'];
                        $agenda->fk_turma = 0;
                        $agenda->save();
                    } else {
                        if ($item['descricao_agenda'] != ''
                            && (!empty($item['data_inicio'])
                            && !empty($item['hora_inicio'])
                            && !empty($item['hora_fim'])
                            && !empty($item['descricao_agenda']))) {
                            $agenda = new CursoTurmaAgenda();
                            $agenda->nome = !empty($item['descricao_agenda']) ? $item['descricao_agenda'] : 'Agenda ' . $key;
                            $agenda->duracao_aula = $item['duracao_aula'];
                            $agenda->data_inicio = $item['data_inicio'];
                            $agenda->data_final = $item['data_inicio'];
                            $agenda->hora_inicio = str_replace(' ', '', $item['hora_inicio']);
                            $agenda->hora_final = str_replace(' ', '', $item['hora_fim']);
                            $agenda->fk_curso = $dadosForm['id'];
                            $agenda->fk_turma = 0;
                            $agenda->save();
                        }
                    }
                }
            }



            if (isset($dadosForm['tags']) && count($dadosForm['tags'])) {
                foreach ($dadosForm['tags'] as $key => $valor) {
                    if (empty($valor['id'])) {
                        if (!empty($valor['nome'])) {
                            $array = [
                                'fk_curso' => $id,
                                'tag' => $valor['nome']
                            ];
                            $cursoTag = new CursoTag();
                            $cursoTag->create($array);
                            unset($cursoTag);
                        }
                    }
                }
            }



            if (isset($dadosForm['quiz']) && count($dadosForm['quiz'])) {
                //verificar se existe fk_quiz
                //se existir, continue normalmente
                //se não, crie novo Quiz, atualize variavel fk_quiz
                $quiz_id = null;
                if(!isset($dadosForm['quiz']['id'])){
                    $quiz = new Quiz();
                    $array_insert_quiz = [
                        'fk_curso' => $id,
                        'percentual_acerto' => 0
                    ];
                    $resultado_quiz = $quiz->create($array_insert_quiz);
                    if ($resultado_quiz) {
                        $quiz_id = $resultado_quiz->id;
                    }
                } else {
                    $quiz_id = $dadosForm['quiz']['id'];
                    $quiz_to_update = Quiz::find($quiz_id);
                    if ($quiz_to_update) {
                        $quiz_to_update->percentual_acerto = 0;
                        $quiz_to_update->update();
                    }
                }

                $questoesids = collect($dadosForm['quiz']['questao'])->map(function ($item, $key) {
                    return $item['id'];
                });

                $questoesdelete = QuizQuestao::where('fk_quiz', $quiz_id)->whereNotIn('id', $questoesids)->get();
                foreach ($questoesdelete as $qtd) {
                    $qtd->delete(); // se der erro de banco, remover essa linha e alterar para setar o status 0 ao invés de deletar
                }

                foreach ($dadosForm['quiz']['questao'] as $key => $item) {
                    if (!empty($item['titulo'])) {
                        if(!empty($item['id'])) {
                            $quiz_questao = QuizQuestao::find($item['id']);
                            if ($quiz_questao) $quiz_questao->update($item);
                            $respostasids = collect($item['opcao'][$item['id']])->map(function ($opcao, $key) use ($item) {
                                return $opcao['id'];
                            });
                            $respostasdelete = QuizResposta::where('fk_quiz_questao', $item['id'])->whereNotIn('id', $respostasids)->get();

                            foreach ($respostasdelete as $rd) {
                                $rd->delete(); // se der erro de banco, remover essa linha e alterar para setar o status 0 ao invés de deletar
                            }
                            if (isset($item['opcao'])) {
                                foreach ($item['opcao'][$item['id']] as $op) {
                                    $quiz_resposta = QuizResposta::findOrFail($op['id']);
                                    if ($quiz_resposta) {
                                        $quiz_resposta->descricao = $op['descricao'];
                                        $quiz_resposta->update();
                                    } else {
                                        for ($i = 1; $i <= 5; $i++) {

                                            if ($item['op']['descricao' . $i] != '') {
                                                $array_insert_quiz_resposta = [
                                                    'label' => $i,
                                                    'descricao' => $item['op']['descricao' . $i],
                                                    'fk_quiz_questao' => $item['id']
                                                ];

                                                $quiz_resposta = new QuizResposta();
                                                $resultado_quiz_resposta = $quiz_resposta->create($array_insert_quiz_resposta);
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            if (!empty($item['titulo'])) {
                                $array_insert_quiz_questao = [
                                    'fk_quiz' => $quiz_id,
                                    'titulo' => $item['titulo'],
                                    'resposta_correta' => $item['resposta_correta'],
                                    'status' => '1',
                                ];

                                $quiz_questao = new QuizQuestao();
                                $resultado_quiz_questao = $quiz_questao->create($array_insert_quiz_questao);

                                for ($i = 1; $i <= 5; $i++) {
                                    if ($item['op']['descricao' . $i] != '') {
                                        $array_insert_quiz_resposta = [
                                            'label' => $i,
                                            'descricao' => $item['op']['descricao' . $i],
                                            'fk_quiz_questao' => $resultado_quiz_questao->id
                                        ];

                                        $quiz_resposta = new QuizResposta();
                                        $resultado_quiz_resposta = $quiz_resposta->create($array_insert_quiz_resposta);
                                        if (!$resultado_quiz_resposta) {
                                            \DB::rollBack();
                                            return response()->json([
                                                'success' => false,
                                                'error' => 'Não foi possível atualizar o registro!',
                                                'errors' => $resultado_quiz_resposta
                                            ]);
                                        }
                                    }
                                }

                            }
                        }
                    }
                }
            }
            ### fim QUIZ ###

            if (!$validator->fails()) {
                $dadosForm['titulo'] = $dadosForm['titulo'];
                $dadosForm['descricao'] = $dadosForm['descricao'];
                if ($request->get('fk_cursos_tipo') !== 5) {
                    $dadosForm['objetivo_descricao'] = ($dadosForm['objetivo_descricao']) ? $dadosForm['objetivo_descricao'] : '';
                    $dadosForm['publico_alvo'] = ($dadosForm['publico_alvo']) ? $dadosForm['publico_alvo'] : '';
                }
                $resultado = $curso->update($dadosForm);

                if ($resultado) {
                    \DB::commit();
                    return response()->json([
                        'success' => true,
                        'message' => 'Curso atualizado com sucesso!'
                    ]);
                } else {
                    \DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error' => 'Não foi possível atualizar o registro!',
                        //'errors' => $resultado
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Não foi possível alterar o registro! Campos inválidos',
                    //'errors' => $validator->messages()
                ]);
            }
        } catch(\Exception $e) {
            \DB::rollback();
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema' . $e->getMessage()
            ]);
        }

    }

    /**
     * Lista Cursos com Status Rascunho
     * @param int $idFaculdade
     * @param int $idTipo
     * @return JsonResponse
     */
    public function rascunhoPorProfessor($idProfessor, $idTipo)
    {
        $cursos = Curso::select(
			'cursos.id',
			'cursos.professor_responde_duvidas',
            'cursos.titulo as nome_curso',
            'cursos.data_criacao as data',
            'cursos_valor.valor',
            'cursos_valor.valor_de',
            'cursos.imagem',
            'cursos.idioma',
		//	'faculdades.fantasia as nome_faculdade',
			'professor.nome as nome_professor',
            'professor.sobrenome as sobrenome_professor',
            'fk_cursos_tipo as tipo',
            'cursos.data_criacao',
            'cursos.data_atualizacao'
		)
		->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
		//->join('faculdades', 'faculdades.id', '=', 'cursos.fk_faculdade')
        ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
        ->where('cursos.status', '1');

        if ($idProfessor) {
            $cursos->where('professor.id', $idProfessor);
        }

        if ($idTipo) {
            $cursos->where('cursos.fk_cursos_tipo', $idTipo);
        }

        $cursos->where('cursos_valor.data_validade', null);

        $data = $cursos->get();
        return response()->json([
            'items' => $data,
            'count' => count($data)
        ]);
    }

    /**
     * Lista Cursos com Status Enviado
     * @param int $idFaculdade
     * @param int $idTipo
     * @return JsonResponse
     */
    public function enviadoPorProfessor($idProfessor, $idTipo)
    {
        $cursos = Curso::select(
			'cursos.id',
			'cursos.professor_responde_duvidas',
			'cursos.slug_curso',
            'cursos.titulo as nome_curso',
            'cursos_valor.valor',
            'cursos_valor.valor_de',
            'cursos.idioma',
            'cursos.imagem',
		//	'faculdades.fantasia as nome_faculdade',
			'professor.nome as nome_professor',
            'professor.sobrenome as sobrenome_professor',
            'fk_cursos_tipo as tipo',
            'cursos.data_criacao',
            'cursos.data_atualizacao'
		)
		->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
		//->join('faculdades', 'faculdades.id', '=', 'cursos.fk_faculdade')
        ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
        ->whereNotIn('cursos.status', [1]);

        if ($idProfessor) {
            $cursos->where('professor.id', $idProfessor);
        }

        if ($idTipo) {
            $cursos->where('cursos.fk_cursos_tipo', $idTipo);
        }

        $cursos->where('cursos_valor.data_validade', null);

        $data = $cursos->get();
        return response()->json([
            'items' => $data,
            'count' => count($data)
        ]);
    }


    /**
     * Lista Cursos por professor
     * @param int $idProfessor
     * @return JsonResponse
     */
    public function cursoPorProfessor($idProfessor, $idTipo, $idFaculdade = 7)
    {
        try {
            $data['rascunhos'] = Curso::cursosPorProfessorFront($idProfessor, $idTipo, [1], $idFaculdade);
            $data['enviados'] = Curso::cursosPorProfessorFront($idProfessor, $idTipo, [2, 3, 4], $idFaculdade);
            $data['publicados'] = Curso::cursosPorProfessorFront($idProfessor, $idTipo, [5], $idFaculdade);
            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);
        } catch(\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Retorna lista de cursos por professor (status aprovação)
     *
     * @param int $idProfessor
     * @param int $idTipo
     *
     * @return JsonResponse
     */
    public function statusAprovacao($idProfessor, $idTipo = 1)
    {
        $cursos = Curso::select(
			'cursos.id',
            'cursos.titulo as nome_curso',
            'cursos.imagem',
            'cursos.status',
            'cursos.criacao',
            'fk_cursos_tipo as tipo'
		)
        ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
        ->where('professor.id', $idProfessor);

        if ($idTipo) {
            $cursos->where('cursos.fk_cursos_tipo', $idTipo);
        }

        $data = $cursos->get();
        return response()->json([
            'items' => $data,
            'count' => count($data)
        ]);
    }

    /**
     * Lista de Categorias por Curso
     * @param int $idCurso
     * @return JsonResponse
     */
    public function categorias_por_curso($idCurso = 1)
    {
        try {
            $categorias = CursoCategoriaCurso::select(
                'cursos_categoria.id',
                'cursos_categoria.titulo'
            )
                ->join('cursos_categoria', 'cursos_categoria.fk_curso', '=', 'cursos_categoria_curso.fk_curso');
            //->join('faculdades', 'faculdades.id', '=', 'cursos_categoria.fk_faculdade');

            if ($idCurso) {
                $categorias->where('cursos_categoria.fk_curso', '=', $idCurso);
            }

            $categorias->where('cursos_categoria.status', '=', 1);

            $data = $categorias->get();
            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * Lista de Cursos por Categoria
     * @param int $idCategoria
     * @return JsonResponse
     */
    public function cursos_por_categoria($idCategoria = 1)
    {
        try {
            $categorias = CursoCategoriaCurso::select(
                'cursos.id',
                'cursos.titulo',
                'cursos.imagem'
            )
                ->join('cursos', 'cursos.id', '=', 'cursos_categoria_curso.fk_curso');
            //->join('faculdades', 'faculdades.id', '=', 'cursos_categoria.fk_faculdade');

            if ($idCategoria) {
                $categorias->where('cursos_categoria.fk_curso_cageoria', '=', $idCategoria);
            }

            $categorias->where('cursos_categoria.status', '=', 1);

            $data = $categorias->get();
            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * Lista de Cursos Presenciasi por Aluno
     * @param int $idAluno
     * @return JsonResponse
     */
    public function cursosPresenciaisPorAluno($idAluno, Request $request)
    {
        try {
            $data = Curso::cursosPresenciaisAluno($idAluno, $request->header('Faculdade', 7));
            return response()->json([
                'items' => $data,
                'count' => count($data),
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * Lista de Cursos Presenciais De Trilha por Aluno
     * @param int $idAluno
     * @return JsonResponse
     */
    public function cursosTrilhaPresenciaisPorAluno($idAluno)
    {
        try {
            $data = Curso::cursosTrilhaPresenciaisAluno($idAluno);
            return response()->json([
                'items' => $data,
                'count' => count($data),
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * Lista de Cursos Remotos por Aluno
     * @param int $idCategoria
     * @return JsonResponse
     */
    public function cursosRemotosPorAluno($idAluno, Request $request)
    {
        try {
            $data = Curso::cursosRemotosAluno($idAluno, $request->header('Faculdade', 7));

            return response()->json([
                'items' => $data,
                'count' => count($data),
            ]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * Lista de Cursos Remotos De Trilha por Aluno
     * @param int $idAluno
     * @return JsonResponse
     */
    public function cursosTrilhaRemotosPorAluno($idAluno)
    {
        try {
            $data = Curso::cursosTrilhaHidridosAluno($idAluno);

            return response()->json([
                'items' => $data,
                'count' => count($data),
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * Lista de Cursos Online por Aluno
     * @param int $idAluno
     * @param Request $request
     * @return JsonResponse
     */
    public function cursosOnlinePorAluno($idAluno, Request $request) {
        try {
            $data = Curso::cursosOnlineAluno($idAluno,  $request->header('Faculdade', 7));
            return response()->json([
                'items' => $data,
                'count' => count($data),
            ]);
        }  catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * Lista de Cursos Online por Aluno
     * @param int $idAluno
     * @return JsonResponse
     */
    public function cursosOnlineIniciadosPorAluno($idAluno, Request $request)
    {
        try {
            $data = Curso::cursosOnlineAlunoIniciados($idAluno,  $request->header('Faculdade', 7));
            return response()->json([
                'items' => $data,
                'count' => count($data),
            ]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * Lista de Cursos Online Trilha por Aluno
     * @param int $idAluno
     * @return JsonResponse
     */
    public function cursosTrilhaOnlinePorAluno($idAluno)
    {
        try {
            $data = Curso::cursosTrilhaOnlineAluno($idAluno);
            return response()->json([
                'items' => $data,
                'count' => count($data),
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }




    /**
     * Lista de Faculdades
     *
     * @return JsonResponse
     */
    public function faculdades()
    {
        try {
            $faculdades = Faculdade::all()
                ->where('status', '=', 1)
                ->pluck('titulo', 'id');

            $data = $faculdades->get();
            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * Lista de Modulos por Curso
     *
     * @param int $idCurso
     * @return JsonResponse
     */
    public function modulos_por_curso($idCurso)
    {
        try {
            $modulos = CursoSecao::modulosCurso($idCurso);
            $lista = [];
            foreach ($modulos->toArray() as $secao) {
                $lista[$secao['id']]['nome_secao'] = $secao['nome_secao'];
                $lista[$secao['id']]['modulos'][] = [
                    'modulo_id' => $secao['modulo_id'],
                    'titulo' => $secao['titulo'],
                    'descricao' => $secao['descricao'],
                    'tipo_modulo' => $secao['tipo_modulo'],
                    'url_video' => $secao['url_video'],
                    'url_arquivo' => ($secao['url_arquivo']) ? $secao['url_arquivo'] : null,
                    'carga_horaria' => $secao['carga_horaria'],
                    'aula_ao_vivo' => $secao['aula_ao_vivo'],
                    'data_aula_ao_vivo' => $secao['data_aula_ao_vivo'],
                    'hora_aula_ao_vivo' => $secao['hora_aula_ao_vivo'],
                    'link_aula_ao_vivo' => $secao['link_aula_ao_vivo'],
                    'data_fim_aula_ao_vivo' => $secao['data_fim_aula_ao_vivo'],
                    'hora_fim_aula_ao_vivo' => $secao['hora_fim_aula_ao_vivo'],
                ];
            }

            $retorno = [];
            $cont = 0;
            foreach ($lista as $item) {
                $retorno[$cont] = $item;
                $cont++;
            }

            return response()->json([
                'items' => $retorno,
                'count' => $cont,
//            'query' => $query->toSql()
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * Agenda por Curso
     *
     * @param int $idCurso
     * @return JsonResponse
     */
    public function agendas_por_curso($idCurso)
    {
        try {
            $data = Curso::agendaPorCurso($idCurso);
            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * Tags por Curso
     *
     * @param int $idCurso
     * @return JsonResponse
     */
    public function tags_por_curso($idCurso)
    {
        try {
            $tags_cadastradas = CursoTag::select('id', 'tag')->where('fk_curso', '=', $idCurso)->get();

            $data = $tags_cadastradas;
            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * Comentários por Curso
     *
     * @param int $idCurso
     * @return JsonResponse
     */
    public function comentarios_por_curso($idCurso)
    {
        try {
            $comentarios = CursoAvaliacao::select('cursos_avaliacao.*', 'usuarios.nome as nome_aluno')->join('usuarios', 'usuarios.id', '=', 'cursos_avaliacao.fk_aluno')->where('fk_curso', '=', $idCurso);
            $data = $comentarios->get();
            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * Busca de Cursos
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search($idTipo = null, Request $request)
    {
        try {
            $search = $request->all();
            $idFaculdade = $request->header('Faculdade', null);

            $data = Curso::search($idTipo, $search, $idFaculdade);
            if ($idTipo) {
                $resultado = [];
                foreach ($data as $presencial) {

                    if (isset($idTipo) && $idTipo == 2) {
                        $presencial['data'] = CursoTurmaAgenda::select('cursos_turmas_agenda.*')
                            ->join('cursos_turmas', 'cursos_turmas_agenda.fk_turma', '=', 'cursos_turmas.id')
                            ->where('cursos_turmas.fk_curso', '=', $presencial['id'])
                            ->orderBy('data_inicio', 'asc')
                            ->pluck('data_inicio')
                            ->first(); // essa query pega a menor data inicial disponível entre os dias da agenda e trata como data inicial do curso
                    }
                    if (isset($idTipo) && $idTipo == 4) {
                        $parte_presencial = CursoTurmaAgenda::select('cursos_turmas_agenda.*')
                            ->join('cursos_turmas', 'cursos_turmas_agenda.fk_turma', '=', 'cursos_turmas.id')
                            ->where('cursos_turmas.fk_curso', '=', $presencial['id'])->get();
                        $presencial_count = count($parte_presencial);
                        $parte_online = CursoSecao::modulosCurso($presencial['id']);
                        $online_count = count($parte_online);
                        $total = $online_count + $presencial_count;
                        if($total != 0){
                            $presencial['percentual_online'] = round(($online_count/$total) * 100) . '%';
                            $presencial['percentual_presencial'] = round(($presencial_count/$total) * 100) . '%';
                        } else {
                            $presencial['percentual_online'] = 0;
                            $presencial['percentual_presencial'] = 0;
                        }

                        //obter primeiro e ultimo modulo do curso
                        $primeira_ultima_data = CursoTurmaAgenda::select('cursos_turmas_agenda.*')
                            ->join('cursos_turmas', 'cursos_turmas_agenda.fk_turma', '=', 'cursos_turmas.id')
                            ->where('cursos_turmas.fk_curso', '=', $presencial['id'])
                            ->orderBy('data_inicio', 'asc')
                            ->pluck('data_inicio');

                        $presencial['primeira_data'] = $primeira_ultima_data
                            ->first();
                        $presencial['ultima_data'] = $primeira_ultima_data
                            ->last();
                    }

                    $presencial['gratis'] = isset($presencial['gratis']) ? (int) $presencial['gratis'] : 0;
                    array_push($resultado, $presencial);
                }
                $data = $resultado;
            }

            return response()->json([
                'items' => array_values($data),
                'count' => count($data)
            ]);
            
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /* Retorna Sidebar
     *
     * @param int $idCurso
     * @return Json
     */
    public function sidebarCurso($idCurso, $idUsuario = false)
    {
        try {
            $retorno = [];
            $data = Curso::select(
                'cursos.id',
                'cursos.professor_responde_duvidas',
                'cursos.slug_curso',
                'cursos.titulo as nome_curso',
                //'faculdades.fantasia as nome_faculdade',
                'cursos.idioma',
                'cursos.formato',
                'cursos.imagem',
                'cursos_valor.valor',
                'cursos_valor.valor_de',
                'professor.nome as nome_professor',
                'professor.sobrenome as sobrenome_professor',
                'professor.mini_curriculum as sobre_professor',
                'cursos.fk_cursos_tipo as tipo'
                //"certificados.id as certificado"
            )
                ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
                ->leftJoin('certificados', 'certificados.fk_curso', '=', 'cursos.id')
                ///->join('faculdades', 'faculdades.id', '=', 'cursos.fk_faculdade')
                ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
                ->join('cursos_tipo', 'cursos_tipo.id', '=', 'cursos.fk_cursos_tipo')
                ->where('cursos_valor.data_validade', null)
                ->where('cursos.id', $idCurso)->first();

            if ($data) {
                $retorno = $data->toArray();

                $lista_categorias = CursoCategoriaCurso::select('cursos_categoria.id', 'cursos_categoria.titulo')
                    ->join('cursos_categoria', 'cursos_categoria.id', '=', 'cursos_categoria_curso.fk_curso_categoria')
                    ->where('cursos_categoria_curso.fk_curso', $idCurso)
                    ->get()
                    ->toArray();

                $lista_modulos = CursoModulo::select(
                    DB::raw("SEC_TO_TIME(SUM(TIME_TO_SEC(carga_horaria))) as total_minutos"),
                    DB::raw("COUNT(1) as total_modulos"))
                    ->join('cursos_secao', 'cursos_secao.id', '=', 'cursos_modulos.fk_curso_secao')
                    ->where('cursos_secao.fk_curso', $idCurso)
                    ->where('cursos_modulos.status', 1)
                    ->get()
                    ->toArray();

                $total_minutos = $lista_modulos[0]['total_minutos'];
                $total_minutos = (int)(mb_substr($total_minutos, 0, 2))*60 + (int)(mb_substr($total_minutos, 3, 2));
                $total_minutos = ($total_minutos/60);
                $decimal = $total_minutos - floor($total_minutos);
                $lista_modulos[0]['total_minutos'] = ($decimal > 0 && $decimal < 1) ? floor($total_minutos) + 1 : floor($total_minutos);
                $retorno['categorias'] = $lista_categorias;
                $retorno['modulos_info'] = $lista_modulos;

                $quiz = Quiz::select('quiz.*')->where('quiz.fk_curso', '=', $idCurso)->first();
                $retorno['possui_quiz'] = ($quiz)? 1 : 0;

                if($idUsuario){
                    $usuario = Usuario::find($idUsuario);
                    $idFaculdade = $usuario->fk_faculdade_id;

                    $disponibilidade = CursosFaculdades::select("duracao_dias")
                    ->where('fk_curso', $idCurso)
                    ->where('fk_faculdade', $idFaculdade)->first();
                    if($disponibilidade){
                        //$retorno['tempo_finalizar'] = $disponibilidade['duracao_dias'];

                        $data_aprovacao_pedido = Curso::select('pedidos_historico_status.data_inclusao')
                        ->join('pedidos_item', 'pedidos_item.fk_curso', '=', 'cursos.id')
                        ->join('pedidos', 'pedidos.id', '=', 'pedidos_item.fk_pedido')
                        ->join('pedidos_historico_status', 'pedidos_historico_status.fk_pedido', '=', 'pedidos.id')
                        ->where([
                            ['cursos.id', '=', $idCurso],
                            ['pedidos.fk_usuario', '=', $idUsuario],
                            ['pedidos.status', '=', '2'],
                            ['pedidos_historico_status.fk_pedido_status', '=', '2']])
                            ->first();

                        $retorno['tempo_finalizar'] = null;
                        if (!empty($data_aprovacao_pedido) && $disponibilidade['duracao_dias']) {
                            $diferenca_dias = strtotime('now') - strtotime($data_aprovacao_pedido->data_inclusao);
                            $diferenca_dias = floor($diferenca_dias / (60 * 60 * 24));
                            if ($diferenca_dias) $retorno['tempo_finalizar'] = $disponibilidade['duracao_dias'] - $diferenca_dias;
                            else $retorno['tempo_finalizar'] = $disponibilidade['duracao_dias'];
                        }
                    }

                    $criterios = ConclusaoCursosFaculdades::select("fk_certificado as emite_certificado",
                    "nota_trabalho as nota_corte_trabalho",
                    "nota_quiz as nota_corte_quiz",
                    "freq_minima as frequencia_minima")
                        ->where('fk_curso', '=', $idCurso)
                        ->where('fk_faculdade', '=', $idFaculdade)->first();
                    if($criterios){
                        $retorno['criterios'] = $criterios;
                    }
                }
            }

            return response()->json([
                'data' => $retorno
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

	public function modulosAssistidosPorCursoAluno($idCurso, $idUsuario){
        try {
            $modulos_assistidos = CursoModulo::select(DB::raw('DISTINCT fk_modulo, fk_usuario, cursos_modulos.fk_curso'))
                ->join('modulos_usuarios', 'fk_modulo', '=', 'cursos_modulos.id')
                ->where([
                    ['fk_usuario', '=', $idUsuario],
                    ['cursos_modulos.fk_curso', '=', $idCurso]])
                #->pluck('id')
                ->where('cursos_modulos.status', 1)
                ->pluck('fk_modulo')
                ->toArray();

            $total_modulos = CursoModulo::select(DB::raw("COUNT(1) as total_modulos"))
                ->join('cursos_secao', 'cursos_secao.id', '=', 'cursos_modulos.fk_curso_secao')
                ->where('cursos_secao.fk_curso', $idCurso)
                ->where('cursos_modulos.status', 1)
                ->get()
                ->first();

            $progresso = 0;
            if (count($modulos_assistidos) != 0 && $total_modulos['total_modulos'] != 0)
                $progresso = (count($modulos_assistidos) / $total_modulos['total_modulos']);

            return response()->json([
                'items' => $modulos_assistidos,
                'totalModulos' => $total_modulos['total_modulos'],
                'progresso' => $progresso
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
	}

    public function setModuloAssistido(Request $request){
        try {
            $data = $request->only('fk_modulo', 'fk_usuario', 'id_curso');
            $moduloObjeto = new ModuloUsuario($data);
            $moduloObjeto->save();

            //Ao terminar de assistir um módulo, verificar possibilidade de emissão de certificado
            $certificadoHelper = new CertificadoHelper();
            $retorno = $certificadoHelper->emiteCertificado($data['fk_usuario'], $data['id_curso']);

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function criarCursoOnline()
    {
        $data = [
            "projeto" => 0,
            "titulo" => "Blá Blá",
            "descricao" => "teste teste teste",
            "teaser" => "",
            "duracao" => "20 h",
            "preco" => "R$ 150,00",
            "produtora" => 0,
            "precoVenda" => "R$ 160,00",
            "categoria" => 1,
            "certificado" => 0,
            "questionarioTipo" => "multiplaescolha",
            "modulos" => [
               [
                  "nome" => "Modulo 1",
                  "subModulos" => [
                     [
                        "nome" => "blpá blá",
                        "duracao" => "20h",
                        "codigoVimeo" => "xxxxx",
                        "arquivo" => [

                        ]
                     ]
                  ]
               ],
               [
                  "nome" => "Modulo 2",
                  "subModulos" => [
                     [
                        "nome" => "blpá blá",
                        "duracao" => "20h",
                        "codigoVimeo" => "xxxxx",
                        "arquivo" => [

                        ]
                     ]
                  ]
               ]
            ],
            "image" => [

            ],
            "professores" => [
               null,
               null,
               null
            ],
            "curadores" => [
               null,
               null,
               null
            ],
            "questionario" => [
               [
                  "questao" => "XXX XX XXXX",
                  "alternativas" => [
                     [
                        "alternativa" => "sadad sadiusadfaoijd asodijasd"
                     ],
                     [
                        "alternativa" => "sadad sadiusadfaoijd asodijasd"
                     ],
                     [
                        "alternativa" => "sadad sadiusadfaoijd asodijasd"
                     ]
                  ],
                  "alternativaCorreta" => "sadad sadiusadfaoijd asodijasd"
               ],
               [
                  "questao" => "YYY YY YYYYYYY",
                  "alternativas" => [
                     [
                        "alternativa" => "sadad sadiusadfaoijd asodijasd"
                     ],
                     [
                        "alternativa" => "sadad sadiusadfaoijd asodijasd"
                     ],
                     [
                        "alternativa" => "sadad sadiusadfaoijd asodijasd"
                     ]
                  ],
                  "alternativaCorreta" => "sadad sadiusadfaoijd asodijasd"
               ]
            ]
        ];
    }

    /**
     * Curso Modulo
     * @param $id
     * @return JsonResponse
     */
    public function modulo($id)
    {
        try {
            /*$data = CursoModulo::select(
                'cursos_modulos.*'
            )
                ->where('cursos_modulos.id', $id)
                ->where('cursos_modulos.status', 1)
                ->get()
                ->toArray();*/
            $data = CursoModulo::find($id);
            if ($data->url_arquivo == '') $data->url_arquivo = null;
            $modulo = [];
            array_push($modulo, $data); // alterado porque o front espera array
            return response()->json([
                'data' => $modulo
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

        /**
     * Cursos favoritos por aluno
     *
     * @param $request
     * @return JsonResponse
     */
    public function favoritar(Request $request)
    {
        try {
            $data = $request->only('fk_aluno', 'fk_curso');
            $favoritoObjeto = new CursoFavorito($data);

            return response()->json([
                'success' => ($favoritoObjeto->save() ? true : false)
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function desfavoritar(Request $request) {
        try {
            $aluno = $request->get('fk_aluno');
            $curso = $request->get('fk_curso');
            $favorito = CursoFavorito::where('fk_aluno', '=', $aluno)
                ->where('fk_curso', '=', $curso)
                ->delete();
            return response()->json([
                'success' => $favorito
            ]);
        } catch(\Exception  $e) {
            return response()->json([
                'success' => false,
                'mensagem' => $e->getMessage()
            ]);
        }
    }
    /**
     * Cursos favoritos por aluno
     *
     * @param $idAluno
     * @return JsonResponse
     */
    public function favoritos($idAluno)
    {
        try {
            $favoritos = Curso::cursosFavorito($idAluno);

            return response()->json([
                'items' => $favoritos,
                'count' => count($favoritos)
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function agenda_cursos($month) {

        $loggedUser = JWTAuth::user();

        try {
            $lista = [];
            $date = new \DateTime();

            $date->setDate($date->format('Y'), $month, 1);
            $date->setTime(0, 0, 0);

            $date1 = new \DateTime();
            $date1->setDate($date->format('Y'), $month, 31);



            $query = Curso::select('cursos.endereco_presencial', 'cursos_turmas_agenda.*')
                ->join('cursos_turmas', 'cursos_turmas.fk_curso', '=', 'cursos.id')
                ->join('cursos_turmas_agenda', 'cursos_turmas_agenda.fk_turma', '=', 'cursos_turmas.id')

                ->whereBetween('cursos_turmas_agenda.data_inicio', [$date, $date1]);

            if ($loggedUser->fk_perfil == 2) {
                $query->join('cursos_faculdades', 'cursos.id', '=', 'cursos_faculdades.fk_curso');
                $query->where('cursos_faculdades.fk_faculdade', $loggedUser->fk_faculdade_id);
            } else {
                $professor = ViewUsuarioCompleto::where('fk_usuario_id', $loggedUser->id)->first();
                $query->where(function ($query) use ($professor) {
                    $query->where('fk_professor', $professor->id)
                        ->orWhere('fk_professor_participante', $professor->id);
                });
            }

            $agendas = $query->get();

            $agendaCurso = [];
            foreach ($agendas as $agenda) {
                setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
                date_default_timezone_set('America/Sao_Paulo');

                $dateAgendaCurso = date_create($agenda['data_inicio']);
                $agendaCurso[] = [
                    "day" => (string)$dateAgendaCurso->format('j'),
                    "month" => (int)$dateAgendaCurso->format('m'),
                    "title" => $agenda['nome'] . ' - ' . $agenda['descricao'],
                    "date" => strftime('%d de %B de %Y', strtotime($dateAgendaCurso->format('Y-m-d'))) . ', das ' . $agenda['hora_inicio'] . ' às ' . $agenda['hora_final'],
                    "local" => $agenda['endereco_presencial']
                ];
            }

            return response()->json($agendaCurso);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function minhasEstatisticas($id, Request $request) {
        try {
            $cursosPresenciais = Curso::cursosPresenciaisAluno($id);
            $cursosOnline = Curso::cursosOnlineAlunoIniciados($id, $request->header('Faculdade', 7));
            $cursosRemotos = Curso::cursosRemotosAluno($id);
            $horasPresenciais = 0;
            $horasOnline = 0;
            $horasRemoto = 0;
            $progresso = 0;

            foreach ($cursosPresenciais as $item) {
                $horasPresenciais = $horasPresenciais + $this->somaHorasAgenda(Curso::agendaPorCurso($item->id));
                $progresso = $progresso + $this->calculaAgendasPassadas(Curso::agendaPorCurso($item->id));
            }

            foreach ($cursosOnline as $cursoOnline) {
                $horasOnline = $horasOnline + $this->somaHorasModulos(CursoSecao::modulosCurso($cursoOnline->id));
                $progresso = $progresso + $this->calculaModulosAssistidos($id, $cursoOnline->id);
            }
            foreach ($cursosRemotos as $cursoRemoto) {
                $horasRemoto = $horasRemoto + $this->somaHorasAgenda(Curso::agendaPorCurso($cursoRemoto->id)) + $this->somaHorasModulos(CursoSecao::modulosCurso($cursoRemoto->id));
                $progresso = $progresso + $this->calculaAgendasPassadas(Curso::agendaPorCurso($cursoRemoto->id)) + $this->calculaModulosAssistidos($id, $cursoRemoto->id);
            }

            $totalHoras = $horasRemoto + $horasOnline + $horasPresenciais;
            $totalCursadas = $progresso;
            $aindaFaltam = $totalHoras - $totalCursadas;
            $data = [
                'horasPresenciais' => $horasPresenciais,
                'horasOnline' => $horasOnline,
                'horasRemoto' => $horasRemoto,
                'totalHoras' => $totalHoras,
                'totalCursadas' => $totalCursadas,
                'aindaFaltam' => $aindaFaltam
            ];

            return response()->json([
                'items' => $data,
                'count' => 1
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }
    public function somaHorasModulos ($modulos) {
        $modulos = collect($modulos)->pluck('modulo_id');

        $total_minutos = CursoModulo::select(
            //DB::raw("(SUM(TIME_TO_SEC( `carga_horaria` )/60)/3600) as total_minutos"))
            DB::raw("(SUM(TIME_TO_SEC( `carga_horaria` ))/3600) as total_minutos"))
            ->whereIn('id', $modulos)
            ->where('cursos_modulos.status', 1)
            ->get()
            ->toArray();
        return $total_minutos[0]['total_minutos'];
    }

    public function somaHorasAgenda ($agendas) {
        $cont = 0;
        foreach ($agendas as $agenda) {
            $start = strtotime($agenda->data_inicio. ' ' . $agenda->hora_inicio);
            $end = strtotime($agenda->data_final. ' ' . $agenda->hora_final);
            $horas = (($end - $start) / 3600);
            $cont = $cont + $horas;
        }
        return $cont;
    }
    public function calculaAgendasPassadas ($agendas) {
        $somar = [];
        foreach ($agendas as $agenda) {
            $start = strtotime($agenda->data_inicio. ' ' . $agenda->hora_inicio);
            $end = strtotime('now');
            if ($start  < $end) {
                array_push($somar, $agenda);
            }
        }
        return $this->somaHorasAgenda($somar);
    }

    public function calculaModulosAssistidos ($id, $idCurso) {

        $modulos_assistidos = CursoModulo::select('*', 'fk_modulo as modulo_id')
            ->join('modulos_usuarios', 'fk_modulo', '=', 'cursos_modulos.id')
            ->where([
                ['fk_usuario', '=', $id],
                ['cursos_modulos.fk_curso', '=', $idCurso]])
            ->where('cursos_modulos.status', 1)
            #->pluck('id')
            ->get();
        $progresso = $this->somaHorasModulos($modulos_assistidos);
        return $progresso;
    }

    public function uploadFile(Request $request) {
        try {
            $input = $request->get('input');
            $tipo = $request->get('tipo');
            $file = $request->file('imagem');
            $fileName = date('YmdHis') . '_' . $file->getClientOriginalName();
            if ($file->move('files/' . $tipo . '/' . $input, $fileName)) {
                return response()->json([
                    'success' => true,
                    'data' => $fileName
                ]);

            }
            return response()->json([
                'success' => false,
                'error' => 'Erro ao salvar o arquivo'
            ]);
        } catch(\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * Função que guarda dados do usuário que gostaria de ser avisado quando uma nova turma for criada para determinado curso
     * @param Request $request
     * @return JsonResponse
     */
    public function aviseNovasTurmas(Request $request) {
        try {
           $aviseme = new AvisarNovasTurmas();
           $validator = Validator::make($request->all(), $aviseme->rules, $aviseme->messages);
           if ($validator->fails()) {
               return response()->json([
                   'success' => false,
                   'error' => 'Existem campos inválidos, verifique-os e tente novamente',
                   'validator' => $validator->messages()->all()
               ]);
           }
           $aviseme = AvisarNovasTurmas::create($request->except('_token'));
           if ($aviseme) {
               return response()->json([
                    'success' => true,
                    'message' => 'Registro salvo com sucesso! Você será avisado assim que uma nova turma for formada para este curso!'
               ]);
           }
        } catch(\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function checkIfCourseHasWaitingPayments($idCurso, $idUsuario, Request $request) {
        try {
            if (empty($idUsuario)) {
                return response()->json(['success' => true,]);
            }

            $sSelect = "select * from pedidos
                            join pedidos_item on pedidos_item.fk_pedido = pedidos.id
                        where pedidos.status in ( 1, 5)
                        AND fk_curso = {$idCurso}
                        AND fk_faculdade = {$request->header('Faculdade', 1)}
                        AND fk_usuario = {$idUsuario}";

            $results = DB::select($sSelect);

            return response()->json(['success' => empty($results),]);

        } catch(\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'exception' => $e->getMessage(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }

    }

    public function getMaxValueCourse() {
        try {

            $sSelect = "select
                                max(valor_de) as max_valor_de
                            from cursos
                                inner join cursos_valor on cursos_valor.fk_curso = cursos.id
                            where cursos.status = 5";

            $results = DB::select($sSelect);

            return response()->json(['success' => true, 'items' => $results,]);

        } catch(\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'exception' => $e->getMessage(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function retornarTodosCursos($idTipoCurso = null, Request $request) {
        try {

            $sSelect = "SELECT
                    cursos.id,
                    cursos.titulo,
                    cursos.professor_responde_duvidas,
                    cursos.slug_curso,
                    cursos.fk_cursos_tipo,
                    cursos.imagem,
                    cursos.duracao_total,
                    cursos_tipo.titulo AS curso_tipo,
                    cursos_tipo.titulo AS tipo,
                    cursos.fk_professor AS id_professor,
                    professor.nome AS nome_professor,
                    professor.sobrenome AS sobrenome_professor,
                    cursos_valor.valor,
                    cursos_valor.valor_de
                FROM cursos
                    join cursos_tipo ON cursos.fk_cursos_tipo = cursos_tipo.id
                    join professor ON professor.id = cursos.fk_professor
                    join cursos_valor ON cursos_valor.fk_curso = cursos.id
                    join cursos_faculdades ON  cursos_faculdades.fk_curso = cursos.id
                WHERE cursos.status = 5
                AND cursos_faculdades.fk_faculdade = ". $request->header('Faculdade', 1);

            if (!empty($idTipoCurso)) {
                $sSelect .= " AND cursos.fk_cursos_tipo = " . $idTipoCurso;
            }

            $sSelect .= " ORDER BY cursos.titulo ";

            $results = DB::select($sSelect);

            return response()->json(['success' => true, 'items' => $results,]);

        } catch(\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'exception' => $e->getMessage(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function getCursosItv($id, Request $request, ItvService $itvService) {
        try {
            
            $cursosItv = $itvService
                            ->setIdFaculdade($request->header('Faculdade', 7))
                            ->retornarCursosAlunoItv(Usuario::findOrFail($id));

            return response()->json($cursosItv);
        } catch (\Exception $error) {
            return response()->json(['success' => false, 'message' => $error->getMessage(), 'trace' => $error->getTraceAsString()], 401);
        }
    }

    public function getCalendarioCursosItv($id, Request $request, ItvService $itvService) {
        try {
            return response()->json($itvService->retornarCalendarioItv(Usuario::findOrFail($id)));
        } catch (Exception $error) {
            return response()->json(['success' => false, 'message' => $error->getMessage(),], 401);
        }
    }

    public function modalidade() {
        return response()->json(['success' => true, 'items' => CursoTipo::all(),]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function listarCursosHome(Request $request) {
        ini_set('max_execution_time', -1);
        try {

            $idFaculdade = $request->header('Faculdade', 7);

            $tiposAtivos = ConfiguracoesTiposCursosAtivos::where('fk_faculdade_id', $idFaculdade)->where('status', 1)->first();
            $tipos = [];
            if ($tiposAtivos->ativar_cursos_online) {
                $tipos[] =  1;
            }

            if ($tiposAtivos->ativar_cursos_presenciais) {
                $tipos[] =  2;
            }

            if ($tiposAtivos->ativar_cursos_hibridos) {
                $tipos[] =  4;
            }

            $tipoCursos = CursoTipo::all()->where('status', 1)->whereIn('id', $tipos);
            $categorias = CursoCategoria::distinct()->select('cursos_categoria.*')
                    ->join('cursos_categoria_curso', 'cursos_categoria_curso.fk_curso_categoria', '=', 'cursos_categoria.id')
                    ->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos_categoria_curso.fk_curso')
                    ->where('cursos_categoria.status', 1)
                    ->where('cursos_faculdades.fk_faculdade', $idFaculdade)
                    ->get();

            $cursosCategoria = [];
            foreach ($categorias as $categoria) {
                foreach ($tipoCursos as $tipo) {

                    $listaCursos = Curso::search($tipo->id,
                        [
                            'categoria_id' => $categoria->id,
                            'fk_cursos_tipo' => $tipo->id
                        ], $idFaculdade
                    );

                    if (!empty($listaCursos)) {
                        $cursosCategoria[$this->getTipoCurso($tipo->id)][] = [
                            'categoria' => $categoria,
                            'cursos' => $listaCursos
                        ];
                    }
                }
            }

            return response()->json(['success' => true, 'items' => $cursosCategoria,]);

        } catch(\Exception $e) {

            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'exception' => $e->getMessage(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }

    }

    /**
     * @param Request $request
     * @param int $perPage
     * @return JsonResponse
     */
    public function listarCursosHomePaginado(Request $request, $idTipo, $perPage = 10) {
        ini_set('max_execution_time', -1);
        $cursos =
            Curso::select(
                    'cursos.id',
                    'cursos.titulo',
                    'cursos.slug_curso',
                    'cursos_tipo.titulo as curso_tipo',
                    'cursos.fk_cursos_tipo',
                    'cursos.duracao_total',
                    'cursos.imagem',
                    'cursos_valor.valor',
                    'cursos_valor.valor_de',
                    'professor.nome as nome_professor',
                    'professor.sobrenome as sobrenome_professor',
                    'professor.id as id_professor',
                    DB::raw('cursos_tipo.titulo as tipo'),
                    DB::raw('(cursos_valor.valor_de - cursos_valor.valor) as promocao'),
                    DB::raw('COUNT(pedidos_item.fk_curso) as vendidos'),
                    'cursos_faculdades.curso_gratis as gratis'
                )
                    ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
                    ->leftJoin('professor', 'cursos.fk_professor', '=', 'professor.id')
                    ->leftJoin('pedidos_item', 'pedidos_item.fk_curso', '=', 'cursos.id')
                    ->leftJoin('cursos_tipo', 'cursos_tipo.id', '=', 'cursos.fk_cursos_tipo')
                    ->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id')
                    ->where('cursos.status', '=', 5)
                    ->where('cursos.fk_cursos_tipo', '=', $idTipo)
                    ->where('cursos_faculdades.fk_faculdade', $request->header('Faculdade', 1))

                    ->groupBy(
                                'cursos.id',
                                'cursos.titulo',
                                'cursos_tipo.titulo',
                                'cursos.fk_cursos_tipo',
                                'cursos.duracao_total',
                                'cursos.imagem',
                                'cursos_valor.valor',
                                'cursos_valor.valor_de',
                                'professor.nome',
                                'professor.id',
                                'cursos_faculdades.curso_gratis',
                                'professor.sobrenome', 'cursos_tipo.titulo'
                            )
                    ->orderBy(DB::raw('RAND(1234)'))
                    ->paginate($perPage);

        return response()->json(['success' => true, 'items' => $cursos,]);
    }

    public function getTipoCurso($idTipo) {
        switch ($idTipo) {
            case 1:
                return 'online';
                break;
            case 2:
                return 'presencial';
                break;
            case 4:
                return 'remoto';
                break;
        }
    }

    public function getHoraServidor(){
        $hours = [
            'data' => date('Y-m-d'),
            'hora' => date('H:i:s')
        ];
        return response()->json(['success' => true, 'retorno' => $hours]);
    }
    public function getCursoIDBySlug($slug_curso, $tipo_curso_id){
        try {
            $curso = Curso::select('id','titulo','slug_curso')
                ->where('slug_curso', $slug_curso)
                ->where('fk_cursos_tipo', $tipo_curso_id)
                ->get()
                ->toArray();
            return response()->json([
                'data' => $curso[0]
            ]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail();
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function detalhesCursoCorrente(
        ItvService $itvService, 
        Curso $curso, 
        EstruturaCurricular $estrutura, 
        CursoCategoria $categoria,
        Usuario $usuario,
        Request $request
    ) {
        
        try {
            return response()->json(
                $itvService->setIdFaculdade($request->header('Faculdade', 7))
                    ->setIdCurso($curso->id)
                    ->listarCursosItv($categoria, $usuario, $estrutura)[0]
            );
        } catch (Exception $error) {
            return response()->json(['success' => false, 'message' => $error->getMessage(),], 401);
        }
    }

}
