<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailsTemplate extends Model
{
    protected $table = 'pagamento_bradesco';
    protected $fillable = ['fk_criador_id', 'fk_atualizador_id', 'criacao', 'atualizacao', 'status', 'titulo', 'text'];

    public $timestamps = false;

    public $rules = [
        'titulo' => 'required',
        'text' => 'required'
    ];

    public $messages = [
        'titulo' => 'Título',
        'text' => 'Text'
    ];
}
