<?php

namespace App\Http\Controllers\API;


use App\AvisarNovasTurmas;
use App\Curso;
use App\Exports\CursosVencidos;
use App\Exports\CursosVencidosExport;
use App\Helper\EducazMail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

class CursosVencidosController extends Controller
{
    public function index(Request $request)
    {
        try {
            $data = AvisarNovasTurmas::select('cursos.id',
                'cursos.titulo',
                'cursos.fk_cursos_tipo',
                'avisar_novas_turmas.nome_aluno',
                'avisar_novas_turmas.data_atualizacao',
                'avisar_novas_turmas.data_criacao',
                'cursos_tipo.titulo as curso_tipo',
                'faculdades.fantasia as faculdade',
                'avisar_novas_turmas.email_aluno')
                ->join('cursos', 'avisar_novas_turmas.fk_curso', '=', 'cursos.id')
                ->join('cursos_tipo', 'cursos_tipo.id', '=', 'cursos.fk_cursos_tipo')
                ->join('faculdades', 'faculdades.id', '=', 'avisar_novas_turmas.fk_faculdade')
                ->where('faculdades.id', '=', $request->header('Faculdade', 1))
                ->get();

            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function avisarNovasTurmas(Request $request) {
        try {
            $url = $request->root();
            $interessados = $request->all();
            $educazmail = new EducazMail($request->header('Faculdade', 7));
            if ($interessados) {
                foreach ($interessados as $interessado) {
                    $urlPortal = '';
                    if ($url == "http://localhost:8000" || $url == "http://127.0.0.1:8000") {
                        $urlPortal = $urlPortal . 'http://localhost:4200/#/';
                    } elseif ($url == "http://ec2-3-81-68-4.compute-1.amazonaws.com") {
                        $urlPortal = $urlPortal . 'http://18.215.45.177/#/';
                    } else {
                        $urlPortal = $urlPortal . 'http://educaz20.educaz.com.br/#/';
                    }
                    $curso = Curso::find($interessado['id']);
                    if ($curso->fk_cursos_tipo == 1) {
                        $urlPortal = $urlPortal . 'curso-online/';
                    } elseif ($curso->fk_cursos_tipo == 2) {
                        $urlPortal = $urlPortal . 'curso-presencial/';
                    } else {
                        $urlPortal = $urlPortal . 'curso-remoto/';
                    }
                    $urlPortal = $urlPortal . $curso->id . '/detalhe';
                    $data = [
                        'messageTo' => $interessado['email_aluno'],
                        'messageData' => [
                            'nome' => $interessado['nome_aluno'],
                            'curso' => $curso,
                            'url' => $urlPortal,
                        ],
                        'messageSubject' => "Aviso sobre novas turmas"
                    ];

                    $educazmail->avisoNovasTurmas($data);
                }
                return response()->json([
                    'success' => true,
                    'message' => 'Alunos avisados com sucesso!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'É necessário escolher ao menos um interessado a ser avisado!'
                ]);
            }
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }
}

