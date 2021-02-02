<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AssinaturaConteudo extends Model
{
    use Notifiable, Cachable;
    
    protected $primaryKey = 'id';
    protected $table = 'assinatura_conteudos';
    protected $fillable = ['fk_assinatura','fk_conteudo','assinatura'];

    public $timestamps = false;

    public $rules = [
        'fk_assinatura' => 'required',
        'fk_conteudo' => 'required',
        'assinatura' => 'required'
    ];

    public $messages = [
        'fk_assinatura' => 'Assinatura',
        'fk_conteudo' => 'Curso',
        'assinatura' => 'Status',
    ];



}
