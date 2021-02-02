<?php

namespace App\Http\Controllers\Admin;

use App\ConfiguracoesPixels;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Spatie\Analytics\Analytics;
use Spatie\Analytics\AnalyticsClientFactory;
use Spatie\Analytics\Period;

class GraficoTempoMedioNavegacaoController extends Controller{
    
    public function index(Request $request){
        
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $parametros = $this->processaRequest($request);
        
        $GAclient = AnalyticsClientFactory::createForConfig(config('analytics'));
        $ga = new Analytics($GAclient,$parametros['id_visualizacao']);
        
        $this->arrayViewData['grafico_tempo_medio_navegacao'] = $ga->performQuery(
            Period::create(Carbon::parse($parametros['data'][0]),Carbon::parse($parametros['data'][1])),
            'ga:avgTimeOnPage',[
                'dimensions'    => 'ga:pagePath',
                'sort'          => '-ga:avgTimeOnPage',
                'max-results'   => '20'
            ]
        )->rows;
        
        $this->arrayViewData['data'] = $parametros['data'];
        $this->arrayViewData['all_ies'] = ConfiguracoesPixels::select(DB::raw("CONCAT(faculdades.fantasia,' - ',id_visualizacao) AS ies, configuracoes_pixel.id "))
                                                ->join('faculdades','faculdades.id','configuracoes_pixel.fk_faculdade_id')
                                                ->where('id_visualizacao','>',0)
                                                ->get()
                                                ->pluck('ies','id');
        
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }
    
    private function processaRequest($request){
        
        $parametros = [];
        
        if( $request->has('data') && !empty($request->get('data')) ){
            $explode = explode("-",$request->get('data'));
            $parametros['data'] = [Carbon::createFromFormat('d/m/Y',trim($explode[0]))->format('Y-m-d 00:00:00'),Carbon::createFromFormat('d/m/Y',trim($explode[1]))->format('Y-m-d 23:59:59')];
        }else{
            $parametros['data'] = [Carbon::today()->subYear(1)->format('Y-m-d'),Carbon::today()->format('Y-m-d')];
        }
        
        if( $request->has('id_configuracoes_pixel') && !empty($request->get('id_configuracoes_pixel')) ){
            $parametros['id_visualizacao'] = ConfiguracoesPixels::where('id','=',$request->get('id_configuracoes_pixel'))->first()->id_visualizacao;
        }else{
            $parametros['id_visualizacao'] = ConfiguracoesPixels::where('id_visualizacao','>',0)->first()->id_visualizacao;
        }
        
        return $parametros;
        
    }
    
}
