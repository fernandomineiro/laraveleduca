@extends('layouts.app')


@section('content')
    <div class="box padding20">
        <div class="col-md-9">
        	<h2>{{ $modulo['moduloDetalhes']->modulo }}</h2>
        </div>
		<div class="col-md-3" style="margin-top: 20px;">
			<form method="POST" action="{{ url('/admin/'.$modulo['moduloDetalhes']->rota.'/exportar') }}" id="form-export-to" class="pull-right" style="float: right;">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" name="export-to-type" id="export-to-type">
			</form>
			<div class="btn-toolbar pull-right" role="toolbar">
				<div class="btn-group mr-2" role="group">
    				<a href="{{ route('admin.'.$modulo['moduloDetalhes']->rota.'.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
    			</div>
    			<div class="btn-group mr-2" role="group">
    				<button class="btn btn-danger dropdown-toggle" type="button" data-toggle="dropdown"> Exportar para
    					<i class="fa fa-angle-down"></i>
    				</button>
    				<ul class="dropdown-menu" id="dropdown-menu-export-to" role="menu">
    					<li><a href="javascript:void(0)">XLS</a></li>
    					<li><a href="javascript:void(0)">XLSX</a></li>
    					<li><a href="javascript:void(0)">CSV</a></li>
    				</ul>
    			</div>
            </div>
            
		</div>
        <hr class="clear hr"/>

        @if(count($objLst) > 0)
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Lista de registros encontrados</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered table-striped dataTable">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Registrado em</th>
                            <th>CNPJ</th>
                            <th>Razão Social</th>
                            <th>Fantasia</th>
                            <th>Usuário</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($objLst as $obj)
                            <tr>
                                <td>{{ $obj->faculdade_id }}</td>
                                <td style="text-align: center">{{ implode('/', array_reverse(explode('-', substr($obj->registro, 0, 10)))) . ' ' . substr($obj->registro, 11, 8) }}</td>

                                <td>{{ $obj->cnpj }}</td>
                                <td><a href="{{ $obj->faculdade_id }}/editar">{{ $obj->razao_social }}</a></td>

                                <td>{{ $obj->fantasia }}</td>
                                @if($obj->usuario_ativo == 1)
                                    <td style="text-align: center">ATIVO</td>
                                @else
                                    <td style="text-align: center">DESATIVADO</td>
                                @endif

                                <td style="text-align: center">
                                    @if($obj->usuario_ativo == 1)
                                        <a href="{{ $obj->faculdade_id }}/editar"
                                           class="btn btn-default btn-sm"><i class="fa fa-fw fa-edit"></i></a>
                                        {{ Form::open(['method' => 'DELETE', 'route' => ['admin.faculdade.deletar', $obj->faculdade_id], 'style' => 'display:inline;']) }}
                                        <button type="submit" class="btn btn-danger btn-sm"><i
                                                class="fa fa-fw fa-trash"></i></button>
                                        {{ Form::close() }}
                                    @endif
									<a href="{{ url('/admin/usuario/'.$obj->fk_usuario_id.'/recuperarcredenciais') }}"
										class="btn btn-default btn-sm" title="Enviar credenciais">
										<i class="fa fa-fw fa-send"></i>
									</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>

                    </table>
                </div>
            </div>
        @else
            <div class="alert alert-info">Nenhum registro no banco!</div>
        @endif
    </div>
@endsection

@push('js')
    <script type="text/javascript">
        $('#dropdown-menu-export-to li a').click(function (e) {
            e.preventDefault();
            var $valor = $(this).text();
            $('#export-to-type').val($valor);
            $('#form-export-to').submit();
        });
    </script>
@endpush
