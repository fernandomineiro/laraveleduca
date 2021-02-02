<?php

namespace App;
use App\UsuarioAssinatura;
use App\AssinaturaRepasse;
use App\AssinaturaPagamento;
use App\PedidoItem;
use App\ModuloUsuario;
use App\Curso;
use DB;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Assinatura extends Model
{
    use Notifiable, Cachable;
    
    protected $fillable = [
        'titulo',
        'fk_trilha',
        'qtd_cursos',
        'periodo_em_dias',
        'status',
        'criacao',
        'atualizacao',
        'fk_tipo_assinatura',
        'valor_de',
        'valor',
        'fk_certificado',
        'fk_faculdade',
        'tipo_periodo', // 1 - Anual // 2 - Semestral // 3- Cancelamento Manual
        'plano_wirecard_id',
        'fk_criador_id',
        'fk_atualizador_id',
        'data_criacao',
        'data_atualizacao',
        'descricao',
        'tipo_liberacao'
    ];

    protected $primaryKey = 'id';
    protected $table = "assinatura";
    public $timestamps = false;

    public $rules = [
        'fk_tipo_assinatura' => 'required|numeric|min:0|not_in:0',
        // 'status' => 'required',
        'titulo' => 'required',
        //'valor' => 'required',
        'valor_de' => 'required',
        'tipo_periodo' => 'required|numeric|min:0|not_in:0',
//        'fk_assinatura_faculdade' => 'required_if:fk_tipo_assinatura,==,1',
    ];

    public $messages = [
        'fk_tipo_assinatura.not_in' => 'O tipo de assinatura é um campo obrigatório',
        'status.required' => 'O status da assinatura é um campo obrigatório',
        'titulo.required' => 'O título da assinatura é um campo obrigatório',
        'valor.required' => 'O valor da assinatura é um campo obrigatório',
        'valor_de.required' => 'O valor de venda assinatura é um campo obrigatório',
        'tipo_periodo.not_in' => 'O tipo de plano da assinatura é um campo obrigatório',
        'fk_assinatura_faculdade.required_if' => 'O projeto da assinatura é um campo obrigatório caso o tipo de assinatura FULL',
        'titulo' => 'Nome',
        'fk_tipo_assinatura.required' => 'Tipo assinatura',
        'fk_trilha' => 'Trilha',
        'qtd_cursos' => 'Quantidade de Cursos',
        'periodo_em_dias' => 'Periodo renovação Cursos (em dias)',
        'status' => 'Status',
        'valor_de.required' => 'Valor (De)',
        'valor.required' => 'Valor',
        'fk_certificado' => 'Certificado',
        'fk_faculdade' => 'Projeto/IES',
        'tipo_periodo.required' => 'Periodo'
    ];



    public function cursos()
    {
        return $this->belongsToMany('App\Curso', 'assinatura_conteudos', 'fk_assinatura', 'fk_conteudo');
    }

    public function trilhas()
    {
        return $this->belongsToMany('App\Trilha', 'assinatura_conteudos', 'fk_assinatura', 'fk_conteudo');
    }

    public function usuarios() {
        return $this->belongsToMany('App\Usuario', 'usuario_assinatura', 'fk_usuario', 'fk_assinatura');
    }

    # RELATORIO ASSINATURAS CANCELADAS
    public static function relatorio_assinaturas_canceladas($data){
        $query = UsuarioAssinatura::select(DB::raw("COUNT(usuarios_assinaturas.id) AS total"),
        DB::raw("YEAR(usuarios_assinaturas.cancelamento_agendado) AS ano"))
        ->join("assinatura AS a", "a.id", "=", "usuarios_assinaturas.fk_assinatura")
        ->join("pedidos AS p", "p.id", "=", "usuarios_assinaturas.fk_pedido")
        ->where("usuarios_assinaturas.status", 0)->where("p.fk_faculdade", $data['fk_faculdade']);

        $query->whereBetween('usuarios_assinaturas.cancelamento_agendado', $data['periodo']);

        switch ($data['agrupar_por']) {
            case 'mes':
                $query->addSelect(DB::raw("MONTH(usuarios_assinaturas.cancelamento_agendado) AS mes"));
                $query->groupBy(DB::raw("YEAR(usuarios_assinaturas.cancelamento_agendado), MONTH(usuarios_assinaturas.cancelamento_agendado)"));
                $query->orderBy(DB::raw("YEAR(usuarios_assinaturas.cancelamento_agendado), MONTH(usuarios_assinaturas.cancelamento_agendado)"));
            break;

            case 'ano':
                $query->addSelect(DB::raw("YEAR(usuarios_assinaturas.cancelamento_agendado) AS ano"));
                $query->groupBy(DB::raw("YEAR(usuarios_assinaturas.cancelamento_agendado)"));
                $query->orderBy(DB::raw("YEAR(usuarios_assinaturas.cancelamento_agendado)"));
            break;

            default:
                $query->addSelect(DB::raw("WEEK(usuarios_assinaturas.cancelamento_agendado) AS semana"));
                $query->groupBy(DB::raw("YEARWEEK(usuarios_assinaturas.cancelamento_agendado, 2)"));
                $query->orderBy(DB::raw("YEARWEEK(usuarios_assinaturas.cancelamento_agendado, 2)"));
            break;
        }

        return $query;
    }

    # RELATORIO ASSINATURAS CANCELADAS
    public static function relatorio_assinaturas_abandonadas($data){
        $query = PedidoItem::select(DB::raw("COUNT(p.id) AS total, YEAR(p.criacao) AS ano"))
        ->join("pedidos AS p", "p.id", "=", "pedidos_item.fk_pedido")
        ->where("pedidos_item.fk_assinatura", ">", 0)
        ->where("p.status", 1)->where("p.criacao", "<", date('Y-m-d H:i:s', strtotime("-1 hour")))
        ->where("p.fk_faculdade", $data['fk_faculdade']);

        $query->whereBetween('p.criacao', $data['periodo']);

        switch ($data['agrupar_por']) {
            case 'mes':
                $query->addSelect(DB::raw("MONTH(p.criacao) AS mes"));
                $query->groupBy(DB::raw("YEAR(p.criacao), MONTH(p.criacao)"));
                $query->orderBy(DB::raw("YEAR(p.criacao), MONTH(p.criacao)"));
            break;

            case 'ano':
                $query->addSelect(DB::raw("YEAR(p.criacao) AS ano"));
                $query->groupBy(DB::raw("YEAR(p.criacao)"));
                $query->orderBy(DB::raw("YEAR(p.criacao)"));
            break;

            default:
                $query->addSelect(DB::raw("WEEK(p.criacao) AS semana"));
                $query->groupBy(DB::raw("YEARWEEK(p.criacao, 2)"));
                $query->orderBy(DB::raw("YEARWEEK(p.criacao, 2)"));
            break;
        }

        return $query;
    }

    public static function total_views_mes($mes, $ano, $fk_faculdade, $fk_assinatura){
        $query = ModuloUsuario::select(DB::raw('COUNT(DISTINCT CONCAT(modulos_usuarios.fk_modulo, modulos_usuarios.fk_usuario)) AS total'),
            DB::raw('GROUP_CONCAT(DISTINCT p.id) AS pedidos'))
            ->join('cursos_modulos AS cm', 'cm.id', '=', 'modulos_usuarios.fk_modulo')
            ->join('pedidos AS p', 'p.fk_usuario', '=', 'modulos_usuarios.fk_usuario')
            ->join('pedidos_item AS pi', 'pi.fk_pedido', '=', 'p.id');

        $query->where('p.fk_faculdade', $fk_faculdade)
              ->where('p.status', 2)
              ->where('url_video', '>', 0)
              ->where('pi.fk_assinatura', $fk_assinatura)
              ->where(DB::raw('MONTH(modulos_usuarios.criacao)'), $mes)
              ->where(DB::raw('YEAR(modulos_usuarios.criacao)'), $ano);

        $query->where(DB::raw('(SELECT COUNT(*) FROM pedidos AS p2 INNER JOIN pedidos_item AS pi2 ON p2.id = pi2.fk_pedido
        WHERE p2.fk_usuario = modulos_usuarios.fk_usuario AND pi2.fk_curso = cm.fk_curso AND p2.status = 2)'), '<=', '0');

        return $query->first();
    }

    public static function total_arrecadado($mes, $ano, $fk_faculdade, $fk_assinatura){
        $query = Pedido::select(DB::raw('SUM(pedidos.valor_liquido) AS total, COUNT(fk_usuario) AS total_assinantes'))
        ->join('assinatura_pagamento AS ap', 'ap.fk_pedido', '=', 'pedidos.id')
        ->join('pedidos_item AS pi', 'pi.fk_pedido', '=', 'pedidos.id')
        ->where('ap.status', 1)
        ->where('pi.fk_assinatura', $fk_assinatura)
        ->where('pedidos.fk_faculdade', $fk_faculdade)
        ->where(DB::raw('MONTH(ap.data_criacao)'), $mes)
        ->where(DB::raw('YEAR(ap.data_criacao)'), $ano);

        return $query->first();
    }

    public static function total_views_curso($pedidos, $mes, $ano, $fk_faculdade, $fk_assinatura){
        $query = ModuloUsuario::select(DB::raw('COUNT(DISTINCT CONCAT(modulos_usuarios.fk_modulo, modulos_usuarios.fk_usuario)) as views'),
            'p.fk_usuario', 'cm.fk_curso', 'modulos_usuarios.fk_modulo', 'p.fk_faculdade')
            ->join('cursos_modulos AS cm', 'cm.id', '=', 'modulos_usuarios.fk_modulo')
            ->join('pedidos AS p', 'p.fk_usuario', '=', 'modulos_usuarios.fk_usuario')
            ->join('pedidos_item AS pi', 'pi.fk_pedido', '=', 'p.id')
            ->join('cursos AS c', 'c.id', '=', 'cm.fk_curso');

        $query->where('p.fk_faculdade', $fk_faculdade)
              ->where('p.status', 2)
              ->where('url_video', '>', 0)
              ->where('pi.fk_assinatura', $fk_assinatura)
              ->where(DB::raw('MONTH(modulos_usuarios.criacao)'), $mes)
              ->where(DB::raw('YEAR(modulos_usuarios.criacao)'), $ano);

        $query->where(DB::raw('(SELECT COUNT(*) FROM pedidos AS p2 INNER JOIN pedidos_item AS pi2 ON p2.id = pi2.fk_pedido
        WHERE p2.fk_usuario = modulos_usuarios.fk_usuario AND pi2.fk_curso = cm.fk_curso AND p2.status = 2)'), '<=', '0');

        $query->groupBy(DB::raw('CONCAT(modulos_usuarios.fk_modulo)'));

        return $query->get();
    }

    public static function cursos_views_mes($pedidos, $mes, $ano, $fk_faculdade, $fk_assinatura){
        $query = ModuloUsuario::select(DB::raw('GROUP_CONCAT(DISTINCT c.id) AS ids'))
            ->join('cursos_modulos AS cm', 'cm.id', '=', 'modulos_usuarios.fk_modulo')
            ->join('pedidos AS p', 'p.fk_usuario', '=', 'modulos_usuarios.fk_usuario')
            ->join('pedidos_item AS pi', 'pi.fk_pedido', '=', 'p.id')
            ->join('cursos AS c', 'c.id', '=', 'cm.fk_curso');

        $query->where('p.fk_faculdade', $fk_faculdade)
              ->where('p.status', 2)
              ->where('url_video', '>', 0)
              ->where('pi.fk_assinatura', $fk_assinatura)
              ->where(DB::raw('MONTH(modulos_usuarios.criacao)'), $mes)
              ->where(DB::raw('YEAR(modulos_usuarios.criacao)'), $ano);

        $query->where(DB::raw('(SELECT COUNT(*) FROM pedidos AS p2 INNER JOIN pedidos_item AS pi2 ON p2.id = pi2.fk_pedido
        WHERE p2.fk_usuario = modulos_usuarios.fk_usuario AND pi2.fk_curso = cm.fk_curso AND p2.status = 2)'), '<=', '0');

        return $query->first();
    }

    public static function parceiros_por_pedidos($cursos){
        $query = Curso::select(DB::raw('GROUP_CONCAT(DISTINCT fk_professor) AS professores,
        GROUP_CONCAT(DISTINCT fk_professor_participante) AS professores_participantes,
        GROUP_CONCAT(DISTINCT fk_parceiro) AS parceiros, GROUP_CONCAT(DISTINCT fk_curador) AS curadores, GROUP_CONCAT(DISTINCT fk_produtora) AS produtoras,
        COUNT(DISTINCT fk_professor) + COUNT(DISTINCT fk_professor_participante) + COUNT(DISTINCT fk_parceiro) +
        COUNT(DISTINCT fk_curador) + COUNT(DISTINCT fk_produtora) AS total'))->whereIn('id', $cursos)->where('status', '>=', 0);

        return $query->first();
    }

    public static function parceiros_por_curso($fk_curso){
        $query = Curso::select(
            'professorprincipal_share',
            'professorparticipante_share',
            'curador_share',
            'produtora_share',
            DB::raw('(SELECT fk_usuario_id FROM professor AS p WHERE p.id = cursos.fk_professor) AS fk_usuario_professor,
            (SELECT fk_usuario_id FROM professor AS p WHERE p.id = cursos.fk_professor_participante) AS fk_usuario_professor_participante,
            (SELECT fk_usuario_id FROM curadores AS c WHERE c.id = cursos.fk_curador) AS fk_usuario_curador,
            (SELECT fk_usuario_id FROM produtora AS p WHERE p.id = cursos.fk_produtora) AS fk_usuario_produtora')
            )->where('id', $fk_curso);

        return $query->first();
    }

    public static function relatorio_repasses_assinaturas($data){
        $query = AssinaturaRepasse::select('f.fantasia', 'assinatura_repasse.total_assinantes',
         'assinatura_repasse.total_arrecadado','assinatura_repasse.total_parceiros', DB::raw('SUM(arp.total_views) AS total_views'),
        'assinatura_repasse.valor_view', 'arp.percentual_repasse', 'a.titulo AS plano',
        DB::raw('FORMAT(SUM((((arp.total_views * assinatura_repasse.valor_view) / 100) * arp.percentual_repasse)), 2) AS repasse_total'),
        'u.nome', 'arp.fk_usuario', 'arp.tipo_usuario');

        $query->join('assinatura_repasse_parceiro AS arp', 'assinatura_repasse.id', '=', 'arp.fk_assinatura_repasse');
        $query->join('faculdades AS f', 'assinatura_repasse.fk_faculdade', '=', 'f.id');
        $query->join('assinatura AS a', 'assinatura_repasse.fk_assinatura', '=', 'a.id');
        $query->join('usuarios AS u', 'arp.fk_usuario', '=', 'u.id');

        if (!empty($data['ies']) && $data['ies'] > 0){
            $query->where('assinatura_repasse.fk_faculdade', $data['ies']);
        }

        if (!empty($data['plano']) && $data['plano'] > 0){
            $query->where('assinatura_repasse.fk_assinatura', $data['plano']);
        }

        $query->where('mes', $data['mes']);
        $query->where('ano', $data['ano']);

        switch ($data['group_by']) {
            case 'parceiro':
                $query->groupBy(DB::raw('fk_usuario'));
            break;

            case 'tipo':
                $query->groupBy(DB::raw('arp.tipo_usuario'));
            break;

            case 'ies':
                $query->groupBy(DB::raw('f.id'));
            break;

            case 'plano':
                $query->groupBy(DB::raw('fk_assinatura'));
            break;

            default:
                $query->groupBy(DB::raw('fk_usuario, fk_assinatura'));
            break;
        }

        return $query;
    }

    public static function relatorio_arrecadacao_assinaturas($data){
        $query = AssinaturaRepasse::select('f.fantasia', 'assinatura_repasse.total_assinantes',
        'assinatura_repasse.total_arrecadado','assinatura_repasse.total_parceiros', 'assinatura_repasse.total_views',
       'assinatura_repasse.valor_view', 'a.titulo AS plano',
       'u.nome', 'arp.fk_usuario', 'arp.tipo_usuario');

        $query->join('assinatura_repasse_parceiro AS arp', 'assinatura_repasse.id', '=', 'arp.fk_assinatura_repasse');
        $query->join('faculdades AS f', 'assinatura_repasse.fk_faculdade', '=', 'f.id');
        $query->join('assinatura AS a', 'assinatura_repasse.fk_assinatura', '=', 'a.id');
        $query->join('usuarios AS u', 'arp.fk_usuario', '=', 'u.id');

        $query->where('mes', $data['mes']);
        $query->where('ano', $data['ano']);

        if (!empty($data['ies']) && $data['ies'] > 0){
            $query->where('assinatura_repasse.fk_faculdade', $data['ies']);
        }

        if (!empty($data['plano']) && $data['plano'] > 0){
            $query->where('assinatura_repasse.fk_assinatura', $data['plano']);
        }

        $query->groupBy(DB::raw('assinatura_repasse.fk_faculdade, fk_assinatura'));

        return $query;
    }

}
