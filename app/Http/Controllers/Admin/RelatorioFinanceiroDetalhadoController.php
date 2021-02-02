<?php

namespace App\Http\Controllers\Admin;

use App\Pedido;
use App\PedidoStatus;
use App\Exports\RelatorioVendasExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Faculdade;
use App\CursoTipo;
use App\Exports\RelatorioFinanceiroDetalhadoExport;
use App\Impostos;
use Illuminate\Support\Facades\DB;

class RelatorioFinanceiroDetalhadoController extends Controller{

    public function index(Request $request){
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        
        $parametros = $this->processaRequest($request);
        if ($request->input('export') == 1){
            $currentDate = (new Carbon)->format('Ymdhis');

            return (new RelatorioFinanceiroDetalhadoExport($parametros))->download('relatorio_financeirodetalhado_'.$currentDate.'.xlsx');
        }

        $this->arrayViewData['pedidos'] = $this->listaRelatorioFinanceiro($parametros)->paginate(4);

        $this->arrayViewData['table'] = view('relatorio.financeirodetalhado.table_pedidos', $this->arrayViewData);

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
        $parametros['orderby'] = 'pedidos.id';
        $parametros['sort'] = 'DESC';
        
        if($request->get('orderby') && $request->get('sort')){
            $parametros['orderby'] = $request->get('orderby');
            $parametros['sort'] = $request->get('sort');
        }
        
        if( $request->has('pedido_pid') && !empty($request->get('pedido_pid')) ){
            $parametros['pedido_pid'] = $request->get('pedido_pid');
        }

        if ($request->has('produto_pago') && !is_null($request->get('produto_pago'))) {
            $parametros['produto_pago'] = $request->get('produto_pago');
        }
        
        if( $request->has('pedidos_status') && !empty($request->get('pedidos_status')) ){
            $parametros['pedidos_status'] = $request->get('pedidos_status');
        }
        
        if( $request->has('ies') && !empty($request->get('ies')) ){
            $parametros['ies'] = $request->get('ies');
        }
        
        if( $request->has('nome_item') && !empty($request->get('nome_item')) ){
            $parametros['nome_item'] = $request->get('nome_item');
        }
        
        if( $request->has('nome_professor') && !empty($request->get('nome_professor')) ){
            $parametros['nome_professor'] = $request->get('nome_professor');
        }
        
        if( $request->has('nome_produtora') && !empty($request->get('nome_produtora')) ){
            $parametros['nome_produtora'] = $request->get('nome_produtora');
        }
        
        if( $request->has('nome_curador') && !empty($request->get('nome_curador')) ){
            $parametros['nome_curador'] = $request->get('nome_curador');
        }
        
        if( $request->has('tipo_item') && !empty($request->get('tipo_item')) ){
            $parametros['tipo_item'] = $request->get('tipo_item');
        }
        
        if( $request->has('data_compra') && !empty($request->get('data_compra')) ){
            $explode = explode('-', $request->get('data_compra'));
            $parametros['data_compra'] = [Carbon::createFromFormat('d/m/Y',trim($explode[0]))->format('Y-m-d'),Carbon::createFromFormat('d/m/Y',trim($explode[1]))->format('Y-m-d')];
        } elseif ($request->has('data_compra_de') && !is_null($request->get('data_compra_de')) && $request->has('data_compra_ate') && !is_null($request->get('data_compra_ate'))) {
            $parametros['data_compra'] = [(new Carbon($request->get('data_compra_de')))->format('Y-m-d'), (new Carbon($request->get('data_compra_ate')))->format('Y-m-d')];
        } else {
            $parametros['data_compra'] = [Carbon::today()->subDay(30)->format('Y-m-d'), Carbon::today()->format('Y-m-d')];
        }
        
        if( $request->has('aluno') && !empty($request->get('aluno')) ){
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
            ->join('faculdades', 'faculdades.id', 'pedidos.fk_faculdade')
            ->leftjoin('pagamento', 'pedidos.id', 'pagamento.fk_pedido')
            ->leftjoin('nfe', 'pedidos.id', 'nfe.fk_pedido')
            ->select(
                'pedidos.pid as pedido_pid',
                'pedidos.metodo_pagamento as pedido_metodo_pagamento',
                'pedidos_status.titulo as pedido_status',
                'faculdades.razao_social as faculdade_nome',
                'alunos.id as aluno_id',
                'alunos.nome as aluno_nome',
                'alunos.cpf as aluno_cpf',
                'alunos.sobre_nome as aluno_sobrenome',
                'pedidos.criacao as data_venda',
                'pagamento.parcelas as parcelas',
                'nfe.recebido as recebido',
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

        if (isset($parametros['pedidos_status']) && !empty($parametros['pedidos_status'])) {
            $query->where('pedidos.status', $parametros['pedidos_status']);
        }

        if (isset($parametros['ies']) && !empty($parametros['ies']) && is_array($parametros['ies'])) {
            $query->whereIn('faculdades.id', $parametros['ies']);
        } elseif (isset($parametros['ies']) && !empty($parametros['ies']) && !is_array($parametros['ies'])) {
            $query->where('faculdades.id', $parametros['ies']);
        }

        if (isset($parametros['nome_item']) && !empty($parametros['nome_item'])) {
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

        if (isset($parametros['tipo_item']) && !empty($parametros['tipo_item'])) {
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
            $query->where(function ($query) use ($parametros) {
                $query->whereNotNull('pedidos_item.fk_curso')
                    ->whereIn('pedidos_item.fk_curso', [DB::raw("(SELECT cursos.id FROM cursos INNER JOIN professor ON professor.id = cursos.fk_professor WHERE professor.nome LIKE '%".$parametros['nome_professor']."%' OR professor.sobrenome LIKE '%".$parametros['nome_professor']."%')")]);
            });
        }

        if (isset($parametros['nome_produtora']) && !empty($parametros['nome_produtora'])) {
            $query->where(function ($query) use ($parametros) {
                $query->whereNotNull('pedidos_item.fk_curso')
                    ->whereIn('pedidos_item.fk_curso', [DB::raw("(SELECT cursos.id FROM cursos INNER JOIN produtora ON produtora.id = cursos.fk_produtora WHERE produtora.fantasia LIKE '%".$parametros['nome_produtora']."%')")]);
            });
        }

        if(isset($parametros['nome_curador']) && !empty($parametros['nome_curador'])){
            $query->where(function ($query) use ($parametros) {
                $query->whereNotNull('pedidos_item.fk_curso')
                    ->whereIn('pedidos_item.fk_curso', [DB::raw("(SELECT cursos.id FROM cursos INNER JOIN curadores ON curadores.id = cursos.fk_curador WHERE curadores.nome_fantasia LIKE '%".$parametros['nome_curador']."%')")]);
            });
        }

        $parametros['data_compra'][0] = $parametros['data_compra'][0]. ' 00:00:00';
        $parametros['data_compra'][1] = $parametros['data_compra'][1]. ' 23:59:59';
        if(isset($parametros['data_compra']) && !empty($parametros['data_compra'])){
            $query->whereBetween('pedidos.criacao', $parametros['data_compra']);
        }

        if(isset($parametros['aluno']) && !empty($parametros['aluno'])){
            $query->where(function ($query) use ($parametros) {
                $query->where('aluno_nome', 'like', '%'.$parametros['aluno'].'%')
                    ->orWhere('aluno_sobrenome', 'like', '%'.$parametros['aluno'].'%');
            });
        }

        if (isset($parametros['cursos_ids']) && !is_null($parametros['cursos_ids'])) {
            $query->whereIn('pedidos_item.fk_curso', $parametros['cursos_ids']);
        }

        $query->orderBy($parametros['orderby'], $parametros['sort']);

        return $query;
    }

    private function caseValorBruto()
    {
        return "case 
            when pedidos.metodo_pagamento = 'gratis' and ((pedidos.valor_bruto != 0 and pedidos.valor_desconto = 0) || (pedidos.valor_bruto = 0 and pedidos.valor_desconto = 0)) then 'Grátis'
            else concat('R$ ', format(pedidos_item.valor_bruto, 2, 'pt_BR')) end as valor_bruto, 
            concat('R$ ', format(pedidos_item.valor_bruto * 5 / 100, 2, 'pt_BR')) as valor_iss, 
            concat('R$ ', format(pedidos_item.valor_bruto * 3.65 / 100, 2, 'pt_BR')) as valor_pis, 
            concat('R$ ', format(pedidos_item.valor_bruto * 7.5 / 100, 2, 'pt_BR')) as valor_irpj, 
            concat('R$ ', format(pedidos_item.valor_bruto * 3.99 / 100, 2, 'pt_BR')) as tarifa_cartao, 
            concat('R$ ', format(3, 2, 'pt_BR')) as tarifa_boleto, concat('R$ ', format(1, 2, 'pt_BR')) as tarifa_processamento, 
            concat('R$ ', format(pedidos_item.valor_bruto-2-(
                        IF(pedidos.metodo_pagamento = 'boleto', 3,
                        IF(pedidos.metodo_pagamento = 'cartao', pedidos_item.valor_bruto * 3.99 / 100,0)))
                        -(pedidos_item.valor_bruto * 5 / 100)
                        -(pedidos_item.valor_bruto * 3.65 / 100)
                        -(pedidos_item.valor_bruto * 7.5 / 100), 2, 'pt_BR')) as valor_liquido,
            concat('R$ ', format(pedidos_item.valor_bruto / IF(pagamento.parcelas,pagamento.parcelas,2), 2, 'pt_BR')) as valor_parcela,
            concat('R$ ', format((pedidos_item.valor_bruto-(IF(pedidos.metodo_pagamento = 'boleto', 3,IF(pedidos.metodo_pagamento = 'cartao', pedidos_item.valor_bruto * 3.99 / 100,0)))-(pedidos_item.valor_bruto * 5 / 100)-(pedidos_item.valor_bruto * 3.65 / 100)-(pedidos_item.valor_bruto * 7.5 / 100)-2) / pagamento.parcelas, 2, 'pt_BR')) as valor_liquido_parcela";
    }

    private function caseFormaPagamento()
    {
        return "case 
            when pedidos.metodo_pagamento = 'boleto' then 'Boleto Bancário' 
            when pedidos.metodo_pagamento = 'debito_itau' then 'Débito' 
            when pedidos.metodo_pagamento = 'cartao' then 'Cartão de Crédito' 
            when pedidos.metodo_pagamento = 'credito' then 'Cartão de Crédito' 
            when pedidos.metodo_pagamento = 'gratis' then 'Grátis' 
            else null end as metodo_pagamento";
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
            else concat('R$ ', format(pedidos_item.valor_bruto - (pedidos_item.valor_bruto * (pedidos.valor_desconto / pedidos.valor_bruto)), 2,'pt_BR')) end as valor_pago";
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
            when pedidos.fk_cupom is not null then case when pedidos.tipo_cupom_desconto = 1 then concat(pedidos.valor_cupom, '%') when pedidos.tipo_cupom_desconto = 2 then concat('R$ ', pedidos.valor_cupom) else null end
            else null end as cupom_valor";
    }
    
}
