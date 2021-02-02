@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9">
            <h2 class="table">Membership - Relatório de arrecadação</h2>
            <small>Última atualização: {{ $data_atualizacao }}</small>
        </div>
		<div class="col-md-3" style="margin-top: 20px;">
			<form method="POST" action="{{ request()->fullUrl() }}" id="form-export-to" class="pull-right" style="float: right;">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" name="export-to-type" id="export-to-type">

				<input type="hidden" name="pedido_pid" value="{{ request()->get('pedido_pid') }}">
				<input type="hidden" name="data_registro" value="{{ \Carbon\Carbon::parse($data_registro[0])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($data_registro[1])->format('d/m/Y') }}">
				<input type="hidden" name="aluno" value="{{ request()->get('aluno') }}">

				<input type="hidden" name="export" value="1">
                <button type="submit" class="btn btn-success">Exportar</button>
			</form>
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
                            <div class="row">
                                <div class="col-xs-12 col-md-1 col-lg-1">
                                    <div class="form-group">
                                        <label>Mês</label>
                                        {{ Form::select('mes', $meses, (request()->get('mes') ? request()->get('mes') : date('m') ), ['class' => 'form-control', 'id' => 'mes']) }}
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-1 col-lg-1">
                                    <div class="form-group">
                                        <label>Ano</label>
                                        {{ Form::select('ano', $anos, (request()->get('ano') ? request()->get('ano') : date('Y') ), ['class' => 'form-control', 'id' => 'ano']) }}
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-3 col-lg-3">
                                    <div class="form-group">
                                        <label>IES:</label>
                                        {{ Form::select('ies', $faculdades, (request()->get('ies') ? request()->get('ies') : 0 ), ['class' => 'form-control', 'id' => 'ies']) }}
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-3 col-lg-3">
                                    <div class="form-group">
                                        <label>Planos:</label>
                                        {{ Form::select('plano', $planos, (request()->get('plano') ? request()->get('plano') : 0 ), ['class' => 'form-control', 'id' => 'plano']) }}
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-3 col-lg-3 pull-right">
                                    <div class="form-group">
                                        <label></label>
                                        <button type="submit" class="btn btn-block btn-success btn-md">Filtrar</button>
                                    </div>
                                </div>
                            </div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<br>
		<div class="table-responsive" style="overflow-x:auto;">
    		@if(count($repasses) > 0)
				{!! $table !!}
    		@endif
		</div>
		@if ($repasses)
			<div class="row">
				<div class="col-sm-5" style="margin-top: 8px;">
					Mostrando de {{ $repasses->firstItem() }} até {{ $repasses->lastItem() }} de {{ $repasses->total() }} registros
				</div>
				<div class="col-sm-7">
					{!! $repasses->appends(request()->except('page'))->links() !!}
				</div>
			</div>
		@endif
	</div>


@endsection

@push('js')
    <script type="text/javascript">
        $(function() {

            $('#data_registro').val('{{ \Carbon\Carbon::parse($data_registro[0])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($data_registro[1])->format('d/m/Y') }}');

            $('#data_registro').daterangepicker({
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
