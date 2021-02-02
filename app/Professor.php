<?php

namespace App;

use App\Traits\EducazSoftDelete;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Curso;

class Professor extends Model {
    
    use Notifiable, Cachable, EducazSoftDelete;

    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';
    const SOFT_DELETE = 'status';
    const ID_PERFIL = 1;
    const PERFIL_NOME = 'PROFESSOR';

    public $timestamps = true;

    protected $fillable = [
        'id',
        'wirecard_account_id',
        'nome',
        'sobrenome',
        'data_nascimento',
        'cpf',
        'profissao',
        'mini_curriculum',
        'telefone_1',
        'telefone_2',
        'telefone_3',
        'fk_endereco_id',
        'fk_usuario_id',
        'fk_conta_bancaria_id',
        'status',
        'share',
        'fk_criador_id',
        'fk_atualizador_id',
        'data_criacao',
        'data_atualizacao',
        'facebook_link',
        'insta_link',
        'twitter_link',
        'linkedin_link',
        'youteber_link',
        'genero',
        'fk_escola'
    ];

    protected $primaryKey = 'id';
    protected $table = "professor";

    public $rules = [
        'nome' => 'required',
        'mini_curriculum' => 'required',
        'cpf' => 'required',
        'fk_endereco_id' => 'required',
        'email' => 'required',
    ];

    public $messages = [
        'nome.required' => 'Nome é obrigatório',
        'sobrenome.required' => 'Sobrenome é obrigatório',
        'profissao.required' => 'Profissão é um campo obrigatório',
        'cep.required' => 'Cep é obrigatório!',
        'logradouro.required' => 'Logradouro é obrigatório!',
        'numero.required' => 'Número é obrigatório!',
        'fk_estado_id.required' => 'Estado é obrigatório!',
        'fk_cidade_id.required' => 'Cidade é obrigatório!',
        'data_nascimento.required' => 'Data de nascimento do titular é obrigatório!',
        'share.required' => 'Share é um campo obrigatório!',
        'telefone_1.required' => 'Telefone Fixo é um campo obrigatório!',
        'telefone_2.required' => 'Telefone Celular é um campo obrigatório!',
        'bairro.required' => 'Bairro é um campo obrigatório!',
        'mini_curriculum.required' => 'Mini Currículo é um campo obrigatório!',
        'cpf.required' => 'CPF é obrigatório',
        'cpf.cpf' => 'CPF inválido',
        'cpf.unique' => 'CPF já cadastrado no sistema!',
        'fk_endereco_id' => 'Necessário o o Endereço',
        'password.confirmed' => 'A senha e a confirmação de senha devem ser iguais!',
        'password.required' => 'A senha é obrigatória!',
        'password_confirmation.required' => 'A confirmação de senha é obrigatória!',
        'password.min' => 'A senha deve ter no mínimo 8 caracteres!',
        'password_confirmation.min' => 'A confirmação de senha deve ter no mínimo 8 caracteres!',
        'email.required' => 'E-mail é obrigatório',
        'email.unique' => 'E-mail já cadastrado no sistema!',
        'email.email' => 'E-mail é inválido. Por favor não entre com caracteres especiais no seu email.',
        'titular.required' => 'Títular é obrigatório',
        'fk_banco_id.required' => 'Banco é obrigatório',
        'agencia.required' => 'Agência é obrigatório',
        'conta_corrente.required' => 'Número da Conta é obrigatório',
        'tipo_conta.required' => 'Tipo de conta Conta é obrigatório',
        'documento.required' => 'CPF/CNPJ é obrigatório'
    ];

    /**
     * Retorna a classe Endereço associada
     *
     * @return HasOne
     */
    public function endereco()
    {
        return $this->HasOne('\App\Endereco', 'id', 'fk_endereco_id');
    }

    /**
     * Retorna a classe Usuario associada
     *
     * @return HasOne
     */
    public function usuario() {
        return $this->HasOne('\App\Usuario', 'id', 'fk_usuario_id');
    }

