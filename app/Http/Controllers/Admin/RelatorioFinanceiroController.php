<?php

namespace App\Http\Controllers\Admin;

use App\Curso;
use App\Pedido;
use App\PedidoItem;
use App\PedidoStatus;
use App\Exports\RelatorioVendasExport;
use App\Http\Controllers\Controller;
use App\Professor;
use App\TrilhaCurso;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Faculdade;
use App\CursoTipo;
use App\Exports\RelatorioFinanceiroExport;
use App\Impostos;
use Illuminate\Support\Facades\DB;

class RelatorioFinanceiroController extends Controller{

    public function index(Request $request){
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        
        $parametros = $this->processaRequest($request);

        if ($request->input('export') == 1){
            $currentDate = (new Carbon)->format('Ymdhis');

            return (new RelatorioFinanceiroExport($parametros))->download('relatorio_financeiro_'.$currentDate.'.xlsx');
        }

        $this->arrayViewData['pedidos'] = $this->listaRelatorioFinanceiro($parametros)->paginate(10);
        
        if($this->arrayViewData['pedidos']) {
            $model_trilha = new TrilhaCurso();
            $model_pedido_item = new PedidoItem();
            
            $this->arrayViewData['pedidos']->map(function($item) use($model_trilha, $model_pedido_item) {
                if($item->fk_trilha != null) {
                    $trilha_dados = $model_trilha->lista($item->fk_trilha);
                    if($trilha_dados) {
                        foreach ($trilha_dados as $professor) {
                            if($professor) {
                                if(isset($item->nome_professor)) {
                                    $item->professor_nome .= ' -- ' . $professor->nome_professor . ' ' . $professor->sobrenome_professor;
                                } else {
                                    $item->professor_nome = $professor->nome_professor . ' ' . $professor->sobrenome_professor;
                                }
                            }
                        }
                    }
                } 
                elseif ($item->fk_trilha == null) {
                    $professor = $model_pedido_item->where('fk_pedido', '=', $item->pedido_id)
                        ->join('cursos', 'cursos.id', 'pedidos_item.fk_curso')
                        ->join('professor', 'professor.id', 'cursos.fk_professor')
                        ->select('professor.*')
                        ->where('cursos.id', '=', $item->fk_curso)
                        ->first();
//                    dd($item, $professor);
                    if($professor) {
                        if(isset($item->professor_nome)) {
                            $item->professor_nome .= ' -- ' . $professor->nome . ' ' . $professor->sobrenome;
                        } else {
                            $item->professor_nome = $professor->nome . ' ' . $professor->sobrenome;
                        }
                    } else {
                        $item->professor_nome = '--';
                    }
                } else {
                    $item->professor_nome = '--';
                }
                
                
                if(!isset($item->professor_nome)) {
                    $item->professor_nome = '---';
                }
                
                return $item;
            });
        }
        
        $this->arrayViewData['table'] = view('relatorio.financeiro.table_pedidos', $this->arrayViewData);

        $this->arrayViewData['pedidos_status'] = PedidoStatus::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $this->arrayViewData['pedidos_status']->prepend('Selecione', '0');
        
        $this->arrayViewData['faculdades'] = Faculdade::all()->where('status', '=', 1)->pluck('razao_social', 'id');
        $this->arrayViewData['faculdades']->prepend('Selecione', '0');
        
        $this->arrayViewData['tipos_item'] = CursoTipo::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $this->arrayViewData['tipos_item']->prepend('Assinatura','ASSINATURA');
        $this->arrayViewData['tipos_item']->prepend('Trilha','TRILHA');
        $this->arrayViewData['tipos_item']->prepend('Evento', 'EVENTO');
        $this->arrayViewData['tipos_item']->prepend('Selecione', '0');

        $this->arrayViewData['produtoPago'] = ['' => 'Selecione', 'true' => 'Pago', 'false' => 'Gratuíto'];
        
        $this->arrayViewData['data_compra'] = $parametros['data_compra'];
        $this->arrayViewData['model_trilha_curso'] = new TrilhaCurso();
        $this->arrayViewData['model_curso'] = new Curso();
        
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }
    
    public function salvar(Request $request){
        
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        
        $parametros = $this->processaRequest($request);
        
        return (new RelatorioVendasExport($parametros))->download('invoices.xlsx');
        
    }
    
