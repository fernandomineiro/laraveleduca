@extends('layouts.app')

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
									{{ Form::select('id_configuracoes_pixel', $all_ies, (request()->get('id_configuracoes_pixel') ? request()->get('id_configuracoes_pixel') : 1 ), ['class' => 'form-control']) }}
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
    		@if(count($relatorio_tempo_medio_navegacao) > 0)
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Lista de registros encontrados</h3>
                    </div>
                    <div class="box-body" id="divDados">
                        <table class="table table-bordered table-striped dataTable">
                            <thead>
                            <tr>
                                <th>URL</th>
                                <th>Tempo Médio (segundos)</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($relatorio_tempo_medio_navegacao as $dado)
                                <tr>
                                    <td>{{ str_limit($dado[0],70) }}</td>
                                    <td>{{ $dado[1] }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            @else
                <hr class="clear hr"/>
                <div class="row">
                    <div class="alert alert-info">Nenhum registro no banco!</div>
                </div>
            @endif
	</div>
@endsection

@push('js')
    <script type="text/javascript">

        function printData(){
            var divToPrint=document.getElementById("divDados");
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

    </script>
@endpush
