<?php

namespace App\Http\Controllers\Admin;

use App\AssinaturaConteudo;
use App\Curador;
use App\Exports\TrilhasExport;
use App\PedidoItem;
use App\Produtora;
use App\Professor;
use App\TrilhaQuiz;
use App\TrilhaQuizQuestao;
use App\TrilhaQuizResposta;
use App\TrilhasCategoria;
use App\TrilhasFaculdades;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\Trilha;
use App\TrilhaCurso;
use App\Faculdade;
use App\CursoCategoria;
use App\CertificadoLayout;
use Maatwebsite\Excel\Facades\Excel;

class TrilhaController extends Controller
{
    public function index(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $trilhas = Trilha::trilhasLista($request->all());

        $this->arrayViewData['trilhas'] = $trilhas;
        $this->__carregaCombos();
        $this->arrayViewData['lista_faculdades'] = Faculdade::all()->where('status', '=', 1)->pluck('fantasia', 'id')->toArray();
        $this->arrayViewData['lista_categorias'] = CursoCategoria::all()->where('status', '=', 1)->pluck('titulo', 'id')->toArray();

        Trilha::verificarSlugsNaoCadastrados();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    public function incluir()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->__carregaCombos();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }


    public function editar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $this->__carregaCombos($id);
        $this->arrayViewData['trilha'] = Trilha::findOrFail($id);
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    private function __carregaCombos($id = null) {

        $lista_faculdades = Faculdade::all()->where('status', '=', 1)->pluck('fantasia', 'id')->toArray();
        $lista_form_faculdades = array();
        foreach($lista_faculdades as $fk_faculdade => $faculdade) {
            $lista_form_faculdades[$fk_faculdade]['id'] = $fk_faculdade;
            $lista_form_faculdades[$fk_faculdade]['descricao'] = $faculdade;
            $lista_form_faculdades[$fk_faculdade]['ativo'] = 0;
            if($id != null) {
                $faculdadeExiste = TrilhasFaculdades::all()->where('fk_trilha', '=', $id)->where('fk_faculdade', '=', $fk_faculdade)->first();
                if(isset($faculdadeExiste->id)) {
                    $lista_form_faculdades[$fk_faculdade]['ativo'] = 1;
                    $lista_form_faculdades[$fk_faculdade]['gratis'] = $faculdadeExiste->gratis;
                }
            }
        }
        if($id) {
            $lista_categorias_selecionadas = TrilhasCategoria::all()->where('fk_trilha', '=', $id)->pluck('fk_trilha', 'fk_categoria');
            $quiz = TrilhaQuiz::select('trilha_quiz.*')->where('trilha_quiz.fk_trilha', '=', $id)->first();

            if($quiz) {
                $lista_questao = array();
                $lista_resposta = array();

                $quiz_questao = TrilhaQuizQuestao::select('*')->where('trilha_quiz_questao.fk_trilha_quiz', '=', $quiz->id)->get();
                if(count($quiz_questao)) {

                    foreach($quiz_questao as $key => $questao) {
                        $lista_questao[$questao->id] = $questao;

                        $quiz_resposta = TrilhaQuizResposta::select('*')->where('trilha_quiz_resposta.fk_trilha_quiz_questao', '=', $questao->id)->get();
                        if(count($quiz_resposta)) {
                            foreach($quiz_resposta as $k_resposta => $resposta) {
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
        $this->arrayViewData['lista_faculdades'] = $lista_form_faculdades;
        $lista_categorias = CursoCategoria::all()->where('status', '=', 1)->pluck('titulo', 'id')->toArray();
        $lista_form_categorias = array();
        foreach($lista_categorias as $fk_categoria => $categoria) {
            $lista_form_categorias[$fk_categoria]['descricao'] = $categoria;
            $lista_form_categorias[$fk_categoria]['ativo'] = 0;

            if($id != null) {
                if(isset($lista_categorias_selecionadas[$fk_categoria])) {
                    $lista_form_categorias[$fk_categoria]['ativo'] = 1;
                }
            }
        }
        $lista_percentual = ['0' => '0'];
        for ($i = 5; $i <= 100; $i = $i + 5) {
            $lista_percentual[$i] = $i . ' %';
        }
        $this->arrayViewData['lista_percentual'] = $lista_percentual;
        $this->arrayViewData['lista_status'] = [
            '1' => 'Rascunho',
            '2' => 'Revisar',
            '3' => 'Não Aprovado',
            '4' => 'Aprovado',
            '5' => 'Publicado'
        ];

        $this->arrayViewData['lista_categorias'] = $lista_form_categorias;
        $this->arrayViewData['lista_certificados'] = CertificadoLayout::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $professores = Professor::select('professor.*')
            ->join('usuarios', 'professor.fk_usuario_id', '=', 'usuarios.id')
            ->where('professor.status', '=', 1)
            ->get();

        $lista_professor = array();
        foreach($professores as $professor) {
            $lista_professor[$professor->id] = $professor->nome .  ' ' .$professor->sobrenome;
        }

        $this->arrayViewData['lista_professor'] = $lista_professor;

        $this->arrayViewData['lista_curador'] = Curador::all()->where('status', '=', 1)
            ->pluck('razao_social', 'id');

        $this->arrayViewData['lista_produtora'] = Produtora::all()->where('status', '=', 1)
            ->pluck('razao_social', 'id');

        $this->arrayViewData['opcoes'] = array(
            '1' => 'Alternativa 01',
            '2' => 'Alternativa 02',
            '3' => 'Alternativa 03',
            '4' => 'Alternativa 04',
            '5' => 'Alternativa 05'
        );

    }

    public function salvar(Request $request)
    {
        try {
            \DB::beginTransaction();
            if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
            $trilha = new Trilha();
            $validator = Validator::make($request->all(), $trilha->rules, $trilha->messages);

            if (!$validator->fails()) {
                $dados = $request->except('fk_curso');

                if ($request->hasFile('imagem')) {
                    $file = $request->file('imagem');
                    $dados['imagem'] = $this->uploadFile('imagem', $file);
                }

                $dados['status'] = 1;
                $dados['valor'] = isset($dados['valor']) ? str_replace(",", ".", str_replace(".", "", $dados['valor'])) : '';
                $dados['valor_venda'] = isset($dados['valor_venda']) ? str_replace(",", ".", str_replace(".", "", $dados['valor_venda'])) : '';

                $dados['slug_trilha'] = Trilha::configurarSlug($dados['titulo']);

                $dados = $this->insertAuditData($dados);
                $resultado = $trilha->create($dados);

                if ($resultado) {
                    $cursos = $request->get('fk_curso');
                    if (isset($cursos) && count($cursos)) {
                        $trilhacurso = new TrilhaCurso();
                        foreach ($cursos as $curso) {
                            $array = [
                                'fk_curso' => $curso,
                                'fk_trilha' => $resultado->id
                            ];

                            $array = $this->insertAuditData($array, true);
                            $resultado_tc = $trilhacurso->create($array);
                            if (!$resultado_tc) {
                                \DB::rollBack();
                                \Session::flash('mensagem_erro', $this->msgInsertErro);
                                return Redirect::back()->withErrors($validator)->withInput();
                            }
                        }
                    }
                    if (isset($dados['fk_faculdade']) && count($dados['fk_faculdade'])) {
                        foreach ($dados['fk_faculdade'] as $faculdade) {
                            if (isset($faculdade['fk_faculdade'])) {
                                $array = [
                                    'fk_trilha' => $resultado->id,
                                    'fk_faculdade' => $faculdade['fk_faculdade'],
                                    'gratis' => (!empty($dados['gratis'][$faculdade['fk_faculdade']])) ? 1 : 0,
                                ];
                                // inserir aqui model nova após criação da tabela no banco
                                $trilhaFaculdade = new TrilhasFaculdades();
                                $trilhaFaculdade->create($array);
                                unset($trilhaFaculdade);
                            }
                        }
                    }

                    if (isset($dados['fk_categoria']) && count($dados['fk_categoria'])) {
                        foreach ($dados['fk_categoria'] as $id_categoria => $categoria) {
                            $array = [
                                'fk_trilha' => $resultado->id,
                                'fk_categoria' => $id_categoria
                            ];

                            $trilhacategoria = new TrilhasCategoria();

                            $trilhacategoria->create($array);
                            unset($trilhacategoria);
                        }

                    }

                    \DB::commit();
                    \Session::flash('mensagem_sucesso', $this->msgInsert);
                    return redirect('admin/trilha/index');
                } else {
                    \DB::rollBack();
                    \Session::flash('mensagem_erro', $this->msgInsertErro);
                    return Redirect::back()->withErrors($validator)->withInput();
                }
            } else {
                \DB::rollBack();
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } catch(\Exception $exception) {
            \DB::rollBack();
            \Session::flash('mensagem_erro', 'Não foi possível inserir o registro! ' . $exception->getMessage());
            return Redirect::back()->withInput();
        }
    }

    public function atualizar($id, Request $request)
    {
        try {
            if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

            $trilha = Trilha::findOrFail($id);
            $validator = Validator::make($request->all(), $trilha->rules, $trilha->messages);

            $dadosForm = $request->except('_token', 'nomecurso', 'categoria', 'professor');
            $dadosForm['valor'] = isset($dadosForm['valor']) ? str_replace(",", ".", str_replace(".", "", $dadosForm['valor'])) : '';
            $dadosForm['valor_venda'] = isset($dadosForm['valor_venda']) ? str_replace(",", ".", str_replace(".", "", $dadosForm['valor_venda'])) : '';

            //$dadosForm = $this->insertAuditData($dadosForm, false);

            $dadosForm['slug_trilha'] = Trilha::configurarSlug($dadosForm['titulo']);

            if (!$validator->fails()) {
                if ($request->hasFile('imagem')) {
                    $file = $request->file('imagem');
                    $dadosForm['imagem'] = $this->uploadFile('imagem', $file);
                }
                $resultado = $trilha->update($dadosForm);

                if ($resultado) {
                    $faculdadeExistentes = TrilhasFaculdades::all()->where('fk_trilha', '=', $id);
                    foreach ($faculdadeExistentes as $key => $item) {
                        TrilhasFaculdades::where('id', $item['id'])->delete();
                    }

                    if (isset($dadosForm['fk_faculdade']) && count($dadosForm['fk_faculdade'])) {
                        foreach ($dadosForm['fk_faculdade'] as $faculdade) {
                            if (isset($faculdade['fk_faculdade'])) {
                                $array = [
                                    'fk_trilha' => $id,
                                    'fk_faculdade' => $faculdade['fk_faculdade'],
                                    'gratis' => (!empty($dadosForm['gratis'][$faculdade['fk_faculdade']])) ? 1 : 0
                                ];

                                // inserir aqui model nova após criação da tabela no banco
                                $trilhaFaculdade = new TrilhasFaculdades();
                                $trilhaFaculdade->create($array);
                            }
                        }
                    }

                    //deleta categorias e cria de novo
                    $lista_cursos_selecionados = TrilhasCategoria::all()->where('fk_trilha', '=', $id);
                    foreach ($lista_cursos_selecionados as $key => $item) {
                        TrilhasCategoria::where('id', $item['id'])->delete();
                    }
                    if (isset($dadosForm['fk_categoria']) && count($dadosForm['fk_categoria'])) {
                        foreach ($dadosForm['fk_categoria'] as $id_categoria => $categoria) {
                            $array = [
                                'fk_trilha' => $id,
                                'fk_categoria' => $id_categoria
                            ];

                            $array = $this->insertAuditData($array, false);

                            $trilhacategoria = new TrilhasCategoria();
                            $trilhacategoria->create($array);
                        }
                    }

                    if (isset($dadosForm['fk_curso']) && count($dadosForm['fk_curso'])) {
                        $cursos = $dadosForm['fk_curso'];
                        foreach ($cursos as $curso) {
                            $trilhacurso = TrilhaCurso::where('fk_trilha', $id)->where('fk_curso', $curso)->first();
                            if (!$trilhacurso) {
                                $array = [
                                    'fk_curso' => $curso,
                                    'fk_trilha' => $id
                                ];
                                $array = $this->insertAuditData($array, true);
                                $resultado_tc = TrilhaCurso::create($array);
                                if (!$resultado_tc) {
                                    \DB::rollBack();
                                    \Session::flash('mensagem_erro', $this->msgInsertErro);
                                    return Redirect::back()->withErrors($validator)->withInput();
                                }
                            }
                        }
                        $trilhacursos = TrilhaCurso::where('fk_trilha', $id)->whereNotIn('fk_curso', $cursos)->get();
                        foreach ($trilhacursos as $trc) $trc->delete();
                    }

                    \DB::commit();
                    \Session::flash('mensagem_sucesso', $this->msgUpdate);
                    return redirect('admin/trilha/index');
                } else {
                    \DB::rollBack();
                    \Session::flash('mensagem_erro', $this->msgUpdateErro);
                    return Redirect::back()->withErrors($validator)->withInput();
                }
            } else {
                \DB::rollBack();
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } catch(\Exception $exception) {
            \DB::rollBack();
            \Session::flash('mensagem_erro', 'Não foi possível inserir o registro! ' . $exception->getMessage());
            return Redirect::back()->withInput();
        }
    }

    public function deletar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = Trilha::findOrFail($id);

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

    public function uploadFile($input, $file, $tipo = 'trilha')
    {
        if ($file->isValid()) {
            $fileName = date('YmdHis') . '_' . $file->getClientOriginalName();
            if ($file->move('files/' . $tipo . '/' . $input, $fileName)) {
                return $fileName;
            }
        }

        return '';
    }

    /**
     * @param Request $request
     * @return Excel|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportar(Request $request) {
        //if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $retorno = Excel::download(new TrilhasExport($request->all()), 'trilhas.'.strtolower($request->get('export-to-type')).'');
        return $retorno;

    }

}