    /**
     * Retorna a classe Usuario associada
     *
     * @return HasOne
     */
    public function conta()
    {
        return $this->HasOne('\App\ContaBancaria', 'id', 'fk_conta_bancaria_id');
    }

    /**
     * Retorna a classe Curso associada
     *
     * @return HasOne
     */
    public function cursos()
    {
        return $this->hasMany('App\Curso', 'fk_professor', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function escolas()
    {
        return $this->belongsToMany('App\Escola', 'professor_escola', 'fk_professor', 'fk_escola');
    }

    /**
     * Retorna a classe Curso associada
     *
     * @return HasOne
     */
    public function cursosAprovados()
    {
        return $this->hasMany('App\Curso', 'fk_professor', 'id')->where('status', '=', '5');
    }

    public function getNomeCompletoAttribute() {
        return "{$this->nome} {$this->sobrenome}";
    }
    /**
     * Retorna a classe Curso associada
     *
     * @return HasOne
     */
    public function cursosAprovadosByFaculdadeID($professor_id, $faculdade_id)
    {
        return Curso::select(
            'cursos.id as curso_id',
            'cursos.slug_curso',
            'cursos.fk_professor',
            'cursos.duracao_total',
            'cursos.imagem',
            'cursos.titulo',      
            'cursos_tipo.titulo as curso_tipo_titulo',
            'cursos_tipo.id as curso_tipo_id',
            'cursos_faculdades.fk_faculdade', 
            'professor.nome',
            'professor.sobrenome',
            'cursos_valor.valor',
            'cursos_valor.valor_de'
        )
        ->join('cursos_tipo', 'cursos_tipo.id', '=', 'cursos.fk_cursos_tipo')
        ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
        ->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id')
        ->join('faculdades', 'faculdades.id', '=', 'cursos_faculdades.fk_faculdade')
        ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
        ->where('cursos.fk_professor', '=', $professor_id)
        ->where('cursos_faculdades.fk_faculdade', '=', $faculdade_id)
        ->where('cursos.status', '=', 5)
        ->get();
    }

    /**
     * Retorna a classe ProfessorFormacao associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function formacoes()
    {
        return $this->hasMany('App\ProfessorFormacao', 'fk_professor_id');
    }

    /**
     * Retorna a classe Proposta associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function propostas()
    {
        return $this->hasMany('App\Proposta', 'fk_professor');
    }

    /**
     * Retorna os professores de uma categoria
     *
     * @param int $categoriaId
     * @return $professores
     */
    public static function getProfessoresByCategoria($categoriaId = null)
    {
        $professores = self::select('professor.*') // debug 'cursos.id as curso_id', 'cursos.titulo as curso_titulo', 'cursos_categoria.titulo as categoria_titulo', 'cursos_categoria.id as categoria_id'
                ->join('cursos', 'cursos.fk_professor', '=', 'professor.id')
                ->join('cursos_categoria_curso', 'cursos_categoria_curso.fk_curso', '=', 'cursos.id')
                ->join('cursos_categoria', 'cursos_categoria.id', '=', 'cursos_categoria_curso.fk_curso_categoria')
                ->where('cursos_categoria.id', (int) $categoriaId)
                ->distinct('professor.id');

        return $professores->get();
    }

    public function getName() {
        $nome = $this->nome;
        $sobre_nome = $this->sobrenome;
        return trim("$nome $sobre_nome");
    }

    /**
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function validade(array $data) {
        return (new Professor)->_validate($data);
    }

    /**
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function _validate(array $data) {

        $object = $this;
        return Validator::make($data, [
            'nome' => 'required',
//            'mini_curriculum' => 'required',
            'sobrenome' => 'required',
//            'data_nascimento' => 'required',
//            'cep' => 'required',
////            'logradouro' => 'required',
////            'bairro' => 'required',
////            'numero' => 'required',
////            'fk_estado_id' => 'required',
////            'fk_cidade_id' => 'required',
////            'profissao' => 'required',
//////            'telefone_1' => 'required',
////            'telefone_2' => 'required',
            'password' => 'required|min:8|confirmed|sometimes',
            'password_confirmation' => 'required|min:8|sometimes',
//            'email' => [
//                'required',
//                'email',
//                Rule::unique('usuarios', 'email')->where(function ($query) use ($object, $data) {
//                    if (!empty($data['fk_usuario_id'])) {
//                        $query->where('id', '!=', $data['fk_usuario_id']);
//                    }
//                    $query->where('status', '=','1');
//                    $query->where('fk_perfil', UsuariosPerfil::PROFESSOR);
//                    $query->where('email', '=', $data['email']);
//                }),
//            ]
        ], $this->messages);
    }
    /**
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function _validateApi(array $data) {

        $object = $this;
        return Validator::make($data, [
            'nome' => 'required',
            'mini_curriculum' => 'required',
            'sobrenome' => 'required',
            'data_nascimento' => 'required',
            'cep' => 'required',
            'logradouro' => 'required',
            'bairro' => 'required',
            'numero' => 'required',
            'estado' => 'required',
            'cidade' => 'required',
            //'profissao' => 'required',
            'telefone_2' => 'required',
            'password' => 'required|min:8|confirmed|sometimes',
            'password_confirmation' => 'required|min:8|sometimes',
            'email' => [
                'required',
                'email',
                Rule::unique('usuarios', 'email')->where(function ($query) use ($object, $data) {
                    if (!empty($data['fk_usuario_id'])) {
                        $query->where('id', '!=', $data['fk_usuario_id']);
                    }
                    $query->where('status', '=','1');
                    $query->where('email', '=', $data['email']);
                }),
            ]
        ], $this->messages);
    }

    public function getCursosCards() {

        $cursosProfessor = [];

        /** @var Curso $curso */
        foreach ($this->cursosAprovados as $curso) {
            $disponibilidade = $curso->verificarDisponivelVenda($curso, $this);
            $cursosProfessor[] = [
                'curso_tipo' => $curso->cursoTipo->titulo,
                'fk_cursos_tipo' => $curso->cursoTipo->id,
                'id' => $curso->id,
                'id_professor' => $curso->fk_professor,
                'duracao_total' => $curso->duracao_total,
                'imagem' => $curso->imagem,
                'nome_professor' => $this->nome,
                'sobrenome_professor' => $this->sobrenome,
                'tipo' => $curso->cursoTipo->titulo,
                'titulo' => $curso->titulo,
                'valores' => $curso->valor,
                'indisponivel_venda' => $disponibilidade,
                'valor' => !empty($curso->valor) ? $curso->valor->valor : null ,
                'valor_de' => !empty($curso->valor) ? $curso->valor->valor_de : null,
            ];

        }

        return $cursosProfessor;

    }

    public function getCursosCardsByFaculdadeID($professor_id, $faculdade_id) {

        $cursosProfessor = [];

        /** @var Curso $curso */
        foreach ($this->cursosAprovadosByFaculdadeID($professor_id, $faculdade_id) as $curso) {
            $disponibilidade = $curso->verificarDisponivelVenda($curso, $this);
            $cursosProfessor[] = [
                'curso_tipo' => $curso->curso_tipo_titulo,
                'fk_cursos_tipo' => $curso->curso_tipo_id,
                'id' => $curso->curso_id,
                'id_professor' => $curso->fk_professor,
                'duracao_total' => $curso->duracao_total,
                'imagem' => $curso->imagem,
                'nome_professor' => $curso->nome,
                'sobrenome_professor' => $curso->sobrenome,
                'tipo' => $curso->curso_tipo_titulo,
                'titulo' => $curso->titulo,
                'valores' => $curso->valor,
                'indisponivel_venda' => $disponibilidade,
                'valor' => !empty($curso->valor) ? $curso->valor : null ,
                'valor_de' => !empty($curso->valor_de) ? $curso->valor_de : null, 
                'slug_curso' => $curso->slug_curso
            ];

        }

        return $cursosProfessor;

    }

}
