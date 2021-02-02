@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Relatório Financeiro Detalhado</h2></div>
		<div class="col-md-3" style="margin-top: 20px;">
			<form method="GET" action="{{ request()->fullUrl() }}" id="form-export-to" class="pull-right" style="float: right;">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">										
				<input type="hidden" name="export-to-type" id="export-to-type">

				<input type="hidden" name="aluno" value="{{ request()->get('aluno') }}">
                <input type="hidden" name="export_finances" value="{{ request()->get('export_finances') }}">

                <input type="hidden" name="pedido_pid" value="{{ request()->get('pedido_pid') }}">
                <input type="hidden" name="pedidos_status" value="{{ request()->get('pedidos_status') }}">
                <input type="hidden" name="produto_pago" value="{{ request()->get('produto_pago') }}">
                <input type="hidden" name="ies" value="{{ request()->get('ies') }}">
                <input type="hidden" name="tipo_item" value="{{ request()->get('tipo_item') }}">
                <input type="hidden" name="nome_item" value="{{ request()->get('nome_item') }}">
                <input type="hidden" name="nome_professor" value="{{ request()->get('nome_professor') }}">
                <input type="hidden" name="nome_produtora" value="{{ request()->get('nome_produtora') }}">
                <input type="hidden" name="nome_curador" value="{{ request()->get('nome_curador') }}">
                <input type="hidden" name="data_compra" value="{{ \Carbon\Carbon::parse($data_compra[0])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($data_compra[1])->format('d/m/Y') }}">

				<input type="hidden" name="export" value="1">

                @csrf

			</form>
			<div class="btn-group pull-right">
				<button class="btn btn-success dropdown-toggle" type="button" data-toggle="dropdown"> Exportar para
					<i class="fa fa-angle-down"></i>
				</button>
				<ul class="dropdown-menu" id="dropdown-menu-export-to" role="menu">
					<li>
                    <a href="/exports_finances" method="GET">XLS</a>
                        @csrf
                    </li>
				</ul>
			</div>
		</div>
		<hr class="clear hr" />
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
									<label>Pedido:</label>
									<input type="text" name="pedido_pid" class="form-control" placeholder="ID do Pedido" value="{{ request()->get('pedido_pid') }}">
								</div>
							</div>

                            <div class="col-xs-6 col-md-3 col-lg-3">
								<div class="form-group">
									<label>Status do Pagamento:</label>
                                    {{ Form::select('pedidos_status', $pedidos_status, (request()->get('pedidos_status') ? request()->get('pedidos_status') : 0 ), ['class' => 'form-control']) }}
								</div>
							</div>

							<div class="col-xs-6 col-md-3 col-lg-3">
								<div class="form-group">
									<label>Produto Pago:</label>
                                    {{ Form::select('produto_pago', $produtoPago, (request()->get('produto_pago') ? request()->get('produto_pago') : 0 ), ['class' => 'form-control']) }}
								</div>
							</div>

							<div class="col-xs-6 col-md-3 col-lg-3">
								<div class="form-group">
									<label>IES:</label>
                                    {{ Form::select('ies', $faculdades, (request()->get('ies') ? request()->get('ies') : 0 ), ['class' => 'form-control']) }}
								</div>
							</div>

							<div class="col-xs-6 col-md-3 col-lg-3">
								<div class="form-group">
									<label>Modalidade do Produto:</label>
                                    {{ Form::select('tipo_item', $tipos_item, (request()->get('tipo_item') ? request()->get('tipo_item') : 0 ), ['class' => 'form-control']) }}
								</div>
							</div>

							<div class="col-xs-6 col-md-3 col-lg-3">
								<div class="form-group">
									<label>Produto Adquirido:</label>
									<input type="text" name="nome_item" class="form-control" placeholder="Nome do Produto" value="{{ request()->get('nome_item') }}">	
								</div>
							</div>

							<div class="col-xs-6 col-md-3 col-lg-3">
								<div class="form-group">
									<label>Professor:</label>
									<input type="text" name="nome_professor" class="form-control" placeholder="Nome do Professor" value="{{ request()->get('nome_professor') }}">
								</div>
							</div>

							<div class="col-xs-6 col-md-3 col-lg-3">
								<div class="form-group">
									<label>Produtora:</label>
									<input type="text" name="nome_produtora" class="form-control" placeholder="Nome da Produtora" value="{{ request()->get('nome_produtora') }}">
								</div>
							</div>

							<div class="col-xs-6 col-md-3 col-lg-3">
								<div class="form-group">
									<label>Curador:</label>
									<input type="text" name="nome_curador" class="form-control" placeholder="Nome do Curador" value="{{ request()->get('nome_curador') }}">
								</div>
							</div>

							<div class="col-xs-6 col-md-3 col-lg-3">
                                <label for="data_compra">Data da Compra:</label>
                                <div class="form-group form-inline">
									<input type="text" name="data_compra" id="data_compra" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; " />
								    <a class="btn btn-default" onclick="reset_datacompra()">Limpar data</a>
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
        @if(count($pedidos) > 0)
            {!! $table !!}
        @else
            <div class="alert alert-info">Nenhum registro no banco!</div>
        @endif
		<div class="row">
			<div class="col-sm-5">
				Mostrando de {{ $pedidos->firstItem() }} até {{ $pedidos->lastItem() }} de {{ $pedidos->total() }} registros
			</div>
		</div>
	</div>
@endsection

@push('js')
	<script type="text/javascript">
        $(function() {
            
            $('#data_compra').val('{{ \Carbon\Carbon::parse($data_compra[0])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($data_compra[1])->format('d/m/Y') }}');

            $('#data_compra').daterangepicker({
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
        
        function reset_datacompra() {
            event.preventDefault();
            $('#data_compra').val('');
        }

        </script>
@endpush	

