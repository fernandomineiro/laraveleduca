<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\QuizQuestao;

use App\Quiz;
use App\QuizResposta;

class QuizQuestaoController extends Controller
{
    public function index($id_quiz)
    {
        $quiz_questoes = QuizQuestao::select('quiz_questao.*',
            \DB::raw('(select count(1) from quiz_resposta where quiz_resposta.fk_quiz_questao = quiz_questao.id) as qtd_resposta'))
            ->join('quiz', 'quiz_questao.fk_quiz', '=', 'quiz.id')
            ->where('quiz.id', $id_quiz)
            ->get();

        $lista_status = array('0' => 'Inativo', '1' => 'Ativo');

        $respostas = [];
        foreach ($quiz_questoes as $k => $questao) {
            if ($questao->qtd_resposta) {
                $respostas[$questao->id] = QuizResposta::select('*')->where('fk_quiz_questao', $questao->id)->get();
            }
        }

        return view('quiz_questao.lista', [
            'quiz_questoes' => $quiz_questoes,
            'lista_status' => $lista_status,
            'id_quiz' => $id_quiz,
            'respostas' => $respostas
        ]);
    }

    public function incluir($id_quiz)
    {
        $lista_status = array('0' => 'Inativo', '1' => 'Ativo');

        $alternativas = [
            '1' => 'Alternativa 01',
            '2' => 'Alternativa 02',
            '3' => 'Alternativa 03',
            '4' => 'Alternativa 04',
            '5' => 'Alternativa 05'
        ];

        return view('quiz_questao.formulario', [
            'lista_status' => $lista_status,
            'id_quiz' => $id_quiz,
            'alternativas' => $alternativas
        ]);
    }

    public function editar($id)
    {
        $quiz_questao = QuizQuestao::findOrFail($id);
        $quiz_resposta = QuizResposta::select('*')->where('fk_quiz_questao', $id)->get();

        $lista_status = array('0' => 'Inativo', '1' => 'Ativo');

        $alternativas = [
            1 => 'Alternativa 01',
            2 => 'Alternativa 02',
            3 => 'Alternativa 03',
            4 => 'Alternativa 04',
            5 => 'Alternativa 05'
        ];

        $lista_respostas = array();
        foreach ($quiz_resposta as $resposta) {
            $lista_respostas[$resposta->label] = $resposta->descricao;

        }
        return view('quiz_questao.formulario', [
            'quiz_questao' => $quiz_questao,
            'lista_status' => $lista_status,
            'alternativas' => $alternativas,
            'quiz_resposta' => $quiz_resposta,
            'lista_respostas' => $lista_respostas
        ]);
    }

    public function salvar(Request $request)
    {
        $quiz_questao = new QuizQuestao();
        $validator = Validator::make($request->all(), $quiz_questao->rules, $quiz_questao->messages);

        $dados = $request->all();
        $respostas = [];
        for ($i = 1; $i <= 10; $i++) {
            if (isset($dados['op_' . $i])) {
                $respostas[$i] = $dados['op_' . $i];
                unset($dados['op_' . $i]);
            }
        }


        $dados = $this->insertAuditData($dados);

        if (!$validator->fails()) {
            $resultado = $quiz_questao->create($dados);
            $quiz_resposta = new QuizResposta();
            foreach ($respostas as $label => $resposta) {
                $quiz_resposta->create([
                    'label' => $label,
                    'descricao' => $resposta,
                    'fk_quiz_questao' => $resultado->id
                ]);
            }

            if ($resultado) {
                \Session::flash('mensagem_sucesso', 'Cadastrado com Sucesso!');
                return redirect()->route('admin.quiz_questao', $dados['fk_quiz']);
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }

    public function atualizar($id, Request $request)
    {
        $quiz_questao = QuizQuestao::findOrFail($id);
        $validator = Validator::make($request->all(), $quiz_questao->rules, $quiz_questao->messages);

        $dados = $request->all();
        $respostas = [];
        for ($i = 1; $i <= 10; $i++) {
            if (isset($dados['op_' . $i])) {
                $respostas[$i] = $dados['op_' . $i];
                unset($dados['op_' . $i]);
            }
        }

        if (!$validator->fails()) {
            $dadosForm = $request->except('_token');

            $dadosForm = $this->insertAuditData($dadosForm, false);

            $resultado = $quiz_questao->update($dadosForm);

            $quiz_resposta = new QuizResposta();
            foreach ($respostas as $label => $resposta) {

                $dados_resposta = QuizResposta::select('*')
                    ->where('fk_quiz_questao', $id)
                    ->where('label', $label)
                    ->first();

                if ($dados_resposta) {
                    if (isset($dados_resposta->descricao) && ($dados_resposta->descricao != $resposta)) {
                        $dados_resposta->descricao = $resposta;
                        $dados_resposta->save();
                    }
                }
            }

            if ($resultado) {
                \Session::flash('mensagem_sucesso', 'Atualizado com Sucesso!');
                return redirect()->route('admin.quiz_questao', $id);
            } else {
                \Session::flash('mensagem_erro', 'Não foi possível atualizar o registro!');
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }

    public function deletar($id, Request $request)
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota, passa-se false para nao carregar variaveis de views
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = QuizQuestao::findOrFail($id);

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
