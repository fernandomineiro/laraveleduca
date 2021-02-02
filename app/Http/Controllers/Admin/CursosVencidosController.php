<?php

namespace App\Http\Controllers\Admin;

use App\AvisarNovasTurmas;
use App\Curso;
use App\Exports\CursosVencidos;
use App\Exports\CursosVencidosExport;
use App\Helper\EducazMail;
use App\Usuario;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

class CursosVencidosController extends Controller
{
    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['cursos'] = AvisarNovasTurmas::select('cursos.id',
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
            ->get();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    /**
     * @param Request $request
     * @return Excel|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportar(Request $request) {
        //if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $retorno = Excel::download(new CursosVencidos(),'listadeespera.'.strtolower($request->get('export-to-type')).'');
        return $retorno;

    }

    public function avisarnovasturmas(Request $request) {
        try {
            $url = $request->root();
            $interessados = json_decode($request->get('interessados'));

            if ($interessados) {
                foreach ($interessados as $interessado) {
                    $urlPortal = '';
                    if ($url == "http://localhost:8000") {
                        $urlPortal = $urlPortal . 'http://localhost:4200/#/';
                    } elseif ($url == "http://ec2-3-81-68-4.compute-1.amazonaws.com") {
                        $urlPortal = $urlPortal . 'http://18.215.45.177/#/';
                    } else {
                        $urlPortal = $urlPortal . 'http://educaz20.educaz.com.br/#/';
                    }
                    $interessado = json_decode($interessado);
                    $curso = Curso::find($interessado->id);
                    if ($curso->fk_cursos_tipo == 1) {
                        $urlPortal = $urlPortal . 'curso-online/';
                    } elseif ($curso->fk_cursos_tipo == 2) {
                        $urlPortal = $urlPortal . 'curso-presencial/';
                    } else {
                        $urlPortal = $urlPortal . 'curso-remoto/';
                    }
                    $urlPortal = $urlPortal . $curso->id . '/detalhe';
                    $data = [
                        'messageTo' => $interessado->email_aluno,
                        'messageData' => [
                            'nome' => $interessado->nome_aluno,
                            'curso' => $curso,
                            'url' => $urlPortal,
                        ],
                        'messageSubject' => "Aviso sobre novas turmas"
                    ];
                    $aluno = Usuario::where('email', $interessado->email_aluno)->first();

                    $educazmail = new EducazMail(!empty($aluno->fk_faculdade_id) ? $aluno->fk_faculdade_id : 7);
                    $educazmail->avisoNovasTurmas($data);
                }
                \Session::flash('mensagem_sucesso', 'Alunos avisados com sucesso!');
                return Redirect::back();
            } else {
                \Session::flash('mensagem_erro', 'É necessário escolher ao menos um interessado a ser avisado!');
                return Redirect::back();
            }
        } catch (\Exception $e) {
            \Session::flash('mensagem_erro', 'Não foi possível avisar os interessados! ' . $e->getMessage());
            return Redirect::back();
        }
    }
}
