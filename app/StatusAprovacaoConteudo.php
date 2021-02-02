<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class StatusAprovacaoConteudo extends Model
{
    protected $table = 'status_aprovacao_conteudo';
    CONST APROVADO              = 1;
    CONST REPROVADO             = 2;
    CONST AGUARDANDOANALISE     = 3;
}