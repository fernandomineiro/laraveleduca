<?php

namespace App\Http\Controllers\Admin;

use App\Aluno;
use App\Assinatura;
use App\CupomAlunos;
//use App\CupomAssinaturas;
use App\CupomAlunoSemRegistro;
use App\CupomCursos;
use App\CupomCursosCategorias;
//use App\CupomEventos;
use App\CupomTrilhas;
use App\Curso;
use App\CursoCategoria;
use App\Eventos;
use App\Faculdade;
use App\Imports\PreCadastroImport;
use App\Trilha;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Yajra\DataTables\Facades\DataTables;

use App\Cupom;
use Maatwebsite\Excel\Facades\Excel;

class CupomController extends Controller
{
    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }
    
    public function getMultiFilterSelectDataCupom(Request $request) {
        $this->arrayViewData['lista_faculdades'] = Faculdade::all()
            ->where('status', '=', 1)
            ->sortBy('fantasia')
            ->pluck('fantasia', 'id')
            ->toArray();
        $this->arrayViewData['cupons'] = Cupom::all()->where('status', '=', 1);
        $this->arrayViewData['tipo_cupom'] = ['1' => 'Percentual (%)', '2' => 'Espécie (R$)'];
        
        foreach ($this->arrayViewData['cupons'] as $cupon) {
            if($cupon->fk_faculdade)
                $cupon->fk_faculdade = $this->arrayViewData['lista_faculdades'][$cupon->fk_faculdade];
            else
                $cupon->fk_faculdade = ' - ';

            $cupon->status = $cupon->status == 1 ? "Ativo" : "Inativo";
            $cupon->tipo_cupom_desconto = $this->arrayViewData['tipo_cupom'][$cupon->tipo_cupom_desconto];
        }
        
        $datatables =  Datatables::of($this->arrayViewData['cupons']);
        return $datatables->make(true);
    }

    public function getAlunosForm(Request $request) {
        $lista_alunos = Aluno::select('alunos.*', 'usuarios.email')
            ->where('alunos.nome', 'LIKE', "%{$request['buscaAluno']}%")
            ->orWhere('alunos.sobre_nome', 'LIKE', "%{$request['buscaAluno']}%")
            ->orWhere('alunos.cpf', 'LIKE', "%{$request['buscaAluno']}%")
            ->join('usuarios', 'usuarios.id', '=', 'alunos.fk_usuario_id')
            ->orWhere('usuarios.email', 'LIKE', "%{$request['buscaAluno']}%")
            ->where('alunos.status', 1)
            ->orderBy('nome', 'asc')->get();
        
        echo \GuzzleHttp\json_encode($lista_alunos);
        exit;
    }

    public function incluir()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['tipo_cupom'] = ['1' => 'Percentual (%)', '2' => 'Espécie (R$)'];

        $this->gerenciar(null);
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {

        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }
        $this->arrayViewData['cupom'] = Cupom::findOrFail($id);
        $this->arrayViewData['tipo_cupom'] = ['1' => 'Percentual (%)', '2' => 'Espécie (R$)'];

        $this->gerenciar($id);
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {
        DB::beginTransaction();
        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }
        $cupom = new Cupom();
        $validator = Validator::make($request->all(), $cupom->rules, $cupom->messages);

        if (!$validator->fails()) {

            $dadosForm = $request->except('_token');
            $dadosForm['data_validade_inicial'] = implode('-',
                array_reverse(explode('/', $dadosForm['data_validade_inicial'])));
            $dadosForm['data_validade_final'] = implode('-',
                array_reverse(explode('/', $dadosForm['data_validade_final'])));

            $dadosForm['valor'] = str_replace(',', '.', str_replace('.', '', $dadosForm['valor']));

            $dadosForm = $this->insertAuditData($dadosForm);

            $resultado = $cupom->create($dadosForm);

            if ($resultado) {
                $retorno = $this->salvarrelacionamentos($request, $resultado->id);
                if ($retorno) {
                    DB::commit();
                    \Session::flash('mensagem_sucesso', $this->msgInsert);
                    return redirect('admin/cupom/index');
                } else {
                    DB::rollBack();
                    \Session::flash('mensagem_erro', $this->msgInsertErro);
                    return Redirect::back()->withErrors($validator)->withInput();
                }
            } else {
                DB::rollBack();
                \Session::flash('mensagem_erro', $this->msgInsertErro);
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            DB::rollBack();
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }


    public function atualizar($id, Request $request)
    {
        DB::beginTransaction();
        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }
        $cupom = Cupom::findOrFail($id);
        $validator = Validator::make($request->all(), $cupom->rules, $cupom->messages);
        if (!$validator->fails()) {

            $dadosForm = $request->except('_token');
            $dadosForm['data_validade_inicial'] = implode('-',
                array_reverse(explode('/', $dadosForm['data_validade_inicial'])));
            $dadosForm['data_validade_final'] = implode('-',
                array_reverse(explode('/', $dadosForm['data_validade_final'])));

            $dadosForm['valor'] = str_replace(',', '.', $dadosForm['valor']);

            $dadosForm = $this->insertAuditData($dadosForm, false);
            $resultado = $cupom->update($dadosForm);

            if ($resultado) {
                $retorno = $this->salvarrelacionamentos($request, $id);
                if ($retorno) {
                    DB::commit();
                    \Session::flash('mensagem_sucesso', $this->msgUpdate);
                    return redirect('admin/cupom/index');
                } else {
                    DB::rollBack();
                    \Session::flash('mensagem_erro', $this->msgUpdateErro);
                    return Redirect::back()->withErrors($validator)->withInput();
                }
            } else {
                DB::rollBack();
                \Session::flash('mensagem_erro', $this->msgUpdateErro);
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            DB::rollBack();
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }

    public function deletar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        $obj = Cupom::findOrFail($id);

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

    public function gerenciar($id)
    {
        /*if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }*/

        // lista_categorias
        $this->arrayViewData['categorias'] = CursoCategoria::all()->where('status', '=', 1)->sortBy('autocomplete')->pluck('autocomplete', 'id')->toArray();

        $this->arrayViewData['lista_faculdades'] = Faculdade::all()
            ->where('status', '=', 1)
            ->sortBy('fantasia')
            ->pluck('fantasia', 'id')
            ->toArray();

        $assinaturas = Assinatura::select(['assinatura.*', 'tipo_assinatura.titulo as tipo', \DB::raw("concat(assinatura.id, ' - ', assinatura.titulo, ' - ', tipo_assinatura.titulo) as autocomplete")])
            ->join('tipo_assinatura', 'assinatura.fk_tipo_assinatura', '=', 'tipo_assinatura.id')
            ->where(['assinatura.status' => 1])
            ->get();

        $this->arrayViewData['lista_assinaturas'] = $assinaturas->pluck('autocomplete', 'id')->toArray();

        $eventos = Eventos::select(
            'eventos.id',
            'eventos.titulo',
            \DB::raw("concat(eventos.id, ' - ', eventos.titulo) as autocomplete"),
            'eventos.descricao',
            'eventos.fk_categoria',
            'cursos_categoria.titulo as categoria',
            'eventos.imagem',
            'eventos.status'
        )->join('cursos_categoria', 'cursos_categoria.id', '=', 'eventos.fk_categoria');

        $this->arrayViewData['lista_eventos'] = $eventos->pluck('autocomplete', 'id')->toArray();

        $lista_cursos = Curso::lista()->sortBy('autocomplete')->pluck('autocomplete', 'id')->toArray();


        $this->arrayViewData['lista_cursos'] = $lista_cursos;

        $this->arrayViewData['lista_trilhas'] = Trilha::searchTrilha(null)->sortBy('autocomplete')->pluck('autocomplete', 'id')->toArray();

        $lista_alunos = Aluno::select('alunos.*', 'usuarios.email')
            ->where('alunos.status', 1)
            ->join('usuarios', 'usuarios.id', '=', 'alunos.fk_usuario_id')
            ->orderBy('nome', 'asc')->get();

        $lista_alunos = $lista_alunos->each(function ($item, $key) use ($lista_alunos) {
            $autocomplete = $item->full_name . ' - ' . $item->email . ' - ' . $item->cpf;
            $lista_alunos[$key] = collect($item)->put('autocomplete', $autocomplete);
        });

        $this->arrayViewData['lista_alunos'] = $lista_alunos->pluck('autocomplete', 'id')->toArray();

        $this->arrayViewData['tipo_cupom'] = ['1' => 'Percentual (%)', '2' => 'Espécie (R$)'];

        if ($id) {
            $this->arrayViewData['cupom'] = Cupom::findOrFail($id);

            $this->retornaRelacionamentoCupons($id);
        }

        // return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.gerenciar', $this->arrayViewData);
    }

    private function retornaRelacionamentoCupons($fk_cupom) {
        $this->arrayViewData['cupom_cursos'] = CupomCursos::select(
            'cupom_cursos.*',
            'cursos.titulo as nome_curso',
            'cursos_valor.valor',
            'cupom.titulo',
            'cupom.codigo_cupom',
            'cupom.descricao',
            'cupom.data_cadastro',
            'cupom.data_validade_inicial',
            'cupom.data_validade_final',
            'cupom.tipo_cupom_desconto',
            'cupom.status',
            'cupom.valor'
        )
        ->join('cursos', 'cursos.id', '=', 'cupom_cursos.fk_curso')
        ->join('cupom', 'cupom.id', '=', 'cupom_cursos.fk_cupom')
        ->where('cupom_cursos.fk_cupom', $fk_cupom)
        ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
        ->get();

        $this->arrayViewData['cupom_alunos'] = CupomAlunos::select(
            'cupom_alunos.*',
            \DB::raw("concat(alunos.nome, ' ', alunos.sobre_nome) as aluno_nome"),
            'alunos.cpf',
            'usuarios.email',
            'cupom.titulo',
            'cupom.codigo_cupom',
            'cupom.descricao',
            'cupom.data_cadastro',
            'cupom.data_validade_inicial',
            'cupom.data_validade_final',
            'cupom.tipo_cupom_desconto',
            'cupom.status',
            'cupom.valor'
        )
        ->join('alunos', 'alunos.id', '=', 'cupom_alunos.fk_aluno')
        ->join('usuarios', 'usuarios.id', '=', 'alunos.fk_usuario_id')
        ->join('cupom', 'cupom.id', '=', 'cupom_alunos.fk_cupom')
        ->where('cupom_alunos.fk_cupom', $fk_cupom)
        ->get();

        $this->arrayViewData['cupom_trilhas'] = CupomTrilhas::select(
            'cupom_trilhas.*',
            'trilha.titulo as nome_trilha',
            'trilha.descricao',
            'trilha.valor',
            'trilha.valor_venda',
            'cupom.titulo',
            'cupom.codigo_cupom',
            'cupom.descricao',
            'cupom.data_cadastro',
            'cupom.data_validade_inicial',
            'cupom.data_validade_final',
            'cupom.tipo_cupom_desconto',
            'cupom.status',
            'cupom.valor'
        )
        ->join('trilha', 'trilha.id', '=', 'cupom_trilhas.fk_trilha')
        ->join('cupom', 'cupom.id', '=', 'cupom_trilhas.fk_cupom')
        ->where('cupom_trilhas.fk_cupom', $fk_cupom)
        ->get();

        /*$this->arrayViewData['cupom_assinaturas'] = CupomAssinaturas::select(
            'cupom_assinaturas.*',
            'assinatura.titulo as nome_assinatura',
            'assinatura.descricao',
            'assinatura.valor',
            'assinatura.valor_de',
            'tipo_assinatura.titulo as tipo',
            'cupom.titulo',
            'cupom.codigo_cupom',
            'cupom.descricao',
            'cupom.data_cadastro',
            'cupom.data_validade_inicial',
            'cupom.data_validade_final',
            'cupom.tipo_cupom_desconto',
            'cupom.status',
            'cupom.valor'
        )
        ->join('assinatura', 'assinatura.id', '=', 'cupom_assinaturas.fk_assinatura')
        ->join('cupom', 'cupom.id', '=', 'cupom_assinaturas.fk_cupom')
        ->join('tipo_assinatura', 'assinatura.fk_tipo_assinatura', '=', 'tipo_assinatura.id')
        ->where('cupom_assinaturas.fk_cupom', $fk_cupom)
        ->get();

        $this->arrayViewData['cupom_eventos'] = CupomEventos::select(
            'cupom_eventos.*',
            'eventos.titulo as nome_evento',
            'cupom.titulo',
            'cupom.codigo_cupom',
            'cupom.descricao',
            'cupom.data_cadastro',
            'cupom.data_validade_inicial',
            'cupom.data_validade_final',
            'cupom.tipo_cupom_desconto',
            'cupom.status',
            'cupom.valor'
        )
        ->join('eventos', 'eventos.id', '=', 'cupom_eventos.fk_evento')
        ->join('cupom', 'cupom.id', '=', 'cupom_eventos.fk_cupom')
        ->where('cupom_eventos.fk_cupom', $fk_cupom)
        ->get();*/

        $this->arrayViewData['cupom_cursos_categorias'] = CupomCursosCategorias::select(
            'cupom_cursos_categorias.*',
            'cursos_categoria.titulo as nome_categoria',
            'cupom.titulo',
            'cupom.codigo_cupom',
            'cupom.descricao',
            'cupom.data_cadastro',
            'cupom.data_validade_inicial',
            'cupom.data_validade_final',
            'cupom.tipo_cupom_desconto',
            'cupom.status',
            'cupom.valor'
        )
        ->join('cursos_categoria', 'cupom_cursos_categorias.fk_categoria', '=', 'cursos_categoria.id')
        ->join('cupom', 'cupom.id', '=', 'cupom_cursos_categorias.fk_cupom')
        ->where('cupom_cursos_categorias.fk_cupom', $fk_cupom)
        ->get();

        $this->arrayViewData['cupom_alunos_sem_registro'] = CupomAlunoSemRegistro::select(
            'cupom_aluno_sem_registro.*',
            'cupom.titulo',
            'cupom.codigo_cupom',
            'cupom.descricao',
            'cupom.data_cadastro',
            'cupom.data_validade_inicial',
            'cupom.data_validade_final',
            'cupom.tipo_cupom_desconto',
            'cupom.status',
            'cupom.valor'
        )
        ->join('cupom', 'cupom.id', '=', 'cupom_aluno_sem_registro.fk_cupom')
        ->where('cupom_aluno_sem_registro.fk_cupom', $fk_cupom)
        ->get();
    }

    public function salvarrelacionamentos(Request $request, $fk_cupom) {

        $resultado = $this->salvarRelacionamentosHelper(null, $request->except('_token'), $fk_cupom);
        $resultado = $resultado & $this->importarUsuariosSemCadastro($request, $fk_cupom, null);
        if ($resultado) {
            // \Session::flash('mensagem_sucesso', 'Registros adicionados com sucesso!');
            return true;
        } else {
            // \Session::flash('mensagem_erro', 'Erro ao inserir registro');
            return false;
        }
    }

    private function salvarRelacionamentosHelper($faculdade = null, $dados, $fk_cupom) {
        try {
            DB::beginTransaction();
            if (isset($dados['fk_alunos'])) {
                foreach ($dados['fk_alunos'] as $aluno) {
                    if ($aluno) {
                        $cupom_aluno = CupomAlunos::create([
                            'fk_cupom' => $fk_cupom,
                            'fk_aluno' => $aluno,
                            'fk_faculdade' => $faculdade,
                        ]);
                    }
                }
            }
            if (isset($dados['fk_curso'])) {
                foreach ($dados['fk_curso'] as $curso) {
                    if ($curso) {
                        $cupom_curso = CupomCursos::create([
                            'fk_cupom' => $fk_cupom,
                            'fk_curso' => $curso,
                            'fk_faculdade' => $faculdade,
                        ]);
                    }
                }
            }
            if (isset($dados['fk_categoria'])) {
                foreach ($dados['fk_categoria'] as $categoria) {
                    if ($categoria) {
                        $cupom_categoria = CupomCursosCategorias::create([
                            'fk_cupom' => $fk_cupom,
                            'fk_categoria' => $categoria,
                        ]);
                    }
                }
            }
            if (isset($dados['fk_trilha'])) {
                foreach ($dados['fk_trilha'] as $trilha) {
                    if ($trilha) {
                        $cupom_trilha = CupomTrilhas::create([
                            'fk_cupom' => $fk_cupom,
                            'fk_trilha' => $trilha,
                            'fk_faculdade' => $faculdade,
                        ]);
                    }
                }
            }

            /*if (isset($dados['fk_evento'])) {
                foreach ($dados['fk_evento'] as $evento) {
                    if ($evento) {
                        $cupom_evento = CupomEventos::create([
                            'fk_cupom' => $fk_cupom,
                            'fk_evento' => $evento,
                            'fk_faculdade' => $faculdade,
                        ]);
                    }
                }
            }

            if (isset($dados['fk_assinatura'])) {
                foreach ($dados['fk_assinatura'] as $assinatura) {
                    if ($assinatura) {
                        $cupom_assinatura = CupomAssinaturas::create([
                            'fk_cupom' => $fk_cupom,
                            'fk_assinatura' => $assinatura,
                            'fk_faculdade' => $faculdade,
                        ]);
                    }
                }
            }*/
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
    }

    public function deletarrelacionamentos(Request $request) {
        $result = true;
        $id = $request->get('id');
        $tipo = $request->get('tipo');
        switch ($tipo) {
            case 1:
                $result = CupomAlunos::destroy($id);
                break;
            case 2:
                $result = CupomCursos::destroy($id);
                break;
            case 3:
                $result = CupomCursosCategorias::destroy($id);
                break;
            case 4:
                $result = CupomTrilhas::destroy($id);
                break;
            case 5:
                $result = CupomAlunoSemRegistro::destroy($id);
                break;
            /*case 6:
                //$result = CupomAssinaturas::destroy($id);
                break;
            case 7:
                $result = CupomEventos::destroy($id);
                break;*/
        }

        if ($result) {
            \Session::flash('mensagem_sucesso', 'Registro excluído com sucesso!');
            return Redirect::back();
        } else {
            \Session::flash('mensagem_erro', 'Erro ao excluir registro');
            return Redirect::back()->withInput();
        }
    }

    public function importarUsuariosSemCadastro(Request $request, $id, $id_faculdade){

        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        if (!$request->file('arquivo_excel')) {
            return true;
        }

        try {

            Excel::import(new PreCadastroImport($id, $id_faculdade),$request->file('arquivo_excel'));

        } catch (\Maatwebsite\Excel\Exceptions\SheetNotFoundException $e) {

            Session::flash('mensagem_erro', $this->msgInsertErro);
            return false;

        } catch (\Exception $e) {

            Session::flash('mensagem_erro', $this->msgInsertErro);
            return false;

        }

        return true;

    }
}
