@extends('layouts.app')
@section('styles')
    <style>
        .graficos{
            border: 1px solid transparent;
            border-color: #ddd;
            border-radius: 10px;
            margin-bottom: 5px;
            padding:2px;
        }
    </style>
@endsection

@section('content')
	<div class="box padding20">
		<div class="row">
			<div class="col-md-9"><h2 class="table">{{$modulo['moduloDetalhes']->modulo}}</h2></div>
			<div class="col-md-3" style="margin-top: 20px;">
			<div class="btn-group pull-right">
				<button class="btn btn-default" type="button" id="btn_imprimir"> Imprimir
					<i class="fa fa-print"></i>
				</button>
			</div>
		</div>
		</div>
		
		<div class="panel-group accordion scrollable" id="div_filtros">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#div_filtros" href="#filtro">
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
							<div class="col-xs-6 col-md-3 col-lg-3">
								<div class="form-group">
									<label>Agrupamento:</label>
									{{ Form::select('group', $group, (request()->get('group') ? request()->get('group') : 'mes' ), ['class' => 'form-control']) }}
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
    	<div class="row" style="margin: 0" id="div_graficos">
    		<div class="col-md-6 col-xs-12" style="padding:8px;">
    			<div class="graficos" id="alunos_cadastrados"></div>
    		</div> 		
    		<div class="col-md-6 col-xs-12" style="padding:8px;">
    			<div class="graficos" id="assinantes"></div>
    		</div>
    		<div class="col-md-6 col-xs-12" style="padding:8px;">
    			<div class="graficos" id="assinantes_ativos"></div>
    		</div>
    		<div class="col-md-6 col-xs-12" style="padding:8px;">
    			<div class="graficos" id="assinantes_acessos"></div>
    		</div>
    		<div class="col-md-12 col-xs-12" style="padding:8px;">
    			<div class="graficos" id="assinantes_faixa_etaria"></div>
    		</div>
    		<div class="col-md-12 col-xs-12" style="padding:8px;">
    			<div class="graficos" id="assinantes_cidades"></div>
    		</div>
    		<div class="col-md-6 col-xs-12" style="padding:8px;">
    			<div class="graficos" id="assinaturas"></div>
    		</div>
    		<div class="col-md-6 col-xs-12" style="padding:8px;">
    			<div class="graficos" id="assinaturas_canceladas"></div>
    		</div>
    	</div>
    	
	</div>
