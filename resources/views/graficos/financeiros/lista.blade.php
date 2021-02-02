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
									<label>IES:</label>
                                    {{ Form::select('ies', $faculdades, (request()->get('ies') ? request()->get('ies') : 0 ), ['class' => 'form-control']) }}
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
    			<div class="graficos" id="vendas_unitarias"></div>
    		</div>
    		<div class="col-md-6 col-xs-12" style="padding:8px;">
    			<div class="graficos" id="faturamento_bruto"></div>
    		</div>
    		<div class="col-md-6 col-xs-12" style="padding:8px;">
    			<div class="graficos" id="faturamento_liquido"></div>
    		</div>
    		<div class="col-md-6 col-xs-12" style="padding:8px;">
    			<div class="graficos" id="ticket_medio"></div>
    		</div>
    		<div class="col-md-6 col-xs-12" style="padding:8px;">
    			<div class="graficos" id="categorias_mais_vendidas"></div>
    		</div>
    		<div class="col-md-6 col-xs-12" style="padding:8px;">
    			<div class="graficos" id="cursos_mais_vendidos"></div>
    		</div>
    		<div class="col-md-6 col-xs-12" style="padding:8px;">
    			<div class="graficos" id="parceiros_mais_vendidos"></div>
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

    	// Parceiros Mais Vendidos
    	google.charts.setOnLoadCallback(drawParceirosMaisVendidos);
    	function drawParceirosMaisVendidos() {

    		var data = google.visualization.arrayToDataTable([
				['Curso', 'Quantidade'],
				@foreach($dados_parceiros_mais_vendidos as $dado)
					['{{ $dado->parceiro }}', {{ $dado->quantidade }}],
				@endforeach
	        ]);

			var options = {
				title: 'Parceiros Mais Vendidos',
				titleTextStyle: {
					fontSize: 15,
				},
			};

	        var chart = new google.visualization.PieChart(document.getElementById('parceiros_mais_vendidos'));
	        chart.draw(data, options);
		}

    	// Categorias Mais Vendidas
    	google.charts.setOnLoadCallback(drawCategoriasMaisVendidas);
    	function drawCategoriasMaisVendidas() {

    		var data = google.visualization.arrayToDataTable([
				['Curso', 'Quantidade'],
				@foreach($dados_categorias_mais_vendidas as $dado)
					['{{ $dado->categoria }}', {{ $dado->quantidade }}],
				@endforeach
	        ]);

			var options = {
				title: 'Categorias Mais Vendidas',
				titleTextStyle: {
					fontSize: 15,
				},
			};

	        var chart = new google.visualization.PieChart(document.getElementById('categorias_mais_vendidas'));
	        chart.draw(data, options);
		}

    	// Cursos Mais Vendidos
    	google.charts.setOnLoadCallback(drawCursosMaisVendidos);
    	function drawCursosMaisVendidos() {

    		var data = google.visualization.arrayToDataTable([
				['Curso', 'Quantidade'],
				@foreach($dados_cursos_mais_vendidos as $dado)
					['{{ $dado->curso }}', {{ $dado->quantidade }}],
				@endforeach
	        ]);

			var options = {
				title: '10 Cursos Mais Vendidos',
				titleTextStyle: {
					fontSize: 15,
				},
			};

	        var chart = new google.visualization.PieChart(document.getElementById('cursos_mais_vendidos'));
	        chart.draw(data, options);
		}

    	// Ticket Médio
    	google.charts.setOnLoadCallback(drawTicketMedio);
    	function drawTicketMedio() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Mês');
			data.addColumn('number', 'Quantidade');
			data.addColumn({type: 'string', role: 'tooltip'});

			data.addRows([
				@foreach($dados_pedidos as $dado)
					['{{ $dado->pedido_criacao }}', {{ $dado->faturamento_bruto }}, '{{ 'R$ '.number_format($dado->ticket_medio, 2, ',', '.') }}'],
				@endforeach
			]);

			var options = {
				legend: 'none',
				title: 'Ticket Médio',
				titleTextStyle: {
					fontSize: 15,
				},
				hAxis: {
					format: 'R$ ###,###,###.00'
				},
				vAxis: {
					title: 'Valor (R$)'
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('ticket_medio'));
			chart.draw(data, options);
		}

    	// Faturamento Líquido
    	google.charts.setOnLoadCallback(drawFaturamentoLiquido);
    	function drawFaturamentoLiquido() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Mês');
			data.addColumn('number', 'Quantidade');
			data.addColumn({type: 'string', role: 'tooltip'});

			data.addRows([
				@foreach($dados_pedidos as $dado)
					['{{ $dado->pedido_criacao }}', {{ $dado->faturamento_bruto }}, '{{ 'R$ '.number_format($dado->faturamento_liquido, 2, ',', '.') }}'],
				@endforeach
			]);

			var options = {
				legend: 'none',
				title: 'Faturamento Líquido',
				titleTextStyle: {
					fontSize: 15,
				},
				hAxis: {
					format: 'R$ ###,###,###.00'
				},
				vAxis: {
					title: 'Valor (R$)'
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('faturamento_liquido'));
			chart.draw(data, options);
		}

    	// Faturamento Bruto
    	google.charts.setOnLoadCallback(drawFaturamentoBruto);
    	function drawFaturamentoBruto() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Mês');
			data.addColumn('number', 'Quantidade');
			data.addColumn({type: 'string', role: 'tooltip'});

			data.addRows([
				@foreach($dados_pedidos as $dado)
					['{{ $dado->pedido_criacao }}', {{ $dado->faturamento_bruto }}, '{{ 'R$ '.number_format($dado->faturamento_bruto, 2, ',', '.') }}'],
				@endforeach
			]);

			var options = {
				legend: 'none',
				title: 'Faturamento Bruto',
				titleTextStyle: {
					fontSize: 15,
				},
				hAxis: {
					format: 'R$ ###,###,###.00'
				},
				vAxis: {
					title: 'Valor (R$)'
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('faturamento_bruto'));
			chart.draw(data, options);
		}

    	// Vendas Unitárias
    	google.charts.setOnLoadCallback(drawVendasUnitarias);
    	function drawVendasUnitarias() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Mês');
			data.addColumn('number', 'Quantidade');

			data.addRows([
				@foreach($dados_pedidos as $dado)
					['{{ $dado->pedido_criacao }}', {{ $dado->vendas_unitarias }}],
				@endforeach
			]);

			var options = {
				legend: 'none',
				title: 'Vendas Unitárias',
				titleTextStyle: {
					fontSize: 15,
				},
				vAxis: {
					title: 'Quantidade'
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('vendas_unitarias'));
			chart.draw(data, options);
		}

	</script>
@endpush