    public function processaRequest($request)
    {    
        $parametros = [];
        $parametros['orderby'] = 'pedidos.criacao';
        $parametros['sort'] = 'DESC';
        
        if($request->get('orderby') && $request->get('sort')){
            $parametros['orderby'] = $request->get('orderby');
            $parametros['sort'] = $request->get('sort');
        }
        
        if( $request->has('pedido_pid') && !is_null($request->get('pedido_pid')) && $request->get('pedido_pid') != 'null' ){
            $parametros['pedido_pid'] = $request->get('pedido_pid');
        }

        if ($request->has('produto_pago') && !is_null($request->get('produto_pago')) && $request->get('produto_pago') != 'null') {
            $parametros['produto_pago'] = $request->get('produto_pago');
        }
        
        if( $request->has('pedidos_status') && !is_null($request->get('pedidos_status')) && $request->get('pedidos_status') != 'null'){
            $parametros['pedidos_status'] = $request->get('pedidos_status');
        }
        
        if( $request->has('ies') && !is_null($request->get('ies')) && $request->get('ies') != 'null'){
            $parametros['ies'] = $request->get('ies');
        }
        
        if( $request->has('nome_item') && !is_null($request->get('nome_item')) && $request->get('nome_item') != 'null'){
            $parametros['nome_item'] = $request->get('nome_item');
        }
        
        if( $request->has('nome_professor') && !is_null($request->get('nome_professor')) && $request->get('nome_professor') != 'null'){
            $parametros['nome_professor'] = $request->get('nome_professor');
        }
        
        if( $request->has('nome_produtora') && !is_null($request->get('nome_produtora')) && $request->get('nome_produtora') != 'null'){
            $parametros['nome_produtora'] = $request->get('nome_produtora');
        }
        
        if( $request->has('nome_curador') && !is_null($request->get('nome_curador')) && $request->get('nome_curador') != 'null'){
            $parametros['nome_curador'] = $request->get('nome_curador');
        }
        
        if( $request->has('tipo_item') && !is_null($request->get('tipo_item')) && $request->get('nome_curador') != 'null'){
            $parametros['tipo_item'] = $request->get('tipo_item');
        }
        
        if( $request->has('data_compra') && !is_null($request->get('data_compra')) ){
            $explode = explode('-', $request->get('data_compra'));
            $parametros['data_compra'] = [Carbon::createFromFormat('d/m/Y',trim($explode[0]))->format('Y-m-d'),Carbon::createFromFormat('d/m/Y',trim($explode[1]))->format('Y-m-d')];
        } elseif ($request->has('data_compra_de') && !is_null($request->get('data_compra_de')) && $request->has('data_compra_ate') && !is_null($request->get('data_compra_ate'))) {
            $parametros['data_compra'] = [(new Carbon($request->get('data_compra_de')))->format('Y-m-d'), (new Carbon($request->get('data_compra_ate')))->format('Y-m-d')];
        } else {
            $parametros['data_compra'] = [Carbon::today()->subDay(30)->format('Y-m-d'), Carbon::today()->format('Y-m-d')];
        }
        
        if( $request->has('aluno') && !is_null($request->get('aluno')) && $request->get('nome_curador') != 'null'){
            $parametros['aluno'] = $request->get('aluno');
        }
        
        return $parametros;
    }

    public function delete(){
        echo '<pre style="background-color: #fff;">'; print_r("xxx"); echo '</pre>'; exit();
    }

