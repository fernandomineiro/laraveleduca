<?php

namespace App\Http\Controllers\Admin;

use App\GraficosRelatorios;
use App\Faculdade;
use App\Exports\RelatorioVendasExport;
use Excel;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class GraficosFinanceirosController extends Controller
{
    public function index(Request $request)
    {
        if (!$this->validateAccess(Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $parametros = $this->processaRequestVisaoGeral($request);

        $this->arrayViewData['faculdades'] = Faculdade::all()->where('status', '=', 1)->pluck('razao_social', 'id');
        $this->arrayViewData['faculdades']->prepend('Selecione', '0');
        
        $this->arrayViewData['dados_pedidos'] = GraficosRelatorios::graficos_pedidos($parametros)->get();
        $this->arrayViewData['dados_categorias_mais_vendidas'] = GraficosRelatorios::graficos_categorias_mais_vendidos($parametros)->get();
        $this->arrayViewData['dados_cursos_mais_vendidos'] = GraficosRelatorios::graficos_cursos_mais_vendidos($parametros)->get();
        $this->arrayViewData['dados_parceiros_mais_vendidos'] = GraficosRelatorios::graficos_parceiros_mais_vendidos($parametros)->get();

        $this->arrayViewData['data'] = $parametros['data'];

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {
        if (!$this->validateAccess(Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $parametros = $this->processaRequest($request);

        return (new RelatorioVendasExport($parametros))->download('invoices.xlsx');
    }

    private function processaRequestVisaoGeral($request)
    {
        $parametros = [];

        if ($request->has('data') && !empty($request->get('data'))) {
            $explode = explode("-", $request->get('data'));
            $parametros['data'] = [Carbon::createFromFormat('d/m/Y', trim($explode[0]))->format('Y-m-d 00:00:00'),Carbon::createFromFormat('d/m/Y', trim($explode[1]))->format('Y-m-d 23:59:59')];
        } else {
            $parametros['data'] = [Carbon::today()->subYear(1)->format('Y-m-d'),Carbon::today()->format('Y-m-d')];
        }

        return $parametros;
    }

    private function processaRequest($request)
    {
        $parametros = [];
        $parametros['orderby'] = 'pedidos.id';
        $parametros['sort'] = 'DESC';

        if ($request->get('orderby') && $request->get('sort')) {
            $parametros['orderby'] = $request->get('orderby');
            $parametros['sort'] = $request->get('sort');
        }

        if ($request->has('pedido_pid') && !empty($request->get('pedido_pid'))) {
            $parametros['pedido_pid'] = $request->get('pedido_pid');
        }

        if ($request->has('data_compra') && !empty($request->get('data_compra'))) {
            $explode = explode("-", $request->get('data_compra'));
            $parametros['data_compra'] = [Carbon::createFromFormat('d/m/Y', trim($explode[0]))->format('Y-m-d'),Carbon::createFromFormat('d/m/Y', trim($explode[1]))->format('Y-m-d')];
        } else {
            $parametros['data_compra'] = [Carbon::today()->subDay(30)->format('Y-m-d'),Carbon::today()->format('Y-m-d')];
        }

        if ($request->has('aluno') && !empty($request->get('aluno'))) {
            $parametros['aluno'] = $request->get('aluno');
        }

        return $parametros;
    }

    public function export_finances()
    {
        return Excel::download(new RelatorioVendasExport, 'financeiro.xlsx');
    }
}
