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
			<div class="col-md-9"><h2 class="table">Gráficos - Parceiros</h2></div>
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
                                    <div class="form-group">
                                      <label for="tipo-user">Tipo</label>
                                      {{ Form::select('tipo_user', $tipos_user, (request()->get('tipo_user') ? request()->get('tipo_user') : 0 ), ['class' => 'form-control', 'id' => 'tipo-user']) }}
                                    </div>
                                </div>
                            </div>
							<div class="col-xs-6 col-md-3 col-lg-3" @if (!request()->get('fk_ies')) style="display: none;" @endif >
								<div class="form-group">
									<label>Projeto:</label>
									{{ Form::select('fk_ies', ['' => 'Selecione'] + $lista_ies, (request()->get('fk_ies') ? request()->get('fk_ies') : null), ['class' => 'form-control']) }}
								</div>
							</div>
                            <div class="col-xs-6 col-md-3 col-lg-3" @if (!request()->get('fk_professor')) style="display: none;" @endif >
								<div class="form-group">
									<label>Professor:</label>
									{{ Form::select('fk_professor', ['' => 'Selecione'] + $lista_professor, (request()->get('fk_professor') ? request()->get('fk_professor') : null), ['class' => 'form-control']) }}
								</div>
							</div>
							<div class="col-xs-6 col-md-3 col-lg-3" @if (!request()->get('fk_curador')) style="display: none;" @endif >
								<div class="form-group">
									<label>Curador:</label>
									{{ Form::select('fk_curador', ['' => 'Selecione'] + $lista_curador, (request()->get('fk_curador') ? request()->get('fk_curador') : null), ['class' => 'form-control']) }}
								</div>
							</div>
							<div class="col-xs-6 col-md-3 col-lg-3" @if (!request()->get('fk_produtora')) style="display: none;" @endif >
								<div class="form-group">
									<label>Produtora:</label>
									{{ Form::select('fk_produtora', ['' => 'Selecione'] + $lista_produtora, (request()->get('fk_produtora') ? request()->get('fk_produtora') : null), ['class' => 'form-control']) }}
								</div>
							</div>
							<div class="col-xs-6 col-md-3 col-lg-3" @if (!request()->get('fk_parceiro')) style="display: none;" @endif >
								<div class="form-group">
									<label>Parceiro:</label>
									{{ Form::select('fk_parceiro', ['' => 'Selecione'] + $lista_parceiro, (request()->get('fk_parceiro') ? request()->get('fk_parceiro') : null), ['class' => 'form-control']) }}
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
		@if (count($dados_pedidos) > 0)
			<div class="row" style="margin: 0" id="div_graficos">
				<div class="col-md-6 col-xs-12" style="padding:8px;">
					<div class="graficos" id="faturamento_liquido"></div>
				</div>
			</div>
		@elseif (!request()->get('fk_ies') && !request()->get('fk_professor') && !request()->get('fk_curador') && !request()->get('fk_produtora') && !request()->get('fk_parceiro'))
			<div class="row">
				<div class="col-md-12">
					<div class="alert alert-info">Filtre por usuário...</div>
				</div>
			</div>
		@else
			<div class="row">
				<div class="col-md-12">
					<div class="alert alert-info">Nenhum registro foi encontrado!</div>
				</div>
			</div>
		@endif
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

    	// Faturamento líquido
    	google.charts.setOnLoadCallback(drawVendasUnitarias);
    	function drawVendasUnitarias() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Mês');
			data.addColumn('number', 'Quantidade');

			data.addRows([
				@foreach($dados_pedidos as $dado)
                    [ '{{ $dado->valor_legenda }}', {{ $dado->valor }}],
				@endforeach
			]);

			var options = {
				legend: 'none',
				title: 'Faturamento líquido',
				titleTextStyle: {
					fontSize: 15,
				},
				vAxis: {
					title: 'Quantidade'
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('faturamento_liquido'));
			chart.draw(data, options);
		}

        $('#tipo-user').on('change', function(){
            var id = $( this ).val();

            $('select[name="fk_professor"]').prop('selectedIndex', 0);
            $('select[name="fk_curador"]').prop('selectedIndex', 0);
            $('select[name="fk_produtora"]').prop('selectedIndex', 0);
            $('select[name="fk_parceiro"]').prop('selectedIndex', 0);
            $('select[name="fk_ies"]').prop('selectedIndex',0);

            $('select[name="fk_professor"]').closest('.col-lg-3').hide();
            $('select[name="fk_curador"]').closest('.col-lg-3').hide();
            $('select[name="fk_produtora"]').closest('.col-lg-3').hide();
            $('select[name="fk_parceiro"]').closest('.col-lg-3').hide();
            $('select[name="fk_ies"]').closest('.col-lg-3').hide();

            $('select[name="fk_'+id+'"]').closest('.col-lg-3').show();
        });

	</script>
@endpush