    public function listaRelatorioFinanceiro($parametros)
    {
        $query = DB::table('pedidos_item')
            ->join('pedidos', 'pedidos.id', 'pedidos_item.fk_pedido')
            ->join('pedidos_status', 'pedidos_status.id', 'pedidos.status')
            ->join('alunos', 'pedidos.fk_usuario', 'alunos.fk_usuario_id')
            ->join('usuarios','usuarios.id','alunos.fk_usuario_id')
            ->join('faculdades', 'faculdades.id', 'pedidos.fk_faculdade')
//            ->leftJoin('trilha_curso', 'trilha_curso.fk_trilha', 'pedidos_item.fk_trilha')
//            ->join('cursos', 'cursos.id', 'pedidos_item.fk_curso')
//            ->join('professor','professor.id','cursos.fk_professor')
//            ->orderByDesc('pedidos.pid')
            ->select(
                'usuarios.email',
                'pedidos.id as pedido_id',
                'pedidos.pid as pedido_pid',
                'pedidos_status.titulo as pedido_status',
                'faculdades.razao_social as faculdade_nome',
                'alunos.id as aluno_id',
                'alunos.nome as aluno_nome',
                'alunos.cpf as aluno_cpf',
                'alunos.sobre_nome as aluno_sobrenome',
                'pedidos.criacao as data_venda',
                
                'pedidos_item.fk_trilha',
                'pedidos_item.fk_curso',
//                'trilha_curso.fk_curso as trilha_fk_curso',
                DB::raw($this->caseValorBruto()),
                DB::raw($this->caseFormaPagamento()),
                DB::raw($this->subQueryProdutoNome()),
                DB::raw($this->caseValorPago()),
                DB::raw($this->subQueryCupomCodigo()),
                DB::raw($this->subQueryCupomValor())
            );

        if (isset($parametros['pedido_pid']) && !empty($parametros['pedido_pid'])) {
            $query->where('pedidos.pid', 'like', '%'.$parametros['pedido_pid'].'%');
        }

        if (isset($parametros['produto_pago']) && !is_null($parametros['produto_pago']) && $parametros['produto_pago'] == 'true') {
            $query->where('pedidos.metodo_pagamento', '<>', 'gratis');
        } elseif (isset($parametros['produto_pago']) && !is_null($parametros['produto_pago']) && $parametros['produto_pago'] == 'false') {
            $query->where('pedidos.metodo_pagamento', 'gratis');
        }

        if (isset($parametros['pedidos_status']) && !is_null($parametros['pedidos_status']) && $parametros['pedidos_status'] != '0') {
            $query->where('pedidos.status', $parametros['pedidos_status']);
        }

        if (isset($parametros['ies']) && !is_null($parametros['ies']) && is_array($parametros['ies']) && $parametros['ies'] != '0') {
            $query->whereIn('faculdades.id', $parametros['ies']);
        } elseif (isset($parametros['ies']) && !empty($parametros['ies']) && !is_array($parametros['ies'])) {
            $query->where('faculdades.id', $parametros['ies']);
        }

        if (isset($parametros['nome_item']) && !is_null($parametros['nome_item'])) {
            $query->where(function ($query) use ($parametros) {
                $query->where(function ($query) use ($parametros) {
                    $query->whereNotNull('pedidos_item.fk_curso')
                        ->whereIn('pedidos_item.fk_curso', [DB::raw("(SELECT id FROM cursos WHERE cursos.titulo LIKE '%".$parametros['nome_item']."%')")]);
                })
                ->orWhere(function ($query) use ($parametros) {
                    $query->whereNotNull('pedidos_item.fk_evento')
                        ->whereIn('pedidos_item.fk_evento', [DB::raw("(SELECT id FROM eventos WHERE eventos.titulo LIKE '%".$parametros['nome_item']."%')")]);
                })
                ->orWhere(function ($query) use ($parametros) {
                    $query->whereNotNull('pedidos_item.fk_trilha')
                        ->whereIn('pedidos_item.fk_trilha', [DB::raw("(SELECT id FROM trilha WHERE trilha.titulo LIKE '%".$parametros['nome_item']."%')")]);
                })
                ->orWhere(function ($query) use ($parametros) {
                    $query->whereNotNull('pedidos_item.fk_assinatura')
                        ->whereIn('pedidos_item.fk_assinatura', [DB::raw("(SELECT id FROM assinatura WHERE assinatura.titulo LIKE '%".$parametros['nome_item']."%')")]);
                });
            });
        }

        if (isset($parametros['tipo_item']) && !is_null($parametros['tipo_item']) && $parametros['tipo_item'] != '0') {
            if ($parametros['tipo_item'] == 'EVENTO') {
                $query->whereNotNull('pedidos_item.fk_evento');
            } else if($parametros['tipo_item'] == 'TRILHA') {
                $query->whereNotNull('pedidos_item.fk_trilha');
            } else if($parametros['tipo_item'] == 'ASSINATURA') {
                $query->whereNotNull('pedidos_item.fk_assinatura');
            } else {
                $query->where(function ($query) use ($parametros) {
                    $query->whereNotNull('pedidos_item.fk_curso')
                        ->whereIn('pedidos_item.fk_curso', [DB::raw("(SELECT cursos.id FROM cursos WHERE cursos.fk_cursos_tipo = ".$parametros['tipo_item'].")")]);
                });
            }
        }

        if (isset($parametros['nome_professor']) && !is_null($parametros['nome_professor'])) {
            $query->leftJoin('trilha_curso', 'trilha_curso.fk_trilha', 'pedidos_item.fk_trilha');
        
            $query->where(function ($query) use ($parametros) {
                $query->where(function ($query) use ($parametros) {
                    $query->whereNotNull('pedidos_item.fk_curso');
                    $query->where(function ($query) use ($parametros) {
                            $query->whereIn('pedidos_item.fk_curso', [DB::raw("(SELECT cursos.id FROM cursos INNER JOIN professor ON professor.id = cursos.fk_professor WHERE professor.nome LIKE '%" . $parametros['nome_professor'] . "%' OR professor.sobrenome LIKE '%" . $parametros['nome_professor'] . "%')")]);
                    });
                });

                $query->orwhere(function ($query) use ($parametros) {
                    $query->whereNotNull('pedidos_item.fk_trilha');
                    $query->where(function ($query) use ($parametros) {
                            $query->whereIn('trilha_curso.fk_curso', [DB::raw("(SELECT cursos.id FROM cursos INNER JOIN professor ON professor.id = cursos.fk_professor WHERE professor.nome LIKE '%" . $parametros['nome_professor'] . "%' OR professor.sobrenome LIKE '%" . $parametros['nome_professor'] . "%')")]);
                    });
                });
            });

        }
        
        if (isset($parametros['nome_produtora']) && !is_null($parametros['nome_produtora'])) {
            $query->where(function ($query) use ($parametros) {
                $query->whereNotNull('pedidos_item.fk_curso')
                    ->whereIn('pedidos_item.fk_curso', [DB::raw("(SELECT cursos.id FROM cursos INNER JOIN produtora ON produtora.id = cursos.fk_produtora WHERE produtora.fantasia LIKE '%".$parametros['nome_produtora']."%')")]);
            });
        }

        if(isset($parametros['nome_curador']) && !is_null($parametros['nome_curador'])){
            $query->where(function ($query) use ($parametros) {
                $query->whereNotNull('pedidos_item.fk_curso')
                    ->whereIn('pedidos_item.fk_curso', [DB::raw("(SELECT cursos.id FROM cursos INNER JOIN curadores ON curadores.id = cursos.fk_curador WHERE curadores.nome_fantasia LIKE '%".$parametros['nome_curador']."%')")]);
            });
        }
        
        $parametros['data_compra'][0] = $parametros['data_compra'][0]. ' 00:00:00';
        $parametros['data_compra'][1] = $parametros['data_compra'][1]. ' 23:59:59';
        if(isset($parametros['data_compra']) && !is_null($parametros['data_compra'])){
            $query->whereBetween('pedidos.criacao', $parametros['data_compra']);
        }

        if(isset($parametros['aluno']) && !is_null($parametros['aluno'])){
            $query->where(function ($query) use ($parametros) {
                $query->where('aluno_nome', 'like', '%'.$parametros['aluno'].'%')
                    ->orWhere('aluno_sobrenome', 'like', '%'.$parametros['aluno'].'%');
            });
        }

        if (isset($parametros['cursos_ids']) && !is_null($parametros['cursos_ids'])) {
            $query->whereIn('pedidos_item.fk_curso', $parametros['cursos_ids']);
        }

//        $query->orderBy($parametros['orderby'], $parametros['sort']);

        $query->orderBy('pedidos.pid');
//        $query->latest('pedidos.pid');
        return $query;
    }
    

