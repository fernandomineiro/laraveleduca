<?php

namespace App\Http\Controllers\API;

use App\Curador;
use App\Parceiro;
use App\Pedido;
use App\Produtora;
use App\Professor;
use App\Faculdade;
use App\Assinatura;
use App\Helper\EducazMail;
use App\Helper\AssinaturaHelper;
use App\Http\Controllers\Controller;
use App\ViewUsuarios;
use Carbon\Carbon;
use Spatie\Analytics\Period;
use Tymon\JWTAuth\Facades\JWTAuth;
use DB;
use Illuminate\Http\Request;
use App\UsuariosAssinaturasHistorico;

class RelatoriosGraficosController extends Controller{

    public function __construct() {
        \Config::set('jwt.user', 'App\ViewUsuarios');
        \Config::set('auth.providers.users.model', ViewUsuarios::class);

        parent::__construct();
    }

    private $tipo_perfil;
    private $tipos_perfil_parceiros = [Produtora::PERFIL_NOME,Professor::PERFIL_NOME,Curador::PERFIL_NOME,Parceiro::PERFIL_NOME];
    private $parametros = [];
    private $data;

    public function index($mes, $ano, $ies = false, $export = false){
        try {
            # 2020 FIXO PROVISORIAMENTE SERA ADICIONA FILTRO POR ANO NO FRONT
            $this->processaRequest(['mes' => $mes, 'ano' => '2020', 'ies' => $ies], JWTAuth::user());
            $this->data = "".$ano."-".$mes."";

            $dados = [];
            $dados['fatura'] = $this->processaTotalFatura(Pedido::acesso_restrito_fatura($this->parametros)->get());

            if ($export){
                $this->export($dados['fatura'], $ies);
            }

            $dados['vendas'] = $this->processaTotalVendas(Pedido::acesso_restrito_vendas($this->parametros)->get());
            $dados['acessos'] = $this->processaAcessos();

            return response()->json([
                'items' => $dados,
                'count' => count($dados),
                'parametros' => $this->parametros
            ]);

        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu ('.$e->getMessage().'). O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    private function processaAcessos(){

        $data = explode('-',$this->data);
        $analyticsStartDate = Carbon::createFromDate($data[0], $data[1])->startOfMonth();
        $analyticsEndDate = Carbon::createFromDate($data[0], $data[1])->endOfMonth();

        $dadosAnalytics = \Analytics::performQuery(
            Period::create($analyticsStartDate,$analyticsEndDate),
            'ga:sessions',
            [
                'metrics' => 'ga:sessions, ga:pageviews',
                'dimensions' => 'ga:day'
            ]
        );

        $dados = [];
        foreach($dadosAnalytics->rows as $valor){
            $dados[(int)$valor[0]] = [
                'pageviews'   => $valor[2]
            ];
        }

        ksort($dados);

        return $dados;

    }

    private function processaTotalFatura($pedidos){

        $dadosFatura = [];
        $totalFatura = 0;

        foreach ($pedidos as $pedido) {

            $dadosFatura[] = [
                'curso'         => $pedido->curso,
                'publicacao'    => $pedido->publicacao,
                'vendas'        => $pedido->vendas,
                'valor'         => (float)$pedido->valor,
                'impostos'      => (float)$pedido->impostos,
                'total_receber' => (float)$pedido->total_receber
            ];

            $totalFatura = $totalFatura + $pedido->total_receber;
        }

        $fatura = [];
        $fatura['totalFatura'] = (float)$totalFatura;
        $fatura['cursos'] = $dadosFatura;

        return $fatura;

    }

    private function processaTotalVendas($pedidos){
        $dadosVendasPorDia = [];
        $totalVendas = 0;

        foreach ($pedidos as $pedido) {
            $key = (int) Carbon::createFromFormat("d/m/Y", $pedido->data)->format("j");

            if(key_exists($key, $dadosVendasPorDia)){

                $tmp = [
                    'curso' => $pedido->curso,
                    'valor' => (float)$pedido->valor
                ];

                $atual = $dadosVendasPorDia[$key];

                $dadosVendasPorDia[$key] = [];
                $dadosVendasPorDia[$key][] = $tmp;
                $dadosVendasPorDia[$key][] = $atual;

            }else{

                $dadosVendasPorDia[$key] = [
                    'curso' => $pedido->curso,
                    'valor' => (float)$pedido->valor
                ];

            }

            $totalVendas = $totalVendas + $pedido->valor;
        }

        // Usado para gerar o array com todos os dias do mês que veio do Front

        $start = Carbon::parse($this->data)->startOfMonth();
        $end = Carbon::parse($this->data)->endOfMonth();

        while ($start->lte($end)) {
            if(!array_key_exists($start->format("j"),$dadosVendasPorDia)){
                $dadosVendasPorDia[$start->format("j")] = 0;
            }
            $start->addDay();
        }

        // Reaordenar o array pela chave
        ksort($dadosVendasPorDia);

        $vendas = [];
        $vendas['totalVendas'] = (float)number_format($totalVendas, 2);
        $vendas['vendasPorDia'] = $dadosVendasPorDia;

        return $vendas;

    }

    private function processaRequest($data, $usuario = null){
        if (isset($data['mes'])) {
            $this->parametros['mes'] = $data['mes'];
        }

        if (isset($data['ano'])) {
            $this->parametros['ano'] = $data['ano'];
        }

        if (isset($data['ies']) ) {
            $this->parametros['ies'] = $data['ies'];
        }

        if (!is_null($usuario)) {

            if (!empty($usuario->fk_faculdade_id) && $usuario->fk_perfil == 2) {
                $this->parametros['ies'] = $usuario->fk_faculdade_id;
            }

            // Produtora
            if (!is_null($usuario->fk_perfil) && $usuario->fk_perfil == Produtora::ID_PERFIL) {
                $this->tipo_perfil = Produtora::PERFIL_NOME;
                $produtora = Produtora::where('fk_usuario_id', '=', $usuario->id)->first();
                $this->parametros['produtora_id'] = !is_null($produtora) ? $produtora->id : null;
            }

            // Professor
            if (!is_null($usuario->fk_perfil) && $usuario->fk_perfil == Professor::ID_PERFIL) {
                $this->tipo_perfil = Professor::PERFIL_NOME;
                $professor = Professor::where('fk_usuario_id', '=', $usuario->id)->first();
                $this->parametros['professor_id'] = !is_null($professor) ? $professor->id : null;
            }

            // Curador
            if (!is_null($usuario->fk_perfil) && $usuario->fk_perfil == Curador::ID_PERFIL) {
                $this->tipo_perfil = Curador::PERFIL_NOME;
                $curador = Curador::where('fk_usuario_id', '=', $usuario->id)->first();
                $this->parametros['curador_id'] = !is_null($curador) ? $curador->id : null;
            }

            // Parceiro
            if (!is_null($usuario->fk_perfil) && $usuario->fk_perfil == Parceiro::ID_PERFIL) {
                $this->tipo_perfil = Parceiro::PERFIL_NOME;
                $parceiro = Parceiro::where('fk_usuario_id', '=', $usuario->id)->first();
                $this->parametros['parceiro_id'] = !is_null($parceiro) ? $parceiro->id : null;
            }

        } else {
            throw new \Exception('Usuário não autenticado.');
        }
    }

    public function graficoComparativoFaturamento(Request $Request){
        $user = JWTAuth::user();

        if (!$Request->header('Faculdade')){
            return response()->json(['success' => false, 'error' => 'Faculdade inválida.']);
        }

        if (!empty($user) && isset($user->fk_perfil)){
            $filters = array_merge($Request->all(), $this->getParceiro($user));
            $filters['fk_faculdade'] = $Request->header('Faculdade');
            
            $filters['periodo'][0] = ($Request->input('data_inicial')) ? $Request->input('data_inicial') : '2019-01-01';
            $filters['periodo'][1] = ($Request->input('data_final')) ? $Request->input('data_final') : date('Y-m-d');
            $filters['agrupar_por'] = ($Request->input('agrupar_por')) ? $Request->input('agrupar_por') : 'semana';
        } else {
            return response()->json(['success' => false, 'error' => 'Usuário não encontrado.']);
        }

        $data = Pedido::relatorio_comparativo_faturamento($filters)->get();

        if (isset($data[0]->fk_faculdade) && $data[0]->fk_faculdade == 7){
            foreach ($data as $key => &$faturamento) {
                $totals = Pedido::getTotalsEducaz(explode(",", $faturamento->ids_pedidos));
                $repasses = Pedido::getTotalRepassesParceiros(explode(",", $faturamento->ids_pedidos));
                
                $faturamento->unidades = (int) $faturamento->unidades;
                $faturamento->faturamento =  (float) $totals->faturamento;
                $faturamento->impostos_taxas =  (float) $totals->impostos_taxas;
                $faturamento->repasse =  (float) $repasses->total; 
                $faturamento->liquido =  max((float) number_format($totals->valor_liquido - $repasses->total, 2), 0); 
                $faturamento->semana = (int) $faturamento->semana;

                unset($faturamento->liquido_com_repasses);
                unset($faturamento->total_repasses);
                unset($data[0]->fk_faculdade);
                unset($faturamento->ids_pedidos);
            }
        } else {
            foreach ($data as $key => &$faturamento) {
                $faturamento->unidades = (int) $faturamento->unidades;
                $faturamento->faturamento =  (float) $faturamento->faturamento;
                $faturamento->impostos_taxas =  (float) $faturamento->impostos_taxas;
                $faturamento->liquido =  max((float) $faturamento->liquido, 0);
                $faturamento->semana = (int) $faturamento->semana;

                unset($faturamento->ids_pedidos);
            }
        }

        return response()->json(['success' => true, 'data' => $data->toArray()]);
    }

    public function graficoFaturamentoPorProfessor(Request $Request){
        $user = JWTAuth::user();

        if (!$Request->header('Faculdade')){
            return response()->json(['success' => false, 'error' => 'Faculdade inválida.']);
        }

        $perfils_ies = [10, 22, 2]; # PERFIS DE GESTOR IES E FINANCEIRO IES 
        if (isset($user->fk_perfil) && in_array($user->fk_perfil, $perfils_ies)){
            $filters['fk_faculdade'] = $Request->header('Faculdade');
            
            $filters['periodo'][0] = ($Request->input('data_inicial')) ? $Request->input('data_inicial') : '2019-01-01';
            $filters['periodo'][1] = ($Request->input('data_final')) ? $Request->input('data_final') : date('Y-m-d');
            $filters['agrupar_por'] = ($Request->input('agrupar_por')) ? $Request->input('agrupar_por') : 'semana';
            $filters['fk_professor'] = ($Request->input('fk_professor')) ? $Request->input('fk_professor') : false;
        } else {
            return response()->json(['success' => false, 'error' => 'Usuário não encontrado.']);
        }

        $data = Pedido::relatorio_faturamento_por_professor($filters)->get();

        if (isset($data[0]->fk_faculdade) && $data[0]->fk_faculdade == 7){
            foreach ($data as $key => &$faturamento) {
                $totals = Pedido::getTotalsEducaz(explode(",", $faturamento->ids_pedidos));
                $repasses = Pedido::getTotalRepassesParceiros(explode(",", $faturamento->ids_pedidos));
                
                $faturamento->unidades = (int) $faturamento->unidades;
                $faturamento->faturamento =  (float) $totals->faturamento;
                $faturamento->impostos_taxas =  (float) $totals->impostos_taxas;
                $faturamento->repasse =  (float) $repasses->total; 
                $faturamento->liquido =  max((float) number_format($totals->valor_liquido - $repasses->total, 2), 0); 
                $faturamento->semana = (int) $faturamento->semana;

                unset($faturamento->liquido_com_repasses);
                unset($faturamento->total_repasses);
                unset($data[0]->fk_faculdade);
                unset($faturamento->ids_pedidos);
            }
        } else {
            foreach ($data as $key => &$faturamento) {
                $faturamento->unidades = (int) $faturamento->unidades;
                $faturamento->faturamento =  (float) $faturamento->faturamento;
                $faturamento->impostos_taxas =  (float) $faturamento->impostos_taxas;
                $faturamento->liquido =  max((float) $faturamento->liquido, 0);
                $faturamento->semana = (int) $faturamento->semana;

                unset($faturamento->ids_pedidos);
            }
        }

        return response()->json(['success' => true, 'data' => $data->toArray()]);
    }
    
    public function graficoFaturamentoPorCategoria(Request $Request){
        $user = JWTAuth::user();

        if (!$Request->header('Faculdade')){
            return response()->json(['success' => false, 'error' => 'Faculdade inválida.']);
        }

        $perfils_ies = [10, 22, 2]; # PERFIS DE GESTOR IES E FINANCEIRO IES 
        if (isset($user->fk_perfil) && in_array($user->fk_perfil, $perfils_ies)){
            $filters['fk_faculdade'] = $Request->header('Faculdade');
            
            $filters['periodo'][0] = ($Request->input('data_inicial')) ? $Request->input('data_inicial') : '2019-01-01';
            $filters['periodo'][1] = ($Request->input('data_final')) ? $Request->input('data_final') : date('Y-m-d');
            $filters['agrupar_por'] = ($Request->input('agrupar_por')) ? $Request->input('agrupar_por') : 'semana';
            $filters['fk_categoria'] = ($Request->input('fk_categoria')) ? $Request->input('fk_categoria') : false;
        } else {
            return response()->json(['success' => false, 'error' => 'Usuário não encontrado.']);
        }

        $data = Pedido::relatorio_faturamento_por_categoria($filters)->get();

        if (isset($data[0]->fk_faculdade) && $data[0]->fk_faculdade == 7){
            foreach ($data as $key => &$faturamento) {
                $totals = Pedido::getTotalsEducaz(explode(",", $faturamento->ids_pedidos), $filters);
                $repasses = Pedido::getTotalRepassesParceiros(explode(",", $faturamento->ids_pedidos), $filters);
                
                $faturamento->unidades = (int) $faturamento->unidades;
                $faturamento->faturamento = (float) $totals->faturamento;
                $faturamento->impostos_taxas = (float) $totals->impostos_taxas;
                $faturamento->repasse = (float) $repasses->total; 
                $faturamento->liquido = max((float) number_format($totals->valor_liquido - $repasses->total, 2), 0); 
                $faturamento->semana = (int) $faturamento->semana;

                unset($faturamento->liquido_com_repasses);
                unset($faturamento->total_repasses);
                unset($data[0]->fk_faculdade);
                unset($faturamento->ids_pedidos);
            }
        } else {
            foreach ($data as $key => &$faturamento) {
                $faturamento->unidades = (int) $faturamento->unidades;
                $faturamento->faturamento =  (float) $faturamento->faturamento;
                $faturamento->impostos_taxas =  (float) $faturamento->impostos_taxas;
                $faturamento->liquido =  max((float) $faturamento->liquido, 0);
                $faturamento->semana = (int) $faturamento->semana;

                unset($faturamento->ids_pedidos);
            }
        }

        return response()->json(['success' => true, 'data' => $data->toArray()]);
    }
    
    public function graficoAssinaturasCanceladas(Request $Request){
        $user = JWTAuth::user();

        if (!$Request->header('Faculdade')){
            return response()->json(['success' => false, 'error' => 'Faculdade inválida.']);
        }

        $perfils_ies = [10, 22, 2]; # PERFIS DE GESTOR IES E FINANCEIRO IES 
        if (isset($user->fk_perfil) && in_array($user->fk_perfil, $perfils_ies)){
            $filters['fk_faculdade'] = $Request->header('Faculdade');
            
            $filters['periodo'][0] = ($Request->input('data_inicial')) ? $Request->input('data_inicial') : '2019-01-01';
            $filters['periodo'][1] = ($Request->input('data_final')) ? $Request->input('data_final') : date('Y-m-d');
            $filters['agrupar_por'] = ($Request->input('agrupar_por')) ? $Request->input('agrupar_por') : 'semana';
        } else {
            return response()->json(['success' => false, 'error' => 'Usuário não encontrado.']);
        }

        $data = Assinatura::relatorio_assinaturas_canceladas($filters)->get();

        return response()->json(['success' => true, 'data' => $data->toArray()]);
    }
   
    public function graficoAssinaturasAbandonadas(Request $Request){
        $user = JWTAuth::user();

        if (!$Request->header('Faculdade')){
            return response()->json(['success' => false, 'error' => 'Faculdade inválida.']);
        }

        $perfils_ies = [10, 22, 2]; # PERFIS DE GESTOR IES E FINANCEIRO IES 
        if (isset($user->fk_perfil) && in_array($user->fk_perfil, $perfils_ies)){
            $filters['fk_faculdade'] = $Request->header('Faculdade');
            
            $filters['periodo'][0] = ($Request->input('data_inicial')) ? $Request->input('data_inicial') : '2019-01-01';
            $filters['periodo'][1] = ($Request->input('data_final')) ? $Request->input('data_final') : date('Y-m-d');
            $filters['agrupar_por'] = ($Request->input('agrupar_por')) ? $Request->input('agrupar_por') : 'semana';
        } else {
            return response()->json(['success' => false, 'error' => 'Usuário não encontrado.']);
        }

        $data = Assinatura::relatorio_assinaturas_abandonadas($filters)->get();

        return response()->json(['success' => true, 'data' => $data->toArray()]);
    }

    private function getParceiro($user){
        if (!empty($user->fk_perfil)){
            switch ($user->fk_perfil) {
                case '1':
                    $parceiro = Professor::select('id')->where('fk_usuario_id', $user->id)->first();

                    if (!empty($parceiro->id)){
                        return ['fk_professor' => $parceiro->id];
                    }
                break;
                case '4':
                    $parceiro = Curador::select('id')->where('fk_usuario_id', $user->id)->first();

                    if (!empty($parceiro->id)){
                        return ['fk_curador' => $parceiro->id];
                    }
                break;
                case '5':
                    $parceiro = Produtora::select('id')->where('fk_usuario_id', $user->id)->first();

                    if (!empty($parceiro->id)){
                        return ['fk_produtora' => $parceiro->id];
                    }
                break;
                
                # PERFIS DE GESTOR IES E FINANCEIRO IES 
                case 10:
                case 22:
                case 2:
                    return [];
                break;
            }
        }
    }

    public function totalAssinantesAtivos(Request $Request){
        $user = JWTAuth::user();

        if (!$Request->header('Faculdade')){
            return response()->json(['success' => false, 'error' => 'Faculdade inválida.']);
        }

        $data = [];
        $perfils_ies = [10, 22, 2]; # PERFIS DE GESTOR IES E FINANCEIRO IES 
        if (!empty($user) && in_array($user->fk_perfil, $perfils_ies)){    
            $filters = $Request->all();

            $AssinaturaHelper = new AssinaturaHelper();
            $AssinaturaHelper->atualizarAssinantesAtivos();

            $query = UsuariosAssinaturasHistorico::select('mes', 'ano', 'total', 'tipo');

            if (isset($filters['mes'])){
                $query->where('mes', (int)$filters['mes']);
            }

            if (isset($filters['ano'])){
                $query->where('ano', (int)$filters['ano']);
            }

            $data = $query->get();
        } else {
            return response()->json(['success' => false, 'error' => 'Usuário não encontrado ou sem permissão para acesso.']);
        }

        return response()->json(['success' => true, 'data' => $data->toArray()]);
    }

    public function graficoAssinaturasRealizadas(Request $Request){
        $user = JWTAuth::user();

        if (!$Request->header('Faculdade')){
            return response()->json(['success' => false, 'error' => 'Faculdade inválida.']);
        }

        $perfils_ies = [10, 22, 2]; # PERFIS DE GESTOR IES E FINANCEIRO IES 
        if (isset($user->fk_perfil) && in_array($user->fk_perfil, $perfils_ies)){
            $filters['fk_faculdade'] = $Request->header('Faculdade');
            
            $filters['periodo'][0] = ($Request->input('data_inicial')) ? $Request->input('data_inicial') : '2019-01-01';
            $filters['periodo'][1] = ($Request->input('data_final')) ? $Request->input('data_final') : date('Y-m-d');
            $filters['agrupar_por'] = ($Request->input('agrupar_por')) ? $Request->input('agrupar_por') : 'semana';
            $filters['fk_categoria'] = ($Request->input('fk_categoria')) ? $Request->input('fk_categoria') : false;
        } else {
            return response()->json(['success' => false, 'error' => 'Usuário não encontrado.']);
        }

        $data = Pedido::relatorio_assinaturas_realizadas($filters)->get();


        return response()->json(['success' => true, 'data' => $data->toArray()]);
    }

    public function graficoPedidoPagamentoReprovado(Request $Request){
        $user = JWTAuth::user();

        if (!$Request->header('Faculdade')){
            return response()->json(['success' => false, 'error' => 'Faculdade inválida.']);
        }

        $perfils_ies = [10, 22, 2]; # PERFIS DE GESTOR IES E FINANCEIRO IES 
        if (isset($user->fk_perfil) && in_array($user->fk_perfil, $perfils_ies)){
            $filters['fk_faculdade'] = $Request->header('Faculdade');
            
            $filters['periodo'][0] = ($Request->input('data_inicial')) ? $Request->input('data_inicial') : '2019-01-01';
            $filters['periodo'][1] = ($Request->input('data_final')) ? $Request->input('data_final') : date('Y-m-d');
            $filters['agrupar_por'] = ($Request->input('agrupar_por')) ? $Request->input('agrupar_por') : 'semana';
            $filters['fk_categoria'] = ($Request->input('fk_categoria')) ? $Request->input('fk_categoria') : false;
        } else {
            return response()->json(['success' => false, 'error' => 'Usuário não encontrado.']);
        }

        $data = Pedido::relatorio_pagamento_reprovado($filters)->get();

        $total_liquido = 0;
        if (isset($data[0]->fk_faculdade) && $data[0]->fk_faculdade == 7){
            foreach ($data as $key => &$faturamento) {
                $totals = Pedido::getTotalsEducaz(explode(",", $faturamento->ids_pedidos), $filters);
                $repasses = Pedido::getTotalRepassesParceiros(explode(",", $faturamento->ids_pedidos), $filters);
                
                $faturamento->semana = (int) $faturamento->semana;
                $faturamento->unidades = (int) $faturamento->unidades;
                $faturamento->liquido = max((float) number_format($totals->valor_liquido - $repasses->total, 2), 0); 
                $total_liquido = $faturamento->liquido + $total_liquido;

                unset($faturamento->liquido_com_repasses);
                unset($faturamento->total_repasses);
                unset($data[0]->fk_faculdade);
                unset($faturamento->ids_pedidos);
            }
        } else {
            foreach ($data as $key => &$faturamento) {
                $faturamento->semana = (int) $faturamento->semana;
                $faturamento->unidades = (int) $faturamento->unidades;
                $faturamento->liquido =  max((float) $faturamento->liquido, 0);

                $total_liquido = $faturamento->liquido + $total_liquido;

                unset($faturamento->ids_pedidos);
            }
        }

        foreach ($data as $key => &$faturamento) {
            if (isset($faturamento->liquido) && $faturamento->liquido > 0){
                $faturamento->percentual = (($total_liquido - $faturamento->liquido) / $total_liquido) * 100;
                $faturamento->percentual = (float) number_format(100 - $faturamento->percentual, 2);
            }
        }

        return response()->json(['success' => true, 'data' => $data->toArray()]);
    }

    public function graficoAssinaturasStatus(Request $Request){
        $user = JWTAuth::user();

        if (!$Request->header('Faculdade')){
            return response()->json(['success' => false, 'error' => 'Faculdade inválida.']);
        }

        $perfils_ies = [10, 22, 2]; # PERFIS DE GESTOR IES E FINANCEIRO IES 
        if (isset($user->fk_perfil) && in_array($user->fk_perfil, $perfils_ies)){
            $filters['fk_faculdade'] = $Request->header('Faculdade');
            
            $filters['periodo'][0] = ($Request->input('data_inicial')) ? $Request->input('data_inicial') : '2019-01-01';
            $filters['periodo'][1] = ($Request->input('data_final')) ? $Request->input('data_final') : date('Y-m-d');
            $filters['agrupar_por'] = ($Request->input('agrupar_por')) ? $Request->input('agrupar_por') : 'semana';
        } else {
            return response()->json(['success' => false, 'error' => 'Usuário não encontrado.']);
        }

        $data = Assinatura::relatorio_assinaturas_faturas_pagas($filters)->get();

        if (isset($data[0]->fk_faculdade) && $data[0]->fk_faculdade == 7){
            foreach ($data as $key => &$faturamento) {
                $totals = Pedido::getTotalsEducaz(explode(",", $faturamento->ids_pedidos), $filters);
                $repasses = Pedido::getTotalRepassesParceiros(explode(",", $faturamento->ids_pedidos), $filters);
                
                $faturamento->semana = (int) $faturamento->semana;
                $faturamento->unidades = (int) $faturamento->unidades;
                $faturamento->liquido = max((float) number_format($totals->valor_liquido - $repasses->total, 2), 0); 
                $total_liquido = $faturamento->liquido + $total_liquido;

                unset($faturamento->liquido_com_repasses);
                unset($faturamento->total_repasses);
                unset($data[0]->fk_faculdade);
                unset($faturamento->ids_pedidos);
            }
        } else {
            foreach ($data as $key => &$faturamento) {
                $faturamento->semana = (int) $faturamento->semana;
                $faturamento->unidades = (int) $faturamento->unidades;
                $faturamento->liquido =  max((float) $faturamento->liquido, 0);

                $total_liquido = $faturamento->liquido + $total_liquido;

                unset($faturamento->ids_pedidos);
            }
        }

        return response()->json(['success' => true, 'data' => $data->toArray()]);
    }

    public function export($faturas, $fk_faculdade){
        if ($fk_faculdade){
            $faculdade = Faculdade::select('fantasia')->where('id', $fk_faculdade)->first();
            $data['ies'] = $faculdade->fantasia;
        }

        $data['faturas'] = (!empty($faturas['cursos'])) ? $faturas['cursos'] : array();

        $html = view('relatorio.acesso_restrito.faturas', $data);

        // Definimos o nome do arquivo que será exportado
        $arquivo = 'faturas' . time() . '.xls';

        // Configurações header para forçar o download
        header ("Access-Control-Allow-Origin: *");
        header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header ("Cache-Control: no-cache, must-revalidate");
        header ("Pragma: no-cache");
        header ("Content-type: application/x-msexcel");
        header ("Content-Disposition: attachment; filename=\"{$arquivo}\"" );
        header ("Content-Description: PHP Generated Data" );

        // Envia o conteúdo do arquivo
        echo utf8_decode($html); exit;
    }
}
