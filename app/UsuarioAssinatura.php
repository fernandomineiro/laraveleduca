<?php

namespace App;

use App\Traits\EducazSoftDelete;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class UsuarioAssinatura extends Model
{
    use Notifiable, Cachable;
    
    //use EducazSoftDelete; verificar necessidade de padronizar essa model para utilização do soft delete
    
    protected $fillable = [
        'id', 'fk_usuario', 'fk_assinatura', 'fk_pedido', 'status', 'codigo_assinatura_wirecard', 'fk_criador_id', 'invoice_id_wirecard'
    ];

    protected $primaryKey = 'id';
    protected $table = "usuarios_assinaturas";

    public $timestamps = true;

    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';
    const SOFT_DELETE = 'status';

    public static function relatorio_repasses_assinaturas($data) {
        echo '<pre style="background-color: #fff;">'; print_r("xxxx"); echo '</pre>'; exit();
    }

}