    private function caseValorBruto()
    {
        return "case 
            when pedidos.metodo_pagamento = 'gratis' and ((pedidos.valor_bruto != 0 and pedidos.valor_desconto = 0) || (pedidos.valor_bruto = 0 and pedidos.valor_desconto = 0)) then 'Grátis'
            else concat('', format(pedidos_item.valor_bruto, 2, 'pt_BR')) end as valor_bruto";
    }

    private function caseFormaPagamento()
    {
        return "case 
            when pedidos.metodo_pagamento = 'boleto' then 'Boleto Bancário' 
            when pedidos.metodo_pagamento = 'debito_itau' then 'Débito' 
            when pedidos.metodo_pagamento = 'cartao' then 'Cartão de Crédito' 
            else null end as forma_pagamento";
    }

    private function subQueryProdutoNome()
    {
        return "case
            when pedidos_item.fk_curso is not null then (select cursos.titulo from cursos where cursos.id = pedidos_item.fk_curso)
            when pedidos_item.fk_evento is not null then (select eventos.titulo from eventos where eventos.id = pedidos_item.fk_evento)
            when pedidos_item.fk_trilha is not null then (select trilha.titulo from trilha where trilha.id = pedidos_item.fk_trilha)
            when pedidos_item.fk_assinatura is not null then (select assinatura.titulo from assinatura where assinatura.id = pedidos_item.fk_assinatura)
            else null end as produto_nome";
    }


    private function caseValorPago()
    {
        return "case 
            when pedidos.metodo_pagamento = 'gratis' and ((pedidos.valor_bruto != 0 and pedidos.valor_desconto = 0) || (pedidos.valor_bruto = 0 and pedidos.valor_desconto = 0)) then 'Grátis' 
            else concat('', format(pedidos_item.valor_bruto - (pedidos_item.valor_bruto * (pedidos.valor_desconto / pedidos.valor_bruto)), 2,'pt_BR')) end as valor_pago";
    }

    private function subQueryCupomCodigo()
    {
        return "case 
            when pedidos.fk_cupom is not null then pedidos.codigo_cupom
            else null end as cupom_codigo";
    }

    private function subQueryCupomValor()
    {
        return "case 
            when pedidos.fk_cupom is not null then case when pedidos.tipo_cupom_desconto = 1 then concat(pedidos.valor_cupom, '%') when pedidos.tipo_cupom_desconto = 2 then concat('', pedidos.valor_cupom) else null end
            else null end as cupom_valor";
    }
    
}
