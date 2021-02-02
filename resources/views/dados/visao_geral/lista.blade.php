@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="row">
			<div class="col-md-9"><h2 class="table">Visão Geral</h2></div>
		</div>
      
		<div class="panel-group accordion scrollable" id="accordion2">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#filtro">
							<i class="fa fa-search"></i> Filtros 
							<i class="fa fa-angle-double-down"></i>
						</a>
					</h4>
				</div>
				<div id="filtro" class="panel-collapse">
					<div class="panel-body">
						<form role="form" method="get" enctype="application/x-www-form-urlencoded">
							<div class="col-xs-6 col-md-3 col-lg-3">
								<div class="form-group">
									<label>Período:</label>
									<input type="text" name="data" id="data" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%" />
								</div>	
							</div>
							<div class="col-xs-6 col-md-3 col-lg-3 pull-right">
								<div class="form-group">
									<label></label>
									<button type="submit" class="btn btn-block btn-success btn-md">Filtrar</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		
		<br>
		
    	<div class="row">
    	
    		<div class="col-lg-3 col-xs-6">
				<div class="small-box bg-green" style="min-height: 128px;">
					<div class="inner">
						<h2>{{ 'R$ '.number_format($dados_visao_geral->faturamento_bruto, 2, ',', '.') }}</h2>
						<p>Faturamento Bruto</p>
					</div>
					<div class="icon">
						<i class="icon ion-md-cash"></i>
					</div>
				</div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
				<div class="small-box bg-green" style="min-height: 128px;">
					<div class="inner">
						<h2>{{ 'R$ '.number_format($dados_visao_geral->faturamento_liquido, 2, ',', '.') }}</h2>
						<p>Faturamento Líquido</p>
					</div>
					<div class="icon">
						<i class="icon ion-md-cash"></i>
					</div>
				</div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
				<div class="small-box bg-green" style="min-height: 128px;">
					<div class="inner">
						<h2>{{ 'R$ '.number_format($dados_visao_geral->ticket_medio, 2, ',', '.') }}</h2>
						<p>Ticket Médio</p>
					</div>
					<div class="icon">
						<i class="icon ion-md-cash"></i>
					</div>
				</div>
            </div>
    	
			<div class="col-lg-3 col-xs-6">
				<div class="small-box bg-green" style="min-height: 128px;">
					<div class="inner">
						<h2>{{ $dados_visao_geral->vendas_unitarias }}</h2>
						<p>Vendas Unitárias</p>
					</div>
					<div class="icon">
						<i class="icon ion-md-basket"></i>
					</div>
				</div>
            </div>
            
    	</div>
    	
    	<div class="row">
    	
    		<div class="col-lg-3 col-xs-6">
				<div class="small-box bg-yellow" style="min-height: 128px;">
					<div class="inner">
						<h2>{{ $dados_visao_geral->usuarios_unicos }}</h2>
						<p>Usuários Únicos</p>
					</div>
					<div class="icon">
						<i class="icon ion-md-contacts"></i>
					</div>
				</div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
				<div class="small-box bg-yellow" style="min-height: 128px;">
					<div class="inner">
						<h2>{{ $dados_visao_geral->assinantes_ativos }}</h2>
						<p>Assinantes Ativos</p>
					</div>
					<div class="icon">
						<i class="icon ion-md-contacts"></i>
					</div>
				</div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
				<div class="small-box bg-yellow" style="min-height: 128px;">
					<div class="inner">
						<h2>{{ $dados_visao_geral->ga_pageviews }}</h2>
						<p>Total de Acessos</p>
					</div>
					<div class="icon">
						<i class="icon ion-md-stats"></i>
					</div>
				</div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
				<div class="small-box bg-yellow" style="min-height: 128px;">
					<div class="inner">
						<h2>{{ ($dados_visao_geral->ga_users && $dados_visao_geral->vendas_unitarias) ? number_format(($dados_visao_geral->ga_users / $dados_visao_geral->vendas_unitarias) * 100, 2, '.', '') : '0' }} %</h2>
						<p>Taxa de Conversão</p>
					</div>
					<div class="icon">
						<i class="icon ion-md-pie"></i>
					</div>
				</div>
            </div>
            
		</div>
		
		<div class="row">
    	
    		<div class="col-lg-3 col-xs-6">
				<div class="small-box bg-aqua" style="min-height: 128px;">
					<div class="inner">
						<h2>{{ $dados_visao_geral->cursos_online }}</h2>
						<p>Cursos Online Disponíveis</p>
					</div>
					<div class="icon">
						<i class="icon ion-md-school"></i>
					</div>
				</div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
				<div class="small-box bg-aqua" style="min-height: 128px;">
					<div class="inner">
						<h2>{{ $dados_visao_geral->cursos_presenciais }}</h2>
						<p>Cursos Presenciais Disponíveis</p>
					</div>
					<div class="icon">
						<i class="icon ion-md-school"></i>
					</div>
				</div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
				<div class="small-box bg-aqua" style="min-height: 128px;">
					<div class="inner">
						<h2>{{ $dados_visao_geral->cursos_remotos }}</h2>
						<p>Cursos Remotos Disponíveis</p>
					</div>
					<div class="icon">
						<i class="icon ion-md-school"></i>
					</div>
				</div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
				<div class="small-box bg-aqua" style="min-height: 128px;">
					<div class="inner">
						<h2>{{ $dados_visao_geral->eventos }}</h2>
						<p>Eventos Disponíveis</p>
					</div>
					<div class="icon">
						<i class="icon ion-md-calendar"></i>
					</div>
				</div>
            </div>
            
		</div>
		
		<div class="row">
    	
    		<div class="col-lg-3 col-xs-6">
				<div class="small-box bg-yellow" style="min-height: 128px;">
					<div class="inner">
						<h2>{{ $dados_visao_geral->ies_cadastradas }}</h2>
						<p>IES Cadastradas</p>
					</div>
					<div class="icon">
						<i class="icon ion-md-school"></i>
					</div>
				</div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
				<div class="small-box bg-yellow" style="min-height: 128px;">
					<div class="inner">
						<h2>{{ $dados_visao_geral->professores }}</h2>
						<p>Professores Parceiros</p>
					</div>
					<div class="icon">
						<i class="icon ion-md-school"></i>
					</div>
				</div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
				<div class="small-box bg-yellow" style="min-height: 128px;">
					<div class="inner">
						<h2>{{ $dados_visao_geral->produtoras }}</h2>
						<p>Produtoras Parceiras</p>
					</div>
					<div class="icon">
						<i class="icon ion-md-videocam"></i>
					</div>
				</div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
				<div class="small-box bg-yellow" style="min-height: 128px;">
					<div class="inner">
						<h2>{{ $dados_visao_geral->curadores }}</h2>
						<p>Curadores de Conteúdo</p>
					</div>
					<div class="icon">
						<i class="icon ion-md-filing"></i>
					</div>
				</div>
            </div>
            
       </div>
    	
	</div>
