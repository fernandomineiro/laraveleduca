<?php

namespace App;

use App\Traits\EducazSoftDelete;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoriaTurma extends Model {

    protected $table = 'categoria_turma';
    protected $fillable = [
        'id',
        'fk_turma',
        'fk_categoria',
    ];

    public $timestamps = false;
}
