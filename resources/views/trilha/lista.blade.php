@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Trilhas de Conhecimento</h2></div>
        <form method="POST" action="{{ url('/admin/'.$modulo['moduloDetalhes']->rota.'/exportar') }}" id="form-export-to" class="pull-right" style="float: right;">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="export-to-type" id="export-to-type">
        </form>

        <div class="btn-toolbar pull-right" role="toolbar">
            <div class="btn-group mr-2" role="group">
                <a href="{{ route('admin.trilha.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
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
            <script type="text/javascript">
                $('#dropdown-menu-export-to li a').click(function (e) {
                    e.preventDefault();
                    var $valor = $(this).text();
                    $('#export-to-type').val($valor);
                    $("form-export-to").append($('#form-filtro').html())
                    setTimeout(() => {
                        $('#form-export-to').submit();
                    }, 300)
                });
            </script>
        </div>
		<hr class="clear hr" />
        <br>
        @if(count($trilhas) > 0)
            <div class="table-responsive">
                    <table class="table table-bordered table-striped dataTable"  style="width:100%">
                        <thead>
                            <tr>
                                <th>IES</th>
                                <th>Nome</th>
                                <th>Carga Horária</th>
                                <th>Preço </th>
                                <th>Preço de Venda</th>
                                <th>Categoria</th>
                                <th>Nº de Inscritos</th>
                                <th>Nº de assinaturas</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody >
                        @foreach($trilhas as $trilha)
                            <tr>
                                <td>{{ ($trilha['projetos']) ? $trilha['projetos'] : '-' }}</td>
                                <td>{{ ($trilha['titulo']) ? $trilha['titulo'] : '-'}}</td>
                                <td>{{($trilha['duracao_total']) ? $trilha['duracao_total'] : '00:00'}}</td>
                                <td>{{($trilha['valor']) ? 'R$ '.number_format( $trilha['valor'] , 2, ',', '.') : 0}}</td>
                                <td>{{($trilha['valor_venda']) ? 'R$ '.number_format( $trilha['valor_venda'] , 2, ',', '.') : 0}}</td>
                                <td>{{ ($trilha['categorias']) ? $trilha['categorias'] : '-'}}</td>
                                <td>{{ ($trilha['inscritos']) ? $trilha['inscritos'] : 0}}</td>
                                <td>{{ ($trilha['assinaturas']) ? $trilha['assinaturas'] : 0}}</td>
                                <td>{{ ($trilha['status_nome']) ? $trilha['status_nome'] : '-'}}</td>
                                <td nowrap>
                                    <a href="/admin/trilha/{{ $trilha['id'] }}/editar" class="btn btn-default btn-sm"
                                       data-toggle="tooltip" data-placement="bottom" title="Editar Trilha">
                                        <i class="fa fa-fw fa-edit"></i>
                                    </a>
                                    {{ Form::open(['method' => 'DELETE', 'route' => ['admin.trilha.deletar', $trilha['id']], 'style' => 'display:inline;']) }}
                                    <button type="submit" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="bottom" title="Excluir Trilha">
                                        <i class="fa fa-fw fa-trash"></i>
                                    </button>
                                    {{ Form::close() }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
        @else
            <div class="alert alert-info">Nenhum registro no banco!</div>
        @endif
        </div>
	</div>
    <script type="text/javascript">
        //buscarTrilha()
    </script>
@endsection
