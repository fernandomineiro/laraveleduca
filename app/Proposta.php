<?php

namespace App;

class Proposta extends Model
{
    protected $fillable = [
        'titulo',
        'descricao',
        'url_video',
        'duracao_total',
        'fk_categoria_id',
        'fk_professor',
        'fk_proposta_status',
        'objetivo',
        'publico_alvo',
        'local',
        'sugestao_preco',
        'sugestao_categoria',
        'fk_criador_id',
        'fk_atualizador_id',
        'data_criacao',
        'data_atualizacao',
        'criacao',
        'atualizacao',
        'status',
        'proposta_modulos'
    ];
    protected $primaryKey = 'id';
    
    protected $table = "propostas";
    
    public $rules = [
        'titulo' => 'required',
        'descricao' => 'required',
        //'local' => 'required',
        //'fk_professor' => 'required', não precisa informar pois é adicionado automaticamente ao usar a relação do professor para o save
        'fk_proposta_status' => 'required',
            //'duracao_total' => 'required',
            //'sugestao_preco' => 'required',
            //'sugestao_categoria' => 'required',
    ];
    
    public $messages = [
        'titulo' => 'Título',
        'descricao' => 'Descrição',
        'local' => 'Local',
        'fk_categoria_id' => 'Categoria',
        //'fk_professor' => 'Professor',
        'fk_proposta_status' => 'Status',
        'duracao_total' => 'Duração total',
        'sugestao_preco' => 'Preço',
        'sugestao_categoria' => 'Sugestão de Categoria',
    ];

    /**
     * Retorna a classe PropostaModulos associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function modulos()
    {
        return $this->hasMany('App\PropostaModulo', 'fk_proposta');
    }

    /**
     * Retorna a classe Professor associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function professor()
     {
        return $this->belongsTo('App\Professor', 'fk_professor');
    }

    /**
     * Sobreescreve o save padrão para salvar os módulos caso venham no objeto
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {
        if (isset($this->proposta_modulos)) {
            
            $modulos = $this->proposta_modulos;
            
            unset($this->proposta_modulos);
            
            parent::save($options);

            if ($this->id) {

                $ordem = 0;

                foreach ((array) $modulos as $modulo) {

                    $propostaModulo = new PropostaModulo($modulo);
                    $propostaModulo->ordem_modulo = $ordem++;

                    if (!isset($propostaModulo->status)) {
                        $propostaModulo->status = 1;
                    }

                    $this->modulos()->save($propostaModulo);
                }
            }
        } else {
            parent::save($options);
        }
    }

}
