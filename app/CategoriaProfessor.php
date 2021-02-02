<?php

namespace App;

use App\Traits\EducazSoftDelete;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoriaProfessor extends Model {

    protected $table = 'categoria_professor';
    protected $fillable = [
        'id',
        'fk_usuario',
        'fk_categoria',
    ];

    public $timestamps = false;
}
