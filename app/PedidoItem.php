<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    protected $table = 'pedidos_item';
    protected $primaryKey = 'id';
    protected $fillable = [
        'valor_bruto',
        'valor_desconto',
        'valor_imposto',
        'valor_liquido',
        'fk_produto_externo_id',
        'status',
        'fk_pedido',
        'fk_curso',
        'fk_evento',
        'fk_trilha',
        'fk_assinatura'
    ];

    public $timestamps = false;
    public $rules = [
        'valor_bruto' => 'required',
        'valor_desconto' => 'required',
        'valor_imposto' => 'required',
        'valor_liquido' => 'required',
        'status' => 'required',
        'fk_pedido' => 'required',
    ];
    public $messages = [
        'tipo_item' => 'Tipo',
        'fk_pedido' => 'Pedido',
        'valor_bruto' => 'Valor Bruto',
        'valor_desconto' => 'Valor de Desconto',
        'valor_imposto' => 'valor de Imposto',
        'valor_liquido' => 'Valor Liquido',
        'status' => 'Status'
    ];

    public function curso()
    {
        return $this->hasOne('App\Curso', 'id', 'fk_curso');
    }

    public function evento()
    {
        return $this->hasOne('App\Curso', 'id', 'fk_evento');
    }

    public function trilha()
    {
        return $this->hasOne('App\Trilha', 'id', 'fk_trilha');
    }

    public function assinatura()
    {
        return $this->hasOne('App\Assinatura', 'id', 'fk_assinatura');
    }

    public function faculdade()
    {
        $faculdadeId = 0;

        if (!empty($this->attributes['fk_curso'])) {
            $curso = Curso::find($this->attributes['fk_curso']);
            $faculdadeId = $curso['fk_faculdade'];
        } elseif (!empty($this->attributes['fk_evento'])) {
            $curso = Eventos::find($this->attributes['fk_evento']);
            $faculdadeId = $curso['fk_faculdade'];
        } elseif (!empty($this->attributes['fk_trilha'])) {
            $curso = Trilha::find($this->attributes['fk_trilha']);
            $faculdadeId = $curso['fk_faculdade'];
        } elseif(!empty($this->attributes['fk_assinatura'])) {
            $curso = Assinatura::find($this->attributes['fk_assinatura']);
            $faculdadeId = $curso['fk_faculdade'];
        }

        return $this->hasOne('App\Faculdade', 'id', $faculdadeId);
    }
}
