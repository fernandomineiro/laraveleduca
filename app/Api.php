<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\EducazSoftDelete;

class Api extends Model{

    use EducazSoftDelete;

    const CREATED_AT = 'criacao';
    const UPDATED_AT = 'atualizacao';
    const SOFT_DELETE = 'status';
    public $timestamps = true;

    protected $table = 'apis';
    protected $primaryKey = 'id';
    protected $fillable = [
        'titulo',
        'descricao',
        'tipo',
        'url',
        'params',
        'status',
        'fk_faculdade'
    ];

    public $rules = [
        'titulo' => 'required',
        'tipo' => 'required',
        'url' => 'required',
        'params' => 'required',
        'status' => 'required',
        'fk_faculdade' => 'required|numeric|gt:0'
    ];

    public $messages = [
        'titulo' => 'Titulo',
        'descricao' => 'Descrição',
        'tipo' => 'Tipo',
        'url' => 'URL',
        'params' => 'Parametros',
        'fk_faculdade' => 'Faculdade',
        'status' => 'Status',
        'fk_faculdade.gt' => 'Selecione uma Faculdade válida.'
    ];


}
