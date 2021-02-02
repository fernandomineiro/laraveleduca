@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Relatório de Alunos</h2></div>
		<div class="col-md-3" style="margin-top: 20px;">
			<form method="GET" action="{{ request()->fullUrl() }}" id="form-export-to" class="pull-right" style="float: right;">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" name="export-to-type" id="export-to-type">

				<input type="hidden" name="pedido_pid" value="{{ request()->get('pedido_pid') }}">
				<input type="hidden" name="data_registro" value="{{ \Carbon\Carbon::parse($data_registro[0])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($data_registro[1])->format('d/m/Y') }}">
				<input type="hidden" name="aluno" value="{{ request()->get('aluno') }}">

                <input type="hidden" name="id" id="id_export" value="{{ request()->get('id') }}">
                <input type="hidden" name="nome" id="nome_export" value="{{ request()->get('nome') }}">
                <input type="hidden" name="email" id="email_export" value="{{ request()->get('email') }}">
                <input type="hidden" name="cpf" id="cpf_export" value="{{ request()->get('cpf') }}">
                <input type="hidden" name="ies" id="ies_export" value="{{ request()->get('ies') }}">
                
				<input type="hidden" name="export" value="1">
			</form>
			<div class="btn-group pull-right">
				<button class="btn btn-success dropdown-toggle" type="button" data-toggle="dropdown"> Exportar para
					<i class="fa fa-angle-down"></i>
				</button>
				<ul class="dropdown-menu" id="dropdown-menu-export-to" role="menu">
					<li><a href="javascript:void(0)">XLS</a></li>
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
                            <div class="row">
                                <div class="col-xs-12 col-md-3 col-lg-3">
                                    <div class="form-group">
                                        <label>ID:</label>
                                        <input type="text" name="id" class="form-control" placeholder="ID do Aluno" value="{{ request()->get('id') }}">
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-3 col-lg-3">
                                    <div class="form-group">
                                        <label>Nome:</label>
                                        <input type="text" name="nome" class="form-control" placeholder="Nome" value="{{ request()->get('nome') }}">
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-3 col-lg-3">
                                    <div class="form-group">
                                        <label>E-mail:</label>
                                        <input type="email" name="email" class="form-control" placeholder="E-mail" value="{{ request()->get('email') }}">
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-3 col-lg-3">
                                    <div class="form-group">
                                        <label>CPF:</label>
                                        <input type="text" name="cpf" class="form-control" placeholder="CPF" value="{{ request()->get('cpf') }}">
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-3 col-lg-3">
                                    <div class="form-group">
                                        <label>Data do Cadastro:</label>
                                        <input type="text" name="data_registro" id="data_registro" class="form-group" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%" />
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-3 col-lg-3">
                                    <div class="form-group">
                                        <label>IES:</label>
                                        {{ Form::select('ies', $faculdades, (request()->get('ies') ? request()->get('ies') : 0 ), ['class' => 'form-control', 'id' => 'ies']) }}
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
    		@if(count($alunos) > 0)
				{!! $table !!}
    		@endif
		</div>
		@if ($alunos)
			<div class="row">
				<div class="col-sm-5" style="margin-top: 8px;">
					Mostrando de {{ $alunos->firstItem() }} até {{ $alunos->lastItem() }} de {{ $alunos->total() }} registros
				</div>
				<div class="col-sm-7">
					{!! $alunos->appends(request()->except('page'))->links() !!}
				</div>
			</div>
		@endif
	</div>

    <hr>
    
    <h3>Gráfico de Alunos </h3>

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
