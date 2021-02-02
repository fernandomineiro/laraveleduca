<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cupom extends Model
{
    protected $fillable = ['titulo', 'codigo_cupom', 'descricao', 'data_cadastro', 'data_validade_inicial',
        'numero_maximo_usos', 'numero_maximo_produtos', 'data_validade_final', 'tipo_cupom_desconto', 'status', 'valor',
        'fk_criador_id', 'fk_atualizador_id', 'data_criacao', 'data_atualizacao', 'criacao', 'atualizacao', 'fk_faculdade'];

    protected $primaryKey = 'id';
    protected $table = "cupom";
    public $timestamps = false;

    public $rules = [
        'titulo' => 'required',
        'codigo_cupom' => 'required',
        'descricao' => 'required',
        'numero_maximo_usos' => 'nullable|numeric',
        'data_validade_inicial' => 'required|date_format:d/m/Y|after_or_equal:today',
        'data_validade_final' => 'required|date_format:d/m/Y|after_or_equal:data_validade_inicial',
        'tipo_cupom_desconto' => 'required',
        'numero_maximo_produtos' => 'nullable|numeric',
        'status' => 'required',
        'valor' => 'required'
    ];
    public $rules_edit = [
        'titulo' => 'required',
        'codigo_cupom' => 'required',
        'descricao' => 'required',
        'data_validade_inicial' => 'required|date_format:d/m/Y',
        'data_validade_final' => 'required|date_format:d/m/Y|after_or_equal:data_validade_inicial',
        'tipo_cupom_desconto' => 'required',
        'status' => 'required',
        'valor' => 'required'
    ];

    public $messages = [
        'titulo' => 'Título',
        'codigo_cupom' => 'Código do Cupom',
        'descricao' => 'Descrição',
        'data_cadastro' => 'Data de Cadastro',
        'data_validade_inicial' => 'Data Vigência Inicial',
        'data_validade_final' => 'Data Vigência Final',
        'tipo_cupom_desconto' => 'Tipo de Cupom',
        'status' => 'Status',
        'data_validade_inicial.after_or_equal' => 'A data inicial deve ser igual ou posterior a data de hoje!',
        'data_validade_final.after_or_equal' => 'A data final deve ser igual ou posterior a data inicial!',
        'valor.required' => 'Valor do cupom é obrigatório',
        'titulo.required' => 'Nome do cupom é obrigatório',
        'codigo_cupom.required' => 'Código do cupom é obrigatório',
        'descricao.required' => 'Descrição do cupom é obrigatório',
        'tipo_cupom_desconto.required' => 'Tipo do cupom é obrigatório',
        'status.required' => 'Status do cupom é obrigatório',
        'numero_maximo_usos.numeric' => 'Número máximo de usos do cupom precisa ser um número válido!',
    ];
}
