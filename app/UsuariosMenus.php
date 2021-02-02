<?php


namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class UsuariosMenus extends Model
{
    use Notifiable, Cachable;
    
    protected $table = 'usuarios_menus';
    protected $fillable = ['descricao', 'status', 'fk_criador_id', 'fk_atualizador_id', 'criacao', 'atualizacao'];

    public $timestamps = false;

    public $rules = [
        'descricao' => 'required'
    ];

    public $messages = [
        'descricao' => 'Descrição'
    ];
}
