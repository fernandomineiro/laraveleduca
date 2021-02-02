<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\Curso;

use App\Professor;
use App\Usuario;
use App\Pergunta;
use App\PerguntaResposta;

class TutoriaController extends Controller
{
    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $id_professor = 1;

        $perguntas = Curso::select(
            'cursos.id', 
            'cursos.*', 
            'cursos_valor.valor', 
            'cursos_valor.valor_de', 
            'faculdades.fantasia as faculdade_nome',
            'usuarios.nome as nome_professor',
            'pergunta.id as pergunta_id',
            'pergunta.pergunta' 
        )->join('faculdades', 'faculdades.id', '=', 'cursos.fk_faculdade')
        ->join('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
        ->join('professor', 'cursos.fk_professor', '=', 'professor.id')
        ->join('usuarios', 'usuarios.id', '=', 'professor.fk_usuario_id')
        ->join('pergunta', 'pergunta.fk_curso', '=', 'cursos.id')
        //->where('cursos_valor.data_validade', null)
        ->where('cursos.status', '>', 0)
        ->where('cursos.fk_professor', $id_professor)
        ->get();

        $lista_perguntas = array();

        foreach($perguntas as $pergunta) {
            $lista_perguntas[$pergunta['pergunta_id']] = $pergunta->toArray();

            $resposta = PerguntaResposta::select('*')->where('fk_pergunta', '=', $pergunta['pergunta_id'])->get();
            $lista_perguntas[$pergunta['pergunta_id']]['resposta'] = $resposta->toArray();
        }

        $this->arrayViewData['perguntas'] = $lista_perguntas;
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.index', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'),false)) return redirect()->route($this->redirecTo);

        $dadosForm = $request->except('_token');
        $dadosForm['status'] = 1;

        $perguntaResposta = new PerguntaResposta();
        $validator = Validator::make($dadosForm, $perguntaResposta->rules, $perguntaResposta->messages);
        if (!$validator->fails()) {
            $dadosForm = $this->insertAuditData($dadosForm, false);
            if ($resultado = $perguntaResposta->create($dadosForm)) {
                \Session::flash('mensagem_sucesso', $this->msgInsert);
                return Redirect::back();
            } else {
                \Session::flash('mensagem_erro', $this->msgInsertErro);
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }
    
    public function deletar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

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
}
