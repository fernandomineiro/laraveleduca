<?php

namespace App\Http\Controllers\API;

use App\Helper\EducazMail;
use App\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Assinatura;
use App\AssinaturaRepasse;
use App\AssinaturaRepasseParceiro;
use App\Faculdade;
use App\Pedido;
use App\PedidoItem;
use Tymon\JWTAuth\Facades\JWTAuth;

set_time_limit(1200);

class AssinaturasController extends Controller
{
    public function __construct() {

        parent::__construct();

    }


    /**
     * Listagem de assinaturas
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $assinaturas = Assinatura::select(['assinatura.*', 'assinatura_faculdades.gratis'])
            ->leftJoin('assinatura_faculdades', 'assinatura.id', '=', 'assinatura_faculdades.fk_assinatura')
            ->where(['assinatura.status' => 1, 'assinatura_faculdades.fk_faculdade' => $request->header('Faculdade', 1)])
                ->get();

            foreach ($assinaturas as $key => $dado) {
                $assinaturas[$key]['gratis'] = isset($dado['gratis']) ? (int) $dado['gratis'] : 0;
            }

            return response()->json([
                        'success' => true,
                        'items' => $assinaturas
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

    public function show ($idAssinatura) {
        try {
            $assinatura = Assinatura::findOrFail($idAssinatura);
            $retorno = [
                'assinatura' => $assinatura,
                'trilhas' => $assinatura->trilhas,
                'cursos' => $assinatura->cursos
            ];
            return response()->json([
                'success' => true,
                'items' => $retorno
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
    public function salvar(Request $request) {
        // faz assinatura para o usuario
        try {
            $assinatura = Assinatura::find($request->get('assinatura'));
            $assinatura->usuarios()->attach($request->get('usuario'));
            return response()->json([
                'success' => true
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

    public function deletar(Request $request) {
        // remove assinatura do usuário
    }

    public function retornaAssinaturaUsuario($id) {
        try {

            // retorna assinaturas do usuário
            $usuario = Usuario::find($id);
            return response()->json([
                'success' => true,
                'items' => $usuario->assinaturas
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

    public function minhaAssinatura() {

        try {
            $loggedUser = JWTAuth::user();

            return response()->json([
                'success' => true,
                'items' => $loggedUser->membership()
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

    }

    public function cursos($idAssinatura) {
        try {
            $queryAdicionados = "select distinct
                            cursos.id,
                            cursos.titulo,
                            CONCAT(professor.nome, ' ', professor.sobrenome) as professor_nome,
                            cursos_tipo.titulo as curso_tipo,
                            cursos_valor.valor,
                            cursos_valor.valor_de
                        from cursos
                            JOIN professor on professor.id = cursos.fk_professor
                            join cursos_tipo on cursos_tipo.id = cursos.fk_cursos_tipo
                            join cursos_valor on cursos.id = cursos_valor.fk_curso
                        where cursos.status != 0
                            AND cursos.id in (
                                select fk_conteudo from assinatura_conteudos where fk_assinatura = {$idAssinatura}
                            )
                        order by cursos.id";
            $data = DB::select($queryAdicionados);

            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);

        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema' . $e->getMessage()
            ]);
        }
    }

    public function repasses(Request $Request){
        $planos = PedidoItem::select('fk_assinatura AS id')
        ->where('fk_assinatura', '>', 0)
        ->groupBy('fk_assinatura')->get();

        $faculdades = Pedido::select('fk_faculdade AS id')
        ->where('status', 2)->where('fk_faculdade', '>', 0)
        ->groupBy('fk_faculdade')->get();

        foreach ($planos as $key => $plano) {
            foreach ($faculdades as $key => $faculdade) {
                $mes = (!empty($Request->get('mes'))) ? $Request->get('mes') : date('m');
                $ano = (!empty($Request->get('ano'))) ? $Request->get('ano') : date('Y');

                $views = Assinatura::total_views_mes($mes, $ano, $faculdade->id, $plano->id);

                if (!empty($views->pedidos)){
                    $total_views_modulos = Assinatura::total_views_curso(explode(",", $views->pedidos), $mes, $ano, $faculdade->id, $plano->id);
                    $cursos = Assinatura::cursos_views_mes(explode(",", $views->pedidos), $mes, $ano, $faculdade->id, $plano->id);
                    $parceiros = Assinatura::parceiros_por_pedidos(explode(",", $cursos->ids));
                    $valor_arrecadado = Assinatura::total_arrecadado($mes, $ano, $faculdade->id, $plano->id);

                    if (isset($valor_arrecadado->total) && $valor_arrecadado->total > 0){
                        # VALOR DESTINADO AOS PARCEIROS E DE 30 POR CENTO
                        $valor_total_parceiros = ($valor_arrecadado->total / 100) * 30;

                        # VALOR DA VIEW POR PARCEIRO
                        $valor_view = number_format($valor_total_parceiros / $views->total, 2);

                        if (!empty($views->total) && $views->total > 0){
                            $AssinaturaRepasse = AssinaturaRepasse::updateOrCreate([
                                'fk_faculdade' => $faculdade->id,
                                'fk_assinatura' => $plano->id,
                                'mes' => $mes,
                                'ano' => $ano],
                                ['total_arrecadado' => $valor_arrecadado->total,
                                'valor_view' => $valor_view,
                                'total_views' => $views->total,
                                'total_parceiros' => $parceiros->total,
                                'total_assinantes' => $valor_arrecadado->total_assinantes,
                                'atualizacao' => date('Y-m-d H:i:s')]);

                                if (isset($AssinaturaRepasse->id)){
                                    $this->addRepassePorParceiro($AssinaturaRepasse->id, $total_views_modulos);
                                }
                        }
                    }
                }
            }
        }

        # CRIAR FLUXO DE EXECUCAO BASEADO NOS PARAMENTROS DO GET PARA RODAR MANUALMENTE
    }

    private function addRepassePorParceiro($assinatura_repasse_id, $total_views_modulos){
        $data = [];
        foreach ($total_views_modulos as $key => $view) {
            $repasses = $this->getTotalViewsParceiro($view->toArray());

            foreach ($repasses as $tipo => $parceiro) {
                if (!empty($parceiro['id']) && !empty($parceiro['share'])){
                    $fk_parceiro = $parceiro['id'];

                    $key = $fk_parceiro . '-' . $parceiro['fk_curso'];

                    if (isset($parceiro['id']) && isset($data[$key]['total_views'])){
                        $total_views = $parceiro['views'] + $data[$key]['total_views'];
                    } else {
                        $total_views = $parceiro['views'];
                    }

                    $data[$key] = ['fk_assinatura_repasse' => $assinatura_repasse_id,
                            'fk_usuario' => $fk_parceiro,
                            'tipo_usuario' => $tipo,
                            'total_views' => $total_views,
                            'fk_curso' => $parceiro['fk_curso'],
                            'percentual_repasse' => $parceiro['share'],
                            'atualizacao' => date('Y-m-d H:i:s')];
                }
            }
        }

        foreach ($data as $key => $repasse) {
            AssinaturaRepasseParceiro::updateOrCreate([
            'fk_assinatura_repasse' => $assinatura_repasse_id,
            'fk_usuario' => $repasse['fk_usuario'],
            'tipo_usuario' => $repasse['tipo_usuario'],
            'fk_curso' => $repasse['fk_curso'],
            'percentual_repasse' => $repasse['percentual_repasse']],
            ['total_views' => $repasse['total_views'],
            'atualizacao' => date('Y-m-d H:i:s')]);
        }
    }

    private function getTotalViewsParceiro($view){
        $parceiros_por_curso = Assinatura::parceiros_por_curso($view['fk_curso']);

        if (isset($parceiros_por_curso->professorprincipal_share)){
            $data['professor']['id']    = $parceiros_por_curso->fk_usuario_professor;
            $data['professor']['share'] = (!empty($parceiros_por_curso->professorprincipal_share)) ? $parceiros_por_curso->professorprincipal_share : 0;
            $data['professor']['views'] = $view['views'];
            $data['professor']['fk_curso'] = $view['fk_curso'];

            $data['professores_participante']['id'] = $parceiros_por_curso->fk_usuario_professor_participante;
            $data['professores_participante']['share'] = (!empty($parceiros_por_curso->professorparticipante_share)) ? $parceiros_por_curso->professorparticipante_share : 0;
            $data['professores_participante']['views'] = $view['views'];
            $data['professores_participante']['fk_curso'] = $view['fk_curso'];

            $data['curador']['id'] = $parceiros_por_curso->fk_usuario_curador;
            $data['curador']['share'] = (!empty($parceiros_por_curso->curador_share)) ? $parceiros_por_curso->curador_share : 0;
            $data['curador']['views'] = $view['views'];
            $data['curador']['fk_curso'] = $view['fk_curso'];

            $data['produtora']['id'] = $parceiros_por_curso->fk_usuario_produtora;
            $data['produtora']['share'] = (!empty($parceiros_por_curso->produtora_share)) ? $parceiros_por_curso->produtora_share : 0;
            $data['produtora']['views'] = $view['views'];
            $data['produtora']['fk_curso'] = $view['fk_curso'];

            $faculdade = $this->getFaculdade($view['fk_faculdade']);

            $data['faculdade']['id'] = $faculdade->fk_usuario_id;
            $data['faculdade']['share'] = $faculdade->share;
            $data['faculdade']['views'] = $view['views'];
            $data['faculdade']['fk_curso'] = $view['fk_curso'];
        }

        return $data;
    }

    private function getFaculdade($fk_faculdade){
        $faculdade = Faculdade::select('share', 'fk_usuario_id')->find($fk_faculdade);

        return $faculdade;
    }
}
