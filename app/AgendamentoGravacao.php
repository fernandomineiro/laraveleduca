<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AgendamentoGravacao extends Model
{
    protected $table = 'agendamentos_gravacao';
    protected $fillable = ['fk_parceiro', 'data', 'hora', 'local', 'status', 'nome_curso', 'possui_anexo', 'material_enviado', 'fk_produtora', 'fk_projeto_tipo', 'fk_projeto', 'fk_criador_id', 'fk_atualizador_id', 'criacao', 'atualizacao'];

    public $timestamps = false;

    public $rules = [
        'nome_curso' => 'required',
        'fk_projeto' => 'required',
        'fk_produtora' => 'required',
        'fk_parceiro' => 'required'
    ];

    public static function lista()
    {
        return self::select(
            'agendamentos_gravacao.*',
            'usuarios.nome as parceiro',
            'produtora.razao_social as produtora',
            'faculdades.fantasia as projeto'
        )
            ->join('produtora', 'agendamentos_gravacao.fk_produtora', '=', 'produtora.id')
            ->join('projeto_tipo', 'agendamentos_gravacao.fk_projeto_tipo', '=', 'projeto_tipo.id')
            ->join('faculdades', 'agendamentos_gravacao.fk_projeto', '=', 'faculdades.id')
            ->join('parceiro', 'agendamentos_gravacao.fk_parceiro', '=', 'parceiro.id')
            ->join('usuarios', 'parceiro.fk_usuario_id', '=', 'usuarios.id')
            ->get();
    }

    public $messages = [
        'nome_curso' => 'Nome do curso',
        'fk_parceiro' => 'Informe o parceiro',
        'fk_projeto' => 'Informe o projeto',
        'fk_produtora' => 'Informe a produtora'
    ];

}
