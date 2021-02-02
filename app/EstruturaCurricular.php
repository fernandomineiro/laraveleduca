<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EstruturaCurricular extends Model {
    
    protected $fillable = [
        'titulo',
        'fk_escola',
        'status',
        'criacao',
        'atualizacao',
        'fk_criador_id',
        'fk_atualizador_id',
        'data_criacao',
        'data_atualizacao',
        'slug',
        'fk_orientador',
        'tipo_liberacao',
        'estrutura_livre_cadastro',
        'fk_certificado_layout'
    ];

    protected $primaryKey = 'id';
    protected $table = "estrutura_curricular";
    public $timestamps = false;

    public $rules = [
        'titulo' => 'required',
    ];

    public $messages = [
        'status.required' => 'O status da assinatura é um campo obrigatório',
        'titulo.required' => 'O título da assinatura é um campo obrigatório',
    ];

    public function cursos() {
        return $this->belongsToMany('App\Curso', 'estrutura_curricular_conteudos', 'fk_estrutura', 'fk_conteudo');
    }

    public function escola() {
        return $this->belongsTo('App\Escola', 'fk_escola', 'id');
    }
}
