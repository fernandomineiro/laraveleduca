<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Analytics\Period;
use App\PedidoItem;

class GraficosRelatorios extends Model{
    
    public static function visao_geral($data) {
        
        $whereUsuariosUnicos = isset($data['data']) && !empty($data['data']) ? " where data_criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        $whereAssinaturasAtivos = isset($data['data']) && !empty($data['data']) ? " and data_criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        $whereCursos = isset($data['data']) && !empty($data['data']) ? " and data_criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        $whereEventos = isset($data['data']) && !empty($data['data']) ? " and data_criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        $whereFaculdades = isset($data['data']) && !empty($data['data']) ? " and criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        $whereProfessor = isset($data['data']) && !empty($data['data']) ? " and data_criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        $whereProdutoras = isset($data['data']) && !empty($data['data']) ? " and criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        $whereCuradores = isset($data['data']) && !empty($data['data']) ? " and data_criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        
        $analyticsStartDate = is_null($data['data']) ? \Carbon\Carbon::today()->subDay(30) : \Carbon\Carbon::parse($data['data'][0]);
        $analyticsEndDate = is_null($data['data']) ? \Carbon\Carbon::today() : \Carbon\Carbon::parse($data['data'][1]);
        
        $dadosAnalytics = \Analytics::performQuery(
            Period::create($analyticsStartDate,$analyticsEndDate),
            'ga:sessions',
            [
                'metrics' => 'ga:sessions, ga:pageviews, ga:users'
            ]
        );
        
        $query = Pedido::select(
            DB::raw("
                sum(pedidos.valor_bruto) as faturamento_bruto,
            	sum(pedidos.valor_bruto - pedidos.valor_imposto - pedidos.valor_imposto) as faturamento_liquido,
            	count(pedidos.id) as vendas_unitarias,
            	sum(pedidos.valor_bruto) / count(pedidos.id) as ticket_medio,
                (select count(id) from usuarios ".$whereUsuariosUnicos.") as usuarios_unicos,
                (select count(DISTINCT(fk_usuario)) from usuarios_assinaturas where status in (1,2) ".$whereAssinaturasAtivos.") as assinantes_ativos,
                (select count(id) from cursos where fk_cursos_tipo = 1 and status = 5 ".$whereCursos.") as cursos_online,
                (select count(id) from cursos where fk_cursos_tipo = 2 and status = 5 ".$whereCursos.") as cursos_presenciais,
                (select count(id) from cursos where fk_cursos_tipo = 4 and status = 5 ".$whereCursos.") as cursos_remotos,
                (select count(id) from eventos where status = 1 ".$whereEventos.") as eventos,
                (select count(id) from faculdades where status = 1 ".$whereFaculdades.") as ies_cadastradas,
                (select count(id) from professor where status = 1 ".$whereProfessor.") as professores,
                (select count(id) from produtora where status = 1 ".$whereProdutoras.") as produtoras,
                (select count(id) from curadores where status = 1 ".$whereCuradores.") as curadores,
                (".$dadosAnalytics->totalsForAllResults['ga:pageviews'].") as ga_pageviews,
                (".$dadosAnalytics->totalsForAllResults['ga:users'].") as ga_users
            ")
        );
        
        if(isset($data['data']) && !empty($data['data'])){
            $query->whereBetween('pedidos.criacao',$data['data']);
        }
        
        return $query;
    }
    
    public static function graficos_pedidos($data) {
        
        $whereAssinaturasAtivos = isset($data['data']) && !empty($data['data']) ? " and data_criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        $whereCursos = isset($data['data']) && !empty($data['data']) ? " and data_criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        $whereEventos = isset($data['data']) && !empty($data['data']) ? " and data_criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        $whereFaculdades = isset($data['data']) && !empty($data['data']) ? " and criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        $whereProfessor = isset($data['data']) && !empty($data['data']) ? " and data_criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        $whereProdutoras = isset($data['data']) && !empty($data['data']) ? " and criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        $whereCuradores = isset($data['data']) && !empty($data['data']) ? " and data_criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        
        $query = Pedido::select(
            DB::raw("
                DATE_FORMAT(pedidos.criacao,'%m/%Y') as pedido_criacao,
                sum(pedidos.valor_bruto) as faturamento_bruto,
            	sum(pedidos.valor_bruto - pedidos.valor_imposto - pedidos.valor_imposto) as faturamento_liquido,
            	count(pedidos.id) as vendas_unitarias,
            	sum(pedidos.valor_bruto) / count(pedidos.id) as ticket_medio,
                (select count(DISTINCT(fk_usuario)) from usuarios_assinaturas where status in (1,2) ".$whereAssinaturasAtivos.") as assinantes_ativos,
                (select count(id) from cursos where fk_cursos_tipo = 1 and status = 5 ".$whereCursos.") as cursos_online,
                (select count(id) from cursos where fk_cursos_tipo = 2 and status = 5 ".$whereCursos.") as cursos_presenciais,
                (select count(id) from cursos where fk_cursos_tipo = 4 and status = 5 ".$whereCursos.") as cursos_remotos,
                (select count(id) from eventos where status = 1 ".$whereEventos.") as eventos,
                (select count(id) from faculdades where status = 1 ".$whereFaculdades.") as ies_cadastradas,
                (select count(id) from professor where status = 1 ".$whereProfessor.") as professores,
                (select count(id) from produtora where status = 1 ".$whereProdutoras.") as produtoras,
                (select count(id) from curadores where status = 1 ".$whereCuradores.") as curadores
            ")
        );
        
        $query->whereRaw(DB::raw(" pedidos.criacao is not null "));
        
        if(isset($data['data']) && !empty($data['data'])){
            $query->whereBetween('pedidos.criacao',$data['data']);
        }
        
        $query->orderBy('pedido_criacao');
        $query->groupBy('pedido_criacao');
        
        return $query;
    }

    public static function graficos_parceiros($data) {
        $query = PedidoItem::where(['pedidos.status' => 2]);

        $query->join('pedidos', 'pedidos_item.fk_pedido', '=', 'pedidos.id');
        $query->join('cursos', 'pedidos_item.fk_curso', '=', 'cursos.id');
        $query->join('pedidos_item_split',function($join){
            $join->on('pedidos_item_split.fk_pedido','=','pedidos.id');
            $join->on('pedidos_item_split.fk_curso','=','pedidos_item.fk_curso');
        });

        $query->join('pagamento', 'pedidos.id', '=', 'pagamento.fk_pedido');
        
        if(isset($data['fk_ies']) && !empty($data['fk_ies'])){
            $query->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id');
            $query->addSelect(DB::raw("pedidos_item_split.valor_split_faculdade as valor"));

            $query->where('cursos_faculdades.fk_faculdade','=',$data['fk_ies']);
        }

        if(!empty($data['tipo']) && $data['tipo'] == 'professor'){
            $query->join('professor', 'professor.id', '=', 'cursos.fk_professor');
            $query->addSelect(DB::raw("pedidos_item_split.valor_split_professor as valor "));

            $query->where('professor.id','=', $data['fk_professor']);
        }

        if(!empty($data['tipo']) && $data['tipo'] == 'produtora'){
            $query->join('produtora', 'produtora.id', '=', 'cursos.fk_produtora');
            $query->addSelect(DB::raw("pedidos_item_split.valor_split_produtora as valor "));

            $query->where('produtora.id','=', $data['fk_produtora']);
        }

        if(!empty($data['tipo']) && $data['tipo'] == 'curador'){
            $query->join('curadores', 'curadores.id', '=', 'cursos.fk_curador');
            $query->addSelect(DB::raw("pedidos_item_split.valor_split_curador as valor "));

            $query->where('curadores.id','=', $data['fk_curador']);
        }
        
        if(!empty($data['tipo']) && $data['tipo'] == 'parceiro'){
            $query->join('parceiro', 'parceiro.id', '=', 'cursos.fk_parceiro');
            $query->addSelect(DB::raw("pedidos_item_split.valor_split_parceiro as valor "));

            $query->where('parceiro.id','=', $data['fk_parceiro']);
        }
        
        if(isset($data['data']) && !empty($data['data'])){
            $query->whereBetween('pedidos.criacao',$data['data']);
        }

        return $query;
    }
    
    public static function graficos_parceiros_mais_vendidos($data) {
        $query = Pedido::select(
            DB::raw("
            	usuarios.nome as parceiro,
            	count(pedidos.id) as quantidade
            ")
            );
        
        $query->whereRaw(DB::raw(" pedidos.criacao is not null "));
        
        if(isset($data['data']) && !empty($data['data'])){
            $query->whereBetween('pedidos.criacao',$data['data']);
        }
        
        $query->join('usuarios','usuarios.id','=','pedidos.fk_usuario');
        $query->join('parceiro','parceiro.fk_usuario_id','=','usuarios.id');
        
        $query->orderBy('quantidade','desc');
        $query->groupBy('usuarios.id','usuarios.nome');
        
        $query->limit(10);
        
        return $query;
        
    }
    
    public static function graficos_cursos_mais_vendidos($data) {

        $query = Pedido::select(
            DB::raw("
            	cursos.titulo as curso,
            	count(pedidos.id) as quantidade
            ")
        );
        
        $query->whereRaw(DB::raw(" pedidos.criacao is not null "));
        
        if(isset($data['data']) && !empty($data['data'])){
            $query->whereBetween('pedidos.criacao',$data['data']);
        }
        
        $query->join('pedidos_item','pedidos_item.fk_pedido','=','pedidos.id');
        $query->join('cursos','pedidos_item.fk_curso','=','cursos.id');
        
        $query->orderBy('quantidade','desc');
        $query->groupBy('cursos.id','cursos.titulo');
        
        $query->limit(10);
        
        return $query;
        
    }
    
    public static function graficos_categorias_mais_vendidos($data) {
        
        $query = Pedido::select(
            DB::raw("
            	cursos_tipo.titulo as categoria,
            	count(pedidos.id) as quantidade
            ")
            );
        
        $query->whereRaw(DB::raw(" pedidos.criacao is not null "));
        
        if(isset($data['data']) && !empty($data['data'])){
            $query->whereBetween('pedidos.criacao',$data['data']);
        }
        
        $query->join('pedidos_item','pedidos_item.fk_pedido','=','pedidos.id');
        $query->join('cursos','pedidos_item.fk_curso','=','cursos.id');
        $query->join('cursos_tipo','cursos.fk_cursos_tipo','=','cursos_tipo.id');
        
        $query->orderBy('quantidade','desc');
        $query->groupBy('cursos_tipo.id','cursos_tipo.titulo');
        
        return $query;
        
    }
    
    public static function graficos_usuarios($data) {
        
        $query = Usuario::select(
            DB::raw("
                count(id) as total,
                DATE_FORMAT(usuarios.data_criacao,'%".$data['group']."/%Y') as usuario_criacao
            ")
        );
        
        $query->whereRaw(DB::raw(" usuarios.data_criacao is not null and status in (1,2) "));
        
        if(isset($data['data']) && !empty($data['data'])){
            $query->whereBetween('usuarios.data_criacao',$data['data']);
        }
        
        $query->orderBy('usuario_criacao');
        $query->groupBy('usuario_criacao');
        
        return $query;
        
    }
    
    public static function graficos_assinantes_ativos($data) {
        
        $query = UsuarioAssinatura::select(
            DB::raw("
                count(DISTINCT(fk_usuario)) as total,
                DATE_FORMAT(usuarios_assinaturas.data_criacao,'%".$data['group']."/%Y') as usuarios_assinaturas_criacao
            ")
        );
        
        $query->whereRaw(DB::raw(" usuarios_assinaturas.data_criacao is not null and status in (1,2) "));
        
        if(isset($data['data']) && !empty($data['data'])){
            $query->whereBetween('usuarios_assinaturas.data_criacao',$data['data']);
        }
        
        $query->orderBy('usuarios_assinaturas_criacao');
        $query->groupBy('usuarios_assinaturas_criacao');
        
        return $query;
        
    }
    
    public static function graficos_assinaturas_canceladas($data) {
        
        $whereAssinaturasAtivos = isset($data['data']) && !empty($data['data']) ? " and data_criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        
        $sql = "
            select
            	count(*) as total,
            	DATE_FORMAT(data_criacao,'%".$data['group']."/%Y') as usuarios_assinaturas_criacao
            from usuarios_assinaturas
            where
            	data_criacao is not null
                and status = 0
                ".$whereAssinaturasAtivos."
            group by usuarios_assinaturas_criacao
            order by usuarios_assinaturas_criacao
        ";
        
        return DB::select($sql);
        
    }
    
    public static function graficos_assinaturas($data) {
        
        $whereAssinaturasAtivos = isset($data['data']) && !empty($data['data']) ? " and data_criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        
        $sql = "
            select
            	count(*) as total,
            	DATE_FORMAT(data_criacao,'%".$data['group']."/%Y') as usuarios_assinaturas_criacao
            from usuarios_assinaturas
            where
            	data_criacao is not null
                ".$whereAssinaturasAtivos."
            group by usuarios_assinaturas_criacao
            order by usuarios_assinaturas_criacao
        ";
        
        return DB::select($sql);
        
    }
    
    public static function graficos_assinantes($data) {
        
        $whereAssinaturasAtivos = isset($data['data']) && !empty($data['data']) ? " and data_criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        
        $sql = "
            select
            	count(DISTINCT(fk_usuario)) as total,
            	DATE_FORMAT(data_criacao,'%".$data['group']."/%Y') as usuarios_assinaturas_criacao
            from usuarios_assinaturas
            where
            	data_criacao is not null
                ".$whereAssinaturasAtivos."
            group by usuarios_assinaturas_criacao
            order by usuarios_assinaturas_criacao
        ";
        
        return DB::select($sql);
        
    }
    
    public static function graficos_assinantes_faixa_etaria($data) {
        
        $whereAssinaturasAtivos = isset($data['data']) && !empty($data['data']) ? " and a.data_criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        
        $sql = "
            SELECT
            	DATE_FORMAT(a.data_criacao,'%".$data['group']."/%Y') as criacao,
                count(CASE WHEN TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) BETWEEN 18 AND 24 THEN 1 END) as faixa_24,
            	count(CASE WHEN TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) BETWEEN 25 AND 34 THEN 1 END) AS faixa_34,
            	count(CASE WHEN TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) BETWEEN 35 AND 44 THEN 1 END) AS faixa_44,
            	count(CASE WHEN TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) BETWEEN 45 AND 54 THEN 1 END) AS faixa_54,
            	count(CASE WHEN TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) BETWEEN 55 AND 64 THEN 1 END) AS faixa_64,
            	count(CASE WHEN TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) >= 65 THEN 1 END) AS faixa_65
            FROM alunos as a
            where 
                YEAR(a.data_nascimento) > 1900 
                and DAYNAME(a.data_criacao) is not null
                ".$whereAssinaturasAtivos."
            group by criacao
            order by criacao
        ";
        
        return DB::select($sql);
        
    }
    
    public static function graficos_assinantes_cidades($data) {
        
        $whereAssinaturasAtivos = isset($data['data']) && !empty($data['data']) ? " and a.data_criacao between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        
        $sql = "
            select
            	c.descricao_cidade as cidade,
            	count(c.id) as total
            from alunos a
            inner join endereco e on e.id = a.fk_endereco_id
            inner join cidades c on c.id = e.fk_cidade_id
            where 
                1 = 1
                ".$whereAssinaturasAtivos."
            group by cidade
            order by total desc
            limit 20
        ";
        
        return DB::select($sql);
        
    }
    
    public static function graficos_assinantes_acessos($data) {
        
        $whereAssinaturasAtivos = isset($data['data']) && !empty($data['data']) ? " and uac.data between '".$data['data'][0]."' and '".$data['data'][1]."' " : "";
        
        $sql = "
            select
            	count(DISTINCT(uac.id)) as total,
            	DATE_FORMAT(uac.data,'%".$data['group']."/%Y') as data_acesso
            from usuarios_assinaturas ua
            inner join usuarios u on u.id = ua.fk_usuario
            inner join usuarios_acessos uac on uac.usuario_id = u.id
            where
            	ua.data_criacao is not null
                and uac.origem = 'FRONT'
                ".$whereAssinaturasAtivos."
            group by data_acesso
            order by data_acesso
        ";
        
        return DB::select($sql);
        
    }
    
    public static function relatorio_audiencia_membership($data) {
        
        $query = UsuarioAssinatura::select(
            DB::raw("
                concat(alunos.nome,' ',alunos.sobre_nome) as aluno,
                faculdades.fantasia as ies,
                tipo_assinatura.titulo as plano,
                usuarios_acessos.data as ultimo_acesso,
                (
            		select 
            			count(id) 
            		from usuarios_acessos 
            		where 
            			usuario_id = usuarios_assinaturas.fk_usuario
            			and `data` >= CURRENT_DATE - INTERVAL 30 DAY
            	) as qnt_acessos_ultimo_30_dias
            ")
        );
        
        $query->join('alunos','alunos.fk_usuario_id','usuarios_assinaturas.fk_usuario');
        $query->join('pedidos_item','pedidos_item.fk_pedido','usuarios_assinaturas.fk_pedido');
        $query->join('assinatura','assinatura.id','usuarios_assinaturas.fk_assinatura');
        $query->join('tipo_assinatura','tipo_assinatura.id','assinatura.fk_tipo_assinatura');
        $query->join('faculdades','faculdades.id','assinatura.fk_faculdade');
        $query->leftJoin('usuarios_acessos', function($join){
            $join->on('usuarios_acessos.usuario_id', '=', 'alunos.fk_usuario_id');
            $join->on('usuarios_acessos.id', '=',DB::raw("
                    (select 
                		max(id) 
                	from usuarios_acessos uac2 
                	where 
                		uac2.usuario_id = alunos.fk_usuario_id)
                ")
            );
        });
        
        $query->where('assinatura.status','=',1);
        $query->where('alunos.status','=',1);
        
        if(isset($data['data']) && !empty($data['data'])){
            $query->whereBetween('usuarios_assinaturas.data_criacao',$data['data']);
        }
        
        if(isset($data['ies']) && !empty($data['ies'])){
            $query->where('faculdades.id','=',$data['ies']);
        }
        
        return $query;
        
        
    }
    
    public static function relatorio_status_assinatura($data) {
        
        $query = UsuarioAssinatura::select(
            DB::raw("
                concat(alunos.nome,' ',alunos.sobre_nome) as aluno,
                alunos.identidade as rg,
                faculdades.fantasia as ies,
                DATE_FORMAT(usuarios_assinaturas.data_criacao,'%d/%m/%Y') as adesao,
                tipo_assinatura.titulo as plano,
                format(assinatura.valor,2,'pt_BR') as valor
            ")
        );
        
        $query->join('alunos','alunos.fk_usuario_id','usuarios_assinaturas.fk_usuario');
        $query->join('pedidos_item','pedidos_item.fk_pedido','usuarios_assinaturas.fk_pedido');
        $query->join('assinatura','assinatura.id','usuarios_assinaturas.fk_assinatura');
        $query->join('tipo_assinatura','tipo_assinatura.id','assinatura.fk_tipo_assinatura');
        $query->join('faculdades','faculdades.id','assinatura.fk_faculdade');
            
        $query->where('assinatura.status','=',1);
        $query->where('alunos.status','=',1);
        
        if(isset($data['data']) && !empty($data['data'])){
            $query->whereBetween('usuarios_assinaturas.data_criacao',$data['data']);
        }
        
        if(isset($data['ies']) && !empty($data['ies'])){
            $query->where('faculdades.id','=',$data['ies']);
        }
        
        return $query;
            
            
    }
    
}
