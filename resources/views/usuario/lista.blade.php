@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9">
        	<h2 class="table">{{ $modulo['moduloDetalhes']->modulo }}</h2>
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

        @if(count($usuarios) > 0)
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Lista de registros encontrados</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered table-striped dataTable">
                        <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Perfil</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($usuarios as $usuario)
                            <tr>
                                <td>{{ $usuario->nome }}</td>
                                <td>{{ $usuario->email }}</td>
                                <td>{{ $lista_perfis[$usuario->fk_perfil] }}</td>
                                <td>{{ $usuario->status ? 'Ativo' : 'Inativo' }}</td>
                                <td style="text-align: center">
                                    <a href="{{ $usuario->id }}/editar"
                                       class="btn btn-default btn-sm" title="Editar"><i
                                            class="fa fa-fw fa-edit"></i></a>
                                    {{ Form::open(['method' => 'DELETE', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.deletar', $usuario->id], 'style' => 'display:inline;']) }}
                                    <button type="submit" class="btn btn-danger btn-sm" title="Excluir" onclick="return confirm('Deseja realmente excluir?')">
                                        <i class="fa fa-fw fa-trash"></i>
                                    </button>
                                    {{ Form::close() }}
                                    <a href="{{ $usuario->id }}/recuperarcredenciais"
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


