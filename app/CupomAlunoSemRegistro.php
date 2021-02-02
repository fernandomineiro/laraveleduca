<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CupomAlunoSemRegistro extends Model
{
    //
    protected $fillable = [
        'fk_cupom',
        'fk_faculdade',
        'cpf',
        'ra',
        'email',
        'nome',
        'numero_usos',
    ];

    protected $primaryKey = 'id';
    protected $table = "cupom_aluno_sem_registro";
    public $timestamps = false;

    public $rules = [
        'fk_cupom' => 'required',
        'email' => 'required',
    ];
}
