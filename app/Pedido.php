<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use App\PedidoItemSplit;

class Pedido extends Model
{
    use Notifiable;
    
    protected $table = 'pedidos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'pid',
        'fk_faculdade',
        'id_wirecard',
        'fk_usuario',
        'valor_bruto',
        'valor_desconto',
        'valor_imposto',
        'valor_liquido',
        'status',
        'fk_criador_id',
        'fk_atualizador_id',
        'criacao',
        'metodo_pagamento',
        'link_boleto',
        'atualizacao',
        'fk_cupom',
        'codigo_cupom',
        'tipo_cupom_desconto',
        'valor_cupom',
        'data_compra_externa'
    ];

    public $timestamps = false;
    public $rules = [
        'fk_usuario' => 'required',
        'valor_bruto' => 'required',
        'valor_desconto' => 'required',
        'valor_imposto' => 'required',
        'status' => 'required'
    ];
    public $messages = [
        'fk_usuario' => 'Usuário',
        'data_criacao' => 'Data Criação',
        'valor_bruto' => 'Valor Bruto',
        'valor_desconto' => 'Valor de Desconto',
        'valor_imposto' => 'valor de Imposto',
        'valor_liquido' => 'Valor Liquido',
        'status' => 'Status'
    ];

    /**
     * Retorna lista com Pedidos e outros dados
     *
     * @return mixed
     */
    public static function lista($id = null){
        $pedidos = Pedido::select(
            'pedidos.*',
            'usuarios.nome as usuario',
            'usuarios.email as email',
            'pedidos_status.titulo as status_titulo',
            'pedidos_status.cor as status_cor',
            'alunos.cpf',
            'alunos.identidade as rg',
            'alunos.telefone_1 as telefone',
            'pagamento.tipo as forma_pagamento'
        )->distinct()

            ->join('usuarios', 'pedidos.fk_usuario', '=', 'usuarios.id')
            ->leftjoin('pagamento', 'pagamento.fk_pedido', '=', 'pedidos.id')
            ->join('alunos', 'pedidos.fk_usuario', '=', 'alunos.fk_usuario_id')
            ->join('pedidos_status', 'pedidos.status', '=', 'pedidos_status.id');

        if ($id) {
            $pedidos->where('usuarios.id', $id);
        }

        $pedidos->orderBy('pedidos.id', 'DESC');

        return $pedidos->with('assinaturas')->with('items.curso')->with('items.evento')->with('items.trilha')->with('items.assinatura')->get();
    }

    public function assinaturas() {
        return $this->hasMany('\App\UsuarioAssinatura', 'fk_pedido', 'id');
    }

    public function items(){
        return $this->hasMany('\App\PedidoItem', 'fk_pedido', 'id');
    }

    public function pedido_status(){
        return $this->hasOne('\App\PedidoStatus', 'id', 'status');
    }

    public function usuario(){
        return $this->hasOne('\App\Usuario', 'id', 'fk_usuario');
    }

    public static function acesso_restrito_vendas($data){

        $query = PedidoItem::select(
                DB::raw(" DATE_FORMAT(pedidos.criacao,'%d/%m/%Y') as data "),
                DB::raw(" cursos.titulo as curso ")
            )
            ->join('pedidos', 'pedidos_item.fk_pedido', '=', 'pedidos.id')
            ->join('cursos', 'pedidos_item.fk_curso', '=', 'cursos.id')
            ->join('pedidos_item_split',function($join){
                $join->on('pedidos_item_split.fk_pedido','=','pedidos.id');
                $join->on('pedidos_item_split.fk_curso','=','pedidos_item.fk_curso');
            })
            ->join('pagamento', 'pedidos.id', '=', 'pagamento.fk_pedido');

        $query->where('pedidos.criacao','>=','2019-12-20');
        $query->whereRaw(DB::raw(" DATE_FORMAT(pedidos.criacao,'%Y') = '".$data['ano']."' "));
        $query->whereRaw(DB::raw(" DATE_FORMAT(pedidos.criacao,'%c') = '".$data['mes']."' "));

        if(isset($data['ies']) && !empty($data['ies'])){
            $query->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id');
            $query->where('cursos_faculdades.fk_faculdade','=',$data['ies']);
            $query->addSelect(DB::raw(" sum(pedidos_item_split.valor_split_faculdade) as valor "));
            $query->addSelect(DB::raw(" sum(pedidos_item_split.impostos_taxas_split_faculdade)  as impostos "));
        }

        if(isset($data['produtora_id']) && !empty($data['produtora_id'])){
            $query->join('produtora', 'produtora.id', '=', 'cursos.fk_produtora');
            $query->where('produtora.id','=',$data['produtora_id']);
            $query->addSelect(DB::raw(" sum(pedidos_item_split.valor_split_produtora) as valor "));
        }

        if(isset($data['professor_id']) && !empty($data['professor_id'])){
            $query->join('professor', 'professor.id', '=', 'cursos.fk_professor');
            $query->where('professor.id','=',$data['professor_id']);
            $query->where('pedidos_item_split.valor_split_professor','>', 0);
            $query->addSelect(DB::raw(" sum(pedidos_item_split.valor_split_professor) as valor "));
        }

        if(isset($data['curador_id']) && !empty($data['curador_id'])){
            $query->join('curadores', 'curadores.id', '=', 'cursos.fk_curador');
            $query->where('curadores.id','=',$data['curador_id']);
            $query->addSelect(DB::raw(" sum(pedidos_item_split.valor_split_curador) as valor "));
        }

        if(isset($data['parceiro_id']) && !empty($data['parceiro_id'])){
            $query->join('parceiro', 'parceiro.id', '=', 'cursos.fk_parceiro');
            $query->where('parceiro.id','=',$data['parceiro_id']);
            $query->addSelect(DB::raw(" sum(pedidos_item_split.valor_split_parceiro) as valor "));
        }

        $query->groupBy(DB::raw(" DATE_FORMAT(pedidos.criacao,'%d/%m/%Y'), cursos.titulo "));

        return $query;

    }

    public static function acesso_restrito_fatura($data){

        $query = PedidoItem::select(
                DB::raw(" cursos.titulo as curso "),
                DB::raw(" DATE_FORMAT(cursos.data_criacao,'%Y-%m-%d %H:%i') as publicacao "),
                DB::raw(" count(pedidos_item.id) vendas ")
            )
            ->join('pedidos', 'pedidos_item.fk_pedido', '=', 'pedidos.id')
            ->join('cursos', 'pedidos_item.fk_curso', '=', 'cursos.id')
            ->join('pedidos_item_split',function($join){
                $join->on('pedidos_item_split.fk_pedido','=','pedidos.id');
                $join->on('pedidos_item_split.fk_curso','=','pedidos_item.fk_curso');
            })
            ->join('pagamento', 'pedidos.id', '=', 'pagamento.fk_pedido');

            $query->where('pedidos.criacao','>=','2019-12-20');
            $query->whereRaw(DB::raw(" DATE_FORMAT(pedidos.criacao,'%Y') = '".$data['ano']."' "));
            $query->whereRaw(DB::raw(" DATE_FORMAT(pedidos.criacao,'%c') = '".$data['mes']."' "));

            if(isset($data['ies']) && !empty($data['ies'])){
                $query->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id');
                $query->where('cursos_faculdades.fk_faculdade','=',$data['ies']);
                $query->addSelect(
                    DB::raw(" sum(pedidos_item_split.impostos_taxas_split_faculdade) as impostos "),
                    DB::raw(" (sum(pedidos_item_split.valor_split_faculdade) + sum(pedidos_item_split.impostos_taxas_split_faculdade))  as valor "),
                    DB::raw(" sum(pedidos_item_split.valor_split_faculdade) as total_receber ")
                );
            }

            if(isset($data['produtora_id']) && !empty($data['produtora_id'])){
                $query->join('produtora', 'produtora.id', '=', 'cursos.fk_produtora');
                $query->where('produtora.id','=',$data['produtora_id']);
                $query->addSelect(
                    DB::raw(" sum(pedidos_item_split.impostos_taxas_split_produtora) as impostos "),
                    DB::raw(" (sum(pedidos_item_split.valor_split_produtora) + sum(pedidos_item_split.impostos_taxas_split_produtora))  as valor "),
                    DB::raw(" sum(pedidos_item_split.valor_split_produtora) as total_receber ")
                );
            }

            if(isset($data['professor_id']) && !empty($data['professor_id'])){
                $query->join('professor', 'professor.id', '=', 'cursos.fk_professor');
                $query->where('professor.id','=',$data['professor_id']);
                $query->where('pedidos_item_split.valor_split_professor','>', 0);
                $query->addSelect(
                    DB::raw(" sum(pedidos_item_split.impostos_taxas_split_professor) as impostos "),
                    DB::raw(" (sum(pedidos_item_split.valor_split_professor) + sum(pedidos_item_split.impostos_taxas_split_professor))  as valor "),
                    DB::raw(" sum(pedidos_item_split.valor_split_professor) as total_receber ")
                );
            }

            if(isset($data['curador_id']) && !empty($data['curador_id'])){
                $query->join('curadores', 'curadores.id', '=', 'cursos.fk_curador');
                $query->where('curadores.id','=',$data['curador_id']);
                $query->addSelect(
                    DB::raw(" sum(pedidos_item_split.impostos_taxas_split_curador) as impostos "),
                    DB::raw(" (sum(pedidos_item_split.valor_split_curador) + sum(pedidos_item_split.impostos_taxas_split_curador))  as valor "),
                    DB::raw(" sum(pedidos_item_split.valor_split_curador) as total_receber ")
                );
            }

            if(isset($data['parceiro_id']) && !empty($data['parceiro_id'])){
                $query->join('parceiro', 'parceiro.id', '=', 'cursos.fk_parceiro');
                $query->where('parceiro.id','=',$data['parceiro_id']);
                $query->addSelect(
                    DB::raw(" sum(pedidos_item_split.impostos_taxas_split_parceiro) as impostos "),
                    DB::raw(" (sum(pedidos_item_split.valor_split_parceiro) + sum(pedidos_item_split.impostos_taxas_split_parceiro))  as valor "),
                    DB::raw(" sum(pedidos_item_split.valor_split_parceiro) as total_receber ")
                );
            }

            $query->groupBy(DB::raw(" cursos.id, cursos.titulo, cursos.data_criacao "));

            return $query;

    }

    public function faculdade(){
        return $this->hasOne('\App\Faculdade', 'id', 'fk_faculdade');
    }

    public static function relatorio_financeiro($data) {

        $query = PedidoItem::select(
            'alunos.*',

            'nfe.nfse_id',
            'professor.id as professor_id',
            DB::raw("concat(professor.nome,' ',professor.sobrenome) as professor_nome"),
            'pedidos_item_split.porcentagem_split_professor as professor_share',
            DB::raw("format(((pedidos.valor_liquido / pagamento.parcelas) * pedidos_item_split.porcentagem_split_professor) / 100,2,'pt_BR')  as professor_share_valor"),

            'produtora.fantasia as produtora_nome',
            'pedidos_item_split.porcentagem_split_produtora as produtora_share',
            DB::raw("format(((pedidos.valor_liquido / pagamento.parcelas) * pedidos_item_split.porcentagem_split_produtora) / 100,2,'pt_BR')  as produtora_share_valor"),

            'curadores.nome_fantasia as curador_nome',
            'pedidos_item_split.porcentagem_split_curador as curador_share',
            DB::raw("format(((pedidos.valor_liquido / pagamento.parcelas) * pedidos_item_split.porcentagem_split_curador) / 100,2,'pt_BR')  as curador_share_valor"),

            'parceiro.fantasia as parceiro_nome',
            'pedidos_item_split.porcentagem_split_parceiro as parceiro_share',
            DB::raw("format(((pedidos.valor_liquido / pagamento.parcelas) * pedidos_item_split.porcentagem_split_parceiro) / 100,2,'pt_BR')  as parceiro_share_valor"),

            'endereco.*',
            'cidades.descricao_cidade',
            'estados.descricao_estado',

            'faculdades.id as faculdade_id',
            'faculdades.razao_social as faculdade_nome',

            DB::raw("case when pedidos_item.fk_curso is not null then cursos.id when pedidos_item.fk_evento is not null then eventos.id when pedidos_item.fk_trilha is not null then trilha.id when pedidos_item.fk_assinatura is not null then assinatura.id else '---' end as pedido_item_id"),
            DB::raw("case when pedidos_item.fk_curso is not null then cursos.titulo when pedidos_item.fk_evento is not null then eventos.titulo when pedidos_item.fk_trilha is not null then trilha.titulo when pedidos_item.fk_assinatura is not null then assinatura.titulo else '---' end as pedido_item_nome"),
            DB::raw("case when pedidos_item.fk_curso is not null then cursos_tipo.titulo when pedidos_item.fk_evento is not null then 'Evento' when pedidos_item.fk_trilha is not null then 'Trilha' when pedidos_item.fk_assinatura is not null then 'Assinatura' else '---' end as pedido_item_tipo"),
            'cursos.formato as curso_formato',

            DB::raw("format(cursos_valor.valor,2,'pt_BR') as curso_valor_item"),

            'pedidos.pid as pedido_pid',
            'pedidos.criacao as pedido_criacao',
            DB::raw("case when pedidos.metodo_pagamento = 'gratis' and pedidos.fk_cupom is null then 'Gratis' else concat('R$ ', format(pedidos.valor_bruto - pedidos.valor_desconto,2,'pt_BR')) end as pedido_valor_bruto"),
            DB::raw("format(pedidos.valor_desconto,2,'pt_BR') as pedido_valor_desconto"),
            DB::raw("format(pedidos.valor_imposto,2,'pt_BR') as pedido_valor_imposto"),
            DB::raw("format(pedidos.valor_liquido,2,'pt_BR') as pedido_valor_liquido"),
            DB::raw("format(pedidos.valor_liquido - ifnull((((pedidos.valor_liquido / pagamento.parcelas) * pedidos_item_split.porcentagem_split_professor) / 100),0) - ifnull((((pedidos.valor_liquido / pagamento.parcelas) * pedidos_item_split.porcentagem_split_curador) / 100),0) - ifnull((((pedidos.valor_liquido / pagamento.parcelas) * pedidos_item_split.porcentagem_split_produtora) / 100),0) - ifnull((((pedidos.valor_liquido / pagamento.parcelas) * pedidos_item_split.porcentagem_split_parceiro) / 100),0),2,'pt_BR')  as pedido_valor_liquido_educaz"),
            DB::raw("format((pedidos.valor_liquido / pagamento.parcelas),2,'pt_BR') as pedido_valor_liquido_parcelado"),
            'pedidos_status.titulo as pedido_status',

            /* Totais/Impostos */
            DB::raw("format((pedidos_total.valor_total * pedidos_total.porcentagem_iss) / 100,2,'pt_BR') as pedidos_total_valor_iss"),
            DB::raw("format((pedidos_total.valor_total * pedidos_total.porcentagem_pis_cofins) / 100,2,'pt_BR') as pedidos_total_valor_pis_confins"),
            DB::raw("format((pedidos_total.valor_total * pedidos_total.porcentagem_irpj_csll) / 100,2,'pt_BR') as pedidos_total_valor_irpj_csll"),
            DB::raw("format((pedidos_total.valor_total * pedidos_total.percentual_juros) / 100,2,'pt_BR') as pedidos_total_valor_taxa_cartao"),
            'pedidos_total.percentual_juros as pedidos_total_porcentagem_taxa_cartao',
            DB::raw("format(pedidos_total.valor_taxa_boleto,2,'pt_BR') as pedidos_total_valor_taxa_boleto"),
            DB::raw("format(pedidos_total.valor_taxa_processamento,2,'pt_BR') as pedidos_total_valor_taxa_processamento"),

            DB::raw("case when pedidos.metodo_pagamento = 'boleto' then 'Boleto Bancário' when pedidos.metodo_pagamento = 'debito_itau' then 'Débito' when pedidos.metodo_pagamento = 'cartao' then 'Cartão de Crédito' else '---' end as pagamento_tipo"),
            'pagamento.data_criacao as pagamento_data_pagamento',
            'pagamento.taxa as pagamento_taxa',
            'pagamento.parcelas as pagamento_parcelas',
            'cupom.id as cupom_id',
            'cupom.titulo as cupom_titulo',
            DB::raw("case when cupom.tipo_cupom_desconto = 1 then concat(cupom.valor, '%') when cupom.tipo_cupom_desconto = 2 then concat('R$ ', cupom.valor) else '---' end as cupom_valor")
        )
        ->join('pedidos', 'pedidos_item.fk_pedido', '=', 'pedidos.id')
        ->join('pedidos_status', 'pedidos.status', '=', 'pedidos_status.id')
        ->join('pedidos_total', 'pedidos_total.fk_pedido', '=', 'pedidos.id')
        ->join('usuarios', 'pedidos.fk_usuario', '=', 'usuarios.id')
        ->join('alunos', 'usuarios.id', '=', 'alunos.fk_usuario_id')
        ->join('endereco', 'alunos.fk_endereco_id', '=', 'endereco.id')
        ->join('cidades', 'endereco.fk_cidade_id', '=', 'cidades.id')
        ->join('estados', 'cidades.fk_estado_id', '=', 'estados.id')
        ->leftJoin('pagamento', 'pedidos.id', '=', 'pagamento.fk_pedido')
        ->leftJoin('pedidos_item_split',function($join){
            $join->on('pedidos_item_split.fk_pedido','=','pedidos.id');
            $join->on('pedidos_item_split.fk_curso','=','pedidos_item.fk_curso');
        })
        ->leftJoin('nfe', 'nfe.fk_pedido', '=', 'pedidos.id')
        ->leftJoin('cursos', 'pedidos_item.fk_curso', '=', 'cursos.id')
        ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
        ->leftJoin('cursos_tipo', 'cursos.fk_cursos_tipo', '=', 'cursos_tipo.id')
        ->leftJoin('eventos', 'pedidos_item.fk_evento', '=', 'eventos.id')
        ->leftJoin('trilha', 'pedidos_item.fk_trilha', '=', 'trilha.id')
        ->leftJoin('assinatura', 'pedidos_item.fk_assinatura', '=', 'assinatura.id')
        ->leftJoin('cursos_faculdades', 'cursos.id', '=', 'cursos_faculdades.fk_curso')
        ->leftJoin('faculdades', 'pedidos.fk_faculdade', '=', 'faculdades.id')
        ->leftJoin('produtora', 'cursos.fk_produtora', '=', 'produtora.id')
        ->leftJoin('curadores', 'cursos.fk_curador', '=', 'curadores.id')
        ->leftJoin('professor', 'cursos.fk_professor', '=', 'professor.id')
        ->leftJoin('parceiro', 'cursos.fk_parceiro', '=', 'parceiro.id')
        ->leftJoin('cupom', 'pedidos.fk_cupom', '=', 'cupom.id');

        if(isset($data['pedido_pid']) && !empty($data['pedido_pid'])){
            $query->where('pedidos.pid','like','%'.$data['pedido_pid'].'%');
        }

        if(isset($data['pedidos_status']) && !empty($data['pedidos_status'])){
            $query->where('pedidos.status','=',$data['pedidos_status']);
        }

        if(isset($data['ies']) && !empty($data['ies'])){
            $query->where('cursos_faculdades.fk_faculdade','=',$data['ies']);
        }

        if(isset($data['nome_item']) && !empty($data['nome_item'])){
            $query->where(function($q) use($data){
                $q->where('cursos.titulo','like','%'.$data['nome_item'].'%');
                $q->orWhere('eventos.titulo','like','%'.$data['nome_item'].'%');
                $q->orWhere('trilha.titulo','like','%'.$data['nome_item'].'%');
                $q->orWhere('assinatura.titulo','like','%'.$data['nome_item'].'%');
            });
        }

        if(isset($data['tipo_item']) && !empty($data['tipo_item'])){

            if($data['tipo_item'] == 'EVENTO'){
                $query->whereNotNull('pedidos_item.fk_evento');
            }else if($data['tipo_item'] == 'TRILHA'){
                $query->whereNotNull('pedidos_item.fk_trilha');
            }else if($data['tipo_item'] == 'ASSINATURA'){
                $query->whereNotNull('pedidos_item.fk_assinatura');
            }else{
                $query->where('cursos.fk_cursos_tipo','=',$data['tipo_item']);
            }

        }

        if(isset($data['nome_professor']) && !empty($data['nome_professor'])){

            $query->where(function($q) use($data){
                $q->where('professor.nome','like','%'.$data['nome_professor'].'%');
                $q->orWhere('professor.sobrenome','like','%'.$data['nome_professor'].'%');
            });

        }

        if(isset($data['nome_produtora']) && !empty($data['nome_produtora'])){
            $query->where('produtora.fantasia','like','%'.$data['nome_produtora'].'%');
        }

        if(isset($data['produtora_id']) && !empty($data['produtora_id'])){
            $query->where('produtora.id','=',$data['produtora_id']);
        }

        if(isset($data['professor_id']) && !empty($data['professor_id'])){
            $query->where('professor.id','=',$data['professor_id']);
        }

        if(isset($data['curador_id']) && !empty($data['curador_id'])){
            $query->where('curadores.id','=',$data['curador_id']);
        }

        if(isset($data['nome_curador']) && !empty($data['nome_curador'])){
            $query->where('curadores.nome_fantasia','like','%'.$data['nome_curador'].'%');
        }

        if(isset($data['data_compra']) && !empty($data['data_compra'])){
            $query->whereBetween('pedidos.criacao',$data['data_compra']);
        }

        if(isset($data['aluno']) && !empty($data['aluno'])){
            $query->where('usuarios.nome','like','%'.$data['aluno'].'%');
        }

        $query->orderByRaw( $data['orderby'].' '.$data['sort'] );

        return $query;

    }

    public static function relatorio_parceiro($data) {
        $query = PedidoItem::select(
            DB::raw("concat(professor.id, ' - ', professor.nome,' ',professor.sobrenome) as professor_nome"),
            'pedidos_item_split.porcentagem_split_professor as professor_share',
            'pedidos_item_split.valor_split_professor as professor_share_valor',

            DB::raw("concat(produtora.id, ' - ', produtora.fantasia) as produtora_nome"),
            'pedidos_item_split.porcentagem_split_produtora as produtora_share',
            'pedidos_item_split.valor_split_produtora as produtora_share_valor',

            'curadores.nome_fantasia as curador_nome',
            'pedidos_item_split.porcentagem_split_curador as curador_share',
            'pedidos_item_split.valor_split_curador as curador_share_valor',

            'parceiro.fantasia as parceiro_nome',
            'pedidos_item_split.porcentagem_split_parceiro as parceiro_share',
            'pedidos_item_split.valor_split_parceiro as parceiro_share_valor',

            'faculdades.razao_social as faculdade_nome',
            'pedidos_item_split.split_faculdade_manual',
            'pedidos_item_split.porcentagem_split_faculdade as faculdade_share',
            'pedidos_item_split.valor_split_faculdade as faculdade_share_valor',

            DB::raw("case when pedidos_item.fk_curso is not null then cursos.titulo when pedidos_item.fk_evento is not null then eventos.titulo when pedidos_item.fk_trilha is not null then trilha.titulo when pedidos_item.fk_assinatura is not null then assinatura.titulo else '---' end as pedido_item_nome"),
            DB::raw("case when pedidos_item.fk_curso is not null then cursos_tipo.titulo when pedidos_item.fk_evento is not null then 'Evento' when pedidos_item.fk_trilha is not null then 'Trilha' when pedidos_item.fk_assinatura is not null then 'Assinatura' else '---' end as pedido_item_tipo"),
            'cursos.formato as curso_formato',

            'cursos_valor.valor as curso_valor_item',

            'pedidos.pid as pedido_pid',
            'pedidos.criacao as pedido_criacao',
            DB::raw("format(pedidos.valor_bruto,2,'pt_BR') as pedido_valor_bruto"),
            DB::raw("format(pedidos.valor_desconto,2,'pt_BR') as pedido_valor_desconto"),
            DB::raw("format(pedidos.valor_imposto,2,'pt_BR') as pedido_valor_imposto"),
            'pedidos.valor_liquido as pedido_valor_liquido',

            DB::raw("case when pagamento.tipo = 'boleto' then 'Boleto Bancário' when pagamento.tipo = 'cartao' then 'Cartão de Crédito' else '---' end as pagamento_tipo")
        )
        ->join('pedidos', 'pedidos_item.fk_pedido', '=', 'pedidos.id')
        ->join('pedidos_status', 'pedidos.status', '=', 'pedidos_status.id')
        ->join('pedidos_total', 'pedidos_total.fk_pedido', '=', 'pedidos.id')
        ->join('usuarios', 'pedidos.fk_usuario', '=', 'usuarios.id')
        ->leftJoin('pagamento', 'pedidos.id', '=', 'pagamento.fk_pedido')
        ->leftJoin('pedidos_item_split',function($join){
            $join->on('pedidos_item_split.fk_pedido','=','pedidos.id');
            $join->on('pedidos_item_split.fk_curso','=','pedidos_item.fk_curso');
        })
        ->leftJoin('cursos', 'pedidos_item.fk_curso', '=', 'cursos.id')
        ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
        ->leftJoin('cursos_tipo', 'cursos.fk_cursos_tipo', '=', 'cursos_tipo.id')
        ->leftJoin('eventos', 'pedidos_item.fk_evento', '=', 'eventos.id')
        ->leftJoin('trilha', 'pedidos_item.fk_trilha', '=', 'trilha.id')
        ->leftJoin('assinatura', 'pedidos_item.fk_assinatura', '=', 'assinatura.id')
        ->leftJoin('cursos_faculdades', 'cursos.id', '=', 'cursos_faculdades.fk_curso')
        ->leftJoin('faculdades', 'cursos_faculdades.fk_faculdade', '=', 'faculdades.id')
        ->leftJoin('produtora', 'cursos.fk_produtora', '=', 'produtora.id')
        ->leftJoin('curadores', 'cursos.fk_curador', '=', 'curadores.id')
        ->leftJoin('professor', 'cursos.fk_professor', '=', 'professor.id')
        ->leftJoin('parceiro', 'cursos.fk_parceiro', '=', 'parceiro.id');

        if(isset($data['pedido_pid']) && !empty($data['pedido_pid'])){
            $query->where('pedidos.pid','like','%'.$data['pedido_pid'].'%');
        }

        $query->where('pedidos.status', '=', 2); # TRAS APENAS PEDIDOS PAGOS

        if(isset($data['ies']) && !empty($data['ies'])){
            $query->where('cursos_faculdades.fk_faculdade','=',$data['ies']);
        }

        if(isset($data['nome_item']) && !empty($data['nome_item'])){
            $query->where(function($q) use($data){
                $q->where('cursos.titulo','like','%'.$data['nome_item'].'%');
                $q->orWhere('eventos.titulo','like','%'.$data['nome_item'].'%');
                $q->orWhere('trilha.titulo','like','%'.$data['nome_item'].'%');
                $q->orWhere('assinatura.titulo','like','%'.$data['nome_item'].'%');
            });
        }

        if(isset($data['tipo_item']) && !empty($data['tipo_item'])){
            if($data['tipo_item'] == 'EVENTO'){
                $query->whereNotNull('pedidos_item.fk_evento');
            }else if($data['tipo_item'] == 'TRILHA'){
                $query->whereNotNull('pedidos_item.fk_trilha');
            }else if($data['tipo_item'] == 'ASSINATURA'){
                $query->whereNotNull('pedidos_item.fk_assinatura');
            }else{
                $query->where('cursos.fk_cursos_tipo','=',$data['tipo_item']);
            }
        }

        if(isset($data['nome_professor']) && !empty($data['nome_professor'])){
            $query->where(function($q) use($data){
                $q->where('professor.nome','like','%'.$data['nome_professor'].'%');
                $q->orWhere('professor.sobrenome','like','%'.$data['nome_professor'].'%');
            });
        }

        if(isset($data['nome_produtora']) && !empty($data['nome_produtora'])){
            $query->where('produtora.fantasia','like','%'.$data['nome_produtora'].'%');
        }

        if(isset($data['produtora_id']) && !empty($data['produtora_id'])){
            $query->where('produtora.id','=',$data['produtora_id']);
        }

        if(isset($data['professor_id']) && !empty($data['professor_id'])){
            $query->where('professor.id','=',$data['professor_id']);
        }

        if(isset($data['curador_id']) && !empty($data['curador_id'])){
            $query->where('curadores.id','=',$data['curador_id']);
        }

        if(isset($data['nome_curador']) && !empty($data['nome_curador'])){
            $query->where('curadores.nome_fantasia','like','%'.$data['nome_curador'].'%');
        }

        if(isset($data['data_compra']) && !empty($data['data_compra'])){
            $query->whereBetween('pedidos.criacao',$data['data_compra']);
        }

        if(isset($data['aluno']) && !empty($data['aluno'])){
            $query->where('usuarios.nome','like','%'.$data['aluno'].'%');
        }

        $query->orderByRaw( $data['orderby'].' '.$data['sort'] );

        return $query;
    }

    # RELATORIO COMPARATIVO DE FATURAMENTO 
    public static function relatorio_comparativo_faturamento($data){
        $query = Pedido::select(DB::raw("COUNT(pedidos.id) AS unidades"), DB::raw("GROUP_CONCAT(pedidos.id) AS ids_pedidos"))
        ->join("pedidos_item_split AS pis", "pedidos.id", "=", "pis.fk_pedido")
        ->join("cursos AS c", "pis.fk_curso", "=", "c.id");

        if (!empty($data['fk_professor'])){
            $query->addSelect(DB::raw("(SUM(pis.valor_split_professor) + SUM(pis.impostos_taxas_split_professor)) AS faturamento"));
            $query->addSelect(DB::raw("SUM(pis.impostos_taxas_split_professor) AS impostos_taxas"));
            $query->addSelect(DB::raw("SUM(pis.valor_split_professor) AS liquido"));

            $query->where("c.fk_professor", "=", $data['fk_professor']);
            $query->where("pis.valor_split_professor", ">", 0);
        }

        if (!empty($data['fk_curador'])){
            $query->addSelect(DB::raw("(SUM(pis.valor_split_curador) + SUM(pis.impostos_taxas_split_curador)) AS faturamento"));
            $query->addSelect(DB::raw("SUM(pis.impostos_taxas_split_curador) AS impostos_taxas"));
            $query->addSelect(DB::raw("SUM(pis.valor_split_curador) AS liquido"));

            $query->where("c.fk_curador", "=", $data['fk_curador']);
            $query->where("pis.valor_split_curador", ">", 0);
        }

        if (!empty($data['fk_produtora'])){
            $query->addSelect(DB::raw("(SUM(pis.valor_split_produtora) + SUM(pis.impostos_taxas_split_produtora)) AS faturamento"));
            $query->addSelect(DB::raw("SUM(pis.impostos_taxas_split_produtora) AS impostos_taxas"));
            $query->addSelect(DB::raw("SUM(pis.valor_split_produtora) AS liquido"));

            $query->where("c.fk_produtora", "=", $data['fk_produtora']);
            $query->where("pis.valor_split_produtora", ">", 0);
        }

        $query->where("pedidos.status", "=", 2); # PAGO
        $query->where("pedidos.metodo_pagamento", "!=", "gratis");

        if (!empty($data['fk_faculdade'])){
            $query->where("pedidos.fk_faculdade", "=", $data['fk_faculdade']);

            if (empty($data['fk_professor']) && empty($data['fk_curador']) && empty($data['fk_produtora'])){
                if ($data['fk_faculdade'] == 7){
                    $query->addSelect('pedidos.fk_faculdade');
                } else {
                    $query->addSelect(DB::raw("(SUM(pis.valor_split_faculdade) + SUM(pis.impostos_taxas_split_faculdade)) AS faturamento"));
                    $query->addSelect(DB::raw("SUM(pis.impostos_taxas_split_faculdade) AS impostos_taxas"));
                    $query->addSelect(DB::raw("SUM(pis.valor_split_faculdade) AS liquido"));

                    $query->where("pis.valor_split_faculdade", ">", 0);
                }
            }
        }
        
        $query->whereBetween('pedidos.criacao', $data['periodo']);

        switch ($data['agrupar_por']) {                
            case 'mes':
                $query->addSelect(DB::raw("MONTH(pedidos.criacao) AS mes"));
                $query->groupBy(DB::raw("YEAR(pedidos.criacao), MONTH(pedidos.criacao)"));
                $query->orderBy(DB::raw("YEAR(pedidos.criacao), MONTH(pedidos.criacao)"));
            break;

            case 'ano':
                $query->addSelect(DB::raw("YEAR(pedidos.criacao) AS ano"));
                $query->groupBy(DB::raw("YEAR(pedidos.criacao)"));
                $query->orderBy(DB::raw("YEAR(pedidos.criacao)"));
            break;

            default:
                $query->addSelect(DB::raw("WEEK(pedidos.criacao) AS semana"));
                $query->groupBy(DB::raw("YEARWEEK(pedidos.criacao, 2)"));
                $query->orderBy(DB::raw("YEARWEEK(pedidos.criacao, 2)"));
            break;
        }
        
        return $query;
    }

    # RELATORIO FATURAMENTO POR PROFESSOR
    public static function relatorio_faturamento_por_professor($data){
        $query = Pedido::select(DB::raw("COUNT(pedidos.id) AS unidades"), DB::raw("GROUP_CONCAT(pedidos.id) AS ids_pedidos"),
        DB::raw("CONCAT(p.nome, ' ', p.sobrenome) AS professor_nome"))
        ->join("pedidos_item_split AS pis", "pedidos.id", "=", "pis.fk_pedido")
        ->join("cursos AS c", "pis.fk_curso", "=", "c.id")
        ->join("professor AS p", "p.id", "=", "c.fk_professor");

        $query->where("pedidos.status", "=", 2); # PAGO
        $query->where("pedidos.metodo_pagamento", "!=", "gratis");

        if (!empty($data['fk_professor'])){
            $query->where("c.fk_professor", "=", $data['fk_professor']);
        }

        if (!empty($data['fk_faculdade'])){
            $query->where("pedidos.fk_faculdade", "=", $data['fk_faculdade']);

            if (empty($data['fk_professor'])){
                if ($data['fk_faculdade'] == 7){
                    $query->addSelect('pedidos.fk_faculdade');
                } 
            } else {
                $query->addSelect(DB::raw("(SUM(pis.valor_split_faculdade) + SUM(pis.impostos_taxas_split_faculdade)) AS faturamento"));
                $query->addSelect(DB::raw("SUM(pis.impostos_taxas_split_faculdade) AS impostos_taxas"));
                $query->addSelect(DB::raw("SUM(pis.valor_split_faculdade) AS liquido"));

                $query->where("pis.valor_split_faculdade", ">", 0);
            }
        }
        
        $query->whereBetween('pedidos.criacao', $data['periodo']);

        switch ($data['agrupar_por']) {              
            case 'mes':
                $query->addSelect(DB::raw("MONTH(pedidos.criacao) AS mes"));
                $query->groupBy(DB::raw("YEAR(pedidos.criacao), MONTH(pedidos.criacao)"));
                $query->orderBy(DB::raw("YEAR(pedidos.criacao), MONTH(pedidos.criacao)"));
            break;

            case 'ano':
                $query->addSelect(DB::raw("YEAR(pedidos.criacao) AS ano"));
                $query->groupBy(DB::raw("YEAR(pedidos.criacao)"));
                $query->orderBy(DB::raw("YEAR(pedidos.criacao)"));
            break;

            case 'professor':
                $query->addSelect(DB::raw("WEEK(pedidos.criacao) AS semana"));
                $query->groupBy(DB::raw("YEARWEEK(pedidos.criacao, 2), p.id"));
                $query->orderBy(DB::raw("YEARWEEK(pedidos.criacao, 2)"));
            break;

            default:
            $query->addSelect(DB::raw("WEEK(pedidos.criacao) AS semana"));
                $query->groupBy(DB::raw("c.fk_professor"));
                $query->orderBy(DB::raw("YEAR(pedidos.criacao)"));
            break;
        }

        return $query;
    }

    # RELATORIO FATURAMENTO POR CATEGORIA
    public static function relatorio_faturamento_por_categoria($data){
        $query = Pedido::select(DB::raw("COUNT(pedidos.id) AS unidades"), DB::raw("GROUP_CONCAT(pedidos.id) AS ids_pedidos"),
        DB::raw("cc.titulo AS categoria"))
        ->join("pedidos_item_split AS pis", "pedidos.id", "=", "pis.fk_pedido")
        ->join("cursos AS c", "pis.fk_curso", "=", "c.id")
        ->join("cursos_categoria_curso AS ccc", "c.id", "=", "ccc.fk_curso")
        ->join("cursos_categoria AS cc", "ccc.fk_curso_categoria", "=", "cc.id");

        $query->where("pedidos.status", "=", 2); # PAGO
        $query->where("pedidos.metodo_pagamento", "!=", "gratis");

        if (!empty($data['fk_categoria'])){
            $query->where("cc.id", "=", $data['fk_categoria']);
        }

        if (!empty($data['fk_faculdade'])){
            $query->where("pedidos.fk_faculdade", "=", $data['fk_faculdade']);
            
            if ($data['fk_faculdade'] == 7){
                    $query->addSelect('pedidos.fk_faculdade'); 
            } else {
                $query->addSelect(DB::raw("(SUM(pis.valor_split_faculdade) + SUM(pis.impostos_taxas_split_faculdade)) AS faturamento"));
                $query->addSelect(DB::raw("SUM(pis.impostos_taxas_split_faculdade) AS impostos_taxas"));
                $query->addSelect(DB::raw("SUM(pis.valor_split_faculdade) AS liquido"));

                $query->where("pis.valor_split_faculdade", ">", 0);
            }
        }
        
        $query->whereBetween('pedidos.criacao', $data['periodo']);

        switch ($data['agrupar_por']) {              
            case 'mes':
                $query->addSelect(DB::raw("MONTH(pedidos.criacao) AS mes"));
                $query->groupBy(DB::raw("YEAR(pedidos.criacao), MONTH(pedidos.criacao)"));
                $query->orderBy(DB::raw("YEAR(pedidos.criacao), MONTH(pedidos.criacao)"));
            break;

            case 'ano':
                $query->addSelect(DB::raw("YEAR(pedidos.criacao) AS ano"));
                $query->groupBy(DB::raw("YEAR(pedidos.criacao)"));
                $query->orderBy(DB::raw("YEAR(pedidos.criacao)"));
            break;

            case 'semana':
                $query->addSelect(DB::raw("WEEK(pedidos.criacao) AS semana"));
                $query->groupBy(DB::raw("YEARWEEK(pedidos.criacao, 2), cc.id"));
                $query->orderBy(DB::raw("YEARWEEK(pedidos.criacao, 2)"));
            break;

            default:
                $query->groupBy("cc.id");
                $query->orderBy("cc.titulo");
            break;
        }

        return $query;
    }

    # RELATORIO ASSINATURAS REALIZADAS
    public static function relatorio_assinaturas_realizadas($data){
        $query = Pedido::select(DB::raw("COUNT(pedidos.id) AS total"))
        ->join("pedidos_item AS pi", "pedidos.id", "=", "pi.fk_pedido");

        $query->where("pedidos.status", "=", 2); # PAGO
        $query->where("pi.fk_assinatura", ">", 0);

        if (!empty($data['fk_faculdade'])){
            $query->where("pedidos.fk_faculdade", "=", $data['fk_faculdade']);
        }
        
        $query->whereBetween('pedidos.criacao', $data['periodo']);

        switch ($data['agrupar_por']) {              
            case 'mes':
                $query->addSelect(DB::raw("MONTH(pedidos.criacao) AS mes"));
                $query->addSelect(DB::raw("YEAR(pedidos.criacao) AS ano"));
                $query->groupBy(DB::raw("YEAR(pedidos.criacao), MONTH(pedidos.criacao)"));
                $query->orderBy(DB::raw("YEAR(pedidos.criacao), MONTH(pedidos.criacao)"));
            break;

            case 'ano':
                $query->addSelect(DB::raw("YEAR(pedidos.criacao) AS ano"));
                $query->groupBy(DB::raw("YEAR(pedidos.criacao)"));
                $query->orderBy(DB::raw("YEAR(pedidos.criacao)"));
            break;

            case 'semana':
                $query->addSelect(DB::raw("WEEK(pedidos.criacao) AS semana"));
                $query->addSelect(DB::raw("YEAR(pedidos.criacao) AS ano"));
                $query->groupBy(DB::raw("YEARWEEK(pedidos.criacao, 2)"));
                $query->orderBy(DB::raw("YEARWEEK(pedidos.criacao, 2)"));
            break;

            default:
                $query->addSelect(DB::raw("DAY(pedidos.criacao) AS dia"));
                $query->addSelect(DB::raw("MONTH(pedidos.criacao) AS mes"));
                $query->addSelect(DB::raw("YEAR(pedidos.criacao) AS ano"));
                $query->groupBy(DB::raw("YEAR(pedidos.criacao), MONTH(pedidos.criacao), DAY(pedidos.criacao)"));
                $query->orderBy(DB::raw("YEAR(pedidos.criacao), MONTH(pedidos.criacao), DAY(pedidos.criacao)"));
            break;
        }

        return $query;
    }

    # RELATORIO PEDIDOS NAO APROVADOS
    public static function relatorio_pagamento_reprovado($data){
        $query = Pedido::select(DB::raw("COUNT(pedidos.id) AS unidades"), DB::raw("GROUP_CONCAT(pedidos.id) AS ids_pedidos"))
        ->leftJoin("pedidos_item_split AS pis", "pedidos.id", "=", "pis.fk_pedido");

        $query->where("pedidos.status", 4);

        if (!empty($data['fk_faculdade'])){
            $query->where("pedidos.fk_faculdade", "=", $data['fk_faculdade']);
        }
        
        $query->whereBetween('pedidos.criacao', $data['periodo']);
        
        if (!empty($data['fk_faculdade'])){
            $query->where("pedidos.fk_faculdade", "=", $data['fk_faculdade']);
            
            if ($data['fk_faculdade'] == 7){
                    $query->addSelect('pedidos.fk_faculdade'); 
            } else {
                $query->addSelect(DB::raw("SUM(pis.valor_split_faculdade) AS liquido"));

                $query->where("pis.valor_split_faculdade", ">", 0);
            }
        }

        switch ($data['agrupar_por']) {              
            case 'mes':
                $query->addSelect(DB::raw("MONTH(pedidos.criacao) AS mes"));
                $query->addSelect(DB::raw("YEAR(pedidos.criacao) AS ano"));
                $query->groupBy(DB::raw("YEAR(pedidos.criacao), MONTH(pedidos.criacao)"));
                $query->orderBy(DB::raw("YEAR(pedidos.criacao), MONTH(pedidos.criacao)"));
            break;

            case 'ano':
                $query->addSelect(DB::raw("YEAR(pedidos.criacao) AS ano"));
                $query->groupBy(DB::raw("YEAR(pedidos.criacao)"));
                $query->orderBy(DB::raw("YEAR(pedidos.criacao)"));
            break;

            case 'semana':
                $query->addSelect(DB::raw("WEEK(pedidos.criacao) AS semana"));
                $query->addSelect(DB::raw("YEAR(pedidos.criacao) AS ano"));
                $query->groupBy(DB::raw("YEARWEEK(pedidos.criacao, 2)"));
                $query->orderBy(DB::raw("YEARWEEK(pedidos.criacao, 2)"));
            break;

            default:
                $query->addSelect(DB::raw("DAY(pedidos.criacao) AS dia"));
                $query->addSelect(DB::raw("MONTH(pedidos.criacao) AS mes"));
                $query->addSelect(DB::raw("YEAR(pedidos.criacao) AS ano"));
                $query->groupBy(DB::raw("YEAR(pedidos.criacao), MONTH(pedidos.criacao), DAY(pedidos.criacao)"));
                $query->orderBy(DB::raw("YEAR(pedidos.criacao), MONTH(pedidos.criacao), DAY(pedidos.criacao)"));
            break;
        }

        return $query;
    }

    public static function getTotalsEducaz($data, $filters = array()){
        if (!empty($filters['fk_categoria'])){
            $query = Pedido::select(DB::raw("SUM(pi.valor_bruto) AS valor_bruto, SUM(pi.valor_liquido) AS valor_liquido, 
            (SUM(pi.valor_liquido) + SUM(pi.valor_imposto)) AS faturamento, SUM(pi.valor_imposto) AS impostos_taxas"));

            $query->join("pedidos_item AS pi", "pedidos.id", "=", "pi.fk_pedido");
            $query->join('cursos AS c', 'pi.fk_curso', '=', 'c.id');
            $query->join('cursos_categoria_curso AS ccc', 'c.id', '=', 'ccc.fk_curso');
            $query->join('cursos_categoria AS cc', 'ccc.fk_curso_categoria', '=', 'cc.id');

            $query->whereIn('pedidos.id', $data);
            $query->where('pi.valor_liquido', ">", 0);
            $query->where('cc.id', $filters['fk_categoria']);

            $query->get();
        } else {
            $query = Pedido::select(DB::raw("SUM(valor_bruto) AS valor_bruto, SUM(valor_liquido) AS valor_liquido, 
            (SUM(valor_liquido) + SUM(valor_imposto)) AS faturamento, SUM(valor_imposto) AS impostos_taxas"));
            $query->whereIn('id', $data);
        }

        return $query->first();
    }

    public static function getTotalRepassesParceiros($data, $filters = array()){
        $query = PedidoItemSplit::select(DB::raw("SUM(valor_split_professor) + SUM(valor_split_curador) + SUM(valor_split_produtora)  + SUM(valor_split_faculdade) AS total"));
        
        if (!empty($filters['fk_categoria'])){
            $query->join('cursos AS c', 'pedidos_item_split.fk_curso', '=', 'c.id');
            $query->join('cursos_categoria_curso AS ccc', 'c.id', '=', 'ccc.fk_curso');
            $query->join('cursos_categoria AS cc', 'ccc.fk_curso_categoria', '=', 'cc.id');

            $query->where('cc.id', $filters['fk_categoria']);
        }

        $query->whereIn('fk_pedido', $data);

        return $query->first();
    }
}
