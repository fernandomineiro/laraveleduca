<?php

namespace App\Http\Controllers\API;

use App\AgendaEventos;
use App\Helper\EducazMail;
use App\ViewUsuarios;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eventos;
use Illuminate\Support\Facades\Validator;

class EventoController extends Controller
{

    public function __construct() {
        \Config::set('jwt.user', 'App\ViewUsuarios');
        \Config::set('auth.providers.users.model', ViewUsuarios::class);

        parent::__construct();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($idFaculdade = null)
    {
        try {
            $eventos = Eventos::select(
                'eventos.id',
                'eventos.titulo',
                'eventos.descricao',
                'eventos.fk_categoria',
                'cursos_categoria.titulo as categoria',
                'eventos.imagem',
                'eventos.status',
                'faculdades.fantasia as nome_faculdade',
                'faculdades.id as id_faculdade'
            )
                ->join('faculdades', 'faculdades.id', '=', 'eventos.fk_faculdade')
                ->join('cursos_categoria', 'cursos_categoria.id', '=', 'eventos.fk_categoria');

            if ($idFaculdade) {
                $eventos->where('eventos.fk_faculdade', '=', $idFaculdade);
            }

            $eventos->where('eventos.status', '=', 5);

            $data = $eventos->get();
            foreach ($data as $evento) {
                $evento['agendas'] = AgendaEventos::select(
                    'agenda_evento.descricao as agenda_descricao',
                    'agenda_evento.data_inicio as agenda_data_inicio',
                    'agenda_evento.data_final as agenda_data_final',
                    'agenda_evento.hora_inicio as agenda_hora_inicio',
                    'agenda_evento.hora_final as agenda_hora_final',
                    'agenda_evento.valor as agenda_valor'
                )
                    ->where('agenda_evento.fk_evento', '=', $evento->id)->get();
            }
            return response()->json(['items' => $data, 'count' => count($data)]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function create(Request $request) {
        try {
            $eventos = new Eventos();
            $validator = Validator::make($request->all(), $eventos->rules, $eventos->messages);

            if (!$validator->fails()) {
                $dadosForm = $request->except('_token');
                if ($request->hasFile('imagem')) {
                    $file = $request->file('imagem');
                    $dadosForm['imagem'] = $this->uploadFile('imagem', $file);
                }

                $dadosForm = $this->insertAuditDataApi($dadosForm);
                $resultado = $eventos->create($dadosForm);

                if ($resultado) {
                    return response()->json([
                        'success' => true,
                        'message' => $this->msgInsert
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => $this->msgInsertErro
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Não foi possível inserir o registro! Campos inválidos'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Não foi possível inserir o registro! ' . $e->getMessage()
            ]);
        }

    }

    public function retornaPorId($idEvento)
    {
        try {
            $eventos = Eventos::select(
                'eventos.id',
                'eventos.titulo',
                'eventos.descricao',
                'eventos.fk_categoria',
                'cursos_categoria.titulo as categoria',
                'eventos.imagem',
                'eventos.status',
                'eventos.endereco',
                'faculdades.fantasia as nome_faculdade',
                'faculdades.id as id_faculdade'
            )
                ->join('faculdades', 'faculdades.id', '=', 'eventos.fk_faculdade')
                ->join('cursos_categoria', 'cursos_categoria.id', '=', 'eventos.fk_categoria')
                ->where('eventos.id', '=', $idEvento)
                ->where('eventos.status', '=', 5);

            $data = $eventos->get();
            foreach ($data as $evento) {
                $evento['agendas'] = AgendaEventos::select(
                    'agenda_evento.descricao',
                    'agenda_evento.data_inicio',
                    'agenda_evento.data_final',
                    'agenda_evento.hora_inicio',
                    'agenda_evento.hora_final',
                    'agenda_evento.fk_professor',
                    \DB::raw("CONCAT(professor.nome, ' ', professor.sobrenome) as nome_palestrante"),
                   'professor.mini_curriculum as curriculo',
                    'agenda_evento.valor as agenda_valor'
                )
                    ->leftJoin('professor', 'professor.id', '=', 'agenda_evento.fk_professor')
                    ->where('agenda_evento.fk_evento', '=', $evento->id)
                    ->get();
            }
            return response()->json(['items' => $data, 'count' => count($data)]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrigir o problema'
            ]);
        }
    }

    public function search(Request $request) {
        try {
            $idCategoria = $request->get('idCategoria');
            $sort = $request->get('order1');
            $sort2 = $request->get('order2');
            $cidade = $request->get('cidade');
            $search = $request->get('search');
            $eventos = Eventos::select(
                'eventos.id',
                'eventos.titulo',
                'eventos.imagem',
                'eventos.descricao',
                'eventos.fk_categoria',
                'cursos_categoria.titulo as categoria',
                'eventos.status',
                'faculdades.fantasia as nome_faculdade',
                'faculdades.id as id_faculdade'
            )
                ->join('faculdades', 'faculdades.id', '=', 'eventos.fk_faculdade')
                ->join('cursos_categoria', 'cursos_categoria.id', '=', 'eventos.fk_categoria');

            if (!empty($idCategoria) && $idCategoria != -1) {
                $eventos->where('cursos_categoria.id', '=', $idCategoria);
            }

            if (!empty($cidade) && $cidade != -1) {
                //TODO descobrir a regra desse filtro
                // $eventos->where('cursos_categoria.id', '=', $cidade);
            }

            if (!empty($search)) {
                $eventos->where(\DB::raw('LOWER(eventos.titulo)'), 'like', '%' . strtolower($search) . '%');
            }

            if (!empty($sort)) {
                switch ($sort) {
                    case 'asc':
                        $eventos->orderBy('eventos.titulo', 'asc');
                        break;

                    case 'desc':

                        $eventos->orderBy('eventos.titulo', 'desc');
                        break;

                    case 'vendidos':
                        $eventos->orderBy('vendidos', 'desc');
                        break;

                    /*case 'promocoes':
                        $eventos->orderByRaw('CAST(promocao AS DECIMAL(10,2)) desc');
                        break;*/

                    default:
                        $eventos->orderBy('eventos.id', 'desc');
                        break;
                }
            }

            if (!empty($sort2)) {
                //TODO: decidir como esse filtro será montado
            }

            $eventos->where('eventos.status', '=', 5);

            $data = $eventos->get();
            foreach ($data as $evento) {
                $evento['agendas'] = AgendaEventos::select(
                    'agenda_evento.descricao as agenda_descricao',
                    'agenda_evento.data_inicio as agenda_data_inicio',
                    'agenda_evento.data_final as agenda_data_final',
                    'agenda_evento.hora_inicio as agenda_hora_inicio',
                    'agenda_evento.hora_final as agenda_hora_final',
                    'agenda_evento.valor as agenda_valor'
                )
                    ->where('agenda_evento.fk_evento', '=', $evento['id'])->get();
            }
            return response()->json(['items' => $data, 'count' => count($data)]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function agenda($idEvento)
    {
        try {
            $eventos = Eventos::select(
                'agenda_evento.id',
                'agenda_evento.titulo',
                'agenda_evento.descricao as titulo_agenda',
                'agenda_evento.data_inicio',
                'agenda_evento.data_final',
                'agenda_evento.hora_inicio',
                'agenda_evento.hora_final',
                'agenda_evento.valor',
                'faculdades.fantasia as nome_faculdade',
                'faculdades.id as id_faculdade'
            )
                ->join('faculdades', 'faculdades.id', '=', 'cursos_categoria.fk_faculdade');

            if ($idEvento) {
                $eventos->where('agenda_evento.fk_evento', '=', $idEvento);
            }

            $eventos->where('eventos.status', '=', 5);

            $data = $eventos->get();
            return response()->json(['items' => $data, 'count' => count($data)]);
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
     * Eventos por Professor
     */
    public function eventosPorProfessor($idUsuario)
    {
        try {
            $eventos = Eventos::select(
                'eventos.id',
                'eventos.titulo',
                'eventos.imagem',
                'eventos.descricao',
                'eventos.criacao',
                'eventos.fk_categoria',
                'cursos_categoria.titulo as categoria',
                'eventos.status',
                'faculdades.fantasia as nome_faculdade',
                'faculdades.id as id_faculdade'
            )
                ->join('faculdades', 'faculdades.id', '=', 'eventos.fk_faculdade')
                ->join('cursos_categoria', 'cursos_categoria.id', '=', 'eventos.fk_categoria');

            $user = ViewUsuarios::find($idUsuario);
            if ($user->fk_perfil == 2) {
                $eventos->where('eventos.fk_faculdade', '=', $user->fk_faculdade_id);
            } elseif ($idUsuario) {
                $eventos->where('eventos.fk_criador_id', $idUsuario);
            }

            $eventos->where('eventos.status', '=', 5);

            $data = $eventos->get();
            foreach ($data as $evento) {
                $evento['agendas'] = AgendaEventos::select(
                    'agenda_evento.descricao as agenda_descricao',
                    'agenda_evento.data_inicio as agenda_data_inicio',
                    'agenda_evento.data_final as agenda_data_final',
                    'agenda_evento.hora_inicio as agenda_hora_inicio',
                    'agenda_evento.hora_final as agenda_hora_final',
                    'agenda_evento.valor as agenda_valor'
                )->where('agenda_evento.fk_evento', '=', $evento['id'])
                    ->get();
            }

            return response()->json(['items' => $data, 'count' => count($data)]);
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
