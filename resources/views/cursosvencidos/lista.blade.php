@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9"><h2 class="table">Lista de Espera</h2></div>
        <form method="POST" action="{{ url('/admin/'.$modulo['moduloDetalhes']->rota.'/exportar') }}" id="form-export-to" class="pull-right" style="float: right;">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="export-to-type" id="export-to-type">
        </form>

        <form method="POST" action="{{ url('/admin/'.$modulo['moduloDetalhes']->rota.'/avisarnovasturmas') }}" id="form-avisar-interessados" class="pull-right" style="float: right;">
            @csrf
            <input type="hidden" name="interessados" id="interessados">
        </form>
        <div class="btn-toolbar pull-right" role="toolbar">
            <div class="btn-group mr-2" role="group">
                <a class="btn btn-success right margin-bottom-10" title="Enviar credenciais" id="avisar-interessados">
                    <i class="fa fa-fw fa-send"></i> Avisar Interessados
                </a>
            </div>
            <div class="btn-group mr-2" role="group">
                <button class="btn btn-danger dropdown-toggle" type="button" data-toggle="dropdown"> Exportar para
                    <i class="fa fa-angle-down"></i>
                </button>
                <ul class="dropdown-menu" id="dropdown-menu-export-to" role="menu">
                    <li><a href="javascript:void(0)">XLS</a></li>
                    <li><a href="javascript:void(0)">XLSX</a></li>
                    <!--<li><a href="javascript:void(0)">CSV</a></li>-->
                </ul>
            </div>
        </div>
        <hr class="clear hr" />

        @if(count($cursos) > 0)
            <table class="table table-bordered table-striped dataTable">
                <thead>
                <th>ID Curso</th>
                <th>Nome do Curso</th>
                <th>Tipo</th>
                <th>Projeto</th>
                <th>Data Interesse</th>
                <th>Nome Interessado</th>
                <th>Email Interessado</th>
                <th>
                    Ações <br />
                    <input name="selecionar_todas" type="checkbox" id="selecionar_todas" value="" onclick="marcarTodas();"> Marcar Todos
                </th>
                </thead>
                <tbody>
                @foreach($cursos as $curso)
                    <tr>
                        <td>{{ $curso->id }}</td>
                        <td>{{ $curso->titulo }}</td>
                        <td>{{isset($curso->curso_tipo) ?  $curso->curso_tipo : '-' }}</td>
                        <td>{{$curso->faculdade}}</td>
                        <td>{{($curso->data_criacao) ? date('d/m/Y', strtotime($curso->data_criacao)) : '-'}}</td>
                        <td>{{$curso->nome_aluno}}</td>
                        <td>{{$curso->email_aluno}}</td>
                        <td nowrap>
                            <div class="custom-control custom-checkbox">
                                <div class="col-md-12">
                                    {{ Form::checkbox('interessados[]', $curso, false, ['class' => 'marcar'])}}
                                    Avisar sobre nova turma
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-info">Nenhum registro no banco!</div>
        @endif
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function () {
            $('#avisar-interessados').click(function (e) {
                e.preventDefault();
                let interessados = []
                let valores = [...$('.marcar:checkbox:checked')]
                valores.forEach(e => {
                    interessados.push(e.value)
                })
                $('#interessados').val(JSON.stringify(interessados))
                setTimeout(() => {
                    $('#form-avisar-interessados').submit();
                }, 300)
            })
            $('#dropdown-menu-export-to li a').click(function (e) {
                e.preventDefault();
                var $valor = $(this).text();
                $('#export-to-type').val($valor);
                $("form-export-to").append($('#form-filtro').html())
                setTimeout(() => {
                    $('#form-export-to').submit();
                }, 300)
            });
        });

        function marcarTodas() {
            $(this).prop('checked', !$(this).prop('checked'));
            $('.marcar').prop("checked", $(this).prop("checked"));
        }
    </script>
@endpush

