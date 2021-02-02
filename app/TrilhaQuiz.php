<?php

namespace App;

use App\Traits\EducazSoftDelete;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TrilhaQuiz extends Model
{
    use Notifiable, Cachable, EducazSoftDelete;

    public $timestamps = true;
    const CREATED_AT = 'criacao';
    const UPDATED_AT = 'atualizacao';
    const SOFT_DELETE = 'status';

    protected $table = 'trilha_quiz';
    protected $primaryKey = 'id';
    protected $fillable = [
        'fk_trilha',
        'percentual_acerto',
        'status',
        'tipoquest',
        'fk_atualizador_id',
        'fk_criador_id',
        'criacao',
        'atualizacao'
    ];
}
