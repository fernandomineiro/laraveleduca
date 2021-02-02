<?php

namespace App;

use App\Traits\EducazSoftDelete;
use Illuminate\Database\Eloquent\Model;

class Eventos extends Model{

    use EducazSoftDelete;

    protected $table = 'eventos';
    protected $fillable = ['titulo', 'descricao', 'imagem', 'fk_categoria', 'fk_faculdade', 'fk_criador_id', 'fk_atualizador_id', 'data_criacao',
        'data_atualizacao', 'criacao', 'atualizacao', 'status', 'endereco'];

    public $timestamps = true;
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';
    const SOFT_DELETE = 'status';

    public $rules = [
        'titulo' => 'required',
        'descricao' => 'required',
        'endereco' => 'required',
        'fk_faculdade' => 'required|numeric|min:0|not_in:0',
        'fk_categoria' => 'required|numeric|min:0|not_in:0'
    ];

    public $messages = [
        'titulo' => 'Título é campo obirgatório',
        'descriçao' => 'Descrição é campo obirgatório',
        'endereco.required' => 'Endereço é um campo obirgatório'
    ];


    public function evntosagendados(){
        return $this->hasMany('App\AgendaEventos');
    }
}

