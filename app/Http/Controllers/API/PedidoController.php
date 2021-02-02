<?php

namespace App\Http\Controllers\API;

use App\Cupom;
use App\CupomAlunoSemRegistro;
use App\Helper\EducazMail;
use App\Helper\PedidoGratis;
use App\Usuario;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Pedido;
use App\PedidoItem;
use App\PedidoItemSplit;
use App\Impostos;
use App\PedidoTotal;
use App\Curso;
use App\CursoValor;
use App\CursosFaculdades;
use App\TrilhasFaculdades;
use App\TrilhaCurso;
use App\Trilha;
use App\Faculdade;
use App\Assinatura;
use App\AssinaturaFaculdade;
use App\Helper\TaxasPagamento;

class PedidoController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $pedidos = Pedido::lista();

            return response()->json([
                'data' => $pedidos
            ]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * Grava Pedidos
     *
     * @param lluminate\Http\Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $data = $request->all();

        $faculdade = Faculdade::select('url')->find($request->header('Faculdade', 1));

        $valor_desconto = $data['pedido']['desconto'];
        $valor_imposto = $this->getValorImpostosTaxas($data['pedido']['valor'] - $valor_desconto);

        if (($data['pedido']['valor'] - $valor_desconto) <= 0) {
            $valor_liquido = 0;
        } else {
            $valor_liquido = max($data['pedido']['valor'] - $valor_imposto - $valor_desconto, 0);
        }

        if (!isset($data['pedido']['usuario']) || empty($data['pedido']['usuario'])) {
            $mensagemErro = "Por favor informar o aluno.";
        }

        if (isset($mensagemErro) && !empty($mensagemErro)) {
            return response()->json([
                'success' => false,
                'error' => $mensagemErro,
                'data' => $request->all()
            ]);
        }

        $cupomUtilizado = true;
        $cupom = Cupom::find($data['pedido']['fk_cupom']);
        $usuario = Usuario::find($data['pedido']['usuario']);

        $cupom_sem_registro = CupomAlunoSemRegistro::where('email', $usuario->email)
            ->where('cupom_aluno_sem_registro.fk_cupom', $data['pedido']['fk_cupom'])
            ->where(function ($q) use ($data, $request) {
                $q->where('fk_faculdade', $request->header('Faculdade'))
                    ->orWhereNull('fk_faculdade');
            })->get();

        if ($cupom && $cupom->numero_maximo_usos) {
            $cupom->numero_maximo_usos = $cupom->numero_maximo_usos - 1;
            $cupom->save();

            if ($cupom->numero_maximo_usos < 0) {
                $cupomUtilizado = false;
            }
        } elseif (!$cupom || (!is_null($cupom->numero_maximo_usos) && $cupom->numero_maximo_usos <= 0)) {
            $cupomUtilizado = false;
        }

        foreach ($cupom_sem_registro as $dado) {
            if ($dado && $dado->numero_usos) {
                $dado->numero_usos = $dado->numero_usos - 1;
                $dado->save();

                if ($dado->numero_usos < 0) {
                    $cupomUtilizado = false;
                }
            } elseif (!$dado || (!is_null($dado->numero_usos) && $dado->numero_usos <= 0)) {
                $cupomUtilizado = false;
            }
        }


        $pedido = [
            "criacao" => date('Y-m-d H:i:s'),
            'fk_faculdade' => $request->header('Faculdade', 1),
            'fk_usuario' => $data['pedido']['usuario'],
            'valor_bruto' => $data['pedido']['valor'],
            'valor_desconto' => $valor_desconto,
            'valor_liquido' => $valor_liquido,
            'valor_imposto' => ($valor_liquido <= 0) ? 0 : $valor_imposto,
            'status' => 1,
            'fk_cupom' => $cupomUtilizado ? $cupom->id : null,
            'codigo_cupom' => $cupomUtilizado ? $cupom->codigo_cupom : null,
            'tipo_cupom_desconto' => $cupomUtilizado ? $cupom->tipo_cupom_desconto : null,
            'valor_cupom' => $cupomUtilizado ? $cupom->valor : null
        ];

        try {
            DB::beginTransaction();

            $pedidoObjeto = new Pedido($pedido);

            $pedido_gratis = $this->checkPedidoGratis($data['items'], $request->header('Faculdade', 1));

            if (!($id_pedido = $pedidoObjeto->save())) {
                throw new \Exception('Erro ao criar o pedido');
            }

            $pedidoObjeto->pid = date('dmY') . '-' . $pedidoObjeto->id . '-' . $data['pedido']['usuario'];
            $pedidoObjeto->save();

            $splits = array();
            foreach ($data['items'] as $key => $item) {
                $valor_imposto = $this->getValorImpostosTaxas($item['valor']);
                $valor_liquido = $item['valor'] - $valor_imposto - $valor_desconto;

                if ($item['id_curso'] > 0) {
                    $splits[] = $this->getSplitPorItem($pedidoObjeto->id, $item['id_curso'], 'curso',
                        $request->header('Faculdade', 1));
                }

                if ($item['id_trilha'] > 0) {
                    $splits = array_merge($splits,
                        $this->getSplitPorItem($pedidoObjeto->id, $item['id_trilha'], 'trilha',
                            $request->header('Faculdade', 1)));
                }

                if ($item['id_curso'] != null) {
                        $curso = Curso::obter($item['id_curso'], $request->header('Faculdade', 1));
                    $indisponivel_venda = Curso::verificaDisponivelVenda($curso, true);
                    if ($indisponivel_venda) {
                        throw new \Exception('Erro ao criar pedido, curso indisponível para venda! Aguarde novas turmas.');
                    }
                }

                $pedido_item = [
                    'valor_bruto' => $item['valor'],
                    'valor_desconto' => $item['desconto'],
                    'valor_imposto' => $valor_imposto,
                    'valor_liquido' => $valor_liquido,
                    'status' => 1,
                    'fk_pedido' => $pedidoObjeto->id,
                    'fk_curso' => $item['id_curso'],
                    'fk_evento' => $item['id_evento'],
                    'fk_trilha' => $item['id_trilha'],
                    'fk_assinatura' => $item['id_assinatura']
                ];

                $pedidoItemObjeto = new PedidoItem($pedido_item);

                if (!$pedidoItemObjeto->save()) {
                    return response()->json([
                        'success' => true,
                        'data' => Pedido::find($pedidoObjeto->id)->toArray()
                    ]);
                }
            }

            $this->addTotalPedido($pedidoObjeto->id, $data['pedido']['valor'], $valor_desconto);

            DB::commit();

            if ($pedidoObjeto->valor_liquido == 0 || $pedido_gratis === true) {
                /* METODO RESPONSAVEL POR LIBERAR TODOS OS PONTOS DO CURSOS PARA O USUARIO
                VOUCHERS EMAILS HISTORICO E ETC */
                $PedidoGratis = new PedidoGratis;
                $release = $PedidoGratis->release($pedidoObjeto->id, $request->header('Faculdade', 1));

                return response()->json([
                    'success' => true,
                    'data' => Pedido::find($pedidoObjeto->id)->toArray(),
                    'redirect' => '/perfil/cursos'
                ]);
            }

            if (!empty($splits)) {
                foreach ($splits as $key => $split) {
                    PedidoItemSplit::create($split);
                }
            }

            return response()->json([
                'success' => true,
                'data' => Pedido::find($pedidoObjeto->id)->toArray()
            ]);

        } catch (\InvalidArgumentException $e) {
            DB::rollBack();

            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'messages' => $e->getMessage(),
                'data' => $request->all()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'messages' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function usuarioPedidos($id) {
        try {
            $pedidos = Pedido::lista($id);
            return response()->json([
                'success' => true,
                'count' => count($pedidos),
                'items' => $pedidos
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    private function addTotalPedido($fk_pedido, $total, $desconto){
        $impostos = Impostos::select('porcentagem_iss', 'porcentagem_pis_cofins', 'porcentagem_irpj_csll', 'valor_taxa_boleto', 'valor_taxa_processamento')->first();

        if (isset($impostos->porcentagem_iss)){
            $totais = $impostos->toArray();

            $totais['valor_total'] = $total;
            $totais['valor_desconto'] = $desconto;

            $matchThese = array('fk_pedido' => $fk_pedido);
            $pedidos_total = PedidoTotal::updateOrCreate($matchThese, $totais);
        }
    }

    private function getValorImpostosTaxas($total_base){
        $impostos = Impostos::select('porcentagem_iss', 'porcentagem_pis_cofins', 'porcentagem_irpj_csll', 'valor_taxa_boleto', 'valor_taxa_processamento')->first();

        if (isset($impostos->porcentagem_iss)){
            $imposto_percentual = $impostos->porcentagem_iss + $impostos->porcentagem_pis_cofins + $impostos->porcentagem_irpj_csll;
            $total = ($total_base / 100) * $imposto_percentual;

            return number_format($total, 2);
        }
    }

    private function getSplitPorItem($fk_pedido, $id, $tipo, $fk_faculdade){
        $data = array();
        if ($tipo == 'curso'){
            $curso = Curso::select(['cursos.*', 'cursos_valor.valor', 'cursos_valor.valor_de'])->join('cursos_valor', 'cursos.id', '=', 'cursos_valor.fk_curso')->where('cursos.id', $id)->first();

            if (isset($curso->id)){
                $data['fk_pedido'] = $fk_pedido;
                $data['fk_curso'] = $curso->id;

                $data['porcentagem_split_professor'] = $curso->professorprincipal_share;
                $data['porcentagem_split_professor_participante'] = $curso->professorparticipante_share;
                $data['porcentagem_split_curador'] = $curso->curador_share;
                $data['porcentagem_split_faculdade'] = $this->getParcentagemFaculdade($fk_faculdade);
                $data['porcentagem_split_produtora'] = $curso->produtora_share;

                # VALOR DE REPASSE COM SUBTRACAO DAS TAXAS
                $curso_valor = ($curso->valor > 0) ? $curso->valor : $curso->valor_de;

                $data['valor_split_professor'] = (($curso_valor / 100) * $curso->professorprincipal_share);
                $data['valor_split_professor_participante'] = (($curso_valor / 100) * $curso->professorparticipante_share);
                $data['valor_split_curador'] = (($curso_valor / 100) * $curso->curador_share);
                $data['valor_split_faculdade'] = (($curso_valor / 100) * $this->getParcentagemFaculdade($fk_faculdade));
                $data['valor_split_produtora'] = (($curso_valor / 100) * $curso->produtora_share);

                # CONFIG REPASSE QUE NAO SERAO FEITO AUTOMATICAMENTE NA WIRECARD
                # O PERCENTUAL PARA REPASSES MANUAIS SERAO UTILIZADAS NOS RELATORIOS
                $data['split_professor_manual'] = $curso->professorprincipal_share_manual;
                $data['split_professor_participante_manual'] = $curso->professorparticipante_share_manual;
                $data['split_curador_manual'] = $curso->curador_share_manual;
                $data['split_produtora_manual'] = $curso->produtora_share_manual;

                /* FIXO ATE AJUSTAREM O CADASTRO DE PARCEIRO */
                $data['porcentagem_split_parceiro'] = 0;
                $data['valor_split_parceiro'] = 0;
                $data['split_parceiro_manual'] = 0;
            }
        } elseif ($tipo == 'trilha'){
            $trilha = Trilha::select('valor_venda')->where('id', $id)->first();

            $cursos = TrilhaCurso::select(DB::raw('SUM(cursos_valor.valor) AS total'))
            ->where(['fk_trilha' => $id, 'cursos.status' => 5])
            ->join('cursos_valor', 'cursos_valor.fk_curso', '=', 'trilha_curso.fk_curso')
            ->join('cursos', 'cursos.id', '=', 'trilha_curso.fk_curso')
            ->first();

            if (isset($cursos->total) && $cursos->total > 0){
                /* CALCULA A DIREFERENCA PERCENTUAL ENTRE COMPRA CURSOS SEPARADAS OU AGRUPADOS NA TRILHA */
                $dif = $cursos->total - $trilha->valor_venda;
                $dif_percentual = ($dif * 100) / $cursos->total;

                $cursos = TrilhaCurso::select('cursos.id AS id', 'cursos_valor.valor', 'cursos.fk_faculdade', 'cursos.professorprincipal_share', 'cursos.professorparticipante_share',
                'curador_share', 'cursos.produtora_share', 'professorparticipante_share_manual', 'produtora_share_manual', 'professorprincipal_share_manual', 'curador_share_manual')
                ->where(['fk_trilha' => $id, 'cursos.status' => 5])
                ->join('cursos', 'cursos.id', '=', 'trilha_curso.fk_curso')
                ->join('cursos_valor', 'cursos_valor.fk_curso', '=', 'trilha_curso.fk_curso')
                ->get();

                if (isset($cursos[0])){
                    $i = 0;
                    foreach ($cursos as $key => $curso) {
                        $data[$i]['fk_pedido'] = $fk_pedido;
                        $data[$i]['fk_curso'] = $curso->id;

                        $data[$i]['porcentagem_split_professor'] = $this->calcPercentualCursoNaTrilha($dif_percentual, $curso->professorprincipal_share);
                        $data[$i]['porcentagem_split_professor_participante'] = $this->calcPercentualCursoNaTrilha($dif_percentual, $curso->professorparticipante_share);
                        $data[$i]['porcentagem_split_curador'] = $this->calcPercentualCursoNaTrilha($dif_percentual, $curso->curador_share);
                        $data[$i]['porcentagem_split_faculdade'] = $this->getParcentagemFaculdade($fk_faculdade);
                        $data[$i]['porcentagem_split_produtora'] = $this->calcPercentualCursoNaTrilha($dif_percentual, $curso->produtora_share);

                        # VALOR DE REPASSE COM SUBTRACAO DAS TAXAS
                        $curso_valor = ($curso->valor > 0) ? $curso->valor : $curso->valor_de;

                        $data[$i]['valor_split_professor'] = number_format((($curso_valor / 100) * $data[$i]['porcentagem_split_professor']), 2);
                        $data[$i]['valor_split_professor_participante'] = number_format((($curso_valor / 100) * $data[$i]['porcentagem_split_professor_participante']), 2);
                        $data[$i]['valor_split_curador'] = number_format((($curso_valor / 100) * $data[$i]['porcentagem_split_curador']), 2);
                        $data[$i]['valor_split_faculdade'] = number_format((($curso_valor / 100) * $this->getParcentagemFaculdade($data[$i]['porcentagem_split_faculdade'])), 2);
                        $data[$i]['valor_split_produtora'] = number_format((($curso_valor / 100) * $data[$i]['porcentagem_split_produtora']), 2);

                        /* FIXO ATE AJUSTAREM O CADASTRO DE PARCEIRO */
                        $data[$i]['porcentagem_split_parceiro'] = 0;
                        $data[$i]['split_parceiro_manual'] = 0;

                        # CONFIG REPASSE QUE NAO SERAO FEITO AUTOMATICAMENTE NA WIRECARD
                        # O PERCENTUAL PARA REPASSES MANUAIS SERAO UTILIZADAS NOS RELATORIOS
                        $data[$i]['split_professor_manual'] = $curso->professorprincipal_share_manual;
                        $data[$i]['split_professor_participante_manual'] = $curso->professorparticipante_share_manual;
                        $data[$i]['split_curador_manual'] = $curso->curador_share_manual;
                        $data[$i]['split_produtora_manual'] = $curso->produtora_share_manual;

                        $i++;
                    }
                }
            }
        }

        return $data;
    }

    private function calcPercentualCursoNaTrilha($dif_percentual, $percentual_original){
        return ($percentual_original / 100) * (100 - $dif_percentual);
    }

    public function getParcentagemFaculdade($fk_faculdade){
        $faculdade = Faculdade::where('id', $fk_faculdade)->first();

        if (!empty($faculdade->share)){
            return $faculdade->share;
        } else {
            return 0;
        }
    }

    public function checkPedidoGratis($itens, $fk_faculdade){
        $gratis = false;
        if (count($itens) == 1){
            foreach ($itens as $key => $item) {
                if (!empty($item['id_curso'])){
                    $curso_faculdade = CursosFaculdades::where(['fk_curso' => $item['id_curso'], 'fk_faculdade' => $fk_faculdade])->first();

                    if ($curso_faculdade->curso_gratis == 1){
                        $gratis = true;
                    }
                } elseif (!empty($item['id_trilha'])){
                    $trilha_faculdade = TrilhasFaculdades::where(['fk_trilha' => $item['id_trilha'], 'fk_faculdade' => $fk_faculdade])->first();

                    if ($trilha_faculdade->gratis == 1){
                        $gratis = true;
                    }
                } elseif (!empty($item['id_assinatura'])){
                    $assinatura_faculdade = AssinaturaFaculdade::where(['fk_assinatura' => $item['id_assinatura'], 'fk_faculdade' => $fk_faculdade])->first();

                    if ($assinatura_faculdade->gratis == 1){
                        $gratis = true;
                    }
                }
            }
        }

        return $gratis;
    }
}
