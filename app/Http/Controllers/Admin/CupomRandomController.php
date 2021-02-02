<?php

namespace App\Http\Controllers\Admin;

use App\CupomAlunos;
use App\CupomCursos;
use App\CupomCursosCategorias;
use App\CupomTrilhas;
use App\Curso;
use App\CursoCategoria;
use App\Faculdade;
use App\Http\Controllers\Controller;

use App\Cupom;
use App\Exports\CuponsExport;
use App\Trilha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class CupomRandomController extends Controller
{
    //
    public function incluir(){
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['lista_faculdades'] = Faculdade::all()->where('status', '=', 1)->sortBy('fantasia')->pluck('fantasia', 'id')->toArray();

        $this->arrayViewData['categorias'] = CursoCategoria::all()->where('status', '=', 1)->sortBy('autocomplete')->pluck('autocomplete', 'id')->toArray();

        $lista_cursos = Curso::lista()->sortBy('autocomplete')->pluck('autocomplete', 'id')->toArray();


        $this->arrayViewData['lista_cursos'] = $lista_cursos;

        $this->arrayViewData['lista_trilhas'] = Trilha::searchTrilha(null)->sortBy('autocomplete')->pluck('autocomplete', 'id')->toArray();


        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request){
        DB::beginTransaction();
        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);
        $request->validate([
            'total_cupons' => 'required | numeric',
            'faculdade' => 'required'
        ]);

        try {
            $faculdade = Faculdade::find((int) $request->get('faculdade'));
            $data_inicio = date("Y-m-d");
            $data_fim = date("Y-m-d", strtotime('+1 years'));
            $total_cupons = (int) $request->get('total_cupons');
            $numero_maximo_usos = $request->get('numero_maximo_usos');
            $numero_maximo_produtos = $request->get('numero_maximo_produtos');
            $tipo_cupom_desconto = $request->get('tipo_cupom_desconto');
            $valor = $request->get('valor');

            $data = [];
            for ($i = 1; $i <= $total_cupons; $i++) {
                $retorno = Cupom::create([
                    'titulo' => $faculdade->fantasia . ' Cupom ' . $i,
                    'codigo_cupom' => Str::random(12),
                    'descricao' => 'Cupom gerado automaticamente para a faculdade ' . $faculdade->fantasia,
                    'data_validade_inicial' => $data_inicio,
                    'data_validade_final' => $data_fim,
                    'data_cadastro' => $data_inicio,
                    'tipo_cupom_desconto' => ($tipo_cupom_desconto) ? $tipo_cupom_desconto : 1,
                    'numero_maximo_usos' => ($numero_maximo_usos) ? $numero_maximo_usos : 1,
                    'numero_maximo_produtos' => ($numero_maximo_produtos) ? $numero_maximo_produtos : 1,
                    'fk_faculdade' => $faculdade->id,
                    'status' => 1,
                    'valor' => ($valor) ? $valor : 100
                ]);
                $trilhas = ($request->get('fk_trilha')) ? implode(', ', $request->get('fk_trilha')) : '';
                $categorias = ($request->get('fk_categoria')) ? implode(', ', $request->get('fk_categoria')) : '';
                $cursos = ($request->get('fk_curso')) ? implode(', ', $request->get('fk_curso')) : '';
                $retorno2 = $this->salvarRelacionamentos(null, $request->except('_token'), $retorno->id);
                $retorno = collect($retorno);
                $retorno->put('categorias', $categorias);
                $retorno->put('trilhas', $trilhas);
                $retorno->put('cursos', $cursos);
                array_push($data, $retorno);
            }

            $this->arrayViewData['lista_inseridos'] = $data;
            $this->arrayViewData['tipo_cupom'] = ['1' => 'Percentual (%)', '2' => 'Espécie (R$)'];

            //return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);

            DB::commit();
            return Excel::download(new CuponsExport($data), 'cupons.xlsx');

        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('mensagem_erro', $this->msgInsertErro);
            return Redirect::back()->withErrors($e->getMessage())->withInput();
        }

    }

    private function salvarRelacionamentos($faculdade = null, $dados, $fk_cupom) {
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
    }

    /**
     * @param Request $request
     * @return Excel|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportar(Request $request) {
        //if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        dd($request->get('cupons'));
        $retorno = Excel::download(new CuponsExport($request->get('cupons')), 'cupons.'.strtolower($request->get('export-to-type')).'');
        return $retorno;

    }
}
