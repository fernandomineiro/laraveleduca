<?php

namespace App\Http\Controllers\API;

use App\BannerSecundario;
use App\Configuracao;
use App\ConfiguracaoEmailsFaculdade;
use App\ConfiguracoesBanners;
use App\ConfiguracoesEstilos;
use App\ConfiguracoesHomeFooterHeader;
use App\ConfiguracoesLogotipos;
use App\ConfiguracoesPaginas;
use App\ConfiguracoesParceiros;
use App\Faculdade;
use App\ConfiguracoesPixels;
use App\ConfiguracoesPolitica;
use App\ConfiguracoesRedesSociais;
use App\ConfiguracoesSac;
use App\ConfiguracoesTermo;
use App\ConfiguracoesTiposCursosAtivos;
use App\Helper\EducazMail;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ConfiguracaoController extends Controller
{
    /**
     * Retorna configuraçoes por faculdade
     * @param integer $idFaculdade
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($idFaculdade, Request $request)
    {
        try {
            $data = Configuracao::select()
                        ->where('fk_faculdade', !empty((int) $idFaculdade) ? $idFaculdade : $request->header('Faculdade', 1))
                        ->first();

            return response()->json([
                'data' => $data
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
     * Retorna emails por faculdade
     * @param integer $idFaculdade
     * @return \Illuminate\Http\JsonResponse
     */
    public function emails($idFaculdade, Request $request)
    {
        try {
            $data = ConfiguracaoEmailsFaculdade::select()
                        ->where('fk_faculdade_id', !empty((int) $idFaculdade) ? $idFaculdade : $request->header('Faculdade', 1))
                        ->first();

            return response()->json([
                'data' => $data
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


    public function bannersSecundarios($idFaculdade, Request $request) {
        try {
            $banners = BannerSecundario::select('*')
                ->where('fk_faculdade_id', !empty((int) $idFaculdade) ? $idFaculdade : $request->header('Faculdade', 7))
                ->where('status', 1);

            $items = $banners->first();

            return response()->json([
                'items' => $items,
            ]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }
    /**
     * Retorna banners por faculdade
     * @param integer $idFaculdade
     * @return \Illuminate\Http\JsonResponse
     */
    public function banners($idFaculdade, $pagina = 'home', $id = null, Request $request){
        try {
            if (!is_null($id) && $pagina == 'categoria') {
                $banners = ConfiguracoesBanners::select('*')
                    ->where('fk_faculdade_id', !empty((int) $idFaculdade) ? $idFaculdade : $request->header('Faculdade', 1))
                    ->where('pagina', 'categoria_' . $id);
            } else {
                $banners = ConfiguracoesBanners::select('*')
                    ->where('data_hora_inicio', '<=', Carbon::now())
                    ->where('data_hora_termino', '>=', Carbon::now())
                    ->where('fk_faculdade_id', !empty((int) $idFaculdade) ? $idFaculdade : $request->header('Faculdade', 1))
                    ->where('pagina', $pagina);
            }

            $items = $banners->get();

            return response()->json([
                'items' => $items,
                'count' => count($items)
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
     * Retorna estilos css por faculdade
     * @param integer $idFaculdade
     * @return \Illuminate\Http\JsonResponse
     */
    public function estilos($idFaculdade, Request $request)
    {
        try {
            $items = ConfiguracoesEstilos::select()
                        ->where('fk_faculdade_id', !empty((int) $idFaculdade) ? $idFaculdade : $request->header('Faculdade', 1))
                        ->get();

            return response()->json([
                'items' => $items,
                'count' => count($items)
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

    public function pixels($idFaculdade, Request $request)
    {
        try {
            $items = ConfiguracoesPixels::where('fk_faculdade_id', !empty((int) $idFaculdade) ? $idFaculdade : $request->header('Faculdade', 1))->get();

            return response()->json([
                'items' => $items,
                'count' => count($items)
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

    public function parceiros($idFaculdade)
    {
        try {
            $data = Faculdade::select(
                'faculdades.id',
                'faculdades.razao_social',
                'faculdades.fantasia as nome_faculdade',
                'configuracoes_parceiros.id as parceiroId',
                'configuracoes_parceiros.link',
                'configuracoes_parceiros.imagem',
                'configuracoes_parceiros.descricao'
            )
                ->join('configuracoes_parceiros', 'fk_faculdade_id', '=', 'faculdades.id')
                ->where('configuracoes_parceiros.status', '=', '1')
                ->where('configuracoes_parceiros.fk_faculdade_id', '=', $idFaculdade)
                ->get();

            return response()->json([
                'items' => $data,
                'count' => count($data)
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
     * Retorna politica por faculdade
     * @param integer $idFaculdade
     * @return \Illuminate\Http\JsonResponse
     */
    public function politica($idFaculdade, Request $request)
    {
        try {
            $items = ConfiguracoesPolitica::select()
                        ->where('fk_faculdade_id', !empty((int) $idFaculdade) ? $idFaculdade : $request->header('Faculdade', 1))
                        ->where('status', 1)
                        ->get();

            return response()->json([
                'items' => $items,
                'count' => count($items)
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
     * Retorna textos padrões de paginas por faculdade
     * @param integer $idFaculdade
     * @return \Illuminate\Http\JsonResponse
     */
    public function paginas($idFaculdade, Request $request)
    {
        try {
            $items = ConfiguracoesPaginas::select()->where('fk_faculdade_id',
                    !empty((int) $idFaculdade) ? $idFaculdade : $request->header('Faculdade', 1))
                ->where('status', 1)
                ->get();

            return response()->json([
                'items' => $items,
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
     * Retorna termo por faculdade
     * @param integer $idFaculdade
     * @return \Illuminate\Http\JsonResponse
     */
    public function termo( $idFaculdade, Request $request) {
        try {
            $items = ConfiguracoesTermo::select()
                ->where('fk_faculdade_id', !empty((int) $idFaculdade) ? $idFaculdade : $request->header('Faculdade', 1))
                ->where('status', 1)
                ->first();

            $array[] = $items;
            return response()->json([
                'items' => $array,
                'count' => count($array),
            ]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * Retorna redes sociais por faculdade
     * @param integer $idFaculdade
     * @return \Illuminate\Http\JsonResponse
     */
    public function redesSociais($idFaculdade, Request $request)
    {
        try {
            $items = ConfiguracoesRedesSociais::select()->where('fk_faculdade_id', !empty((int) $idFaculdade) ? $idFaculdade : $request->header('Faculdade', 1))->get();

            return response()->json([
                'items' => $items,
                'count' => count($items)
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
     * Retorna logotipos por faculdade
     * @param integer $idFaculdade
     * @return \Illuminate\Http\JsonResponse
     */
    public function logotipos($idFaculdade, Request $request)
    {
        try {
            $items = ConfiguracoesLogotipos::select()->where('fk_faculdade_id', !empty((int) $idFaculdade) ? $idFaculdade : $request->header('Faculdade', 1))->get();

            return response()->json([
                'items' => $items,
                'count' => count($items)
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
     * Retorna sac por faculdade
     * @param integer $idFaculdade
     * @return \Illuminate\Http\JsonResponse
     */
    public function sac($idFaculdade, Request $request)
    {
        try {
            $items = ConfiguracoesSac::select()->where('fk_faculdade_id', !empty((int) $idFaculdade) ? $idFaculdade : $request->header('Faculdade', 1))->get();

            return response()->json([
                'items' => $items,
                'count' => count($items)
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

    /**
     * Retorna home e footer por faculdade
     * @param integer $idFaculdade
     * @return \Illuminate\Http\JsonResponse
     */
    public function homeAndFotter($idFaculdade, Request $request)
    {
        try {
            $items = ConfiguracoesHomeFooterHeader::select()->where('fk_faculdade_id', !empty((int) $idFaculdade) ? $idFaculdade : $request->header('Faculdade', 1))->get();

            return response()->json([
                'items' => $items,
                'count' => count($items)
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

    public function configuracoesFaculdade($idFaculdade, Request $request) {
        try {
            $idFaculdade = !empty((int) $idFaculdade) ? $idFaculdade : $request->header('Faculdade', 1);

            $logo = ConfiguracoesLogotipos::select(
                'id',
                'status',
                'descricao',
                'url_logtipo',
                'slug',
                'fk_faculdade_id'
            )->where('fk_faculdade_id', $idFaculdade)->where('status', 1)->first();


            $redesSociais = ConfiguracoesRedesSociais::select(
                'id',
                'status',
                'titulo',
                'rede_url',
                'fk_faculdade_id'
            )->where('fk_faculdade_id', $idFaculdade)->where('status', 1)->get();

            $sac = ConfiguracoesSac::select(
                'id',
                'status',
                'descricao',
                'telefone_1',
                'telefone_2',
                'skype',
                'hangouts',
                'whatsapp',
                'telegram',
                'email',
                'url_sac',
                'fk_faculdade_id',
                'slug'
            )->where('fk_faculdade_id', $idFaculdade)->where('status', 1)->first();

            $tiposCursos = ConfiguracoesTiposCursosAtivos::select()->where('fk_faculdade_id', $idFaculdade)->where('status', 1)->first();
            $googleAnylitcs = ConfiguracoesPixels::where('tipo_pixel', 'google')->where('status', 1)->where('fk_faculdade_id', $idFaculdade)->first();
            $facebook = ConfiguracoesPixels::where('tipo_pixel', 'facebook')->where('status', 1)->where('fk_faculdade_id', $idFaculdade)->first();

            $estilos = ConfiguracoesEstilos::select(
                        'configuracoes_variaveis.descricao',
                        'configuracoes_estilos_variaveis.value',
                        'configuracoes_variaveis.nome'
                    )
                ->join('configuracoes_estilos_variaveis', 'configuracoes_estilos_variaveis.fk_configuracoes_estilos_id', '=', 'configuracoes_estilos.id')
                ->join('configuracoes_variaveis', 'configuracoes_estilos_variaveis.fk_configuracoes_variaveis_id', '=', 'configuracoes_variaveis.id')
                ->where('fk_faculdade_id', $idFaculdade)
                ->where('configuracoes_estilos.status', 1)->get();


            return response()->json([
                'items' => [
                    'logo' => $logo,
                    'redesSociais' => $redesSociais,
                    'tiposCursosAtivos' => $tiposCursos,
                    'sac' => $sac,
                    'ga' => $googleAnylitcs,
                    'facebook' => $facebook,
                    'itv' =>  !empty($tiposCursos['tipo_layout']) && $tiposCursos['tipo_layout'] === 1 ? $tiposCursos['tipo_layout'] : 0,
                    'layoutHome' => !empty($tiposCursos['tipo_layout']) ? $tiposCursos['tipo_layout'] : 0,
                    'estilos' => $estilos
                ],
            ]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }
}
