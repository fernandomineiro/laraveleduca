<?php

namespace App\Http\Controllers\API;

use App\Curso;
use App\CursoTurmaInscricao;
use App\Helper\EducazMail;
use App\ViewUsuarioCompleto;
use App\ViewUsuarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Professor;
use App\CursoTurmaAgenda;
use App\CursoTurmaAgendaPresenca;
use App\Helper\CertificadoHelper;

use Tymon\JWTAuth\Facades\JWTAuth;


class CusroTurmaPresencasController extends Controller {
    public function __construct() {
        \Config::set('jwt.user', 'App\ViewUsuarios');
        \Config::set('auth.providers.users.model', ViewUsuarios::class);
        parent::__construct();

        /**
         * TODO: Pegar o ID do professor logado
         */
        $this->professorId = 1;
    }


    /**
     * Retorna os cursos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function lista() {
       try {

           $lista = [];
           $loggedUser = JWTAuth::user();
           if ($loggedUser->fk_perfil == 2) {
               $cursos = Curso::select('cursos.*')->join('cursos_faculdades', 'cursos.id', '=', 'cursos_faculdades.fk_curso')
                   ->where('cursos_faculdades.fk_faculdade', $loggedUser->fk_faculdade_id)->get();
           } else {
               $professor = ViewUsuarioCompleto::with('cursos')->where(['fk_usuario_id' => $loggedUser->id])->first();
               $cursos = $professor->cursos;
           }

            /**
             * Percorre os cursos do professor
             */

            $indexCurso = 0;
            foreach ($cursos as $c => $curso) {

                if ($curso->fk_cursos_tipo == Curso::ONLINE ) { // || count($curso->turmas) == 0
                    continue;
                }

                $lista[$indexCurso] = [
                    'courseId' => $curso->id,
                    'courseName' => $curso->titulo,
                    'panelOpenState' => false,
                    'courseType' => $curso->fk_cursos_tipo,
                    'countTurmas' => count($curso->turmas),
                    'turmas' => [],
                ];

                /**
                 * Percorre as Turmas do curso
                 */
                if ($turmas = $curso->turmas) {

                    foreach ($turmas as $t => $turma) {

                        $lista[$indexCurso]['turmas'][$t] = [
                            'agenda' => [],
                            'inscricoes' => []
                        ];

                        /**
                         * Percorre a agenda da turma
                         */
                        if ($agenda = $turma->agenda) {
                            foreach ($agenda as $dia) {
                                $dia = $dia->toArray();
                                $dia['data'] = (new \DateTime($dia['data']))->format('d/m/Y');
                                $lista[$indexCurso]['turmas'][$t]['agenda'][] = $dia;
                            }
                        }
                    }
                }
                $indexCurso++;
            }

            return response()->json(['items' => $lista, $cursos]);


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
     * Confirma ou remove uma presença
     *
     * @param lluminate\Http\Request
     * @param $agendaId - Id da agenda
     * @return \Illuminate\Http\JsonResponse
     */
    public function atualizar(Request $request) {
       try {
            $data = [];

            if (!isset($request->dayId)) {
                throw new \Exception('agenda_id expected');
            }

            if (!isset($request->students)) {
                throw new \Exception('alunos expected');
            }

            foreach ($request->students as $aluno) {

                $presenca = CursoTurmaAgendaPresenca::firstOrCreate([
                            'fk_usuario' => $aluno['id'],
                            'fk_agenda' => $request->dayId
                ]);

                $presenca->presente = (boolean) $aluno['presente'];
                $presenca->save();

                $agenda = CursoTurmaAgenda::where('fk_curso', $request->courseId )->get();
                $records = DB::select("select count(*) as quantidade_presenca
                                    from cursos_turmas_agenda_presenca 
                                        join cursos_turmas_agenda ON cursos_turmas_agenda.id = cursos_turmas_agenda_presenca.fk_agenda
                                    where fk_usuario = {$aluno['id']}
                                        AND presente = 1
                                        AND fk_curso = ".$request->courseId);
                $inscricao = CursoTurmaInscricao::where('fk_usuario', $aluno['id'])->where('fk_curso', $request->courseId)->first();
                if (!empty($agenda) && !empty($records[0])) {
                    $inscricao->percentual_completo = (100 / count($agenda) * $records[0]->quantidade_presenca);
                    $inscricao->save();
                }

                $data[] = [$presenca->toArray(), $agenda, $records, $inscricao];

                //Quando atribuir presença ao aluno, verificar possibilidade de emissão de certificado
                $certificadoHelper = new CertificadoHelper();
                $retornoCertificado = $certificadoHelper->emiteCertificado($aluno['id'], $request->courseId);
            }

            return response()->json([ 'success' => true, 'data' => $data]);

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
     * Retorna os cursos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function agenda($turmaId)
    {
       try {

            $lista = [];

            $agendas = CursoTurmaAgenda::where([
                         'fk_turma' => $turmaId
             ])->get();

            foreach($agendas as $agenda) {

                $lista[] = [
                    "day" => (new \DateTime($agenda->data))->format('d'),
                    "month" => (new \DateTime($agenda->data))->format('m'),
                    "year" => (new \DateTime($agenda->data))->format('Y'),
                    "title" => $agenda->turma->curso->titulo,
                    "agenda_nome" => $agenda->nome,
                    "agenda_descricao" => $agenda->descricao,
                    "date" => $agenda->turma->data,
                    "local" => $agenda->turma->curso->endereco_presencial
                ];

            }

            return response()->json([
                        'items' => $lista,
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
     * Retorna os cursos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function presencas($agendaId)
    {
       try {

            $lista = [];

            $presencas = CursoTurmaAgendaPresenca::where([
                         'fk_agenda' => $agendaId
             ])->get();

            foreach($presencas as $presenca) {

                $lista[] = [
                    "id" => $presenca->aluno->id,
                    "name" => $presenca->aluno->nome,
                    "rg" => $presenca->aluno->rg,
                    "presente" => $presenca->presente,
                   // 'presencePercent' => 0 // TODO: fazer lógica se necessário
                ];

            }

            return response()->json([
                        'items' => $lista,
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

    public function students($courseId, $turmaId, $agendaId) {
        try {
            $students = Curso::select('usuarios.id', 'usuarios.nome', 'cursos_turmas_inscricao.percentual_completo', 'cursos_turmas_agenda_presenca.presente')
                ->join('cursos_turmas', 'cursos_turmas.fk_curso', '=', 'cursos.id')
                ->join('cursos_turmas_inscricao', function ($join) {
                    $join->on('cursos_turmas_inscricao.fk_curso', '=', 'cursos.id');
                    $join->on('cursos_turmas_inscricao.fk_turma', '=', 'cursos_turmas.id');
                })
                ->join('cursos_turmas_agenda', 'cursos_turmas.id', '=', 'cursos_turmas_agenda.fk_turma')
                ->join('usuarios', 'usuarios.id', '=', 'cursos_turmas_inscricao.fk_usuario')
                ->leftjoin('cursos_turmas_agenda_presenca', function ($join) {
                    $join->on('cursos_turmas_agenda_presenca.fk_agenda', '=', 'cursos_turmas_agenda.id');
                    $join->on('cursos_turmas_agenda_presenca.fk_usuario', '=', 'usuarios.id');
                })
                ->where('cursos.id', $courseId)
                ->where('cursos_turmas_agenda.id', $agendaId)
                ->where('cursos_turmas.id', $turmaId)->get()->toArray();

            return response()->json($students);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

}
