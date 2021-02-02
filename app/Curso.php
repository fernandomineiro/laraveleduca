<?php

namespace App;

use App\Services\ItvService;
use App\Traits\EducazSoftDelete;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Curso extends Model
{
    use Notifiable, EducazSoftDelete, Sluggable;

    public $timestamps = true;

    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';
    const SOFT_DELETE = 'status';

    const ONLINE = 1;
    const PRESENCIAL = 2;
    const REMOTO = 4;
    const MENTORIA = 5;
    const SEMIPRESENCIAL = 6;

    protected $table = 'cursos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'titulo',
        'slug_curso',
        'descricao',
        'objetivo_descricao',
        'publico_alvo',
        'teaser',
        'fk_cursos_tipo',
        'fk_produtora',
        'fk_conteudista',
        'fk_curador',
        'status',
        'trabalho',
        'fk_professor',
        'fk_professor_participante',
        'endereco_presencial',
        'descricao_datas_presencial',
        'imagem',
        'numero_maximo_alunos',
        'numero_minimo_alunos',
        'fk_faculdade',
        'idioma',
        'formato',
        'data_criacao',
        'data_atualizacao',
        'fk_certificado',
        'curador_share',
        'curador_share_manual',
        'produtora_share',
        'produtora_share_manual',
        'professorparticipante_share',
        'professorparticipante_share_manual',
        'professorprincipal_share',
        'professorprincipal_share_manual',
        'duracao_total',
        'duracao_dias',
        'disponibilidade_dias',
        'fk_criador_id',
        'professor_responde_duvidas',
        'ementa'
    ];

    public $rules = [
        'titulo' => 'required',
        'fk_cursos_tipo' => 'required',
        'fk_professor' => 'required'
    ];

    public $messages = [
        'titulo.required' => 'Título do Curso é obrigatório',
        'fk_professor.required' => 'Professor do Curso é obrigatório',
        'titulo.unique' => 'Curso Título deve ser unico por faculdade',
        'fk_cursos_tipo.required' => 'Tipo do Curso é obrigatório',
        'duracao_dias.numeric' => 'A duração em dia deve ser um número inteiro',
        'disponibilidade_dias.numeric' => 'A disponibilidade para venda em dias deve ser um número inteiro',
        'numero_maximo_alunos.numeric' => 'O número máximo de alunos deve ser um número inteiro',
        'numero_minimo_alunos.numeric' => 'O número mínimo de alunos deve ser um número inteiro',
    ];

    public function _validate($data)
    {
        $obj = $this;

        return Validator::make($data, [
            'fk_professor' => 'required',
            'fk_cursos_tipo' => 'required',
            //'duracao_dias' => 'sometimes|numeric',
            //'disponibilidade_dias' => 'sometimes|numeric',
            //'numero_maximo_alunos' => 'sometimes|numeric',
            //'numero_minimo_alunos' => 'sometimes|numeric',
            //'duracao_total' => 'sometimes|numeric',
            'titulo' => [
                'required'
            ]
        ], $this->messages);
    }

    static function validate ($data)
    {
        return self::_validate($data);
    }

    /**
     * @param int $idFaculdade
     * @return bool
     */
    public static function isLayoutEstruturaCurricular(int $idFaculdade): bool {
        $tiposCursos = self::retornaConfiguracoesFaculdade($idFaculdade);
        return !empty($tiposCursos['tipo_layout']) && $tiposCursos['tipo_layout'] == 1;
    }

    /**
     * @param int $idFaculdade
     * @return mixed
     */
    private static function retornaConfiguracoesFaculdade(int $idFaculdade) {
        return ConfiguracoesTiposCursosAtivos::select()
                ->where('fk_faculdade_id', $idFaculdade)
                ->where('status', 1)
                ->first();
    }

    public function professor() {
        return $this->hasOne('\App\Professor', 'id', 'fk_professor');
    }

    /**
     * Retorna a classe CursoTipo associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cursoTipo() {
        return $this->hasOne('\App\CursoTipo', 'id', 'fk_cursos_tipo');
    }

    /**
     * Retorna a classe CursoTurma associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function turmas() {
        return $this->hasMany('App\CursoTurma', 'fk_curso');
    }

    /**
     * Retorna a classe CursoTurma associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function valor() {
        return $this->hasOne('App\CursoValor', 'fk_curso');
    }
    /**
     * Retorna a classe CursoTurma associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function faculdade() {
        return $this->hasOne('App\CursosFaculdades', 'fk_curso');
    }

    public function avaliacao() {
        return $this->hasMany(CursoAvaliacao::class, 'fk_curso', 'id');
    }

    public function trabalho() {
        return $this->hasOne(CursosTrabalhos::class, 'fk_cursos', 'id');
    }

    public function criarTrabalho() {
        return $this->trabalho()->updateOrCreate(
            [ 'fk_cursos' => $this->id ],
            [
                'status' => 1,
                'titulo' => 'TCC - '. $this->titulo,
                'fk_cursos' => $this->id
            ]
        );
    }
    
    /**
     * Retorna todos os cursos por tipo:
     * (online, presencial, remoto)
     *
     */
    public static function lista($idTipo = null, $idCategoria = null, $status = 5, $idFaculdade = null) {
        $cursos = Curso::distinct()->select(
            \DB::raw("CONCAT(cursos.id,' - ', cursos.titulo, ' - ', cursos_tipo.titulo) as autocomplete"),
			'cursos.id',
            'cursos.titulo as nome_curso',
            'cursos.slug_curso',
            'cursos_valor.valor',
            'cursos.imagem',
            'cursos_valor.valor_de',
            'cursos.idioma',
            'cursos_tipo.titulo as curso_tipo',
			//'faculdades.fantasia as nome_faculdade',
			'professor.nome as nome_professor',
			'professor.sobrenome as sobrenome_professor',
			'fk_cursos_tipo as tipo'
		)
		->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
        ->join('cursos_tipo', 'cursos_tipo.id', '=', 'cursos.fk_cursos_tipo')
		//->join('faculdades', 'faculdades.id', '=', 'cursos.fk_faculdade')
        ->join('professor', 'professor.id', '=', 'cursos.fk_professor');

        if ($status) {
            $cursos->where('cursos.status', '=', $status);
        }

        if ($idCategoria) {
            $cursos->join('cursos_categoria_curso', 'cursos_categoria_curso.fk_curso', '=', 'cursos.id');
            $cursos->where('cursos_categoria_curso.fk_curso_categoria', '=', $idCategoria);
        }

        if ($idTipo) {
            $cursos->where('cursos.fk_cursos_tipo', '=', $idTipo);
        }

        if ($idFaculdade) {
            $cursos->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id')
                ->where('cursos_faculdades.fk_faculdade', $idFaculdade);
        }

        $cursos->where('cursos_valor.data_validade', null);
        $cursos->orderBy('cursos.id', 'desc');


        return $cursos->get();
    }

    public static function obter($idCurso, $idFaculdade = 1) {
        $curso = Curso::select(
            'cursos.id',
            'cursos.titulo as nome_curso',
            'cursos.slug_curso',
            'cursos.descricao as sobre_curso',
            'cursos.idioma',
            'cursos.formato',
            'cursos.imagem',
            'cursos.objetivo_descricao',
            'cursos.publico_alvo',
            'cursos.teaser',
            //'cursos.trabalho',
            'cursos.endereco_presencial',
            'cursos.duracao_total',
            'cursos.data_atualizacao',
            'cursos.fk_certificado as certificado',
            'cursos_valor.valor',
            'cursos_valor.valor_de',
            //'faculdades.fantasia as nome_faculdade',
            'professor.nome as nome_professor',
            'professor.sobrenome as sobrenome_professor',
            'professor.mini_curriculum as sobre_professor',  
            'usuarios.foto as foto_professor',          
            'fk_cursos_tipo as tipo',
            'cursos_faculdades.curso_gratis as gratis',
            'cursos.professor_responde_duvidas',
            'cursos.numero_minimo_alunos',
            'cursos.numero_maximo_alunos'
        )
        ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
        //->join('faculdades', 'faculdades.id', '=', 'cursos.fk_faculdade')
        ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
        ->join('cursos_faculdades', 'cursos.id', '=', 'cursos_faculdades.fk_curso')
        ->join('cursos_tipo', 'cursos_tipo.id', '=', 'cursos.fk_cursos_tipo')
        ->join('usuarios', 'usuarios.id', '=', 'professor.fk_usuario_id')
        ->where('cursos_valor.data_validade', null)
        ->where('cursos.id', $idCurso)
        ->where('cursos_faculdades.fk_faculdade', $idFaculdade)->first();

        $quiz = Quiz::select('quiz.*')->where('quiz.fk_curso', '=', $idCurso)->first();
        $curso['quiz_id'] = ($quiz)? $quiz->id : null;

        $lista_modulos = Curso::select(DB::raw("SUM(TIME_TO_SEC(cursos_modulos.carga_horaria)) as total_minutos"), DB::raw("COUNT(1) as total_modulos"))
                                    ->join('cursos_secao', 'cursos_secao.fk_curso', '=', 'cursos.id')
                                    ->join('cursos_modulos', 'cursos_modulos.fk_curso_secao', '=', 'cursos_secao.id')
                                    ->where('cursos_secao.fk_curso', $idCurso)
                                    ->where('cursos_secao.status', '=', 1)
                                    ->where('cursos_modulos.status', 1)
                                    ->first();

        $lista_categorias = CursoCategoriaCurso::select('cursos_categoria.id', 'cursos_categoria.titulo')
                                    ->join('cursos_categoria', 'cursos_categoria.id', '=', 'cursos_categoria_curso.fk_curso_categoria')
                                    ->where('cursos_categoria_curso.fk_curso', $idCurso)
                                    ->get()->toArray();

        $lista_faculdades = CursosFaculdades::select('faculdades.*', 'cursos_faculdades.duracao_dias', 'cursos_faculdades.disponibilidade_dias')
                                    ->join('faculdades', 'faculdades.id', '=', 'cursos_faculdades.fk_faculdade')
                                    ->where('cursos_faculdades.fk_curso', $idCurso)
                                    ->get()->toArray();

        $curso['categorias'] = $lista_categorias;
        $total_minutos = round((((int)$lista_modulos['total_minutos']) / 3600), 2);
        $decimal = $total_minutos - floor($total_minutos);
        $curso['total_minutos'] = ($decimal > 0 && $decimal < 1) ? floor($total_minutos) + 1 : floor($total_minutos);
        $curso['total_modulos'] = $lista_modulos['total_modulos'];
        $curso['faculdades'] = $lista_faculdades;

        return $curso;
    }

    public static function cursosPorProfessor($idProfessor, $tipo = 1)
    {
        $cursos = Curso::select(
			'cursos.id',
            'cursos.titulo as nome_curso',
            'cursos.titulo as nome_curso',
            'cursos.imagem',
			'cursos_valor.valor',
            'cursos_valor.valor_de',
			// 'faculdades.fantasia as nome_faculdade',
			'professor.nome as nome_professor',
			'professor.sobrenome as sobrenome_professor',
            'cursos.fk_cursos_tipo as tipo',
            'cursos.idioma'
		)
		->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
		//->join('faculdades', 'faculdades.id', '=', 'cursos.fk_faculdade')
        ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
        ->where('cursos.fk_cursos_tipo', $tipo)
        ->where('cursos_valor.data_validade', null)
        ->where('professor.id', $idProfessor);

        return $cursos->get();
    }

    public static function cursosPorProfessorFront($idProfessor, $idTipo, $status, $idFaculdade)
    {
        $cursos = Curso::select(
            'cursos.id',
            'cursos.titulo as nome_curso',
            'cursos.slug_curso',
            'cursos_valor.valor',
            'cursos_valor.valor_de',
            'cursos.idioma',
            'cursos.imagem',
            'cursos.status',
            'professor.nome as nome_professor',
            'professor.sobrenome as sobrenome_professor',
            'fk_cursos_tipo as tipo',
            'cursos.data_criacao',
            'cursos.data_atualizacao'
        )
            ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
            ->leftJoin('professor', 'professor.id', '=', 'cursos.fk_professor');

        $professor = Professor::where('fk_usuario_id', $idProfessor)->first();

        if ($professor) {
            $cursos->where(function($query) use ($idProfessor, $professor){
                $query->where('cursos.fk_professor', $professor->id)
                    ->orWhere('cursos.fk_criador_id', $idProfessor);
            });
            // $cursos->join('cursos_faculdades', 'cursos.id', '=', 'cursos_faculdades.fk_curso');
            // $cursos->where('cursos_faculdades.fk_faculdade', $user->fk_faculdade_id); linha removida para que seja retornado apenas os cursos cadastrados na faculdade logada
        } else {
            $cursos->where('cursos.fk_criador_id', $idProfessor);
        }

        if ($idTipo) {
            $cursos->where('cursos.fk_cursos_tipo', $idTipo);
        }

        $cursos->join('cursos_faculdades', 'cursos.id', '=', 'cursos_faculdades.fk_curso')
            ->where('cursos_faculdades.fk_faculdade', $idFaculdade); // linha adicionada para retornar apenas os cursos que o professor possuir na faculdade do ambiente

        if ($status) {
            $cursos->whereIn('cursos.status', $status);
        }

        return $cursos->get();
    }

    public static function cursosFavorito($idAluno) {
        $cursos = Curso::select(
			'cursos.id',
            'cursos.titulo as nome_curso',
            'cursos_valor.valor',
            'cursos.imagem',
            'cursos.slug_curso',
            'cursos_valor.valor_de',
            'cursos.idioma',
			// 'faculdades.fantasia as nome_faculdade',
			'professor.nome as nome_professor',
            'professor.sobrenome as sobrenome_professor',
			'fk_cursos_tipo as tipo'
        )
        ->join('cursos_favorito', 'cursos_favorito.fk_curso', '=', 'cursos.id')
		->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
		// ->join('faculdades', 'faculdades.id', '=', 'cursos.fk_faculdade')
        ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
        ->where('cursos_favorito.fk_aluno', $idAluno);

        $lista = $cursos->get()->toArray();

        $cursos = [];
        foreach($lista as $key => $item) {
            $cursos[$item['id']] = $item;
        }

        return $cursos;
    }
    public static function cursosPresenciaisAluno($idAluno, $idFaculdade = 7) {

        $configuracoes = ConfiguracoesTiposCursosAtivos::where('fk_faculdade_id', $idFaculdade)->where('status', 1)->first();
        if ($configuracoes->tipo_layout === 1) {
            return self::cursosEstruturaCurricularAlunoIniciado($idFaculdade, $idAluno, 2);
        }

        $cursos = Curso::select(
            'cursos.id',
            'cursos.titulo as nome_curso',
            'cursos_valor.valor',
            'cursos_valor.valor_de',
            'cursos.idioma',
            'cursos.slug_curso',
            'cursos.imagem',
            'pedidos.criacao',
            //'faculdades.fantasia as nome_faculdade',
            'professor.nome as nome_professor',
            'professor.sobrenome as sobrenome_professor',
            'cursos.fk_cursos_tipo as tipo'
        )->distinct()
            ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
            //->join('faculdades', 'faculdades.id', '=', 'cursos.fk_faculdade')
            ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
            ->join('pedidos_item', 'pedidos_item.fk_curso', '=', 'cursos.id')
            ->join('pedidos', 'pedidos.id', '=', 'pedidos_item.fk_pedido')
            //->where('pedidos.fk_usuario', $idAluno);
            ->where([
                ['pedidos.fk_usuario', '=', $idAluno],
                ['pedidos.status', '=', '2'],
                ['fk_cursos_tipo', '=', '2']]);

        return $cursos->get();
    }

    public static function cursosTrilhaPresenciaisAluno($idAluno) {
        $cursos = Trilha::select(
            'cursos.id',
            'cursos.titulo as nome_curso',
            'cursos_valor.valor',
            'cursos_valor.valor_de',
            'cursos.idioma',
            'cursos.slug_curso',
            'cursos.imagem',
            // 'faculdades.fantasia as nome_faculdade',
            'professor.nome as nome_professor',
            'professor.sobrenome as sobrenome_professor',
            'cursos.fk_cursos_tipo as tipo'
        )->distinct()
            ->join('trilha_curso', 'trilha_curso.fk_trilha', '=', 'trilha.id')
            ->join('cursos', 'trilha_curso.fk_curso', '=', 'cursos.id')
            ->join('pedidos_item', 'pedidos_item.fk_trilha', '=', 'trilha.id')
            ->join('pedidos', 'pedidos_item.fk_pedido', '=', 'pedidos.id')
            ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
            // ->join('faculdades', 'faculdades.id', '=', 'cursos.fk_faculdade')
            ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
            ->where(
                [
                    ['pedidos.fk_usuario', '=', $idAluno],
                    ['pedidos.status', '=', '2'],
                    ['cursos.fk_cursos_tipo', '=', '2']
                ]
            );

        return $cursos->get();
    }

    public static function cursosOnlineAluno($idAluno, $idFaculdade = 7) {

        if (empty((int) $idAluno)) {
            return collect([]);
        }

        /** @var Usuario $user */
        $user = Usuario::find($idAluno);
        if (self::isLayoutEstruturaCurricular($idFaculdade)) {
            $cursosEstrutura = (new ItvService())
                ->setIdFaculdade($idFaculdade)
                ->retornarCursosAlunoItv($user);
        }

        $membership = $user->membership();

        $cursosAssinatura = [];
        
        if ($membership->isNotEmpty()) {
            
            $aulasAoVivoCompradas = 
                Curso::select('cursos.id')
                    ->distinct()
                    ->join('pedidos_item', 'pedidos_item.fk_curso', 'cursos.id')
                    ->join('pedidos', 'pedidos.id', 'pedidos_item.fk_pedido')
                    ->where('pedidos.fk_usuario', $idAluno)
                    ->where('pedidos.fk_faculdade', $idFaculdade)
                    ->where('pedidos.status', 2)->get();
            
            $cursosAoVivo = 
                Curso::select('cursos.id')
                    ->distinct()
                    ->join('cursos_categoria_curso', 'cursos_categoria_curso.fk_curso', 'cursos.id')
                    ->join('cursos_categoria', 'cursos_categoria.id', 'cursos_categoria_curso.fk_curso_categoria')
                    ->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id')
                    ->where('cursos_categoria.id', 70)
                    ->where('cursos_faculdades.fk_faculdade', '=', $idFaculdade)
                    ->whereNotIn('cursos.id', $aulasAoVivoCompradas->pluck('id'))
                    ->get();
            
           $cursos = Curso::select(
                    'cursos.id',
                    'cursos.titulo as nome_curso',
                    'cursos_valor.valor',
                    'cursos_valor.valor_de',
                    'cursos.idioma',
                    'cursos.slug_curso',
                    'cursos.imagem',
                    'professor.nome as nome_professor',
                    'professor.sobrenome as sobrenome_professor',
                    'fk_cursos_tipo as tipo'
                )->distinct()
                ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
                ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
                ->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id')
                ->where(
                    [
                        ['fk_cursos_tipo', '=', '1'],
                        ['cursos.status', '=', '5'],
                        ['cursos_faculdades.fk_faculdade', '=', $idFaculdade],
                    ]
                )->whereNotIn('cursos.id', $cursosAoVivo->pluck('id'));

            if ($membership->contains('tipo_assinatura_id', '=', TipoAssinatura::FULL)) {
                return $cursos->get();
            }

            $cursos->join('assinatura_conteudos', 'assinatura_conteudos.fk_conteudo', '=', 'cursos.id')
                ->whereIn('fk_assinatura', $membership->pluck('id'));

            $cursosAssinatura = $cursos->get();
        }

        $cursos = Curso::select(
            'cursos.id',
            'cursos.titulo as nome_curso',
            'cursos_valor.valor',
            'cursos_valor.valor_de',
            'cursos.idioma',
            'cursos.slug_curso',
            'cursos.imagem',
            'professor.nome as nome_professor',
            'professor.sobrenome as sobrenome_professor',
            'fk_cursos_tipo as tipo'
        )->distinct()
            ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
            ->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id')
            ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
            ->join('pedidos_item', 'pedidos_item.fk_curso', '=', 'cursos.id')
            ->join('pedidos', 'pedidos.id', '=', 'pedidos_item.fk_pedido')
            ->where(
                [
                    ['pedidos.fk_usuario', '=', $idAluno],
                    ['fk_cursos_tipo', '=', '1'],
                    ['pedidos.status', '=', '2'],
                    ['cursos_faculdades.fk_faculdade', '=', $idFaculdade],
                ]
            );


        $cursosTrilhas = Curso::select(
            'cursos.id',
            'cursos.titulo as nome_curso',
            'cursos_valor.valor',
            'cursos_valor.valor_de',
            'cursos.idioma',
            'cursos.slug_curso',
            'cursos.imagem',
            'professor.nome as nome_professor',
            'professor.sobrenome as sobrenome_professor',
            'fk_cursos_tipo as tipo'
        )->distinct()
            ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
            ->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id')
            ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
            ->join('trilha_curso', 'cursos.id', '=', 'trilha_curso.fk_curso')
            ->join('trilha', 'trilha.id', '=', 'trilha_curso.fk_trilha')
            ->join('pedidos_item', 'pedidos_item.fk_trilha', '=', 'trilha.id')
            ->join('pedidos', 'pedidos.id', '=', 'pedidos_item.fk_pedido')
            ->where(
                [
                    ['pedidos.fk_usuario', '=', $idAluno],
                    ['fk_cursos_tipo', '=', '1'],
                    ['pedidos.status', '=', '2'],
                    ['cursos_faculdades.fk_faculdade', '=', $idFaculdade],
                ]
            );

        if ($cursosAssinatura instanceof Collection) {
            $pedidos = $cursos->get();
            foreach ($pedidos as $pedido) {
                $cursosAssinatura->push($pedido);
            }

            if (!empty($cursosEstrutura)) {
                foreach ($cursosEstrutura as $pedido) {
                    $cursosAssinatura->push($pedido);
                }
            }
        } else {
            $cursosAssinatura = $cursos->get();

            if (!empty($cursosEstrutura)) {
                foreach ($cursosEstrutura as $pedido) {
                    $cursosAssinatura->push($pedido);
                }
            }
        }

        $trilhas = $cursosTrilhas->get();
        foreach ($trilhas as $trilha) {
            $cursosAssinatura->push($trilha);
        }

        return $cursosAssinatura;
    }

    public static function cursosEstruturaCurricularAlunoIniciado ($idFaculdade, $idAluno, $tipoCurso, $statusCurso = 5) {
        $cursos = Curso::select(
            'cursos.id',
            'cursos.titulo as nome_curso',
            'cursos_valor.valor',
            'cursos_valor.valor_de',
            'cursos.idioma',
            'cursos.slug_curso',
            'cursos.imagem',
            'professor.nome as nome_professor',
            'professor.sobrenome as sobrenome_professor',
            'fk_cursos_tipo as tipo'
        )->distinct()
            ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
            ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
            ->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id')
            ->join('cursos_modulos_alunos', 'cursos_modulos_alunos.fk_curso_id', '=', 'cursos.id')
            ->join('cursos_categoria_curso', 'cursos_categoria_curso.fk_curso', '=', 'cursos.id')
            ->join('estrutura_curricular_conteudo', function ($join) {

                $join->on('estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos.id');
                $join->on('estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria_curso.fk_curso_categoria');

            })
            ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_conteudo.fk_estrutura')
            ->join('estrutura_curricular_usuario', 'estrutura_curricular_usuario.fk_estrutura', '=', 'estrutura_curricular.id')
            ->where(
                [
                    ['fk_cursos_tipo', '=', $tipoCurso],
                    ['cursos.status', '=', '5'],
                    ['cursos_faculdades.fk_faculdade', '=', $idFaculdade],
                    ['cursos_modulos_alunos.fk_aluno_id', '=', $idAluno],
                    ['estrutura_curricular_usuario.fk_usuario', '=', $idAluno],
                ]
            );

        return $cursos->get();
    }

    public static function cursosOnlineAlunoIniciados($idAluno, $idFaculdade = 7) {

        if (empty((int) $idAluno)) {
            return [];
        }

        if (self::isLayoutEstruturaCurricular($idFaculdade)) {
            $cursosEstrutura = self::cursosEstruturaCurricularAlunoIniciado($idFaculdade, $idAluno, 1);
        }

        $user = Usuario::find($idAluno);
        $membership = $user->membership();

        $cursosAssinatura = [];
        if (!empty($membership)) {
            $cursos = Curso::select(
                'cursos.id',
                'cursos.titulo as nome_curso',
                'cursos_valor.valor',
                'cursos_valor.valor_de',
                'cursos.idioma',
                'cursos.slug_curso',
                'cursos.imagem',
                'professor.nome as nome_professor',
                'professor.sobrenome as sobrenome_professor',
                'fk_cursos_tipo as tipo'
            )->distinct()
                ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
                ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
                ->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id')
                ->join('cursos_modulos_alunos', 'cursos_modulos_alunos.fk_curso_id', '=', 'cursos.id')
                ->where(
                    [
                        ['fk_cursos_tipo', '=', '1'],
                        ['cursos.status', '=', '5'],
                        ['cursos_faculdades.fk_faculdade', '=', $idFaculdade],
                        ['cursos_modulos_alunos.fk_aluno_id', '=', $idAluno],
                    ]
                );

            $hasFullAssinatura = false;
            $assinaturaIds = [];
            foreach ($membership as $assinatura) {
                if ($assinatura->tipo_assinatura_id == 1) {
                    $hasFullAssinatura = true;
                }

                $assinaturaIds[] = $assinatura->id;
            }

            if ($hasFullAssinatura) {
                return $cursos->get();
            }

            $cursos->join('assinatura_conteudos', 'assinatura_conteudos.fk_conteudo', '=', 'cursos.id')
                ->whereIn('fk_assinatura', $assinaturaIds);

            $cursosAssinatura = $cursos->get();
        }

        $cursos = Curso::select(
            'cursos.id',
            'cursos.titulo as nome_curso',
            'cursos_valor.valor',
            'cursos_valor.valor_de',
            'cursos.idioma',
            'cursos.slug_curso',
            'cursos.imagem',
            'professor.nome as nome_professor',
            'professor.sobrenome as sobrenome_professor',
            'fk_cursos_tipo as tipo'
        )->distinct()
            ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
            ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
            ->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id')
            ->join('cursos_modulos_alunos', 'cursos_modulos_alunos.fk_curso_id', '=', 'cursos.id')
            ->join('pedidos_item', 'pedidos_item.fk_curso', '=', 'cursos.id')
            ->join('pedidos', 'pedidos.id', '=', 'pedidos_item.fk_pedido')
            ->where('pedidos.fk_usuario', $idAluno)
            ->where('fk_cursos_tipo', 1)
            ->where('pedidos.status', 2)
            ->where('cursos.status', 5)
            ->where('cursos_faculdades.fk_faculdade', $idFaculdade);

        if ($cursosAssinatura instanceof Collection) {
            $pedidos = $cursos->get();
            foreach ($pedidos as $pedido) {
                $cursosAssinatura->push($pedido);
            }

            if (!empty($cursosEstrutura)) {
                foreach ($cursosEstrutura as $pedido) {
                    $cursosAssinatura->push($pedido);
                }
            }


        } else {
            $cursosAssinatura = $cursos->get();

            if (!empty($cursosEstrutura)) {
                foreach ($cursosEstrutura as $pedido) {
                    $cursosAssinatura->push($pedido);
                }
            }
        }

        return $cursosAssinatura;
    }

    public static function cursosTrilhaOnlineAluno($idAluno) {
        $cursos = Trilha::select(
            'cursos.id',
            'cursos.titulo as nome_curso',
            'cursos_valor.valor',
            'cursos_valor.valor_de',
            'cursos.idioma',
            'cursos.slug_curso',
            'cursos.imagem',
            'pedidos.criacao',
            // 'faculdades.fantasia as nome_faculdade',
            'professor.nome as nome_professor',
            'professor.sobrenome as sobrenome_professor',
            'fk_cursos_tipo as tipo'
        )->distinct()
            ->join('trilha_curso', 'trilha_curso.fk_trilha', '=', 'trilha.id')
            ->join('cursos', 'trilha_curso.fk_curso', '=', 'cursos.id')
            ->join('pedidos_item', 'pedidos_item.fk_trilha', '=', 'trilha.id')
            ->join('pedidos', 'pedidos_item.fk_pedido', '=', 'pedidos.id')
            ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
            //->join('faculdades', 'faculdades.id', '=', 'cursos.fk_faculdade')
            ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
            ->where(
                [
                    ['pedidos.fk_usuario', '=', $idAluno],
                    ['pedidos.status', '=', '2'],
                    ['fk_cursos_tipo', '=', '1']
                ]
            );

        return $cursos->get();
    }

    public static function cursosRemotosAluno($idAluno, $idFaculdade = 7) {

        $configuracoes = ConfiguracoesTiposCursosAtivos::where('fk_faculdade_id', $idFaculdade)->where('status', 1)->first();
        if ($configuracoes->tipo_layout === 1) {
            return self::cursosEstruturaCurricularAlunoIniciado($idFaculdade, $idAluno, 4);
        }

        $cursos = Curso::select(
            'cursos.id',
            'cursos.titulo as nome_curso',
            'cursos_valor.valor',
            'cursos_valor.valor_de',
            'cursos.idioma',
            'cursos.slug_curso',
            'cursos.imagem',
            // 'faculdades.fantasia as nome_faculdade',
            'professor.nome as nome_professor',
            'professor.sobrenome as sobrenome_professor',
            'fk_cursos_tipo as tipo',
            'pedidos.criacao'
        )->distinct()
            ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
            //->join('faculdades', 'faculdades.id', '=', 'cursos.fk_faculdade')
            ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
            ->join('pedidos_item', 'pedidos_item.fk_curso', '=', 'cursos.id')
            ->join('pedidos', 'pedidos.id', '=', 'pedidos_item.fk_pedido')
            ->where([
                ['pedidos.fk_usuario', '=', $idAluno],
                ['pedidos.status', '=', '2'],
                ['fk_cursos_tipo', '=', '4']]);

        return $cursos->get();
    }

    public static function cursosTrilhaHidridosAluno($idAluno) {
        $cursos = Trilha::select(
            'cursos.id',
            'cursos.titulo as nome_curso',
            'cursos_valor.valor',
            'cursos_valor.valor_de',
            'cursos.idioma',
            'cursos.slug_curso',
            'cursos.imagem',
            // 'faculdades.fantasia as nome_faculdade',
            'professor.nome as nome_professor',
            'professor.sobrenome as sobrenome_professor',
            'fk_cursos_tipo as tipo'
        )->distinct()
            ->join('trilha_curso', 'trilha_curso.fk_trilha', '=', 'trilha.id')
            ->join('cursos', 'trilha_curso.fk_curso', '=', 'cursos.id')
            ->join('pedidos_item', 'pedidos_item.fk_trilha', '=', 'trilha.id')
            ->join('pedidos', 'pedidos_item.fk_pedido', '=', 'pedidos.id')
            ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
            // ->join('faculdades', 'faculdades.id', '=', 'cursos.fk_faculdade')
            ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
            ->where(
                [
                    ['pedidos.fk_usuario', '=', $idAluno],
                    ['pedidos.status', '=', '2'],
                    ['fk_cursos_tipo', '=', '4']
                ]
            );

        return $cursos->get();
    }

    public static function agendaPorCurso($idCurso){
        $agenda = CursoTurmaAgenda::where('fk_curso', '=', $idCurso);

        return $agenda->get();
    }

    public function verificarDisponivelVenda($curso, $retorno) {
        return self::verificaDisponivelVenda($curso, $retorno);
    }

    public static function verificaDisponivelVenda($curso, $retorno) {
        if ($curso->id) {
            $maxAlunos = $curso->numero_maximo_alunos;
            $faculdades_curso = CursosFaculdades::where('fk_curso', '=', $curso->id)->get();
            $dateexpiracao = new \DateTime(trim($curso->data_atualizacao)); // alterar para data de publicação quando for feito o processo de aprovação do curso
            $pedidos = PedidoItem::where('fk_curso', $curso->id)->pluck('fk_pedido');

            foreach ($faculdades_curso as $fc) {
                if ($fc->disponibilidade_dias && $dateexpiracao) {
                    $diassoma = '+' . $fc->disponibilidade_dias . ' day';
                    $dateexpiracao->modify($diassoma);
                    $now = new \DateTime('now');
                    if ($dateexpiracao < $now) {
                        $fc->indisponivel_venda = true; // indisponivel para venda
                    }
                }

                /* if ($fc->duracao_dias) {
                    $diassoma = '+' . $fc->duracao_dias . ' day';
                    if ($dateexpiracao->modify($diassoma) > date("Y-m-d h:i:s")) {
                        $fc->indisponivel_acesso = true; // indisponivel para acesso pelo aluno
                    }
                } else {
                    $fc->indisponivel_acesso = false; // disponivel para acesso pelo aluno
                }*/
                $count = Pedido::where('fk_faculdade', $fc->fk_faculdade)->whereIn('id', $pedidos)->count();

                if ($maxAlunos) {
                    if ($count >= $maxAlunos) {
                        $fc->indisponivel_venda = true; // indisponivel para venda
                    }
                }

                $fc->save();
                if ($retorno && $fc->save()) {
                    return $fc->indisponivel_venda;
                }
            }
        }
    }

    public static function search($idTipo, $search, $idFaculdade = 1) {
        config()->set('database.connections.mysql.strict', false);
        \DB::reconnect();

        if ($idTipo === '5') {
            return self::search_mentoria($idTipo, $search, $idFaculdade);
        }

        $cursos = Curso::select(
            'cursos.id',
            'cursos.titulo',
            'cursos_tipo.titulo as curso_tipo',
            'cursos.fk_cursos_tipo',
            'cursos.duracao_total',
            'cursos.imagem',
            'cursos.slug_curso',
            'cursos_valor.valor',
            'cursos_valor.valor_de',
            'professor.nome as nome_professor',
            'professor.sobrenome as sobrenome_professor',
            'professor.id as id_professor',
            DB::raw('cursos_tipo.titulo as tipo'),
            DB::raw('(cursos_valor.valor_de - cursos_valor.valor) as promocao'),
            DB::raw('COUNT(pedidos_item.fk_curso) as vendidos')
        )
            ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
            ->leftJoin('professor', 'cursos.fk_professor', '=', 'professor.id')
            ->leftJoin('pedidos_item', 'pedidos_item.fk_curso', '=', 'cursos.id')
            ->leftJoin('cursos_tipo', 'cursos_tipo.id', '=', 'cursos.fk_cursos_tipo');


        $cursos->where('cursos.status', '=', 5)
            ->groupBy('cursos.id',
                'cursos.titulo',
                'curso_tipo',
                'cursos.fk_cursos_tipo',
                'cursos.duracao_total',
                'cursos.imagem',
                'cursos.slug_curso',
                'cursos_valor.valor',
                'cursos_valor.valor_de',
                'nome_professor',
                'professor.id',
                'sobrenome_professor',
                'tipo');



        if (isset($search['categoria_id'])) {
            $cursos->join('cursos_categoria_curso', 'cursos_categoria_curso.fk_curso', '=', 'cursos.id');
            $cursos->where('cursos_categoria_curso.fk_curso_categoria', $search['categoria_id']);
        }

        if (isset($search['professor_id'])) {
            $cursos->where('fk_professor', $search['professor_id']);
        }

        $idFaculdade = !empty($search['fk_faculdade']) ?  $search['fk_faculdade'] : $idFaculdade;

        if (!empty($idFaculdade)) {
            $cursos->groupBy('cursos_faculdades.indisponivel_venda');
        }

        $tipos = [];
        if (!empty($idFaculdade)) {

            $cursos->addSelect(DB::raw('ifnull(cursos_faculdades.curso_gratis, 0) as gratis'));
            $cursos->addSelect(DB::raw('cursos_faculdades.indisponivel_venda'));

            $cursos->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id')
                ->where('cursos_faculdades.fk_faculdade', $idFaculdade);

            $tiposAtivos = ConfiguracoesTiposCursosAtivos::where('fk_faculdade_id', $idFaculdade)->first();

            if ($tiposAtivos->ativar_cursos_online) {
                $tipos[] =  1;
            }

            if ($tiposAtivos->ativar_cursos_presenciais) {
                $tipos[] =  2;
            }

            if ($tiposAtivos->ativar_cursos_hibridos) {
                $tipos[] =  4;
            }

            if ($tiposAtivos->ativar_cursos_mentoria) {
                $tipos[] =  5;
            }
        }

        if (!empty($search['fk_cursos_tipo'])) {
            $cursos->where('cursos.fk_cursos_tipo', $search['fk_cursos_tipo']);
        } elseif (!empty($idTipo)) {
            $cursos->where('cursos.fk_cursos_tipo', $idTipo);
        } else if (!empty($tipos)) {
            $cursos->whereIn('cursos.fk_cursos_tipo', $tipos);
        }

        if (isset($search['sort'])) {

            switch ($search['sort']) {
                case 'asc':

                    $cursos->orderBy('cursos.titulo', 'asc');
                    break;
                case 'desc':

                    $cursos->orderBy('cursos.titulo', 'desc');
                    break;

                case 'best-seller':
                    $cursos->join('pedidos', 'pedidos.id', '=', 'pedidos_item.fk_pedido')
                        ->where('pedidos.fk_faculdade', $idFaculdade)
                        ->havingRaw('COUNT(pedidos_item.fk_curso) > ?', [0])
                        ->orderBy('vendidos', 'desc');
                    break;

                case 'latest':

                    if (!empty($idFaculdade)) {
                        $cursos->where('cursos_faculdades.curso_gratis', 0);
                    }

                    $cursos->orderBy('cursos.data_criacao', 'desc');

                    break;

                case 'promotions':
                    if (!empty($idFaculdade)) {

                        $checkCursoGratis = Curso::select(
                            DB::raw('count(cursos_faculdades.curso_gratis) curso_gratis')
                        )
                            ->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id')
                            ->where('cursos_faculdades.fk_faculdade', $idFaculdade)
                            ->where('cursos_faculdades.curso_gratis', 1)
                            ->first();

                        if (!empty($checkCursoGratis->curso_gratis) && $checkCursoGratis->curso_gratis) {
                            $cursos->where('cursos_faculdades.curso_gratis', 1)->orderBy('gratis', 'desc');
                        } else {
                            $cursos->whereNotNull('cursos_valor.valor')
                                ->whereRaw('(cursos_valor.valor_de - cursos_valor.valor) > ?', 0)
                                ->orderBy('promocao', 'desc');
                        }
                    } else {
                        $cursos->whereNotNull('cursos_valor.valor')
                            ->whereRaw('(cursos_valor.valor_de - cursos_valor.valor) > ?', 0)
                            ->orderBy('promocao', 'desc');
                    }

                    break;
            }
        }

        if (isset($search['trilha_id'])) {
            $cursos->join('trilha_curso', 'trilha_curso.fk_curso', '=', 'cursos.id');
            $cursos->where('trilha_curso.fk_trilha', $search['trilha_id']);
        }

        if (isset($search['price'])) {
            $cursos->whereRaw('(case WHEN cursos_valor.valor IS NOT NULL THEN cursos_valor.valor <= ? ELSE cursos_valor.valor_de <= ? END)',
                [(float) $search['price'], (float) $search['price']]);
        }

        if (isset($search['search'])) {
            $cursos->where(DB::raw('LOWER(cursos.titulo)'), 'like', '%' . mb_strtolower($search['search'], mb_detect_encoding($search['search'])) . '%');
        }

        // Remove itens duplicados por causa dos joins
        //$data = [];
        //$cursos = $cursos->take(20)->skip(0)->get()->toArray();
        $cursos = $cursos->get()->toArray();

        return collect($cursos)->unique()->toArray();
    }

    public static function search_mentoria($idTipo, $search, $idFaculdade = 1) {
        config()->set('database.connections.mysql.strict', false);
        \DB::reconnect();

        $cursos = Curso::select(
            'cursos.id',
            'cursos.titulo',
            'cursos_tipo.titulo as curso_tipo',
            'cursos.fk_cursos_tipo',
            'cursos.duracao_total',
            'cursos.imagem',
            'cursos.slug_curso',
            'professor.nome as nome_professor',
            'professor.sobrenome as sobrenome_professor',
            'professor.id as id_professor',
            DB::raw('cursos_tipo.titulo as tipo')
        )
            ->leftJoin('professor', 'cursos.fk_professor', '=', 'professor.id')
            ->leftJoin('cursos_tipo', 'cursos_tipo.id', '=', 'cursos.fk_cursos_tipo');


        $cursos->where('cursos.status', '=', 5)
            ->groupBy('cursos.id',
                'cursos.titulo',
                'curso_tipo',
                'cursos.fk_cursos_tipo',
                'cursos.duracao_total',
                'cursos.imagem',
                'cursos.slug_curso',
                'nome_professor',
                'professor.id',
                'sobrenome_professor',
                'tipo'
            );

        if (isset($search['professor_id'])) {
            $cursos->where('fk_professor', $search['professor_id']);
        }

        if (!empty($search['fk_cursos_tipo'])) {
            $cursos->where('cursos.fk_cursos_tipo', $search['fk_cursos_tipo']);
        } elseif (!empty($idTipo)) {
            $cursos->where('cursos.fk_cursos_tipo', $idTipo);
        }

        if (isset($search['sort'])) {

            switch ($search['sort']) {
                case 'asc':

                    $cursos->orderBy('cursos.titulo', 'asc');
                    break;
                case 'desc':

                    $cursos->orderBy('cursos.titulo', 'desc');
                    break;

                case 'latest':
                    $cursos->orderBy('cursos.data_criacao', 'desc');

                    break;
            }
        }

        if (isset($search['search'])) {
            $cursos->where(DB::raw('LOWER(cursos.titulo)'), 'like', '%' . mb_strtolower($search['search'], mb_detect_encoding($search['search'])) . '%');
        }

        $cursos = $cursos->get()->toArray();

        return collect($cursos)->unique()->toArray();
    }

    public static function conteudo($idTipo, $idFaculdade = 1) {
        config()->set('database.connections.mysql.strict', false);
        \DB::reconnect();

        $cursos = Curso::select(
            'cursos.id',
            'cursos.titulo',
            'cursos_tipo.titulo as curso_tipo',
            'cursos.fk_cursos_tipo',
            'cursos.imagem',
            'cursos.slug_curso',
            'professor.nome as nome_professor',
            'professor.sobrenome as sobrenome_professor',
            'professor.id as id_professor',
            DB::raw('cursos_tipo.titulo as tipo')
        )
//            ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
            ->leftJoin('professor', 'cursos.fk_professor', '=', 'professor.id')
            ->leftJoin('pedidos_item', 'pedidos_item.fk_curso', '=', 'cursos.id')
            ->leftJoin('cursos_tipo', 'cursos_tipo.id', '=', 'cursos.fk_cursos_tipo');

        $tipos = [];
        if (!empty($idFaculdade)) {
            $cursos->addSelect(DB::raw('cursos_faculdades.curso_gratis as gratis'));
            $cursos->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id')
                ->where('cursos_faculdades.fk_faculdade', $idFaculdade);

            $tiposAtivos = ConfiguracoesTiposCursosAtivos::where('fk_faculdade_id', $idFaculdade)->first();

            if ($tiposAtivos->ativar_cursos_online) {
                $tipos[] =  1;
            }

            if ($tiposAtivos->ativar_cursos_presenciais) {
                $tipos[] =  2;
            }

            if ($tiposAtivos->ativar_cursos_hibridos) {
                $tipos[] =  4;
            }
        }

        if (!empty($search['fk_cursos_tipo'])) {
            $cursos->where('cursos.fk_cursos_tipo', $search['fk_cursos_tipo']);
        } elseif (!empty($idTipo)) {
            $cursos->where('cursos.fk_cursos_tipo', $idTipo);
        } else if (!empty($tipos)) {
            $cursos->whereIn('cursos.fk_cursos_tipo', $tipos);
        }

        $cursos->with('avaliacao');

        $cursos->where('cursos.status', '=', 5)
            ->groupBy('cursos.id',
                'cursos.titulo',
                'curso_tipo',
                'cursos.fk_cursos_tipo',
                'cursos.imagem',
                'cursos.slug_curso',
                'nome_professor',
                'professor.id',
                'sobrenome_professor',
                'tipo'
            );

        return $cursos->get();
    }

    public static function getCursoMentoria($id)
    {
        $result = Curso::select(
                        'cursos.id',
                        'cursos.slug_curso',
                        'cursos.titulo',
                        'cursos.descricao',
                        'cursos.teaser',
                        'cursos.duracao_total',
                        'professor.nome as nome_professor',
                        'professor.fk_usuario_id as id_professor',
                        'professor.sobrenome as sobrenome_professor',
                        'professor.mini_curriculum as mini_curriculum_professor',
                        'usuarios.foto as foto_usuario_professor'
                    )
                        ->leftJoin('professor', 'cursos.fk_professor', '=', 'professor.id')
                        ->leftJoin('usuarios', 'usuarios.id', '=', 'professor.fk_usuario_id')
                        ->leftJoin('cursos_tipo', 'cursos_tipo.id', '=', 'cursos.fk_cursos_tipo')
                        ->where('cursos.id', $id)
                        ->first();

        $comentarios = CursoAvaliacao::select('cursos_avaliacao.*', 'usuarios.nome as nome_aluno')
            ->join('usuarios', 'usuarios.id', '=', 'cursos_avaliacao.fk_aluno')
            ->where('fk_curso', '=', $result->id)
            ->get();

        return array_merge($result->toArray(), ['comentarios' => $comentarios]);;
    }

    /**
     * Retorna todos os cursos por tipo:
     * (online, presencial, remoto)
     *
     */
    public static function listaITV($idUsuario, $idTipo = null, $status = 5, $idFaculdade = 1, $idCategoria = null, $idEstrutura = null) {

        $cursos = Curso::distinct()->select(
            'cursos.id',
            'cursos.titulo as nome_curso',
            'cursos.idioma',
            'cursos.slug_curso',
            'cursos.imagem',
            'cursos_valor.valor',
            'cursos_valor.valor_de',
            'professor.nome as nome_professor',
            'professor.sobrenome as sobrenome_professor',
            'fk_cursos_tipo as tipo',
            'estrutura_curricular_conteudo.data_inicio',
            'estrutura_curricular_conteudo.ordem',
            'estrutura_curricular.tipo_liberacao',
            DB::raw('IFNULL(cursos_concluidos.fk_curso, 0) as curso_concluido')
        )
            ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
            ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
            ->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id')
            ->join('cursos_categoria_curso', 'cursos_categoria_curso.fk_curso', '=', 'cursos.id')
            ->join('estrutura_curricular_conteudo', function ($join) {

                $join->on('estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos.id');
                $join->on('estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria_curso.fk_curso_categoria');

            })
            ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_conteudo.fk_estrutura')
            ->join('estrutura_curricular_usuario', 'estrutura_curricular_usuario.fk_estrutura', '=', 'estrutura_curricular.id')
            ->leftjoin('cursos_concluidos', function ($join) {
                $join->on('cursos_concluidos.fk_faculdade', '=', 'cursos_faculdades.fk_faculdade');
                $join->on('cursos_concluidos.fk_usuario', '=', 'estrutura_curricular_usuario.fk_usuario');
                $join->on('cursos_concluidos.fk_curso', '=', 'cursos.id');
                //
            })
            ->where('cursos.status', $status)
            ->where('cursos_faculdades.fk_faculdade', $idFaculdade)
            ->where('estrutura_curricular_usuario.fk_usuario', $idUsuario);

        if (!empty($idCategoria)) {
            $cursos->where('cursos_categoria_curso.fk_curso_categoria', $idCategoria);
        }

        if (!empty($idTipo)) {
            $cursos->where('cursos.fk_cursos_tipo', $idTipo);
        }

        if (!empty($idEstrutura)) {
            $cursos->where('estrutura_curricular.id', $idEstrutura);
        }

        $cursos->orderBy(DB::raw('TIMEDIFF(`estrutura_curricular_conteudo`.`data_inicio`, now()) >= 0'));
        $cursos->orderBy('estrutura_curricular_conteudo.ordem', 'asc');

        $cursos = $cursos->get()->toArray();

        return collect($cursos)->unique()->toArray();
    }

    static function verificarSlugsNaoCadastrados(){
        $results = DB::table('cursos')
            ->select('id', 'titulo', 'slug_curso')
            ->whereNull('slug_curso')
            ->get()
            ->toArray();
        $cursos = [];
        foreach($results as $i => $rs){
            $cursos[] = (array)$rs;
        }
        if(empty($cursos)){
            return;
        }else{
            foreach($cursos as $i => $rs){
                DB::table('cursos')
                    ->where('id', $rs['id'])
                    ->limit(1)
                    ->update(
                        [
                            'slug_curso' => self::configurarSlugCurso($rs['titulo'])
                        ]
                    );
            }
        }
        return;
    }

    static function configurarSlugCurso($curso){

    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable() {
        return [
            'slug_curso' => [
                'source' => 'titulo'
            ]
        ];
    }
}
