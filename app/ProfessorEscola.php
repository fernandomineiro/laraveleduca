<?php

namespace App;

use App\Traits\EducazSoftDelete;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfessorEscola extends Model {

    use Notifiable;

    const CREATED_AT = 'criacao';
    const UPDATED_AT = 'atualizacao';

    protected $table = 'professor_escola';
    protected $fillable = [
        'id',
        'fk_professor',
        'fk_escola',
    ];
}