@endsection

@push('js')
	<script type="text/javascript">
        $(function() {
        
			$('#data').val('{{ isset($data[0]) ? \Carbon\Carbon::parse($data[0])->format('d/m/Y') : \Carbon\Carbon::today()->subDay(30)->format('d/m/Y') }} - {{ isset($data[0]) ? \Carbon\Carbon::parse($data[1])->format('d/m/Y') : \Carbon\Carbon::today()->format('d/m/Y') }}');
                
            $('#data').daterangepicker({
                "locale": {
                    "format": "DD/MM/YYYY",
                    "separator": " - ",
                    "applyLabel": "Aplicar",
                    "cancelLabel": "Cancelar",
                    "fromLabel": "De",
                    "toLabel": "Até",
                    "customRangeLabel": "Custom",
                    "weekLabel": "W",
                    "daysOfWeek": [
                        "Dom",
                        "Seg",
                        "Ter",
                        "Qua",
                        "Qui",
                        "Sex",
                        "Sab"
                    ],
                    "monthNames": [
                        "Janeiro",
                        "Fevereiro",
                        "Março",
                        "Abril",
                        "Maio",
                        "Junho",
                        "Julho",
                        "Agosto",
                        "Setembro",
                        "Outubro",
                        "Novembro",
                        "Dezembro"
                    ],
                    "firstDay": 1
                },
                ranges: {
                   'Hoje': [moment(), moment()],
                   'Ontem': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                   'Últimos 7 dias': [moment().subtract(6, 'days'), moment()],
                   'Últimos 30 dias': [moment().subtract(30, 'days'), moment()],
                   'Este mês': [moment().startOf('month'), moment().endOf('month')],
                   'Último mês': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });
        
        });

        $('#dropdown-menu-export-to li a').click(function (e) {
		    e.preventDefault();
		    var $valor = $(this).text();
		    $('#export-to-type').val($valor);
		    $('#form-export-to').submit();
		});
        
        </script>
@endpush	
