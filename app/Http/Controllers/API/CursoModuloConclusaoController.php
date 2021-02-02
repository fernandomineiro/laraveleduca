<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\CursoModuloAluno;
use App\CursoModulo;

class CursoModuloConclusaoController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Adicionar Modulo
     *
     * @param integer                  $cursoId
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adicionarModulosPorCurso(int $cursoId, Request $request)
    {
        $payload = $request->all();
        $validacao = $this->validaInput($payload, ['faculdade', 'aluno', 'pedido']);

        if ($validacao instanceof JsonResponse) {
            return $validacao;
        }

        $modulosDoCurso = CursoModulo::select()->where('fk_curso', '=', $cursoId)
            ->where('status', '=', 1)
            ->orderBy('ordem', 'asc')
            ->get();

        try {

            foreach ($modulosDoCurso as $modulo) {
                $moduloCurso = CursoModuloAluno::where([
                    ['fk_curso_id', '=', $cursoId],
                    ['fk_faculdade_id', '=', $payload['faculdade']],
                    ['fk_aluno_id', '=', $payload['aluno']],
                    ['fk_curso_modulo_id', '=', $modulo->id],
                ])->first();

                if (!empty($moduloCurso)) {
                    continue;
                }

                $cursoModuloAluno = new CursoModuloAluno();
                $cursoModuloAluno->fk_curso_id = $cursoId;
                $cursoModuloAluno->fk_faculdade_id = $payload['faculdade'];
                $cursoModuloAluno->fk_aluno_id = $payload['aluno'];
                $cursoModuloAluno->fk_curso_modulo_id = $modulo->id;
                $cursoModuloAluno->data_criacao = date('Y-m-d H:i:s');
                $cursoModuloAluno->flag_concluido = 0;
                $cursoModuloAluno->fk_pedido_id = $payload['pedido'];
                $cursoModuloAluno->save();
            }

            return response()->json([
                'success' => true,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
        }
    }

    /**
     * @param array $dados
     * @param array $regra
     * @return \Illuminate\Http\JsonResponse
     */
    protected function validaInput($dados, $regra = [])
    {
        foreach ($regra as $field) {
            if (!array_key_exists($field, $dados)) {
                return response()->json([
                    'success' => false,
                    'error' => $field . ' é obrigatório!',
                    'data' => $dados
                ]);
            }
        }
    }

    /**
     * Marcar modulo como: Concluido/Pendente
     *
     * @param integer                  $cursoId
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mudarStatusModulo(int $cursoId, Request $request)
    {
        $payload = $request->all();
        $validacao = $this->validaInput($payload, ['aluno', 'modulo', 'status']);

        if ($validacao instanceof JsonResponse) {
            return $validacao;
        }

        $cursoModuloAluno = CursoModuloAluno::where('fk_curso_id', '=', $cursoId)
            ->where('fk_aluno_id', '=', $payload['aluno'])
            ->where('fk_curso_modulo_id', '=', $payload['modulo'])
            ->first();

        $cursoModuloAluno->flag_concluido = $payload['status'];
        $cursoModuloAluno->save();

        return response()->json([
            'success' => true,
            'data' => CursoModuloAluno::find($cursoModuloAluno->id)->toArray()
        ]);
    }

    /**
     * Verificar Status do Modulo
     *
     * @param integer                  $cursoId
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarModulo(int $cursoId, Request $request)
    {
        $payload = $request->all();
        $validacao = $this->validaInput($payload, ['aluno', 'modulo']);

        if ($validacao instanceof JsonResponse) {
            return $validacao;
        }

        $cursoModuloAluno = CursoModuloAluno::where('cursos_modulos_alunos.fk_aluno_id', $payload['aluno'])
            ->where('cursos_modulos_alunos.fk_curso_id', $cursoId)
            ->where('cursos_modulos_alunos.fk_curso_modulo_id', $payload['modulo'])
            ->first();

        return response()->json([
            'concluido' => $cursoModuloAluno->flag_concluido ? '1' : '0'
        ]);
    }

    /**
     * Verifica Percentual de Conclusão
     *
     * @param integer                  $cursoId
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarPercentualConclusao(int $cursoId, Request $request)
    {
        $payload = $request->all();
        $validacao = $this->validaInput($payload, ['aluno']);

        if ($validacao instanceof JsonResponse) {
            return $validacao;
        }

        $dataConcluido = CursoModuloAluno::where('cursos_modulos_alunos.fk_aluno_id', $payload['aluno'])
            ->where('cursos_modulos_alunos.fk_curso_id', $cursoId)
            ->where('cursos_modulos_alunos.flag_concluido', 1)
            ->get();

        $dataRestante = CursoModuloAluno::where('cursos_modulos_alunos.fk_aluno_id', $payload['aluno'])
            ->where('cursos_modulos_alunos.fk_curso_id', $cursoId)
            ->where('cursos_modulos_alunos.flag_concluido', 0)
            ->get();

        $total = count($dataConcluido->toArray()) + count($dataRestante->toArray());
        $concluido = count($dataConcluido->toArray());
        $restante = count($dataRestante->toArray());

        $data = [
            'total_modulos' => $total,
            'total_concluido' => $concluido,
            'total_restante' => $restante,
            'percentual_concluido' => round(($concluido / $total * 100), 0) . '%'
        ];

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Resumo por Curso
     *
     * @param integer                  $cursoId
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resumo(int $cursoId, Request $request)
    {
        $payload = $request->all();
        $validacao = $this->validaInput($payload, ['aluno']);

        if ($validacao instanceof JsonResponse) {
            return $validacao;
        }

        $dataConcluido = CursoModuloAluno::select('fk_curso_modulo_id')
            ->where('cursos_modulos_alunos.fk_aluno_id', $payload['aluno'])
            ->where('cursos_modulos_alunos.fk_curso_id', $cursoId)
            ->where('cursos_modulos_alunos.flag_concluido', 1)
            ->get();

        $dataRestante = CursoModuloAluno::select('fk_curso_modulo_id')
            ->where('cursos_modulos_alunos.fk_aluno_id', $payload['aluno'])
            ->where('cursos_modulos_alunos.fk_curso_id', $cursoId)
            ->where('cursos_modulos_alunos.flag_concluido', 0)
            ->get();

        $total = count($dataConcluido->toArray()) + count($dataRestante->toArray());
        $concluido = count($dataConcluido->toArray());
        $restante = count($dataRestante->toArray());

        $resumo = [
            'modulos_concluidos' => $dataConcluido,
            'modulos_restante' => $dataRestante
        ];

        $data = [
            'total_modulos' => $total,
            'total_concluido' => $concluido,
            'total_restante' => $restante,
            'percentual_concluido' => round(($concluido / $total * 100), 0) . '%',
            'resumo' => $resumo
        ];

        return response()->json([
            'data' => $data
        ]);
    }
}
