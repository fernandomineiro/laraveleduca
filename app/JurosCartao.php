<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JurosCartao extends Model
{
    protected $table = 'juros_cartao';
    protected $fillable = ['parcela', 'percentual', 'minimo'];
    public $timestamps  = false;
}
