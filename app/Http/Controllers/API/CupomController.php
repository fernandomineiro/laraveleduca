<?php

namespace App\Http\Controllers\API;

use App\Aluno;
use App\CupomAlunos;
use App\CupomAlunoSemRegistro;
//use App\CupomAssinaturas;
use App\CupomCursos;
use App\CupomCursosCategorias;
//use App\CupomEventos;
use App\CupomTrilhas;
use App\Curso;
use App\CursoCategoria;
use App\Helper\EducazMail;
use App\Usuario;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Cupom;

class CupomController extends Controller
{
    /**
     * Validar Cupom e Retorna o Valor de Desconto
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validar(Request $request)
    {
        try {
            $request2 = $request;
            $request = $request->all();

            if (!isset($request['codigo_cupom']) || empty($request['codigo_cupom'])) {
                return response()->json([
                    'success' => false,
                    'messages' => 'Favor enviar o codigo do cupom!'
                ]);
            }

            $data = Cupom::where('codigo_cupom', $request['codigo_cupom'])
                ->where('data_validade_inicial', '<=', date('Y-m-d'))
                ->where('data_validade_final', '>=', date('Y-m-d'))
                ->first();

            if (!$data || empty($data)) {
                return response()->json([
                    'success' => false,
                    'messages' => 'Cupom inexistente ou fora da validade!'
                ]);
            }

            if (!isset($request['valor']) || empty($request['valor'])) {
                return response()->json([
                    'success' => false,
                    'messages' => 'Favor enviar o valor do pedido!'
                ]);
            }

            $faculdade = $request2->header('Faculdade', 7);
            if($data['fk_faculdade'] && $data['fk_faculdade'] != $faculdade) {
                return response()->json([
                    'success' => false,
                    'messages' => 'Essa faculdade não possui acesso a este cupom!'
                ]);
            }

            if ($data['tipo_cupom_desconto'] == '1') {
                $data['valor'] = round($request['valor'] / 100 * $data['valor'], 2);
            } else {
                $valor = $request['valor'] - $data['valor'];
                if ($valor < 0) {
                    $data['valor'] = $request['valor'];
                    /*return response()->json([
                        'success' => false,
                        'messages' => 'O valor do desconto não pode ser maior que o valor do pedido!'
                    ]);*/
                }
            }

            $total_produtos = count($request['items']['cursos']) + count($request['items']['trilhas']);
            //+count($request['items']['assinaturas']) + count($request['items']['eventos'])
            if ($data['numero_maximo_produtos'] && $total_produtos > $data['numero_maximo_produtos']) {
                return response()->json([
                    'success' => false,
                    'messages' => 'O cupom só pode ser usado para '. $data['numero_maximo_produtos'] .' produto(s)!'
                ]);
            }

            $faculdade = $request['faculdade'];
            $aluno = Aluno::where('fk_usuario_id', $request['aluno'])->first();
            $usuario = Usuario::find($request['aluno']);
            $relacionamentos = $this->retornaRelacionamentoCupons($data['id']);

            if (count($relacionamentos['cupom_cursos']) > 0) {
                // dd($relacionamentos['cupom_cursos']);
                if (count($request['items']['cursos']) > 0) {
                    $cupom_cursos = collect($relacionamentos['cupom_cursos'])
                        ->whereIn('fk_curso', $request['items']['cursos']);
                        //->where('fk_faculdade', '=', $faculdade)
                    if (count($cupom_cursos) != count($request['items']['cursos'])) {
                        return response()->json([
                            'success' => false,
                            'messages' => 'Cursos escolhidos inválidos para este cupom!'
                        ]);
                    }
                }
            }

            if (count($relacionamentos['cupom_alunos']) > 0) {
                if ($faculdade && isset($aluno)) {
                    $filtered = collect($relacionamentos['cupom_alunos'])->filter(function ($value, $key) use ($faculdade, $aluno) {
                        return $value['fk_aluno'] == $aluno->id;// && $value['fk_faculdade'] == $faculdade;
                    });

                    if ($filtered->count() == 0) {
                        return response()->json([
                            'success' => false,
                            'messages' => 'Este usuário não pode usar este cupom!'
                        ]);
                    }
                }
            }

            if (count($relacionamentos['cupom_trilhas']) > 0) {
                if (count($request['items']['trilhas']) > 0) {
                    $cupom_trilhas = collect($relacionamentos['cupom_trilhas'])
                        ->whereIn('fk_trilha', $request['items']['trilhas']);
                        //->where('fk_faculdade', '=', $faculdade);
                    if (count($cupom_trilhas) != count($request['items']['trilhas'])) {
                        return response()->json([
                            'success' => false,
                            'messages' => 'Trilhas escolhidas inválidas para este cupom!'
                        ]);
                    }
                }
            }

            /*if (count($relacionamentos['cupom_assinaturas']) > 0) {
                if (count($request['items']['assinaturas']) > 0) {
                    $cupom_assinaturas = collect($relacionamentos['cupom_assinaturas'])
                        ->whereIn('fk_assinatura', $request['items']['assinaturas'])
                        ->where('fk_faculdade', '=', $faculdade);
                    if (count($cupom_assinaturas) != count($request['items']['assinaturas'])) {
                        return response()->json([
                            'success' => false,
                            'messages' => 'Assinaturas escolhidas inválidas para este cupom!'
                        ]);
                    }
                }
            }

            if (count($relacionamentos['cupom_eventos']) > 0) {
                if (count($request['items']['eventos']) > 0) {
                    $cupom_eventos = collect($relacionamentos['cupom_eventos'])
                        ->whereIn('fk_evento', $request['items']['eventos'])
                        ->where('fk_faculdade', '=', $faculdade);
                    if (count($cupom_eventos) != count($request['items']['eventos'])) {
                        return response()->json([
                            'success' => false,
                            'messages' => 'Trilhas escolhidas inválidas para este cupom!'
                        ]);
                    }
                }
            }*/

            if (count($relacionamentos['cupom_cursos_categorias']) > 0) {
                foreach ($request['items']['cursos'] as $curso) {
                    $categorias = CursoCategoria::select('cursos_categoria.*')
                        ->join('cursos_categoria_curso', 'cursos_categoria.id', '=', 'cursos_categoria_curso.fk_curso_categoria')
                        ->join('cursos', 'cursos.id', '=', 'cursos_categoria_curso.fk_curso')
                        ->where('cursos.id', $curso)->pluck('id');
                    if (count($categorias) > 0) {
                        $cupom_categorias = collect($relacionamentos['cupom_cursos_categorias'])->whereIn('fk_categoria', $categorias);
                        if (count($cupom_categorias) < 1) {
                            return response()->json([
                                'success' => false,
                                'messages' => 'As categorias dos seus produtos são inválidas para este cupom!'
                            ]);
                        }
                    }
                }
            }

            if ($data['numero_maximo_usos'] === 0) {
                return response()->json([
                    'success' => false,
                    'messages' => 'O cupom estourou o número de utilizações!'
                ]);
            }
            if (count($relacionamentos['cupom_alunos_sem_registro']) > 0) {
                $filtered = collect($relacionamentos['cupom_alunos_sem_registro'])->where('email', $usuario->email);

                if ($filtered->count() == 0) {
                    return response()->json([
                        'success' => false,
                        'messages' => 'Este usuário não possui acesso a este cupom!'
                    ]);
                }

                foreach ($filtered as $dado) {
                    if ($dado->numero_usos == 0) {
                        return response()->json([
                            'success' => false,
                            'messages' => 'Este usuário não possui mais usos deste cupom!'
                        ]);
                    }
                }
            }


            return response()->json([
                'success' => isset($data) ? true : false,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema ' . $e->getMessage()
            ]);
        }
    }

    private function retornaRelacionamentoCupons($fk_cupom) {
        $retorno['cupom_cursos'] = CupomCursos::select(
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

        $retorno['cupom_alunos'] = CupomAlunos::select(
            'cupom_alunos.*',
            \DB::raw("concat(alunos.nome, ' ', alunos.sobre_nome) as aluno_nome"),
            'alunos.cpf',
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
            ->join('cupom', 'cupom.id', '=', 'cupom_alunos.fk_cupom')
            ->where('cupom_alunos.fk_cupom', $fk_cupom)
            ->get();

        $retorno['cupom_trilhas'] = CupomTrilhas::select(
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

        /*$retorno['cupom_assinaturas'] = CupomAssinaturas::select(
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

        $retorno['cupom_eventos'] = CupomEventos::select(
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
        $retorno['cupom_cursos_categorias'] = CupomCursosCategorias::select(
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

        $retorno['cupom_alunos_sem_registro'] = CupomAlunoSemRegistro::select(
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

        return $retorno;
    }
}
