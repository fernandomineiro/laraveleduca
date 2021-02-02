<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoEmail extends Model {

    protected $primaryKey = 'id';
    protected $table = 'tipo_email';

    protected $fillable = [
        'titulo',
        'descricao',
        'status',
        'fk_atualizador_id',
        'fk_criador_id',
        'criacao',
        'atualizacao'
    ];

    public $timestamps = false;

    CONST CONFIRMACAO_COMPRA = 1;
    CONST PROMOCOES = 2;
    CONST CERTIFICADOS = 3;
    CONST APROVACOES_CURSO = 4;
    CONST CURSO_A_AVALIAR = 5;
    CONST PROFESSOR_AVISO_TRABALHO = 6;
    CONST ALUNO_AVISO_MENSAGEM = 7;
    CONST STATUS_PAGAMENTO = 8;
    CONST ENVIO_CREDENCIAIS = 9;
    CONST ESQUECI_SENHA = 10;
    CONST BOAS_VINDAS = 11;
    CONST RENOVAR_ASSINATURA = 12;
    CONST AVISO_CURSO_APROVADO = 13;
    CONST AVISO_CURSO_AVALIAR = 14;
    CONST AVISO_TRABALHO_ENVIADO = 15;
    CONST PROFESSOR_AVISO_MENSAGEM = 16;
    CONST AVISO_PAGAMENTO = 17;
    CONST OPINIAO_PROFESSOR = 18;
    CONST PORTAL_ESQUECI_SENHA = 19;
    CONST CONFIRMACAO_COMPRA_BOLETO = 20;
    CONST CONFIRMACAO_COMPRA_ASSINATURA = 21;
    CONST REENVIO_BOLETO = 22;
    CONST COMPROVANTE_PAGAMENTO = 23;
    CONST CADASTRO_PROFESSOR_EM_ANALISE = 24;
    CONST NOTIFICAR_APROVACAO_PROFESSOR = 25;
    CONST AVISO_NOVO_CADASTRO_PROFESSOR = 26;
    CONST CANCELAMENTO_ASSINATURA = 27;
}
