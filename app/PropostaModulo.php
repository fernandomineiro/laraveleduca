<?php

namespace App;

class PropostaModulo extends Model
{
    protected $fillable = [
        'fk_proposta', 
        'titulo',
        'ordem_modulo',
        'url_video', 
        'arquivo', 
        'duracao',
        'fk_criador_id',
        'fk_atualizador_id', 
        'data_criacao', 
        'data_atualizacao',
        'criacao', 
        'atualizacao',
        'status'
    ];
    
    protected $primaryKey = 'id';
    protected $table = "proposta_modulos";

    public $rules = [
        //'ordem_modulo' => 'required',
        //'fk_proposta' => 'required',
        'titulo' => 'required'
    ];

    public $messages = [
        //'ordem_modulo' => 'Ordem',
        'titulo' => 'Título',
        'arquivo' => 'Arquivo',
        'url_video' => 'Url',
        'fk_proposta' => 'Status',
        'duracao' => 'Duração total'
    ];
    
    /**
     * Retorna a classe Proposta associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function proposta()
    {
        return $this->belongsTo('App\Proposta', 'fk_proposta');
    }    
}
