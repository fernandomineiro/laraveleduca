<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\Quiz;

use App\QuizQuestao;
use App\QuizResposta;
use App\QuizResultado;

use App\Curso;

use App\Faculdade;

class QuizController extends Controller
{
    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $this->arrayViewData['quizes'] = Quiz::select('quiz.*', 'cursos.titulo as curso_nome', 'faculdades.fantasia as faculdade_nome',
            \DB::raw('(select count(1) from quiz_questao where quiz_questao.fk_quiz = quiz.id) as qtd_questao'))
            ->join('cursos', 'quiz.fk_curso', '=', 'cursos.id')
            ->join('faculdades', 'cursos.fk_faculdade', '=', 'faculdades.id')
            ->where('quiz.status', '=', 1)
            ->get();

        $lista_percentual = ['0' => '0'];
        for ($i = 5; $i <= 100; $i = $i + 5) {
            $lista_percentual[$i] = $i . ' %';
        }

        $this->arrayViewData['lista_percentual'] = $lista_percentual;
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    public function incluir()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        
        $lista_cursos_tmp = Curso::all()->pluck('titulo', 'id');
        $lista_cursos_tmp->prepend('Selecione', 0);
        $this->arrayViewData['lista_cursos'] = $lista_cursos_tmp;

        $lista_percentual = ['0' => '0'];
        for ($i = 5; $i <= 100; $i = $i + 5) {
            $lista_percentual[$i] = $i . ' %';
        }

        $this->arrayViewData['lista_percentual'] = $lista_percentual;
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $this->arrayViewData['quiz'] = Quiz::findOrFail($id);

        $quizFaculdade= Faculdade::select('faculdades.id')
                            ->join('cursos', 'faculdades.id', '=', 'cursos.fk_faculdade')
                            ->join('quiz', 'cursos.id', '=', 'quiz.fk_curso')
                            ->where('quiz.id', '=', $id)
                            ->first();


        $cursos = Curso::select('faculdades.fantasia as nome', 'cursos.titulo', 'cursos.id')
                        ->join('faculdades', 'cursos.fk_faculdade', '=', 'faculdades.id')
                        ->where('cursos.status', '>', 0)
                        ->where('cursos.fk_faculdade', '=', $quizFaculdade['id'])
                        ->orderBy('faculdades.fantasia')
                        ->get();

        $lista_cursos = [];
        foreach($cursos as $curso) {
            $lista_cursos[$curso['id']] = $curso['nome'] . " - " . $curso['titulo'];
        }

        $this->arrayViewData['lista_cursos'] = $lista_cursos;

        $lista_percentual = ['0' => '0'];
        for ($i = 5; $i <= 100; $i = $i + 5) {
            $lista_percentual[$i] = $i . ' %';
        }

        $this->arrayViewData['lista_percentual'] = $lista_percentual;
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);
        $quiz = new Quiz();
        $validator = Validator::make($request->all(), $quiz->rules, $quiz->messages);

        if (!$validator->fails()) {
            $dadosForm = $request->except('_token');

            $dadosForm = $this->insertAuditData($dadosForm);

            $resultado = $quiz->create($dadosForm);

            if ($resultado) {
                \Session::flash('mensagem_sucesso', $this->msgInsert);
                return Redirect::back();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }

    public function atualizar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);
        $quiz = Quiz::findOrFail($id);
        $validator = Validator::make($request->all(), $quiz->rules, $quiz->messages);

        if (!$validator->fails()) {
            $dadosForm = $request->except('_token');

            $dadosForm = $this->insertAuditData($dadosForm, false);
            $resultado = $quiz->update($dadosForm);

            if ($resultado) {
                \Session::flash('mensagem_sucesso', $this->msgUpdate);
                return Redirect::back();
            } else {
                \Session::flash('mensagem_erro', $this->msgUpdateErro);
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }

    public function deletar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = Quiz::findOrFail($id);

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
}
