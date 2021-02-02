@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9">
        	<h2 class="table">{{ $modulo['moduloDetalhes']->modulo }}</h2>
        </div>
		<div class="col-md-3" style="margin-top: 20px;">
			<div class="btn-toolbar pull-right" role="toolbar">
				<div class="btn-group mr-2" role="group">
    				<a href="{{ route('admin.'.$modulo['moduloDetalhes']->rota.'.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
    			</div>
            </div>
            <script type="text/javascript">
                $('#dropdown-menu-export-to li a').click(function (e) {
            	    e.preventDefault();
            	    var $valor = $(this).text();
            	    $('#export-to-type').val($valor);
            	    $('#form-export-to').submit();
            	});
            </script>
		</div>
        <hr class="clear hr"/>

        @if(count($objlista) > 0)
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Lista de registros encontrados</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered table-striped dataTable">
                        <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($objlista as $diretoria)
                            <tr>
                                <td>{{ $diretoria->nome }}</td>
                                <td>{{ $diretoria->status ? 'Ativo' : 'Inativo' }}</td>
                                <td style="text-align: center">
                                    <a href="{{ $diretoria->id }}/editar"
                                       class="btn btn-default btn-sm" title="Editar"><i
                                            class="fa fa-fw fa-edit"></i></a>
                                    {{ Form::open(['method' => 'DELETE', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.deletar', $diretoria->id], 'style' => 'display:inline;']) }}
                                    <button type="submit" class="btn btn-danger btn-sm" title="Excluir" onclick="return confirm('Deseja realmente excluir?')">
                                        <i class="fa fa-fw fa-trash"></i>
                                    </button>
                                    {{ Form::close() }}
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