@endsection
@push('js')
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	<script type="text/javascript">

    	function printData(){
    	   var divToPrint=document.getElementById("div_graficos");
    	   newWin= window.open("");
    	   newWin.document.write(divToPrint.outerHTML);
    	   newWin.print();
    	   newWin.close();
    	}

    	$(function() {

    		$("#btn_imprimir").click(function () {
    			printData();
    		});
            
    		$('#data').val('{{ \Carbon\Carbon::parse($data[0])->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($data[1])->format('d/m/Y') }}');
                
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
                    "daysOfWeek": ["Dom","Seg","Ter","Qua","Qui","Sex","Sab"],
                    "monthNames": ["Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"],
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


    	google.charts.load('current', {packages: ['corechart', 'bar']});

    	// Assinaturas
    	google.charts.setOnLoadCallback(drawAssinaturas);
    	function drawAssinaturas() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Mês');
			data.addColumn('number', 'Quantidade');

			data.addRows([
				@foreach($dados_assinaturas as $dado)
					['{{ $dado->usuarios_assinaturas_criacao }}', {{ $dado->total }}],
				@endforeach
			]);

			var options = {
				legend: 'none',
				title: 'Assinaturas',
				titleTextStyle: {
					fontSize: 15,
				},
				vAxis: {
					title: 'Quantidade'
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('assinaturas'));
			chart.draw(data, options);
		}

    	// Assinaturas Canceladas
    	google.charts.setOnLoadCallback(drawAssinaturasCanceladas);
    	function drawAssinaturasCanceladas() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Mês');
			data.addColumn('number', 'Quantidade');

			data.addRows([
				@foreach($dados_assinaturas_canceladas as $dado)
					['{{ $dado->usuarios_assinaturas_criacao }}', {{ $dado->total }}],
				@endforeach
			]);

			var options = {
				legend: 'none',
				title: 'Assinaturas Canceladas',
				titleTextStyle: {
					fontSize: 15,
				},
				vAxis: {
					title: 'Quantidade'
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('assinaturas_canceladas'));
			chart.draw(data, options);
		}

    	// Assinantes Ativos
    	google.charts.setOnLoadCallback(drawAssinantesAtivos);
    	function drawAssinantesAtivos() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Mês');
			data.addColumn('number', 'Quantidade');

			data.addRows([
				@foreach($dados_assinantes_ativos as $dado)
					['{{ $dado->usuarios_assinaturas_criacao }}', {{ $dado->total }}],
				@endforeach
			]);

			var options = {
				legend: 'none',
				title: 'Assinantes Ativos',
				titleTextStyle: {
					fontSize: 15,
				},
				vAxis: {
					title: 'Quantidade'
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('assinantes_ativos'));
			chart.draw(data, options);
		}

    	// Assinantes Acessos
    	google.charts.setOnLoadCallback(drawAssinantesAcessos);
    	function drawAssinantesAcessos() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Mês');
			data.addColumn('number', 'Quantidade');

			data.addRows([
				@foreach($dados_assinantes_acessos as $dado)
					['{{ $dado->data_acesso }}', {{ $dado->total }}],
				@endforeach
			]);

			var options = {
				legend: 'none',
				title: 'Assinantes X Acessos',
				titleTextStyle: {
					fontSize: 15,
				},
				vAxis: {
					title: 'Quantidade'
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('assinantes_acessos'));
			chart.draw(data, options);
		}

    	// Assinantes Faixa Etária
    	google.charts.setOnLoadCallback(drawAssinantesFaixaEtaria);
    	function drawAssinantesFaixaEtaria() {

    		var data = google.visualization.arrayToDataTable([
				['Data', '18-24', '25-34', '35-44', '45-54', '55-64', '65+'],
				@foreach($dados_assinantes_faixa_etaria as $dado)
					[
						'{{ $dado->criacao }}',
						{{ $dado->faixa_24 }},
						{{ $dado->faixa_34 }},
						{{ $dado->faixa_44 }},
						{{ $dado->faixa_54 }},
						{{ $dado->faixa_64 }},
						{{ $dado->faixa_65 }}
					],
				@endforeach
			]);

			var options = {
				height: 300,
				title: 'Assinantes X Faixa Etária',
				titleTextStyle: {
					fontSize: 15,
				},
				vAxis: {
					title: 'Quantidade'
				}
			};

	        var chart = new google.visualization.ColumnChart(document.getElementById('assinantes_faixa_etaria'));
	        chart.draw(data, options);
		}

    	// Assinantes Cidades
    	google.charts.setOnLoadCallback(drawAssinantesCidades);
    	function drawAssinantesCidades() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Cidade');
			data.addColumn('number', 'Quantidade');

			data.addRows([
				@foreach($dados_assinantes_cidades as $dado)
					['{{ $dado->cidade }}', {{ $dado->total }}],
				@endforeach
			]);

			var options = {
				legend: 'none',
				title: 'Assinantes X Cidades (Top 20)',
				titleTextStyle: {
					fontSize: 15,
				},
				vAxis: {
					title: 'Quantidade'
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('assinantes_cidades'));
			chart.draw(data, options);
		}

    	// Assinantes
    	google.charts.setOnLoadCallback(drawAssinantes);
    	function drawAssinantes() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Mês');
			data.addColumn('number', 'Quantidade');

			data.addRows([
				@foreach($dados_assinantes as $dado)
					['{{ $dado->usuarios_assinaturas_criacao }}', {{ $dado->total }}],
				@endforeach
			]);

			var options = {
				legend: 'none',
				title: 'Assinantes',
				titleTextStyle: {
					fontSize: 15,
				},
				vAxis: {
					title: 'Quantidade'
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('assinantes'));
			chart.draw(data, options);
		}
    	
    	// Alunos Cadastrados
    	google.charts.setOnLoadCallback(drawAlunosCadastrados);
    	function drawAlunosCadastrados() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Mês');
			data.addColumn('number', 'Quantidade');

			data.addRows([
				@foreach($dados_usuarios as $dado)
					['{{ $dado->usuario_criacao }}', {{ $dado->total }}],
				@endforeach
			]);

			var options = {
				legend: 'none',
				title: 'Alunos Cadastrados',
				titleTextStyle: {
					fontSize: 15,
				},
				vAxis: {
					title: 'Quantidade'
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('alunos_cadastrados'));
			chart.draw(data, options);
		}
    	
	</script>
@endpush

