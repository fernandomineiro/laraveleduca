<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EstruturaCurricularConteudo extends Model
{
    //
    protected $primaryKey = 'id';
    protected $table = 'estrutura_curricular_conteudo';
    protected $fillable = ['fk_estrutura','fk_conteudo', 'ordem', 'fk_categoria', 'data_inicio', 'modalidade'];

    public $timestamps = false;

    public $rules = [
        'fk_estrutura' => 'required',
        'fk_conteudo' => 'required'
    ];

    public $messages = [
        'fk_assinatura' => 'Assinatura',
        'fk_conteudo' => 'Curso',
        'assinatura' => 'Status',
    ];
}
