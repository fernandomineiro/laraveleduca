<?php

namespace App\Http\Controllers\Admin;

use App\ConclusaoCursosFaculdades;
use App\CursosFaculdades;
use App\CursosTrabalhos;
use App\CursoTurma;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use App\Exports\CursosExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\Curso;

use App\Faculdade;
use App\CursoCategoria;
use App\CursoTipo;
use App\CursoValor;
use App\CursoCategoriaCurso;
use App\CursoTag;
use App\Professor;
use App\Produtora;
use App\Parceiro;
use App\Curador;
use App\Usuario;
use App\CertificadoLayout;
use App\CursoTurmaAgenda;
use App\CursoModulo;
use App\CursoSecao;
use App\Quiz;
use App\QuizQuestao;
use App\QuizResposta;
use App\StatusAprovacaoConteudo;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CursoController extends Controller
{

    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['cursos'] = Curso::select('cursos.id', 'cursos.*', 'cursos_valor.valor',
            'cursos_valor.valor_de')
            ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
            ->where('cursos.status', '>', 0)
            ->get();

        $this->arrayViewData['lista_faculdades'] = Faculdade::all()->where('status', '=', 1)->pluck('fantasia', 'id');
        $this->arrayViewData['lista_tipos'] = CursoTipo::all()->where('status', '=', 1)->pluck('titulo', 'id');

        Curso::verificarSlugsNaoCadastrados();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.index', $this->arrayViewData);
    }

    public function lista($tipo)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['cursos'] = Curso::select('cursos.id', 'cursos.*', 'cursos_valor.valor',
            'cursos_valor.valor_de')
            ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
            ->where('cursos.fk_cursos_tipo', $tipo)
            ->orderBy('cursos.titulo', 'asc')
            ->get();

        $this->arrayViewData['cursos_categorias'] = Curso::select('cursos.id', 'cursos_categoria.titulo as categorias')
            ->leftJoin('cursos_categoria_curso', 'cursos_categoria_curso.fk_curso', '=', 'cursos.id')
            ->leftJoin('cursos_categoria', 'cursos_categoria.id', '=', 'cursos_categoria_curso.fk_curso_categoria')
            ->groupBy('cursos.id')
            ->groupBy('cursos_categoria.titulo')
            ->get();

        $sql = "SELECT c.id,group_concat(ccat.titulo separator ', ') as categorias 
                FROM cursos c 
                LEFT JOIN cursos_categoria_curso ccc ON 
                c.id = ccc.fk_curso 
                LEFT JOIN cursos_categoria ccat ON ccc.fk_curso_categoria = ccat.id 
                GROUP BY c.id
                ORDER BY c.titulo";
        $list = DB::select($sql, []);

        $this->arrayViewData['lista_categorias'] = $list;

        $this->arrayViewData['tipo'] = $tipo;
        $this->__carregaCombos();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    /**
     * @param $tipo
     * @return Factory|RedirectResponse|View
     */
    public function incluir($tipo)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->__carregaCombos();
        $this->arrayViewData['tipo'] = $tipo;

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    /**
     * @param $id
     * @return Factory|RedirectResponse|View
     */
    public function editar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $curso = Curso::findOrFail($id);
        if ($curso->fk_criador_id) {
            $this->arrayViewData['usuariocadastro'] = Usuario::find($curso->fk_criador_id);
        }
        $this->arrayViewData['curso'] = $curso;
        $this->arrayViewData['tipo'] = $curso->fk_cursos_tipo;

        $this->__carregaCombos($id);

        $this->arrayViewData['agendas_cadastradas'] = CursoTurmaAgenda::where('cursos_turmas_agenda.fk_curso', '=',
            $id)->get();

        $this->arrayViewData['modulos_cadastrados'] = CursoModulo::where('fk_curso', '=', $id)
            ->where('cursos_modulos.status', 1)
            ->get();
        $this->arrayViewData['tags_cadastradas'] = CursoTag::where('fk_curso', '=', $id)->pluck('tag', 'id');

        $secoes_cadastradas = CursoSecao::select(
            'cursos_secao.id as secao_id',
            'cursos_secao.titulo as secao_titulo',
            'cursos_secao.ordem as secao_ordem',
            'cursos_modulos.id as modulo_id',
            'cursos_modulos.titulo as modulo_titulo',
            'cursos_modulos.carga_horaria as modulo_carga_horaria',
            'cursos_modulos.url_arquivo as modulo_url_arquivo',
            'cursos_modulos.url_video as modulo_url_video',
            'cursos_modulos.aula_ao_vivo', 
            'cursos_modulos.data_aula_ao_vivo', 
            'cursos_modulos.hora_aula_ao_vivo', 
            'cursos_modulos.data_fim_aula_ao_vivo', 
            'cursos_modulos.hora_fim_aula_ao_vivo', 
            'cursos_modulos.link_aula_ao_vivo',
            'cursos_modulos.ordem as modulo_ordem'
        )->join('cursos_modulos', 'cursos_modulos.fk_curso_secao', '=', 'cursos_secao.id')
            ->where('cursos_secao.fk_curso', '=', $id)
            ->where('cursos_secao.status', '=', 1)
            ->where('cursos_modulos.status', 1)
            ->orderBy('cursos_secao.ordem')
            ->orderBy('cursos_modulos.ordem')
            ->get();

        $lista_secoes = [];
        foreach ($secoes_cadastradas as $key => $secao) {
            $lista_secoes[$secao['secao_id']]['titulo'] = $secao['secao_titulo'];
            $lista_secoes[$secao['secao_id']]['ordem'] = $secao['secao_titulo'];
            $lista_secoes[$secao['secao_id']]['modulos'][$secao['modulo_id']] = [
                'titulo' => $secao['modulo_titulo'],
                'carga_horaria' => $secao['modulo_carga_horaria'],
                'url_arquivo' => $secao['modulo_url_arquivo'],
                'url_video' => $secao['modulo_url_video'],
                'aula_ao_vivo' => $secao['aula_ao_vivo'],
                'data_aula_ao_vivo' => $secao['data_aula_ao_vivo'],
                'hora_aula_ao_vivo' => $secao['hora_aula_ao_vivo'],
                'link_aula_ao_vivo' => $secao['link_aula_ao_vivo'],
                'data_fim_aula_ao_vivo' => $secao['data_fim_aula_ao_vivo'],
                'hora_fim_aula_ao_vivo' => $secao['hora_fim_aula_ao_vivo'],
                'ordem' => $secao['modulo_ordem']
            ];
        }

        $this->arrayViewData['secoes_cadastradas'] = $lista_secoes;
        $this->arrayViewData['dados_valor'] = CursoValor::where('fk_curso', $id)->where('data_validade', null)->first();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        try {
            \DB::beginTransaction();

            $dadosForm = $request->except('_token');
            $curso = new Curso();
            $validator = $curso->_validate($request->all());
            if ($validator->fails()) {
                $this->validatorMsg = $validator;
            }
            if (!$validator->fails()) {

                if ($request->hasFile('imagem')) {
                    $file = $request->file('imagem');
                    $dadosForm['imagem'] = $this->uploadFile('imagem', $file);
                }

                $curso = new Curso();
                $validator = $curso->_validate($dadosForm);
                if (!$validator->fails()) {
                    $dadosForm['duracao_total'] = str_replace(",", ".",
                        str_replace(".", "", $request->input('duracao_total')));

                    if (empty($dadosForm['atualizacao'])) {
                        $dadosForm['atualizacao'] = new \DateTime();
                    }

                    $dadosForm['slug_curso'] = Curso::configurarSlugCurso($dadosForm['titulo']);

                    $resultado = $curso->create($dadosForm);
                } else {
                    return Redirect::back()->withErrors($validator)->withInput();
                }

                if ($resultado) {

                    $dataTrabalho = [
                        'status' => 1,
                        'titulo' => $resultado->titulo,
                        'fk_cursos' => $resultado->id
                    ];

                    CursosTrabalhos::updateOrCreate(
                        [
                            'fk_cursos' => $resultado->id
                        ],
                        $dataTrabalho
                    );


                    if (isset($dadosForm['fk_cursos_faculdade']) && count($dadosForm['fk_cursos_faculdade'])) {
                        foreach ($dadosForm['fk_cursos_faculdade'] as $faculdade) {
                            if (isset($faculdade['fk_faculdade']) && $faculdade['fk_faculdade'] != '__IDX__') {
                                $array = [
                                    'fk_curso' => $resultado->id,
                                    'fk_faculdade' => $faculdade['fk_faculdade'],
                                    'duracao_dias' => $faculdade['duracao_dias'],
                                    'disponibilidade_dias' => $faculdade['disponibilidade_dias'],
                                    'curso_gratis' => (isset($faculdade['gratis'])) ? $faculdade['gratis'] : 0,
                                ];
                                // inserir aqui model nova após criação da tabela no banco
                                $cursoFaculdade = new CursosFaculdades();
                                $cursoFaculdade->create($array);
                            }
                        }
                    }


                    ##certificados_cursos_faculdades
                    if (isset($dadosForm['fk_cursos_faculdade']) && count($dadosForm['fk_cursos_faculdade'])) {
                        foreach ($dadosForm['fk_cursos_faculdade'] as $faculdade) {
                            if (isset($faculdade['fk_faculdade']) && $faculdade['fk_faculdade'] != '__IDX__') {
                                $array = [
                                    'fk_curso' => $resultado->id,
                                    'fk_faculdade' => $faculdade['fk_faculdade'],
                                    'fk_certificado' => $dadosForm['conclusao_cursos_faculdades'][$faculdade['fk_faculdade']]['fk_certificado'],
                                    'nota_trabalho' => $dadosForm['conclusao_cursos_faculdades'][$faculdade['fk_faculdade']]['nota_trabalho'],
                                    'nota_quiz' => $dadosForm['conclusao_cursos_faculdades'][$faculdade['fk_faculdade']]['nota_questionario'],
                                    //'freq_minima' => $dadosForm['conclusao_cursos_faculdades'][$faculdade['fk_faculdade']]['frequencia_minima'],
                                ];

                                if (isset($dadosForm['conclusao_cursos_faculdades'][$faculdade['fk_faculdade']]['frequencia_minima'])) {
                                    $array['freq_minima'] = $dadosForm['conclusao_cursos_faculdades'][$faculdade['fk_faculdade']]['frequencia_minima'];
                                } else {
                                    $array['freq_minima'] = 0;
                                }

                                $conclusaoFaculdade = new ConclusaoCursosFaculdades();
                                $conclusaoFaculdade->create($array);
                            }
                        }
                    }

                    if (isset($dadosForm['fk_cursos_categoria']) && count($dadosForm['fk_cursos_categoria'])) {
                        foreach ($dadosForm['fk_cursos_categoria'] as $id_categoria => $categoria) {
                            $array = [
                                'fk_curso' => $resultado->id,
                                'fk_curso_categoria' => $categoria
                            ];

                            $cursoCategoriaCurso = new CursoCategoriaCurso();

                            $array = $this->insertAuditData($array, false);

                            $cursoCategoriaCurso->create($array);
                            unset($cursoCategoriaCurso);
                        }
                    }
                    if (isset($dadosForm['secao']) && isset($dadosForm['modulos'])) {
                        $files_modulos = array();
                        if (isset($_FILES) && count($_FILES)) {
                            foreach ($_FILES['modulos'] as $campo => $campos) {
                                foreach ($campos as $key_secao => $item) {
                                    if ($key_secao !== '__COUNT__' && $key_secao !== '__COUNT_SECAO__') {
                                        foreach ($item as $key_mudulo => $desc) {
                                            $files_modulos[$key_secao][$key_mudulo][$campo] = current($desc);
                                        }
                                    }
                                }
                            }
                        }

                        $files = $request->file('modulos');

                        if (isset($files) && count($files)) {
                            foreach ($files as $key_secao => $secao) {
                                foreach ($secao as $key => $modulo) {
                                    $files_modulos[$key_secao][$key]['name'] = $this->uploadFile('modulos',
                                        $modulo['url_arquivo'], 'modulo');
                                }
                            }
                        }

                        ### MODULOS ###
                        foreach ($dadosForm['secao'] as $key_secao => $secao) {
                            if (($key_secao !== '__COUNT__') && $secao['titulo'] != '') {
                                $curso_secao = new CursoSecao();
                                $curso_secao->titulo = $secao['titulo'];
                                $curso_secao->ordem = $key_secao;
                                $curso_secao->status = 1;
                                $curso_secao->fk_curso = $resultado->id;
                                $curso_secao->save();

                                foreach ($dadosForm['modulos'][$key_secao] as $key => $item) {
                                    if (($key !== '__X__') && $item['titulo'] != '') {
                                        $cursos_modulo = new CursoModulo();
                                        $cursos_modulo->titulo = $item['titulo'];
                                        $cursos_modulo->aula_ao_vivo = $item['aula_ao_vivo'];
                                        $cursos_modulo->data_aula_ao_vivo = isset($item['data_aula_ao_vivo']) ? implode('-',
                                            array_reverse(explode('/', $item['data_aula_ao_vivo']))) : '';
                                        $cursos_modulo->hora_aula_ao_vivo =  str_replace(' ', '', $item['hora_aula_ao_vivo']);

                                        $cursos_modulo->link_aula_ao_vivo = $item['link_aula_ao_vivo'];

                                        $cursos_modulo->data_fim_aula_ao_vivo = isset($item['data_fim_aula_ao_vivo']) ? implode('-',
                                            array_reverse(explode('/', $item['data_fim_aula_ao_vivo']))) : '';
                                        $cursos_modulo->hora_fim_aula_ao_vivo =  str_replace(' ', '', $item['hora_fim_aula_ao_vivo']);

                                        $cursos_modulo->tipo_modulo = !empty($files_modulos[$key_secao][$key]['name']) ? 1 : 2;
                                        $cursos_modulo->url_video = $item['url_video'];
                                        $cursos_modulo->carga_horaria = str_replace(' ', '', $item['carga_horaria']);
                                        $cursos_modulo->url_arquivo = isset($files_modulos[$key_secao][$key]['name']) ? $files_modulos[$key_secao][$key]['name'] : '';
                                        $cursos_modulo->fk_curso = $resultado->id;
                                        $cursos_modulo->fk_curso_secao = $curso_secao->id;
                                        $cursos_modulo->ordem = $key;

                                        $cursos_modulo->status = 1;
                                        $cursos_modulo->save();
                                    }
                                }
                            }
                        }
                    }

                    ### AGENDAS ###
                    $agendas = Input::get('agenda');
                    if (isset($agendas) && count($agendas)) {
                        $turma = new CursoTurma();
                        //$dadosForm['atualizacao'] = new \DateTime();
                        $turma->fill([
                            'fk_curso' => $resultado->id,
                            'nome' => 'Turma - ' . $curso->titulo,
                            'descricao' => 'Turma padrão do curso ' . $curso->titulo,
                            'status' => 1,
                            'atualizacao' => (new \DateTime())->format('Y-m-d H:i:s')
                        ]);
                        $turma->save();

                        foreach ($agendas as $key => $item) {
                            if ($item['descricao'] != '' && ($key != '__X__') && !empty($item['data_inicio'] && !empty($item['descricao']))) {

                                $agenda = new CursoTurmaAgenda();
                                $agenda->nome = !empty($item['descricao']) ? $item['descricao'] : 'Agenda ' . $key;
                                $agenda->data_inicio = implode('-', array_reverse(explode('/', $item['data_inicio'])));
                                $agenda->data_final = isset($item['data_inicio']) ? implode('-',
                                array_reverse(explode('/', $item['data_inicio']))) : '';
                                $agenda->hora_inicio = str_replace(' ', '', $item['hora_inicio']);
                                $agenda->hora_final = str_replace(' ', '', $item['hora_fim']);
                                $agenda->duracao_aula = str_replace(' ', '', $item['duracao_aula']);
                                $agenda->fk_curso = $resultado->id;
                                $agenda->fk_turma = $turma->id;
                                $agenda->atualizacao = (new \DateTime())->format('Y-m-d H:i:s');
                                $agenda->save();
                            }
                        }
                    }


                    ### TAGS ###
                    if (isset($dadosForm['tags']) && count($dadosForm['tags'])) {
                        foreach ($dadosForm['tags'] as $key => $valor) {
                            if (!empty($valor)) {
                                $array = [
                                    'fk_curso' => $resultado->id,
                                    'tag' => $valor
                                ];

                                $cursoTag = new CursoTag();
                                $cursoTag->create($array);
                                unset($cursoTag);
                            }
                        }
                    }

                    ### QUIZ ####
                    #checa se foi preenchido o campo de quiz, senão não insere
                    $count_questao = 0;
                    $count_questao_p = 0;
                    $count_opcao = 0;
                    $count_opcao_p = 0;
                    if (isset($dadosForm['quiz']['questao']) && count($dadosForm['quiz']['questao'])) {
                        foreach ($dadosForm['quiz']['questao'] as $key => $item) {
                            if ($key !== '__X__') {
                                $count_questao += 1;
                                if (!empty($item['titulo'])) {
                                    $count_questao_p += 1;
                                }
                                $count_opcao_alts = 0;
                                $labels = array();
                                $labels_p = array();
                                foreach ($dadosForm['quiz']['op'][$key] as $label => $opcao) {
                                    if ($label !== '__X__') {
                                        array_push($labels, $label);
                                        $count_opcao += 1;
                                        if (!empty($opcao['alternativa'])) {
                                            $count_opcao_p += 1;
                                            $count_opcao_alts +=1;
                                            array_push($labels_p, $label);
                                        }
                                    }
                                }
                                $sequencial_p = true;
                                if( empty($item['titulo']) && count($dadosForm['quiz']['questao']) == 2) {
                                    continue;
                                }

                                if($count_opcao_alts < 3){
                                    \DB::rollBack();
                                    \Session::flash('mensagem_erro', 'Preencha pelo menos três alternativas em cada questão!');
                                    return Redirect::back()->withErrors($validator)->withInput();
                                }

                                if( $count_opcao_alts == 3) {
                                    if($labels[0] == '0') $arr1 = array('0','1','2');
                                    else $arr1 = array('1','2','3');
                                    $sequencial_p = empty(array_diff($arr1, $labels_p));
                                }
                                if( $count_opcao_alts == 4) {
                                    if($labels[0] == '0') $arr1 = array('0','1','2','3');
                                    else $arr1 = array('1','2','3','4');
                                    $sequencial_p = empty(array_diff($arr1, $labels_p));
                                }
                                if(!$sequencial_p){
                                    \DB::rollBack();
                                    \Session::flash('mensagem_erro', 'Preencha as alternativas sequencialmente começando pela primeira!');
                                    return Redirect::back()->withErrors($validator)->withInput();
                                }

                                $index_resposta = ($labels[0] == '0')? ($item['resposta_correta']-1) : $item['resposta_correta'];
                                if(!in_array($index_resposta, $labels_p)){
                                    \DB::rollBack();
                                    \Session::flash('mensagem_erro', 'Selecione uma alternativa válida como resposta correta!');
                                    return Redirect::back()->withErrors($validator)->withInput();
                                }
                            }
                        }
                    }
                    $insere_quiz = false;
                    //permite inserir se não estiver todos os campos de questão vazios
                    if($count_questao_p == 0 && $count_opcao_p > 0){
                        \DB::rollBack();
                        \Session::flash('mensagem_erro', 'Preencha o titulo de todas as questões!');
                        return Redirect::back()->withErrors($validator)->withInput();
                    }
                    if($count_questao_p != 0){
                        if($count_questao_p == $count_questao){
                            if($count_opcao_p > 0){
                                $insere_quiz = true;
                            }
                        } else {
                            \DB::rollBack();
                            \Session::flash('mensagem_erro', 'Preencha o titulo de todas as questões!');
                            return Redirect::back()->withErrors($validator)->withInput();
                        }
                    }
                    if($insere_quiz){
                        $quiz = new Quiz();
                        $array_insert_quiz = [
                            'fk_curso' => $resultado->id,
                            //'percentual_acerto' => $dadosForm['quiz']['percentual_acerto']
                            'percentual_acerto' => 0
                        ];
                        $resultado_quiz = $quiz->create($array_insert_quiz);

                        if ($resultado_quiz) {
                            foreach ($dadosForm['quiz']['questao'] as $key => $item) {
                                if ($key !== '__X__' && !empty($item['titulo'])) {
                                    $array_insert_quiz_questao = [
                                        'fk_quiz' => $resultado_quiz->id,
                                        'titulo' => $item['titulo'],
                                        'resposta_correta' => $item['resposta_correta'],
                                        'status' => '1',
                                    ];
                                    $quiz_questao = new QuizQuestao();
                                    $resultado_quiz_questao = $quiz_questao->create($array_insert_quiz_questao);
                                    foreach ($dadosForm['quiz']['op'][$key] as $label => $opcao) {
                                        if ($label !== '__X__') {
                                            $array_insert_quiz_resposta = [
                                                'label' => $label,
                                                'descricao' => $opcao['alternativa'],
                                                'fk_quiz_questao' => $resultado_quiz_questao->id
                                            ];
                                            $quiz_resposta = new QuizResposta();
                                            $resultado_quiz_resposta = $quiz_resposta->create($array_insert_quiz_resposta);
                                        }
                                    }
                                }
                            }
                        }
                    } ### fim QUIZ ###

                    $preco_cursos_valor = [
                        'fk_curso' => $resultado->id,
                        'valor' => $request->input('valor') ? str_replace(",", ".",
                            str_replace(".", "", $request->input('valor'))) : null,
                        'valor_de' => $request->input('valor_de') ? str_replace(",", ".",
                            str_replace(".", "", $request->input('valor_de'))) : null,
                        'data_inicio' => date('Y-m-d')
                    ];

                    $cursoValor = new CursoValor();
                    $validatorCursoValor = Validator::make($preco_cursos_valor, $cursoValor->rules,
                        $cursoValor->messages);

                    $preco_cursos_valor = $this->insertAuditData($preco_cursos_valor);
                    if (!$validatorCursoValor->fails()) {
                        CursoValor::updateOrCreate([
                            'fk_curso' => $resultado->id,
                        ], $preco_cursos_valor);
                    }
                } else {
                    \DB::rollBack();
                    \Session::flash('mensagem_erro', 'Não foi possível inserir o registro!');
                    return Redirect::back()->withErrors($validator)->withInput();
                }
                \DB::commit();
                \Session::flash('mensagem_sucesso', $this->msgInsert);
                return redirect('admin/curso/' . Input::get('fk_cursos_tipo') . '/lista');
            } else {
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } catch (\Exception $exception) {

            \DB::rollBack();
            \Session::flash('mensagem_erro', 'Não foi possível inserir o registro! ' . $exception->getMessage());
            return Redirect::back()->withInput();
        }
    }

    public function atualizar($id, Request $request) {
        try {
            \DB::beginTransaction();
            if (!$this->validateAccess(\Session::get('user.logged'), false)) {
                return redirect()->route($this->redirecTo);
            }

            $curso = Curso::findOrFail($id);

            $validator = $curso->_validate($request->all());
            if ($validator->fails()) {
                $this->validatorMsg = $validator;
            }

            if (!empty($request->input('valor_de'))) {
                CursoValor::updateOrCreate([
                    'fk_curso' => $id,
                ], $this->insertAuditData([
                    'fk_curso' => $id,
                    'valor' => $request->input('valor') ? str_replace(",", ".",
                        str_replace(".", "", $request->input('valor'))) : null,
                    'valor_de' => $request->input('valor_de') ? str_replace(",", ".",
                        str_replace(".", "", $request->input('valor_de'))) : null,
                    'data_inicio' => date('Y-m-d')
                ]));
            }

            $dadosForm = $request->except('_token');

            $dadosForm['slug_curso'] = Curso::configurarSlugCurso($dadosForm['titulo']);

            if ($request->hasFile('imagem')) {
                $file = $request->file('imagem');
                $dadosForm['imagem'] = $this->uploadFile('imagem', $file);
            } else {
                // $dadosForm['imagem'] = isset($dadosForm['imagem']) ? $dadosForm['imagem'] : '';
            }

            $dataTrabalho = [
                'status' => 1,
                'titulo' => $curso->titulo,
                'fk_cursos' => $id
            ];

            CursosTrabalhos::updateOrCreate(
                [
                    'fk_cursos' => $id
                ],
                $dataTrabalho
            );



            $faculdadeExistentes = CursosFaculdades::all()->where('fk_curso', '=', $id);
            foreach ($faculdadeExistentes as $key => $item) {
                CursosFaculdades::where('id', $item['id'])->delete();
            }
            if (isset($dadosForm['fk_cursos_faculdade']) && count($dadosForm['fk_cursos_faculdade'])) {
                foreach ($dadosForm['fk_cursos_faculdade'] as $faculdade) {
                    if (isset($faculdade['fk_faculdade']) && $faculdade['fk_faculdade'] != '__IDX__') {
                        $array = [
                            'fk_curso' => $id,
                            'fk_faculdade' => $faculdade['fk_faculdade'],
                            'duracao_dias' => $faculdade['duracao_dias'],
                            'disponibilidade_dias' => $faculdade['disponibilidade_dias'],
                            'curso_gratis' => (isset($faculdade['gratis'])) ? $faculdade['gratis'] : 0,
                        ];
                        // inserir aqui model nova após criação da tabela no banco
                        $cursoFaculdade = new CursosFaculdades();
                        $cursoFaculdade->create($array);
                    }
                }
            }

            //criar conclusao cursos faculdades
            $faculdadeExistentes = ConclusaoCursosFaculdades::all()->where('fk_curso', '=', $id);
            foreach ($faculdadeExistentes as $key => $item) {
                ConclusaoCursosFaculdades::where('id', $item['id'])->delete();
            }
            //if (isset($dadosForm['conclusao_cursos_faculdades']) && count($dadosForm['conclusao_cursos_faculdades'])) {
            //    foreach($dadosForm['conclusao_cursos_faculdades'] as $faculdade){
            //        if(isset($faculdade['fk_faculdade'])){
            //        }
            //    }
            //}
            if (isset($dadosForm['fk_cursos_faculdade']) && count($dadosForm['fk_cursos_faculdade'])) {
                foreach ($dadosForm['fk_cursos_faculdade'] as $faculdade) {
                    if (isset($faculdade['fk_faculdade']) && $faculdade['fk_faculdade'] != '__IDX__') {
                        $array = [
                            'fk_curso' => $id,
                            'fk_faculdade' => $faculdade['fk_faculdade'],
                            'fk_certificado' => $dadosForm['conclusao_cursos_faculdades'][$faculdade['fk_faculdade']]['fk_certificado'],
                            'nota_trabalho' => $dadosForm['conclusao_cursos_faculdades'][$faculdade['fk_faculdade']]['nota_trabalho'],
                            'nota_quiz' => $dadosForm['conclusao_cursos_faculdades'][$faculdade['fk_faculdade']]['nota_questionario'],
                            //'freq_minima' => $dadosForm['conclusao_cursos_faculdades'][$faculdade['fk_faculdade']]['frequencia_minima'],
                        ];

                        if (isset($dadosForm['conclusao_cursos_faculdades'][$faculdade['fk_faculdade']]['frequencia_minima'])) {
                            $array['freq_minima'] = $dadosForm['conclusao_cursos_faculdades'][$faculdade['fk_faculdade']]['frequencia_minima'];
                        } else {
                            $array['freq_minima'] = 0;
                        }

                        $conclusaoFaculdade = new ConclusaoCursosFaculdades();
                        $conclusaoFaculdade->create($array);
                    }
                }
            }

            //deleta categorias e cria de novo
            $lista_cursos_selecionados = CursoCategoriaCurso::all()->where('fk_curso', '=', $id);
            foreach ($lista_cursos_selecionados as $key => $item) {
                CursoCategoriaCurso::where('id', $item['id'])->delete();
            }
            if (isset($dadosForm['fk_cursos_categoria']) && count($dadosForm['fk_cursos_categoria'])) {
                foreach ($dadosForm['fk_cursos_categoria'] as $id_categoria => $categoria) {
                    $array = [
                        'fk_curso' => $id,
                        'fk_curso_categoria' => $categoria
                    ];

                    $array = $this->insertAuditData($array, false);

                    $cursoCategoriaCurso = new CursoCategoriaCurso();
                    $cursoCategoriaCurso->create($array);
                    unset($cursoCategoriaCurso);
                }
            }
            if (isset($dadosForm['secao']) && isset($dadosForm['modulos'])) {
                $files_modulos = array();
                if (isset($_FILES['modulos']) && count($_FILES['modulos'])) {
                    foreach ($_FILES['modulos'] as $campo => $campos) {
                        foreach ($campos as $key_secao => $item) {
                            if ($key_secao !== '__COUNT__' && $key_secao !== '__COUNT_SECAO__') {
                                foreach ($item as $key_mudulo => $desc) {
                                    $files_modulos[$key_secao][$key_mudulo][$campo] = current($desc);
                                }
                            }
                        }
                    }
                }

                $files = $request->file('modulos');
                if (isset($files) && count($files)) {
                    foreach ($files as $key_secao => $secao) {
                        foreach ($secao as $key => $modulo) {
                            $files_modulos[$key_secao][$key]['name'] = $this->uploadFile('modulos',
                                $modulo['url_arquivo'], 'modulo');
                        }
                    }
                }
                ### MODULOS ###
                $secoesids = collect($dadosForm['secao'])->map(function ($item, $key) {
                    if ($key != '__COUNT__' && !empty($item)) return $key;
                })->toArray();
                $secoesids = array_values($secoesids);
                $secoesdelete = CursoSecao::where('fk_curso', $id)->pluck('id');
                $secoesdelete = $secoesdelete->diff($secoesids);
                foreach ($secoesdelete as $scd) {
                    $secaodelete = CursoSecao::find($scd);
                    // $secaodelete->delete();
                    $secaodelete->status = 0;
                    $secaodelete->save();

                    $modulosdelete = CursoModulo::where('fk_curso', $id)->where('fk_curso_secao', $scd)->get();
                    foreach ($modulosdelete as $mcd) {
                        // $mcd->delete();
                        $mcd->status = 0;
                        $mcd->save();
                    }
                }

                foreach ($dadosForm['secao'] as $key_secao => $secao) {
                    if ($key_secao != '__COUNT__') {
                        if (isset($secao['id_secao'])) {
                            $curso_secao = CursoSecao::findOrFail($secao['id_secao']);
                            $curso_secao->update($secao);

                            $modulosids = collect($dadosForm['modulos'][$key_secao])->map(function ($item, $key) {
                                if (isset($item['id_modulo'])) return $item['id_modulo'];
                            })->toArray();
                            $modulosids = array_values($modulosids);
                            $modulosdelete = CursoModulo::where('fk_curso', $id)->where('fk_curso_secao', $secao['id_secao'])->whereNotIn('id', $modulosids)->get();
                            foreach ($modulosdelete as $scd) {
                                $scd->status = 0;
                                $scd->save();
                                // $scd->delete();
                            }
                        } else {
                            if (($key_secao !== '__COUNT__') && $secao['titulo'] != '') {
                                $curso_secao = new CursoSecao();
                                $curso_secao->titulo = $secao['titulo'];
                                $curso_secao->ordem = $key_secao;
                                $curso_secao->status = 1;
                                $curso_secao->fk_curso = $id;
                                $curso_secao->save();
                            }
                        }
                        foreach ($dadosForm['modulos'][$key_secao] as $key => $item) {
                            if (isset($item['id_modulo'])) {
                                $item['data_aula_ao_vivo'] = isset($item['data_aula_ao_vivo']) ? implode('-',
                                    array_reverse(explode('/', $item['data_aula_ao_vivo']))) : '';
                                $item['data_fim_aula_ao_vivo'] = isset($item['data_fim_aula_ao_vivo']) ? implode('-',
                                    array_reverse(explode('/', $item['data_fim_aula_ao_vivo']))) : '';
                                $cursos_modulo = CursoModulo::findOrFail($item['id_modulo']);
                                $item['hora_aula_ao_vivo'] = str_replace(' ', '', $item['hora_aula_ao_vivo']);
                                $item['hora_fim_aula_ao_vivo'] = str_replace(' ', '', $item['hora_fim_aula_ao_vivo']);
                                $cursos_modulo->update($item);
                            } else {
                                if (($key !== '__X__') && $item['titulo'] != '') {
                                    $cursos_modulo = new CursoModulo();
                                    $cursos_modulo->titulo = $item['titulo'];
                                    $cursos_modulo->aula_ao_vivo = $item['aula_ao_vivo'];
                                    $cursos_modulo->data_aula_ao_vivo = implode('-', array_reverse(explode('/', $item['data_aula_ao_vivo'])));
                                    $cursos_modulo->hora_aula_ao_vivo = str_replace(' ', '', $item['hora_aula_ao_vivo']);
                                    $cursos_modulo->data_fim_aula_ao_vivo = implode('-', array_reverse(explode('/', $item['data_fim_aula_ao_vivo'])));
                                    $cursos_modulo->hora_fim_aula_ao_vivo = str_replace(' ', '', $item['hora_fim_aula_ao_vivo']);
                                    $cursos_modulo->link_aula_ao_vivo = $item['link_aula_ao_vivo'];
                                    $cursos_modulo->tipo_modulo = !empty($files_modulos[$key_secao][$key]['name']) ? 1 : 2;
                                    $cursos_modulo->url_video = $item['url_video'];
                                    $cursos_modulo->carga_horaria = str_replace(' ', '', $item['carga_horaria']);
                                    $cursos_modulo->url_arquivo = isset($files_modulos[$key_secao][$key]['name']) ? $files_modulos[$key_secao][$key]['name'] : '';
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

            }
            $agendas = Input::get('agenda');

            if($agendas){

                $agendasids = [];
                foreach($agendas as $agenda)
                {
                    if (isset($agenda["id_agenda"]))
                    {
                        $idAgenda = $agenda["id_agenda"];
                        array_push($agendasids, $idAgenda);
                    }
                }

                $agendasdelete = CursoTurmaAgenda::where('fk_curso', $id)->whereNotIn('id', $agendasids)->get();
                foreach ($agendasdelete as $agd) {
                    $agd->delete();
                }
            }

            if (isset($agendas) && count($agendas)) {
                $turma = CursoTurma::where('fk_curso', $id)->first();

                if (empty($turma)) {
                    $turma = new CursoTurma();
                    $turma->fill([
                        'fk_curso' => $id,
                        'nome' => 'Turma - ' . $curso->titulo,
                        'descricao' => 'Turma padrão do curso ' . $curso->titulo,
                        'status' => 1
                    ]);
                    $turma->save();
                }

                foreach ($agendas as $key => $item) {
                    if ($key !== '__X__' && !empty($item['descricao'])) {

                        CursoTurmaAgenda::updateOrCreate([
                            'id' => !empty($item['id_agenda']) ? $item['id_agenda'] : null,
                        ], $this->insertAuditData2([
                            'fk_curso' => $id,
                            'nome' => !empty($item['descricao']) ? $item['descricao'] : 'Agenda ' . $key,
                            'data_inicio' => implode('-', array_reverse(explode('/', $item['data_inicio']))),
                            'data_final' => isset($item['data_inicio']) ? implode('-',
                            array_reverse(explode('/', $item['data_inicio']))) : '',
                            'hora_inicio' => str_replace(' ', '', $item['hora_inicio']),
                            'hora_final' => str_replace(' ', '', $item['hora_fim']),
                            'duracao_aula' => str_replace(' ', '', $item['duracao_aula']),
                            'fk_turma' => $turma->id,
                        ]));
                    }
                }
            }

            if (isset($dadosForm['tags']) && count($dadosForm['tags'])) {
                foreach ($dadosForm['tags'] as $key => $valor) {
                    if (!empty($valor)) {
                        $array = [
                            'fk_curso' => $id,
                            'tag' => $valor
                        ];

                        $cursoTag = new CursoTag();
                        $cursoTag->create($array);
                        unset($cursoTag);
                    }
                }
            }

            if (isset($dadosForm['removeTags']) && count($dadosForm['removeTags'])) {
                foreach ($dadosForm['removeTags'] as $key => $valor) {
                    if (!empty($valor)) {

                        $cursoTag = CursoTag::find($valor);

                        if (!empty($cursoTag)) {
                            $cursoTag->delete();
                        }

                        unset($cursoTag);
                    }
                }
            }

            if (isset($dadosForm['quiz']) && count($dadosForm['quiz'])) {
                ### QUIZ ####
                #checa se foi preenchido o campo de quiz, senão não insere
                $count_questao = 0;
                $count_questao_p = 0;
                $count_opcao = 0;
                $count_opcao_p = 0;

                if (isset($dadosForm['quiz']['questao']) && count($dadosForm['quiz']['questao'])) {
                    if (count($dadosForm['quiz']['questao']) === 1) {
                        $questoes_delete = QuizQuestao::where('fk_quiz', $dadosForm['quiz']['fk_quiz'])->get();

                        foreach ($questoes_delete as $dado) {
                            $dado->delete();
                        }

                        Quiz::where('fk_curso', $id)->delete();
                    }

                    foreach ($dadosForm['quiz']['questao'] as $key => $item) {
                        if ($key !== '__X__') {
                            $count_questao += 1;
                            if (!empty($item['titulo'])) {
                                $count_questao_p += 1;
                            }
                            $count_opcao_alts = 0;
                            $labels = array();
                            $labels_p = array();
                            foreach ($dadosForm['quiz']['op'][$key] as $label => $opcao) {
                                if ($label !== '__X__') {
                                    array_push($labels, $label);
                                    $count_opcao += 1;
                                    if (!empty($opcao['alternativa'])) {
                                        $count_opcao_p += 1;
                                        $count_opcao_alts +=1;
                                        array_push($labels_p, $label);
                                    }
                                }
                            }
                            $sequencial_p = true;
                            if( empty($item['titulo']) &&
                                count($dadosForm['quiz']['questao']) == 2) continue;

                            if($count_opcao_alts < 3){
                                \DB::rollBack();
                                \Session::flash('mensagem_erro', 'Preencha pelo menos três alternativas em cada questão!');
                                return Redirect::back()->withErrors($validator)->withInput();
                            }

                            if( $count_opcao_alts == 3) {
                                if($labels[0] == '0') $arr1 = array('0','1','2');
                                else $arr1 = array('1','2','3');
                                $sequencial_p = empty(array_diff($arr1, $labels_p));
                            }
                            if( $count_opcao_alts == 4) {
                                if($labels[0] == '0') $arr1 = array('0','1','2','3');
                                else $arr1 = array('1','2','3','4');
                                $sequencial_p = empty(array_diff($arr1, $labels_p));
                            }
                            if(!$sequencial_p){
                                \DB::rollBack();
                                \Session::flash('mensagem_erro', 'Preencha as alternativas sequencialmente!');
                                return Redirect::back()->withErrors($validator)->withInput();
                            }

                            $index_resposta = ($labels[0] == '0')? ($item['resposta_correta']-1) : $item['resposta_correta'];
                            if(!in_array($index_resposta, $labels_p)){
                                \DB::rollBack();
                                \Session::flash('mensagem_erro', 'Selecione como resposta correta uma alternativa válida!');
                                return Redirect::back()->withErrors($validator)->withInput();
                            }
                        }
                    }

                    if (count($dadosForm['quiz']['questao']) == 2 && count(array_filter(array_column($dadosForm['quiz']['questao'], 'titulo'))) === 0) {
                        Quiz::where('fk_curso', $id)->delete();
                    }
                }
                $insere_quiz = false;
                //permite inserir se não estiver todos os campos de questão vazios
                if($count_questao_p == 0 && $count_opcao_p > 0){
                    \DB::rollBack();
                    \Session::flash('mensagem_erro', 'Preencha o titulo de todas as questões!');
                    return Redirect::back()->withErrors($validator)->withInput();
                }
                if($count_questao_p != 0){
                    if($count_questao_p == $count_questao){
                        if($count_opcao_p > 0){
                            $insere_quiz = true;
                        }
                    } else {
                        \DB::rollBack();
                        \Session::flash('mensagem_erro', 'Preencha o titulo de todas as questões!');
                        return Redirect::back()->withErrors($validator)->withInput();
                    }
                }
                if ($insere_quiz) {

                    //verificar se existe fk_quiz
                    //se existir, continue normalmente
                    //se não, crie novo Quiz, atualize variavel fk_quiz
                    $quiz_id = null;
                    if (!isset($dadosForm['quiz']['fk_quiz'])) {
                        $quiz = new Quiz();
                        $array_insert_quiz = [
                            'fk_curso' => $id,
                            //'percentual_acerto' => $dadosForm['quiz']['percentual_acerto']
                            'percentual_acerto' => 0
                        ];
                        $resultado_quiz = $quiz->create($array_insert_quiz);
                        if ($resultado_quiz) {
                            $quiz_id = $resultado_quiz->id;
                        }
                    } else {
                        $quiz_id = $dadosForm['quiz']['fk_quiz'];
                        $quiz_to_update = Quiz::findOrFail($quiz_id);
                        //$quiz_to_update->percentual_acerto = $dadosForm['quiz']['percentual_acerto'];
                        $quiz_to_update->percentual_acerto = 0;
                        $quiz_to_update->update();
                    }
                    $questoesids = collect($dadosForm['quiz']['questao'])->map(function ($item, $key) {
                        return $key;
                    });
                    $questoesdelete = QuizQuestao::where('fk_quiz', $quiz_id)->whereNotIn('id', $questoesids)->get();

                    foreach ($questoesdelete as $qtd) {
                        $qtd->delete();
                    }
                    foreach ($dadosForm['quiz']['questao'] as $key => $item) {
                        if ($key !== '__X__') {
                            if (isset($item['id_questao'])) {
                                $quiz_questao = QuizQuestao::findOrFail($item['id_questao']);
                                $quiz_questao->update($item);
                                foreach ($dadosForm['quiz']['op'][$key] as $label => $opcao) {
                                    if ($key !== '__X__' ) {
                                        if (isset($opcao['id_alternativa'])) {
                                            if ($opcao['alternativa']) {
                                                $quiz_resposta = QuizResposta::findOrFail($opcao['id_alternativa']);
                                                $quiz_resposta->descricao = $opcao['alternativa'];
                                                $quiz_resposta->update();
                                            } else {
                                                QuizResposta::destroy($opcao['id_alternativa']);
                                            }
                                        } else {
                                            $array_insert_quiz_resposta = [
                                                'label' => $label,
                                                'descricao' => $opcao['alternativa'],
                                                'fk_quiz_questao' => $item['id_questao']
                                            ];

                                            $quiz_resposta = new QuizResposta();
                                            $resultado_quiz_resposta = $quiz_resposta->create($array_insert_quiz_resposta);
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

                                    foreach ($dadosForm['quiz']['op'][$key] as $label => $opcao) {
                                        //if ($label !== '__X__' && !empty($opcao['alternativa'])) {
                                        if ($label !== '__X__') {
                                            $array_insert_quiz_resposta = [
                                                'label' => $label,
                                                'descricao' => $opcao['alternativa'],
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
                }
                ### fim QUIZ ###
            }

            # CONFIG REPASSE QUE NAO SERAO FEITO AUTOMATICAMENTE NA WIRECARD
            # O PERCENTUAL PARA REPASSES MANUAIS SERAO UTILIZADAS NOS RELATORIOS
            $dadosForm['professorprincipal_share_manual'] = (!isset($dadosForm['professorprincipal_share_manual'])) ? 0 : 1;
            $dadosForm['professorparticipante_share_manual'] = (!isset($dadosForm['professorparticipante_share_manual'])) ? 0 : 1;
            $dadosForm['curador_share_manual'] = (!isset($dadosForm['curador_share_manual'])) ? 0 : 1;
            $dadosForm['produtora_share_manual'] = (!isset($dadosForm['produtora_share_manual'])) ? 0 : 1;

            if (!$validator->fails()) {
                $dadosForm['duracao_total'] = str_replace(",", ".",
                    str_replace(".", "", $request->input('duracao_total')));
                $resultado = $curso->update($dadosForm);

                if ($resultado) {
                    \DB::commit();
                    \Session::flash('mensagem_sucesso', $this->msgUpdate);
                    return redirect('admin/curso/index');
                } else {
                    \DB::rollBack();
                    \Session::flash('mensagem_erro', $this->msgUpdateErro);
                    return Redirect::back()->withErrors($validator)->withInput();
                }
            } else {
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } catch (\Exception $exception) {

            \DB::rollBack();
            \Session::flash('mensagem_erro', 'Não foi possível inserir o registro! ' . $exception->getMessage());
            return Redirect::back()->withInput();
        }
    }


    public function deletar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        $obj = Curso::findOrFail($id);

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

    public function uploadFile($input, $file, $tipo = 'curso')
    {
        if ($file->isValid()) {
            $fileName = date('YmdHis') . '_' . $file->getClientOriginalName();
            if ($file->move('files/' . $tipo . '/' . $input, $fileName)) {
                return $fileName;
            }
        }

        return '';
    }

    private function __carregaCombos($id = null)
    {
        $lista_status = [
            '1' => 'Rascunho',
            '2' => 'Revisar',
            '3' => 'Não Aprovado',
            '4' => 'Aprovado',
            '5' => 'Publicado'
        ];

        $this->arrayViewData['lista_status'] = $lista_status;


        $lista_ordem = [];
        for ($i = 1; $i <= 30; $i++) {
            $lista_ordem[$i] = $i;
        }

        $this->arrayViewData['lista_ordem'] = $lista_ordem;

        if ($id) {
            $quiz = Quiz::select('quiz.*')->where('quiz.fk_curso', '=', $id)->first();
            //$quiz = Quiz::select('quiz.*')->where('quiz.fk_curso', '=', $id)->orderBy('id', 'desc')->first();

            if ($quiz) {
                $lista_questao = array();
                $lista_resposta = array();

                $quiz_questao = QuizQuestao::select('*')->where('quiz_questao.fk_quiz', '=', $quiz->id)->get();
                if (count($quiz_questao)) {

                    foreach ($quiz_questao as $key => $questao) {
                        $lista_questao[$questao->id] = $questao;

                        $quiz_resposta = QuizResposta::select('*')->where('quiz_resposta.fk_quiz_questao', '=',
                            $questao->id)->get();
                        if (count($quiz_resposta)) {
                            foreach ($quiz_resposta as $k_resposta => $resposta) {
                                $lista_resposta[$questao->id][] = $resposta;
                            }
                        }
                    }
                }

                $this->arrayViewData['quiz'] = $quiz;
                $this->arrayViewData['quiz_questao'] = $lista_questao;
                $this->arrayViewData['quiz_resposta'] = $lista_resposta;
            }
        }

        $this->arrayViewData['lista_certificados'] = CertificadoLayout::all()->where('status', '=', 1)->pluck('titulo',
            'id');

        // lista_categorias
        $lista_categorias = CursoCategoria::all()->where('status', '=', 1)->pluck('titulo', 'id');

        $this->arrayViewData['categorias'] = $lista_categorias;
        if ($id != null) {
            $lista_categorias_selecionadas = CursoCategoriaCurso::all()->where('fk_curso', '=',
                $id)->pluck('fk_curso_categoria', 'id');
            $this->arrayViewData['lista_categorias'] = $lista_categorias_selecionadas->toArray();
        }

        // lista_faculdades
        $lista_faculdades = Faculdade::all()->where('status', '=', 1)->pluck('fantasia', 'id');
        $lista_form_faculdades = array();
        $projetosSelecionados = [];
        foreach ($lista_faculdades as $fk_faculdade => $faculdade) {
            if ($id != null) {
                $faculdadeExiste = CursosFaculdades::all()->where('fk_curso', '=', $id)->where('fk_faculdade', '=',
                    $fk_faculdade)->first();
                if (isset($faculdadeExiste->id)) {
                    $projetosSelecionados[] = $faculdadeExiste->fk_faculdade;

                    $lista_form_faculdades[$fk_faculdade]['id'] = $fk_faculdade;
                    $lista_form_faculdades[$fk_faculdade]['descricao'] = $faculdade;
                    $lista_form_faculdades[$fk_faculdade]['disponibilidade_dias'] = null;
                    $lista_form_faculdades[$fk_faculdade]['duracao_dias'] = null;
                    $lista_form_faculdades[$fk_faculdade]['ativo'] = 0;
                    $lista_form_faculdades[$fk_faculdade]['ativo'] = 1;
                    $lista_form_faculdades[$fk_faculdade]['disponibilidade_dias'] = $faculdadeExiste->disponibilidade_dias;
                    $lista_form_faculdades[$fk_faculdade]['duracao_dias'] = $faculdadeExiste->duracao_dias;
                    $lista_form_faculdades[$fk_faculdade]['curso_gratis'] = $faculdadeExiste->curso_gratis;

                }
            }
        }

        $user = Usuario::find($this->userLogged->id);

        // alteraçoes passadas
        $this->arrayViewData['lista_faculdades'] = $lista_form_faculdades;
        //Listar no combo multiselect
        $this->arrayViewData['faculdades'] = $lista_faculdades;
        //valores salvos para setar no multselect
        $this->arrayViewData['projetosSelecionados'] = $projetosSelecionados;

        //certificados_cursos_faculdades
        $lista_conclusao_faculdades = array();
        foreach ($lista_faculdades as $fk_faculdade => $faculdade) {
            $lista_conclusao_faculdades[$fk_faculdade]['id'] = $fk_faculdade;
            $certificados = CertificadoLayout::where('fk_faculdade', '=', $fk_faculdade)->pluck('titulo', 'id');
            $lista_conclusao_faculdades[$fk_faculdade]['lista_certificados'] = $certificados;

            if ($id != null) {
                $conclusao = ConclusaoCursosFaculdades::all()->where('fk_curso', '=', $id)->where('fk_faculdade', '=',
                    $fk_faculdade)->first();
                if (isset($conclusao->id)) {
                    $lista_conclusao_faculdades[$fk_faculdade]['fk_certificado'] = $conclusao->fk_certificado;
                    $lista_conclusao_faculdades[$fk_faculdade]['nota_questionario'] = $conclusao->nota_quiz;
                    $lista_conclusao_faculdades[$fk_faculdade]['nota_trabalho'] = isset($this->arrayViewData['curso']) && $this->arrayViewData['curso']->trabalho == "1" ? $conclusao->nota_trabalho : "";
                    $lista_conclusao_faculdades[$fk_faculdade]['frequencia_minima'] = $conclusao->freq_minima;
                }
            }
        }

        $this->arrayViewData['lista_conclusao'] = $lista_conclusao_faculdades;

        $professores = Professor::select('professor.*')
            ->join('usuarios', 'professor.fk_usuario_id', '=', 'usuarios.id')
            ->where('professor.status', '=', 1)
            ->orderBy('professor.nome')
            ->get();

        $lista_professor = array();
        foreach ($professores as $professor) {
            $lista_professor[$professor->id] = $professor->nome . ' ' . $professor->sobrenome;
        }

        $this->arrayViewData['lista_professor'] = $lista_professor;
        $this->arrayViewData['lista_curador'] = Curador::all()->where('status', '=', 1)->pluck('razao_social', 'id');
        $this->arrayViewData['lista_produtora'] = Produtora::all()->where('status', '=', 1)->pluck('razao_social', 'id');
        $this->arrayViewData['lista_parceiro'] = Parceiro::all()->where('status', '=', 1)->pluck('razao_social', 'id');

        $this->arrayViewData['opcoes'] = array(
            '1' => 'Alternativa 01',
            '2' => 'Alternativa 02',
            '3' => 'Alternativa 03',
            '4' => 'Alternativa 04',
            '5' => 'Alternativa 05'
        );

        $lista_percentual = ['0' => '0'];
        for ($i = 5; $i <= 100; $i = $i + 5) {
            $lista_percentual[$i] = $i . ' %';
        }
        $this->arrayViewData['lista_percentual'] = $lista_percentual;

        $lista_percentual_trabalho = ['0' => '0'];
        for ($i = 5; $i <= 100; $i = $i + 5) {
            $num = $i/(float)10;
            $numLength = mb_strlen($num);
            if($numLength == 1) $lista_percentual[$i] = $num . ".0";
            else $lista_percentual[$i] = $num;
        }

        $lista_percentual_trabalho = $lista_percentual;
        unset($lista_percentual_trabalho['0']);
        $this->arrayViewData['lista_percentual_trabalho'] = $lista_percentual_trabalho;

        $this->arrayViewData['lista_tags'] = CursoTag::all()->where('status', '=', 1)->pluck('tag', 'id');
        $this->arrayViewData['lista_tipos'] = CursoTipo::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $this->arrayViewData['lista_idiomas'] = [
            'Português' => 'Português',
            'Inglês' => 'Inglês',
            'Espanhol' => 'Espanhol'
        ];
        $this->arrayViewData['lista_formatos'] = [
            'Video Aula' => 'Video Aula',
            'Conteúdo Interativo' => 'Conteúdo Interativo'
        ];
        $this->arrayViewData['userLogged'] = $user;
        $this->arrayViewData['lista_check'] = array('' => 'Selecione', '0' => 'Não', '1' => 'Sim');
    }

    public function listarCertificados($id)
    {
        $certificados = CertificadoLayout::where('fk_faculdade', '=', $id)->pluck('titulo', 'id');

        return response()->json([
            [
                '' => 'Este curso não emite certificado',
                //'0' => 'Certificado padrão da Instituição'
            ],
            [
                'Personalizado' => $certificados->toArray(),
            ]
        ]);
    }

    function aprovacao_conteudo()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['cursos'] = Curso::select('cursos.id', 'cursos.titulo', 'cursos_tipo.titulo as curso_tipo',
            'faculdades.fantasia as faculdade', 'cursos_valor.valor', 'cursos_valor.valor_de',
            'status_aprovacao_conteudo.descricao')
            ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
            ->Join('status_aprovacao_conteudo', 'status_aprovacao_conteudo.id', '=', 'cursos.status')
            ->join('faculdades', 'faculdades.id', '=', 'cursos.fk_faculdade')
            ->join('cursos_tipo', 'cursos_tipo.id', '=', 'cursos.fk_cursos_tipo')
            ->where('cursos.status', '=', StatusAprovacaoConteudo::AGUARDANDOANALISE)
            ->get();

        return view('curso.lista_aprovacao_conteudo', $this->arrayViewData);
    }

    public function generatepdf()
    {
        $cursos = Curso::select('cursos.id', 'cursos.*', 'cursos_valor.valor', 'cursos_valor.valor_de')
            ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
            ->where('cursos_valor.data_validade', null)
            ->where('cursos.status', '>', 0)
            ->get();

        $lista_faculdades = Faculdade::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $lista_tipos = CursoTipo::all()->where('status', '=', 1)->pluck('titulo', 'id');

        if (count($cursos)) {
            $html = '<table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">';
            $html .= '<thead>';
            $html .= '<th>Nome do Curso</th>';
            $html .= '<th>Carga Horária</th>';
            $html .= '<th>Preço</th>';
            $html .= '<th>Preço de Venda</th>';
            $html .= '<th>Categorias</th>';
            $html .= '<th>Inscritos</th>';
            $html .= '<th>Certificado</th>';
            $html .= '<th>Professor</th>';
            $html .= '<th>Curador</th>';
            $html .= '<th>Produtora</th>';
            $html .= '</thead>';

            $html .= '<tbody>';

            foreach ($cursos as $curso) {
                $html .= '<tr>';
                $html .= '<td>' . $curso->titulo . '</td>';
                $html .= '<td>-</td>';
                $html .= '<td>R$ ' . number_format($curso->valor_de, 2, ',', '.') . '</td>';
                $html .= '<td>R$ ' . number_format($curso->valor, 2, ',', '.') . '</td>';
                $html .= '<th>-</th>';
                $html .= '<th>-</th>';
                $html .= '<th>-</th>';
                $html .= '<th>-</th>';
                $html .= '<th>-</th>';
                $html .= '<th>-</th>';
                $html .= '</tr>';
            }

            $html .= '</tbody>';
            $html .= '</table>';
        }

        // Definimos o nome do arquivo que será exportado
        $arquivo = 'lista_cursos.xls';
    }

    /**
     * @param Request $request
     * @return Excel|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportar(Request $request)
    {
        //if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $retorno = Excel::download(new CursosExport($request->all()),
            'cursos.' . strtolower($request->get('export-to-type')) . '');
        return $retorno;

    }
}
