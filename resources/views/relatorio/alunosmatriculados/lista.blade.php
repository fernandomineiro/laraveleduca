@extends('layouts.app')

@section('style')
    <style>
        .readonly {
            background: #eee;
            opacity: 1;
        }
    </style>
@endsection
@section('content')
    <div class="box padding20">
        <div class="col-md-9"><h2 class="table">Relat&oacute;rio de Alunos Matriculados</h2></div>
        <div class="col-md-3" style="margin-top: 20px;">
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
                        <form id="formAlunosMatriculados" role="form" method="get" action="{{ url('/admin/relatorio/alunosmatriculados/index') }}">
                            <input type="hidden" name="export" id="export" value="0">
                            <input type="hidden" name="dashboard" id="dashboard" value="list">
                            <div class="row">
                                <div class="col-xs-12 col-md-3 col-lg-3">
                                    <label for="data_registro">Data da Compra:</label>
                                    <div class="form-group form-inline">
                                        <input type="text" name="data_registro" id="data_registro" class="form-group dataPicker"
                                               style="cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 70%"
                                               value="{{request('data_registro')}}"
                                        />
                                        <a class="btn btn-default" onclick="reset_datacompra()">reset</a>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-3 col-lg-3" id="row_id">
                                    <div class="form-group">
                                        <label for="id">ID Aluno:</label>
                                        <input type="number" name="id" id="id" class="form-control" placeholder="ID do Aluno" value="{{request('id')}}">
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-3 col-lg-3" id="row_nome">
                                    <div class="form-group">
                                        <label for="nome">Nome:</label>
                                        <input type="text" name="nome" id="nome" class="form-control" placeholder="Nome" value="{{request('nome')}}">
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-3 col-lg-3 ">
                                    @php
                                        $ies = request('ies');
                                        if($ies == null || $ies == '') {
                                            $ies = '';
                                        }
                                    @endphp
                                    <div class="form-group">
                                        <label for="ies">IES:</label>
                                        <select name="ies" id="ies" class="form-control">
                                            @if(@isset($faculdades) && count($faculdades))
                                                <option value="">Nenhum</option>
                                                @foreach($faculdades as $faculdade)
                                                    <option value="{{ $faculdade->id }}" @if($faculdade->id == $ies) selected @endif>
                                                        {{ $faculdade->nome_faculdade }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 col-md-3 col-lg-3" id="row_curso_id">
                                    <div class="form-group">
                                        <label for="curso_id">ID Curso:</label>
                                        <input type="number" id="curso_id" name="curso_id" class="form-control" placeholder="ID Curso" value="{{request('curso_id')}}">

                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-3 col-lg-3" id="row_curso_nome">
                                    <div class="form-group">
                                        <label for="curso_nome">Nome Curso:</label>
                                        <input type="text" id="curso_nome" name="curso_nome" class="form-control" placeholder="Nome Curso" value="{{request('curso_nome')}}">

                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-3 col-lg-3 {{ request()->input('ies') == 6 ? 'hidden' : ''}}" id="row_data_matricula">
                                    <label for="data_matricula">Data da Matricula:</label>
                                    <div class="form-group form-inline">
                                        <input type="text" name="data_matricula" id="data_matricula" class="form-group dataPicker" value="{{request('data_matricula')}}"
                                               style="cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 70%" />
                                        <a class="btn btn-default" onclick="reset_datamatricula()">reset</a>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-3 col-lg-3" id="row_curso_tipo">
                                    @php
                                        $curso_tipo = request('curso_tipo');
                                        if($curso_tipo == null || $curso_tipo == '') {
                                            $curso_tipo = '';
                                        }
                                    @endphp
                                    <div class="form-group">
                                        <label for="curso_tipo">Tipo de Curso:</label>
                                        <select name="curso_tipo" id="curso_tipo" class="form-control">
                                            <option value=""  @if($curso_tipo === '') selected @endif>Selecionar...</option>
                                            <option value="1" @if($curso_tipo === '1') selected @endif>On-line</option>
                                            <option value="2" @if($curso_tipo === '2') selected @endif>Presenciais</option>
                                            <option value="4" @if($curso_tipo === '4') selected @endif>Remoto</option>
                                            <option value="5" @if($curso_tipo === '5') selected @endif>Mentoria</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                @php
                                    $status_pagamento = request('status_pagamento');
                                    if($status_pagamento == null || $status_pagamento == '') {
                                        $status_pagamento = '';
                                    }
                                @endphp
                                <div class="col-xs-12 col-md-3 col-lg-3 {{ request()->input('ies') == 6 ? 'hidden' : ''}}" id='row_status_pagamento' >
                                    <div class="form-group">
                                        <label for="status_pagamento">Status pagamento:</label>
                                        <select name="status_pagamento" id="status_pagamento" class="form-control">
                                            <option value="" @if($status_pagamento === '') selected @endif>Selecionar...</option>
                                            <option value="1" @if($status_pagamento === '1') selected @endif>Aguardando Pagamento</option>
                                            <option value="2" @if($status_pagamento === '2') selected @endif>Pedido Pago</option>
                                            <option value="3" @if($status_pagamento === '3') selected @endif>Cancelado</option>
                                            <option value="4" @if($status_pagamento === '4') selected @endif>Pagamento não aprovado</option>
                                            <option value="5" @if($status_pagamento === '5') selected @endif>Pagamento em análise</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-3 col-lg-3" id="row_status_conclusao" >
                                    @php
                                        $status_conclusao = request('status_conclusao');
                                        if($status_conclusao != 0 && $status_conclusao != 1) {
                                            $status_conclusao = '--';
                                        }
                                    @endphp
                                    <div class="form-group">
                                        <label for="status_conclusao">Status conclusão:</label>
                                        <select name="status_conclusao" id="status_conclusao" class="form-control">
                                            <option value="--"  @if($status_conclusao === '--') selected @endif>Nenhum</option>
                                            <option value="0" @if($status_conclusao === '0') selected  @endif>Em andamento</option>
                                            <option value="1" @if($status_conclusao === '1') selected @endif>Concluído</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-3 col-lg-3" id="row_email">
                                    <div class="form-group">
                                        <label for="email">E-mail:</label>
                                        <input type="email" name="email" id="email" class="form-control" placeholder="E-mail" value="{{request('email')}}">
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-3 col-lg-3">
                                    <br>
                                    <div class="btn-group btn-group-justified" role="group" aria-label="...">
                                        <div class="btn-group" role="group">
                                            <button type="submit" class="btn  btn-block btn-success" id="btnFiltrar" >Filtrar</button>
                                        </div>
                                        <div class="btn-group" role="group" id="group-btnFiltrarCancelar">
                                            <button type="button" class="btn btn-block btn-secondary" id="btnFiltrarCancelar">Limpar filtro</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </form>

                    </div>
                </div>
            </div>
        </div>
        <br>

        <div class="col-md-12 text-center" style="margin-top: 20px;" >
            <div class="btn-group">
                <div class="load hide"><i class="fa fa-spinner fa-spin fa-5x"></i>
                    <span class="sr-only">Buscando dados...</span>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-xs-12">
                @isset($data)
                    @if(count($data) > 0)
                        <div class="row">
                            <div class="col-sm-5">
                                Mostrando de {{ $data->firstItem() }} até {{ $data->lastItem() }} de {{ $data->total() }} registros
                            </div>
                        </div>

                        @include('relatorio.alunosmatriculados.table_alunos_matriculados')

                        {{ $data->appends(request()->input())->links() }}

                    @else
                        <div class="alert alert-info">Nenhum registro no encontrado, altere o filtro e faça uma nova consulta!</div>
                    @endif
                @endisset



            </div>
        </div>

    </div>

@endsection


@push('js')
    <script>
        let now = moment().format('DD/MM/Y') + ' - ' + moment().format('DD/MM/Y');
        let check_data_registro = "{{request('data_registro')}}";
        let check_data_matricula = "{{request('data_matricula')}}";
        
        $(document).ready(function () {
            if ( $.fn.DataTable.isDataTable('#table_alunos_matriculados') ) {
                $('#table_alunos_matriculados').DataTable().destroy();
            }

            $('#group-btnFiltrarCancelar').addClass('hide');

            $('#btnFiltrarCancelar').click(function () {
                $('#group-btnFiltrarCancelar').addClass('hide');
                limparFiltro();
            });

            $('.dataPicker').daterangepicker({
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

            $('#dropdown-menu-export-to li a').click(function (e) {
                e.preventDefault();
                let $valor = $(this).text();
                $('#export-to-type').val($valor);

                $('#export').val(1);
                $('#formAlunosMatriculados').submit();
                $('#export').val(0);
            });

            $('#ies').click(function() {
                let ies = $('#ies').val();

                if(ies == 6) {
                    $('#row_status_conclusao').addClass('hidden');
                    $('#row_status_pagamento').addClass('hidden');
                    $('#row_data_matricula').addClass('hidden');
                } else {
                    $('#row_status_conclusao').removeClass('hidden');
                    $('#row_status_pagamento').removeClass('hidden');
                    $('#row_id').removeClass('hidden');
                    $('#row_nome').removeClass('hidden');
                    $('#row_curso_id').removeClass('hidden');
                    $('#row_curso_nome').removeClass('hidden');
                    $('#row_data_matricula').removeClass('hidden');
                    $('#row_curso_tipo').removeClass('hidden');
                    $('#row_email').removeClass('hidden');
                }

            })
        
            if(!check_data_registro) {
                $('#data_registro').val('');
            }
            if(!check_data_matricula) {
                $('#data_matricula').val('');
            }
        });

        
        function reset_datacompra() {
            event.preventDefault();
            $('#data_registro').val('');
        }

        function reset_datamatricula() {
            event.preventDefault();
            $('#data_matricula').val('');
        }

    </script>

@endpush
