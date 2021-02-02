@extends('layouts.app')

@section('styles')
    <style>
        .graficos{
            border: 1px solid transparent;
            border-color: #ddd;
            border-radius: 10px;
            margin-bottom: 5px;
            padding:2px;
            height: 350px;
        }
    </style>
@endsection


@section('content')
    <div class="box padding20">
        <div class="row">
            <div class="col-md-9"><h2 class="table">Relat&oacute;rio de Alunos Matriculados</h2></div>
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
                            <div class="col-xs-6 col-md-4 col-lg-4">
                                <div class="form-group">
                                    <label>Período:</label>
                                    <input type="text" name="data_range" id="data_range" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%" />
                                </div>
                            </div>
                            <div class="col-xs-6 col-md-4 col-lg-4">
                                @php
                                    $faculdadeRequest = request('faculdade');
                                    if($faculdadeRequest == null || $faculdadeRequest == '') {
                                        $faculdadeRequest = '';
                                    }
                                @endphp
                                <div class="form-group">
                                    <label>IES:</label>
                                    <select class="form-control" id="faculdade" name="faculdade">
                                        <option value="" @if($faculdadeRequest === '') selected @endif>Selecionar...</option>
                                        @foreach($faculdades as $faculdade)
                                            <option value="{{ $faculdade->id }}" @if($faculdadeRequest == $faculdade->id) selected @endif>
                                                {{ $faculdade->nome_faculdade }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="col-xs-6 col-md-4 col-lg-4">
                                @php
                                    $agrupar_por = request('agrupar_por');
                                    if($agrupar_por == null || $agrupar_por == '') {
                                        $agrupar_por = 'semana';
                                    }
                                @endphp
                                <div class="form-group">
                                    <label>Tempo:</label>
                                    <select class="form-control" id="agrupar_por" name="agrupar_por">
                                        <option value="semana" @if($agrupar_por == 'semana') selected @endif>Semana</option>
                                        <option value="mes" @if($agrupar_por == 'mes') selected @endif>Mês</option>
                                        <option value="ano" @if($agrupar_por == 'ano') selected @endif>Ano</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-xs-8 col-md-6 col-lg-6">
                                <div class="form-group">
                                    <label>Curso:</label>
                                <input type="text" class="form-control" id="curso_nome" name="curso_nome" value="{{ request('curso_nome')}}" placeholder="Nome do curso...">
                                
                                </div>
                            </div>


                            <div class="col-xs-4 col-md-6 col-lg-6 pull-right">
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
            <div class="col-md-12 col-xs-12" style="padding: 0">
                <div class="graficos" id="tempo_medio_navegacao"></div>
            </div>
        </div>

    </div>
@endsection

@push('js')
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        $(function() {
            let now = Date.now();

            $("#btn_imprimir").click(function () {
                printData();
            });


            $('#data_range').val('{{ \Carbon\Carbon::parse($data_inicial)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($data_final)->format('d/m/Y') }}');

            $('#data_range').daterangepicker({
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

        {{--// Tempo Médio de Navegação--}}
        google.charts.setOnLoadCallback(drawTempoMedioNavegacao);

        function drawTempoMedioNavegacao() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Total');
            data.addColumn('number', 'Matriculas');

            data.addRows([
                    @foreach($matriculas as $dado)
                ['{{ $dado->quantidade }}', {{ $dado->total }}],
                @endforeach
            ]);

            var options = {
                legend: 'none',
                title: 'Matriculas',
                titleTextStyle: {
                    fontSize: 15,
                },
                vAxis: {
                    title: 'Total'
                }
            };

            var chart = new google.visualization.ColumnChart(document.getElementById('tempo_medio_navegacao'));
            chart.draw(data, options);
        }

        function printData(){
            var divToPrint=document.getElementById("div_graficos");
            newWin= window.open("");
            newWin.document.write(divToPrint.outerHTML);
            newWin.print();
            newWin.close();
        }


    </script>
@endpush
