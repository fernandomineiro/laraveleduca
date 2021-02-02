<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CursoCategoria extends Model
{
    use Notifiable, Cachable;
    
    protected $table = 'cursos_categoria';
    protected $fillable = [
        'titulo',
        'ementa',
        'disciplina',
        'slug_categoria',
        'status',
        'icone',
        'fk_criador_id',
        'fk_atualizador_id',
        'data_criacao',
        'data_atualizacao',
        'criacao',
        'atualizacao'
    ];

    public $timestamps = false;

    public $messages = [
        'titulo.required' => 'Título é obrigatório',
        'titulo.unique' => 'Categoria título',
        'status.required' => 'Status é obrigatório',
    ];

    protected $appends = ['autocomplete'];

    public function getAutocompleteAttribute() {
        return $this->id . ' - ' . $this->titulo;
    }

    public function _validate($data)
    {
        $obj = $this;
        return Validator::make($data, [
            'status' => 'required',
            'titulo' => [
                'required',
                Rule::unique('cursos_categoria', 'titulo')->ignore($obj->id)->where(function ($query) use ($obj, $data) {
                    $query->where('titulo', '=', $data['titulo']);
                }),
            ]
        ], $this->messages);
    }

    static function validate ($data)
    {
        return self::_validate($data);
    }

    /**
     * Retorna a classe CursoCategoriaCurso  associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function categoriaCurso()
    {
        return $this->hasMany('\App\CursoCategoriaCurso', 'fk_curso_categoria');
    }

		static function verificarSlugsNaoCadastrados(){
			$results = DB::table('cursos_categoria')
				->select('id', 'titulo', 'slug_categoria')
				->whereNull('slug_categoria')
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
					DB::table('cursos_categoria')
						->where('id', $rs['id'])
						->limit(1)
						->update(
							[
								'slug_categoria' => self::configurarSlugCategoria($rs['titulo'])
							]
						);
				}
			}
			return;
		}
		
		static function configurarSlugCategoria($categoria){
			$slug = $categoria;
			$slug = preg_replace('/\s+/', ' ', $slug);
			$slug = str_replace(' ', '-', preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities(trim($slug))));
			$slug = str_replace(['?', '!', '@', '&', '$', '*', '%', '¨', '_', "'", '"', '<', '>', ",", ".", ';', '/', '\\', '', '{', '}', '[', ']', '+', ':'], [''], $slug);
			return strtolower($slug);
    }

}
