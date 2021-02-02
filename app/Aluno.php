<?php

namespace App;

use App\Traits\EducazSoftDelete;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\ModuloUsuario;
use App\Curso;
use App\CursosConcluidos;

class Aluno extends Model {

    use Notifiable, EducazSoftDelete;

    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';
    const SOFT_DELETE = 'status';
    const ID_PERFIL = 14;

    protected $table = "alunos";

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'id',
        'nome',
        'sobre_nome',
        'cpf',
        'identidade',
        'data_nascimento',
        'telefone_1',
        'telefone_2',
        'telefone_3',
        'fk_usuario_id',
        'fk_faculdade_id',
        'status',
        'matricula',
        'semestre',
        'universidade',
        'universidade_outro',
        'curso',
        'curso_outro',
        'curso_superior',
        'fk_endereco_id',
        'data_criacao',
        'data_atualizacao',
        'fk_criador_id',
        'fk_atualizador_id',
        'curso_especializacao',
        'tipo_curso_especializacao',
        'especializacao_universidade',
        'especializacao_universidade_outro',
        'genero'
    ];

    public $messages = [
        'nome.required' => 'Nome é obrigatório',
        'sobre_nome.required' => 'Sobrenome é obrigatório',
        'data_nascimento.required' => 'Data de nascimento é obrigatório!',
        'cep.required' => 'Cep é obrigatório!',
        'logradouro.required' => 'Logradouro é obrigatório!',
        'numero.required' => 'Número é obrigatório!',
        'fk_estado_id.required' => 'Estado é obrigatório!',
        'fk_cidade_id.required' => 'Cidade é obrigatório!',
        'bairro.required' => 'Bairro é um campo obrigatório!',
        'curso_superior.required' => 'Curso Superior é um campo obrigatório!',
        'telefone_1.required' => 'Telefone Fixo é um campo obrigatório!',
        'telefone_2.required' => 'Telefone Celular é um campo obrigatório!',
        'cpf.required' => 'CPF é obrigatório',
        'cpf.unique' => 'CPF já cadastrado no sistema!',
        'fk_usuario_id' => 'Usuário do Aluno é obrigatório',
        'fk_faculdade_id.required' => 'Projeto é obrigatório',
        'fk_endereco_id' => 'Endereço é obrigatório',
        'password.min' => 'A senha deve ter no mínimo 8 caracteres!',
        'password.confirmed' => 'A senha e a confirmação de senha devem ser iguais!',
        'password.required' => 'A senha é obrigatória!',
        'password_confirmation.min' => 'A confirmação de senha deve ter no mínimo 8 caracteres!',
        'password_confirmation.required' => 'A confirmação de senha é obrigatória!',
        'email.unique' => 'E-mail já cadastrado no sistema!',
        'email.required' => 'E-mail é obrigatório!',
    ];
    
    public $rules = [ 'nome' => 'required',];

    protected $appends = ['full_name'];

    /**
     * Retorna a classe Endereço associada
     *
     * @return HasOne
     */
    public function endereco() {
        return $this->HasOne('\App\Endereco', 'id', 'fk_endereco_id');
    }

    /**
     * @return HasOne
     */
    public function usuario() {
        return $this->HasOne('\App\Usuario', 'id', 'fk_usuario_id');
    }

    /**
     * Perfil do Aluno
     * @param integer $idUsuario
     * @return
     */
    public static function perfil($idUsuario) {
        $aluno = Aluno::select('alunos.*', 'usuarios.foto', 'usuarios.email', 'endereco.*', 'cidades.descricao_cidade as cidade', 'estados.uf_estado as estado')
            ->join('usuarios', 'usuarios.id', '=', 'alunos.fk_usuario_id')
            ->leftJoin('endereco', 'endereco.id', '=', 'alunos.fk_endereco_id')
            ->leftJoin('cidades', 'cidades.id', '=', 'endereco.fk_cidade_id')
            ->leftJoin('estados', 'estados.id', '=', 'endereco.fk_estado_id')
            ->where('usuarios.id','=', $idUsuario)->first();

        return $aluno;
    }

    public function getName() {
        return $this->nome . ' ' . $this->sobre_nome;
    }

    public function getFullNameAttribute() {
        return $this->nome . ' ' . $this->sobre_nome;
    }

    /**
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function validade(array $data) {
        return (new Aluno)->_validate($data);
    }

    /**
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function _validate(array $data) {
        $oUser = $this;
        return Validator::make($data, [
            'nome' => 'required',
            'sobre_nome' => 'required',
            'fk_faculdade_id' => 'required',
            'password' => 'required|min:8|confirmed|sometimes',
            'password_confirmation' => 'required|min:8|sometimes',
            'cpf' => [
                'required',
                'cpf',
                Rule::unique('alunos', 'cpf')->where(function ($query) use ($oUser, $data) {
                    if (!empty($data['id'])) {
                        $query->where('id', '!=', $data['id']);
                    }
                    $query->where('status', '!=', 0);
                    $query->where('cpf', '=', $data['cpf']);
                    $query->where('fk_faculdade_id', '=', $data['fk_faculdade_id']);
                }),
            ],
        ], $this->messages);
    }

    public static function dados_aluno($data) 
    {
        try {
            $query = Aluno::select('alunos.*', 'cidades.descricao_cidade', 'estados.uf_estado')
                ->leftJoin('endereco', 'alunos.fk_endereco_id', '=', 'endereco.id')
                ->leftJoin('cidades', 'endereco.fk_cidade_id', '=', 'cidades.id')
                ->leftJoin('estados', 'cidades.fk_estado_id', '=', 'estados.id')
                ->join('faculdades', 'faculdades.id', 'alunos.fk_faculdade_id');

            if (!empty($data['id'])) {
                $query->where('alunos.id', $data['id']);
            } else {
                if (!empty($data['cpf'])) {
                    $query->where('alunos.cpf', 'like', '%' . $data['cpf'] . '%')
                        ->orWhere('alunos.cpf', 'like', '%' . $data['cpf_mask'] , '%');
                }

                if (!empty($data['nome'])) {
                    $query->where(DB::raw('CONCAT(nome, " ", sobre_nome)'), 'like', '%' . $data['nome'] . '%');
                }
                
                if (!empty($data['ies'])) {
                    $query->where('alunos.fk_faculdade_id', $data['ies']);
                }
            }
        } catch (\Exception $e) {
            Log::error($e);
        }

        return $query;
    }

    public static function cursos_online($fk_usuario) {
        $query = ModuloUsuario::select('cursos_modulos.fk_curso', 'cursos.titulo',
        DB::raw("concat(professor.nome, ' ', professor.sobrenome) AS professor_nome"),
        'modulos_usuarios.criacao', 'quiz.percentual_acerto')
        ->where('modulos_usuarios.fk_usuario', $fk_usuario)
        ->join('cursos_modulos', 'cursos_modulos.id', '=', 'modulos_usuarios.fk_modulo')
        ->join('cursos', 'cursos.id', '=', 'cursos_modulos.fk_curso')
        ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
        ->leftJoin('quiz', 'quiz.fk_curso', '=', 'cursos_modulos.fk_curso');

        return $query->get();
    }

    public static function cursos_presenciais($fk_usuario) {
        $query = ModuloUsuario::select('cursos_modulos.fk_curso', 'cursos.titulo',
        DB::raw("concat(professor.nome, ' ', professor.sobrenome) AS professor_nome"),
        'modulos_usuarios.criacao', 'quiz.percentual_acerto')
        ->where('modulos_usuarios.fk_usuario', $fk_usuario)
        ->join('cursos_modulos', 'cursos_modulos.id', '=', 'modulos_usuarios.fk_modulo')
        ->join('cursos', 'cursos.id', '=', 'cursos_modulos.fk_curso')
        ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
        ->leftJoin('quiz', 'quiz.fk_curso', '=', 'cursos_modulos.fk_curso');

        return $query;
    }

    public static function relatorio_aluno($data) {
        $query = Aluno::select(['alunos.id', 'alunos.telefone_1', 'alunos.telefone_2', 'alunos.telefone_3',
        DB::raw(" DATE_FORMAT(alunos.data_criacao,'%Y-%m-%d %H:%i') as criacao "), 'alunos.curso_superior', "alunos.cpf",
        "alunos.identidade", 'alunos.universidade', 'alunos.curso', 'alunos.data_nascimento', 'alunos.curso_especializacao',
        DB::raw("concat(alunos.nome, ' ', alunos.sobre_nome) AS nome"),
        'usuarios.email', "endereco.cep", 'cep', 'logradouro', 'numero', 'complemento', 'bairro',
        'cidades.descricao_cidade', 'estados.uf_estado', 'alunos.fk_faculdade_id', 'faculdades.fantasia AS origem']);

        $query->join('usuarios', 'usuarios.id', '=', 'alunos.fk_usuario_id');
        $query->join('faculdades', 'alunos.fk_faculdade_id', '=', 'faculdades.id');

        $query->leftJoin('endereco', 'alunos.fk_endereco_id', '=', 'endereco.id')
        ->leftJoin('cidades', 'endereco.fk_cidade_id', '=', 'cidades.id')
        ->leftJoin('estados', 'cidades.fk_estado_id', '=', 'estados.id');

        if(!empty($data['id'])){
            $query->where('alunos.id', $data['id']);
        }

        if(!empty($data['cpf'])){
            $query->where(function ($query) use ($data) {
                $query->where('alunos.cpf', $data['cpf'])->orWhere('alunos.cpf', $data['cpf']);
            });
        }

        if(!empty($data['nome'])){
            $query->where( DB::raw("concat(alunos.nome, ' ', alunos.sobre_nome)"),'like','%'.$data['nome'].'%');
        }

        if(!empty($data['email'])){
            $query->where('usuarios.email', $data['email']);
        }

        if(!empty($data['ies'])){
            $query->where('faculdades.id', $data['ies']);
        }

        if(isset($data['data_registro']) && !empty($data['data_registro'])){
            $query->whereBetween('alunos.data_criacao',$data['data_registro']);
        }

        $query->orderByRaw( $data['orderby'].' '.$data['sort'] );

        return $query;
    }
}
