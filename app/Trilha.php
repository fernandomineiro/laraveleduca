<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class Trilha extends Model
{
    use Notifiable;
    
    protected $primaryKey = 'id';
    protected $table = 'trilha';
    protected $fillable = [
        'titulo',
        'valor',
        'status',
        'descricao',
        'fk_atualizador_id',
        'fk_criador_id',
        'criacao',
        'atualizacao',
        'valor_venda',
        'duracao_total',
        'teaser',
        'imagem',
        'questionario', 
        'slug_trilha'
    ];

    public $timestamps = false;

    public $rules = [
        'titulo' => 'required',
        'valor' => 'required',
        //'valor_venda' => 'required',
    ];

    public $messages = [
        'titulo' => 'Nome da Trilha',
        'titulo.required' => 'Nome da Trilha é obrigatório',
        'valor.required' => 'Valor da Trilha é obrigatório',
        'valor_venda' => 'Valor Venda',
        'descricao' => 'Descrição'
    ];


	/**
	 * Retorna todos os cursos por tipo:
	 * (online, presencial, remoto)
	 *
	 */
    public static function lista($idCategoria = null, $idFaculdade = null)
    {
        $trilhas = Trilha::select(
			'trilha.id',
            'trilha.titulo', 
            'trilha.slug_trilha', 
            'trilha.descricao',
			'trilha.valor',
			'trilha.valor_venda',
            'trilha.duracao_total as total_minutos',
            'trilhas_faculdades.gratis'
            //'faculdades.fantasia as nome_faculdade',
            //'faculdades.id as id_faculdade',
		)
        //->join('faculdades', 'faculdades.id', '=', 'trilha.fk_faculdade')
        ->where('trilha.status', 5);

        if($idCategoria) {
            $trilhas->join('trilhas_categoria', 'trilhas_categoria.fk_trilha', '=', 'trilha.id')
                ->join('cursos_categoria', 'cursos_categoria.id', '=', 'trilhas_categoria.fk_categoria')
                ->where('trilhas_categoria.fk_categoria', '=', $idCategoria);
        }

        if (!empty($idFaculdade)) {
            $trilhas->join('trilhas_faculdades', 'trilhas_faculdades.fk_trilha', '=', 'trilha.id')
                ->where('trilhas_faculdades.fk_faculdade', '=', $idFaculdade);
        }

        return $trilhas->get();
    }

    public static function searchTrilha($dados)
    {
        $trilhas = Trilha::select(
            \DB::raw("CONCAT(trilha.id,' - ', trilha.titulo) as autocomplete"),
			'trilha.id',
            'trilha.titulo',
            'trilha.descricao',
			'trilha.valor',
			'trilha.valor_venda',
            'trilha.duracao_total as total_minutos',
            \DB::raw('(trilha.valor - trilha.valor_venda) as promocao'),
            \DB::raw('COUNT(pedidos_item.fk_trilha) as vendidos')
		)
            ->leftJoin('pedidos_item', 'pedidos_item.fk_trilha', '=', 'trilha.id')
            ->where('trilha.status', 5)
            ->groupBy('id',
                'titulo',
                'descricao',
                'valor',
                'valor_venda',
                'total_minutos');

        if(isset($dados['id_categoria']) && $dados['id_categoria'] != '-1') {
            /*$trilhas->join('trilhas_categoria', 'trilhas_categoria.fk_trilha', '=', 'trilha.id')
                ->join('cursos_categoria', 'cursos_categoria.id', '=', 'trilhas_categoria.fk_categoria')
                ->where('trilhas_categoria.fk_categoria', '=', $dados['id_categoria']);
            aparentemente a busca de categoria nesse caso funciona pelo nome da trilha
            */
            $trilhas->where('trilha.id', $dados['id_categoria']);
        }

        if(isset($dados['categoria']) && $dados['categoria'] != '-1') {
            $trilhas->join('trilhas_categoria', 'trilhas_categoria.fk_trilha', '=', 'trilha.id')
                ->join('cursos_categoria', 'cursos_categoria.id', '=', 'trilhas_categoria.fk_categoria')
                ->where('trilhas_categoria.fk_categoria', '=', $dados['categoria']);
        }

        if(isset($dados['search'])) {
            $trilhas->where(\DB::raw('LOWER(trilha.titulo)'), 'like', '%' . strtolower($dados['search']) . '%');
        }

        if(isset($dados['order1'])) {
            switch ($dados['order1']) {
                case 'asc':

                    $trilhas->orderBy('trilha.titulo', 'asc');
                    break;
                case 'desc':

                    $trilhas->orderBy('trilha.titulo', 'desc');
                    break;

                case 'vendidos':
                    $trilhas->orderBy('vendidos', 'desc');
                    break;

                case 'recentes':
                    $trilhas->orderBy('trilha.id', 'desc');

                    break;

                case 'promocoes':
                    $trilhas->orderByRaw('CAST(promocao AS DECIMAL(10,2)) desc');
                    break;

            }
        }

        if(isset($dados['order2'])) {
            //TODO: decidir como esse filtro será montado
        }

        if(isset($dados['cidade'])) {
            //TODO: decidir como esse filtro será montado e se ele deverá ser mantido
        }

        if(isset($dados['preco']) && (float) $dados['preco'] > 0) {
            $trilhas->whereBetween('trilha.valor_venda', [0, (float) $dados['preco']]);
        }

        return $trilhas->get();
    }

    public static function obter($idTrilha)
    {
        $trilha = Trilha::select(
            'trilha.id',
            'trilha.titulo',
            'trilha.descricao',
            'trilha.valor',
            'trilha.valor_venda',
            'trilha.duracao_total as total_minutos'
            //'faculdades.fantasia as nome_faculdade',
            //'faculdades.id as id_faculdade',
          //  'trilha.fk_categoria as id_categoria',
            //'cursos_categoria.titulo as nome_categoria'
        )
        //->join('faculdades', 'faculdades.id', '=', 'trilha.fk_faculdade')
        //->join('cursos_categoria', 'cursos_categoria.id', '=', 'trilha.fk_categoria')
        ->where('trilha.id', $idTrilha);

        return $trilha->get();
    }

    public static function trilhasFavorito($idAluno) {
        $trilhas = Trilha::select(
            'trilha.id',
            'trilha.titulo',
            'trilha.descricao',
            'trilha.valor',
            'trilha.valor_venda',
            'trilha.status'
            //'faculdades.fantasia as nome_faculdade',
            //'cursos_categoria.titulo as categoria',
            //'certificado_layout.titulo as certificado'
        )
            ->join('trilhas_favorito', 'trilhas_favorito.fk_trilha', '=', 'trilha.id')
            //->leftJoin('faculdades', 'faculdades.id', '=', 'trilha.fk_faculdade')
            //->leftJoin('cursos_categoria', 'cursos_categoria.id', '=', 'trilha.fk_categoria')
            //->leftJoin('certificado_layout', 'certificado_layout.id', '=', 'trilha.fk_certificado')
            ->where('trilhas_favorito.fk_usuario', $idAluno);

        $lista = $trilhas->get()->toArray();

        $trilhas = [];
        foreach($lista as $key => $item) {
            $trilhas[$item['id']] = $item;
        }

        return $trilhas;
    }

    public static function trilhasLista($parametros) {
        $trilhas = Trilha::select('trilha.*')
            ->where('trilha.status', '>', 0);

        if(isset($parametros['nome_item']) && !empty($parametros['nome_item'])) $trilhas->where('trilha.titulo', 'like', '%'. $parametros['nome_item']. '%');

        if(isset($parametros['cargahoraria']) && !empty($parametros['cargahoraria'])) $trilhas->where('trilha.duracao_total', (float)$parametros['duracao_total']);

        if(isset($parametros['preco']) && !empty($parametros['preco'])) $trilhas->where('trilha.valor', (float)$parametros['preco']);

        if(isset($parametros['preco_de']) && !empty($parametros['preco_de'])) $trilhas->where('trilha.valor_venda', (float)$parametros['preco_de']);

        if(isset($parametros['status']) && !empty($parametros['status'])) $trilhas->where('trilha.status', '=', $parametros['status']);

        if(isset($parametros['faculdade']) && !empty($parametros['faculdade'])) {
            $trilhas->join('trilhas_faculdades', 'trilhas_faculdades.fk_trilha' ,'=', 'trilha.id');
            $trilhas->where('trilhas_faculdades.fk_faculdade', '=', $parametros['faculdade']);
        }

        if(isset($parametros['categoria']) && !empty($parametros['categoria'])) {
            $trilhas->join('trilhas_categoria', 'trilhas_categoria.fk_trilha' ,'=', 'trilha.id');
            $trilhas->where('trilhas_categoria.fk_categoria', '=', $parametros['categoria']);
        }

        $retorno = $trilhas->get();
        $trilhas_retorno = [];
        $lista_status = [
            '1' => 'Rascunho',
            '2' => 'Revisar',
            '3' => 'Não Aprovado',
            '4' => 'Aprovado',
            '5' => 'Publicado'
        ];
        foreach ($retorno as $trilha) {
            $trilha = collect($trilha);
            $lista_categorias_selecionadas = CursoCategoria::select('cursos_categoria.*')
                ->join('trilhas_categoria', 'trilhas_categoria.fk_categoria', 'cursos_categoria.id')
                ->where('fk_trilha', '=', $trilha['id'])
                ->get();
            $cont = 0;
            $categorias = '';
            foreach ($lista_categorias_selecionadas as $categoria) {
                if ($cont == 0) $categorias = '' . $categoria->titulo;
                else $categorias = $categorias . ', ' . $categoria->titulo;
                $cont++;
            }
            $x = 0;
            $lista_faculdades = Faculdade::select('faculdades.*')->where('status', '=', 1)
                ->join('trilhas_faculdades', 'trilhas_faculdades.fk_faculdade', 'faculdades.id')
                ->where('fk_trilha', '=', $trilha['id'])
                ->distinct()
                ->get();
            $faculdades = '';
            foreach ($lista_faculdades as $faculdade) {
                if ($x == 0) $faculdades = '' . $faculdade->fantasia;
                else $faculdades = $faculdades . ', ' . $faculdade->fantasia;
                $x++;
            }
            $trilha->put('categorias', $categorias);
            $trilha->put('status_nome', $lista_status[$trilha['status']]);
            $trilha->put('projetos', $faculdades);
            $trilha->put('inscritos', PedidoItem::where('fk_trilha', '=', $trilha['id'])->count());
            $trilha->put('assinaturas', AssinaturaConteudo::where('assinatura_conteudos.fk_conteudo', '=', $trilha['id'])
                ->join('assinatura', 'assinatura.id', '=', 'assinatura_conteudos.fk_assinatura')
                ->where('assinatura.fk_tipo_assinatura', 3)
                ->count());
            array_push($trilhas_retorno, $trilha);
        }

        if(isset($parametros['inscritos']) && !empty($parametros['inscritos'])) {
            $trilhas_retorno = collect($trilhas_retorno)->where('inscritos', $parametros['inscritos']);
            $trilhas_retorno = $trilhas_retorno->toArray();
        }

        if(isset($parametros['assinaturas']) && !empty($parametros['assinaturas'])) {
            $trilhas_retorno = collect($trilhas_retorno)->where('assinaturas', $parametros['assinaturas']);
            $trilhas_retorno = $trilhas_retorno->toArray();
        }
        return $trilhas_retorno;
    }

    static function verificarSlugsNaoCadastrados(){
			$results = DB::table('trilha')
					->select('id', 'titulo', 'slug_trilha')
					->whereNull('slug_trilha')
					->get()
					->toArray();
			$categorias = [];
			foreach($results as $i => $rs){
					$categorias[] = (array)$rs;
			}
			if(empty($categorias)){
					return;
			}else{
					foreach($categorias as $i => $rs){
							DB::table('trilha')
									->where('id', $rs['id'])
									->limit(1)
									->update(
											[
													'slug_trilha' => self::configurarSlug($rs['titulo'])
											]
									);
					}
			}
			return;
    }
    
    static function configurarSlug($trilha){
			$slug = $trilha;
			$slug = str_replace('-', '', $slug);
			$slug = preg_replace('/\s+/', ' ', $slug);
			$slug = str_replace(' ', '-', preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities(trim($slug))));
			$slug = str_replace(['?', '!', '@', '&', '$', '*', '%', '¨', '_', "'", '"', '<', '>', ",", ".", ';', '/', '\\', '', '{', '}', '[', ']', '+', ':'], [''], $slug);
			return strtolower($slug);
		}
}

