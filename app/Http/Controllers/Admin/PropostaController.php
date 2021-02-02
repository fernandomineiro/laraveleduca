<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\Proposta;
use App\PropostaStatus;
use App\Professor;

class PropostaController extends Controller
{

    public function index()
    {

        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado

        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['propostas'] = Proposta::all()->where('status', '=', 1);
        $this->arrayViewData['lista_status'] = PropostaStatus::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $this->arrayViewData['lista_professor'] = $this->carregaComboProfessor();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    public function incluir()
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado

        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['lista_status'] = PropostaStatus::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $this->arrayViewData['lista_professor'] = $this->carregaComboProfessor();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }


    public function editar($id)
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado

        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['propostas'] = Proposta::findOrFail($id);
        $this->arrayViewData['lista_status'] = PropostaStatus::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $this->arrayViewData['lista_professor'] = $this->carregaComboProfessor();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado

        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $proposta = new Proposta();
        $validator = Validator::make($request->all(), $proposta->rules, $proposta->messages);

        if (!$validator->fails()) {
            $params = $request->all();
            $proposta->titulo = $params['titulo'];
            $proposta->descricao = $params['descricao'];
            $proposta->url_video = $params['url_video'];
            $proposta->duracao_total = $params['duracao_total'];
            $proposta->fk_professor = $params['fk_professor'];
            $proposta->fk_proposta_status = $params['fk_proposta_status'];
            $proposta->local = $params['local'];
            $proposta->sugestao_categoria = $params['sugestao_categoria'];
            $params['sugestao_preco'] = str_replace('.', '', $params['sugestao_preco']);
            $params['sugestao_preco'] = str_replace(',', '.', $params['sugestao_preco']);
            $proposta->sugestao_preco = $params['sugestao_preco'];

            $proposta->criacao = date('Y-m-d H:i:s');
            $proposta->atualizacao = date('Y-m-d H:i:s');
            $proposta->status = 1;
            $proposta->fk_criador_id = $this->userLogged->id;
            $proposta->fk_atualizador_id = $this->userLogged->id;

            $resultado = $proposta->save();
            if ($resultado) {
                \Session::flash('mensagem_sucesso', $this->msgInsert);
                // return redirect()->action('Admin\PropostaController@editar', ['id'=> $proposta->id ]);
                return Redirect::back();
            } else {
                \Session::flash('mensagem_erro', $this->msgInsertErro);
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }


    public function atualizar($id, Request $request)
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado

        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $proposta = Proposta::findOrFail($id);
        $validator = Validator::make($request->all(), $proposta->rules, $proposta->messages);
        if (!$validator->fails()) {
            $params = $request->all();
            $params['sugestao_preco'] = str_replace('.', '', $params['sugestao_preco']);
            $params['sugestao_preco'] = str_replace(',', '.', $params['sugestao_preco']);
            $params = $this->insertAuditData($params, false);

            $resultado = $proposta->update($params);
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

    public function carregaComboProfessor()
    {
        return Professor::select(DB::raw("CONCAT(nome,' ', sobrenome) AS nome"), 'id')
            ->where('status', '=', 1)
            ->pluck('nome', 'id');
    }

    public function deletar($id, Request $request)
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado

        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = Proposta::findOrFail($id);


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
