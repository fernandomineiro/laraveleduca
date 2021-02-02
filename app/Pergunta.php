<?php

namespace App;

use App\Traits\EducazSoftDelete;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class Pergunta extends Model {

    use EducazSoftDelete;

    const CREATED_AT = 'criacao';
    const UPDATED_AT = 'atualizacao';
    const SOFT_DELETE = 'status';

    const MENSAGEM_LIDA = 1;
    const MENSAGEM_NAO_LIDA = 2;

    protected $primaryKey = 'id';
    protected $table = 'pergunta';

    public $timestamps = true;

    protected $fillable = [
        'pergunta',
        'fk_curso',
        'fk_atualizador_id',
        'fk_criador_id',
        'criacao',
        'atualizacao',
        'status'
    ];

    public $rules = [
        'fk_curso' => 'required',
        'pergunta' => 'required'
    ];

    public $messages = [
        'pergunta' => 'Resposta',
        'fk_curso' => 'Pergunta',
    ];

    /**
     * @param $professorId
     * @return array
     */
    public static function getPerguntasProfessor($professorId, $parametros = null) {
        $sql = "SELECT 
                    p.id, 
                    p.pergunta, 
                    p.fk_curso, 
                    p.status,
                    p.fk_criador_id, 
                    p.data_criacao,
                    p.data_atualizacao,
                    p.data_criacao as sentDate,
                    p.status,
                    c.id as curso_id, 
                    c.fk_professor,
                    u.nome AS name, 
                    c.titulo as courseName,
                    c.fk_cursos_tipo as cursos_tipo,
                    tc.titulo as titulo_cursotipo
                FROM 
                    pergunta p
                INNER JOIN 
                    cursos c ON p.fk_curso = c.id
                INNER JOIN usuarios u ON p.fk_criador_id = u.id
                INNER JOIN cursos_tipo tc on tc.id = c.fk_cursos_tipo
                INNER JOIN cursos_faculdades ON c.id = cursos_faculdades.fk_curso ";

        $loggedUser = ViewUsuarios::find($professorId);
        if ($loggedUser->fk_perfil == 2) {
            $sql .= ' WHERE  cursos_faculdades. fk_faculdade = ?';
            $professorId = $loggedUser->fk_faculdade_id;
        } else {

            $professor = Professor::where('fk_usuario_id', $loggedUser->id)->first();
            if (!empty($professor)) {
                $professorId = $professor->id;
            }

            $sql .= ' WHERE c.fk_professor = ? ';
        }

        if ($parametros) {
            if ($parametros['modalidade'] && $parametros['modalidade'] != 0) {
                $sql .= ' and c.fk_cursos_tipo = ' . $parametros['modalidade'];
            }

            if ($parametros['curso'] && $parametros['curso'] != 0) {
                $sql .= ' and c.id = ' . $parametros['curso'];
            }

            if ($parametros['dataEnvio']) {
                $sql .= ' and DATE(p.data_criacao) = \'' . $parametros['dataEnvio'] . '\'';
            }
        }
        return DB::select($sql, [$professorId]);
    }

    /**
     * @param $id
     * @return |null
     */
    public static function getPerguntaCurso($id) {

        $sql = 'SELECT 
                    p.id, 
                    p.pergunta, 
                    p.fk_curso, 
                    p.status, 
                    p.fk_criador_id, 
                    p.data_criacao, 
                    p.data_atualizacao, 
                    c.id AS curso_id, 
                    c.fk_professor, 
                    u.nome, 
                    c.titulo
                FROM   
                    pergunta p 
                INNER JOIN 
                    cursos c ON p.fk_curso = c.id 
                INNER JOIN 
                    usuarios u ON p.fk_criador_id = u.id 
                WHERE  
                    c.id = ?';

        $result = DB::select($sql, [$id]);

        if (!empty($result)) {
            return $result[0];
        }

        return null;
    }

    /**
     * @param $perguntaId
     * @return array
     */
    public static function getRespostas($perguntaId, $curso = null, $idUsuario) {
        $sql = 'SELECT 
                    pr.id, 
                    pr.resposta, 
                    pr.status, 
                    pr.fk_criador_id, 
                    pr.data_criacao, 
                    pr.atualizacao
                 FROM  
                    pergunta_resposta pr
                WHERE 
                    pr.fk_pergunta = ?';
        if($curso) {
            $sql .= ' and (pr.fk_criador_id = ' . $curso->professor->fk_usuario_id . ' or pr.fk_criador_id = ' . $idUsuario . ')';
        }
        
        $sql .=' ORDER BY pr.id DESC';
        
         return DB::select($sql, [$perguntaId]);
    }

    /**
     * @param $cursoId
     * @param $usuarioId
     * @return array
     * @throws \Exception
     */
    public static function getChatCurso($cursoId, $usuarioId) {
        $pergunta = self::getPerguntaCurso($cursoId);
        $curso = Curso::with('professor')->where('id', $cursoId)->first();
        if (empty($pergunta)) {
            $pergunta = self::query()->create([
                'pergunta' => 'Faça sua pergunta ao professor.',
                'fk_curso' => $cursoId,
                'status' => self::MENSAGEM_NAO_LIDA,
                'fk_criador_id' => $curso->professor->fk_usuario_id
            ]);
        }

        return self::_buildChat($pergunta, $usuarioId, false, $curso);
    }

    /**
     * @param $pergunta
     * @param $usuarioId
     * @return array
     * @throws \Exception
     */
    protected static function _buildChat($pergunta, $usuarioId, $professor = false, $curso = null) {

        if (!$professor) {
            $respostas = self::getRespostas($pergunta->id, $curso, $usuarioId); 
        } else {
            $respostas = self::getRespostas($pergunta->id, null, $usuarioId);
        }

        $chatUser = Usuario::select()->where(['id' => $pergunta->fk_criador_id])->first();
        $chat = [
            "name" => $pergunta->nome,
            "courseName" => $pergunta->titulo,
            "id_pergunta" => $pergunta->id,
            "profilePicture" => $chatUser->foto,
            "messages" => []
        ];

        foreach($respostas as $resposta) {
            $user = Usuario::select()->where(['id' => $resposta->fk_criador_id])->first();
            $chat['messages'][] = [
                "type" => ($usuarioId == $resposta->fk_criador_id ? "my" : "other"),
                "sentDate" => (new \DateTime($resposta->data_criacao))->format('d/m/Y'),
                "sentTime" => (new \DateTime($resposta->data_criacao))->format('H:i:s'),
                "content" => $resposta->resposta,
                "user" => $user
            ];

            if ($usuarioId !== $resposta->fk_criador_id ) {
                $resp = PerguntaResposta::find($resposta->id);
                $resp->status = Pergunta::MENSAGEM_LIDA;
                $resp->save();
            }
        }

        $chat['messages'][] = [
            "type" => $professor ? "my" : "other",
            "sentDate" => (new \DateTime($pergunta->data_criacao))->format('d/m/Y'),
            "sentTime" => (new \DateTime($pergunta->data_criacao))->format('H:i:s'),
            "content" => $pergunta->pergunta,
            "user" => $chatUser
        ];

        return $chat;
    }

    /**
     * @param $id
     * @return |null
     */
    public static function getPergunta($id) {

        $sql = 'SELECT 
                    p.id, 
                    p.pergunta, 
                    p.fk_curso, 
                    p.status, 
                    p.fk_criador_id, 
                    p.data_criacao, 
                    p.data_atualizacao, 
                    c.id AS curso_id, 
                    c.fk_professor, 
                    u.nome, 
                    c.titulo
                FROM   
                    pergunta p 
                INNER JOIN cursos c ON p.fk_curso = c.id 
                INNER JOIN usuarios u ON p.fk_criador_id = u.id 
                WHERE p.id = ?';

        $result = DB::select($sql, [$id]);

        if (!empty($result)) {
            return $result[0];
        }

        return null;
    }

    /**
     * @param $idPergunta
     * @param $idUsuario
     * @return array
     * @throws \Exception
     */
    public static function getChat($idPergunta, $idUsuario) {
        $pergunta = self::getPergunta($idPergunta);
        return self::_buildChat($pergunta, $idUsuario, true);
    }
}
