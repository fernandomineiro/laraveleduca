@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Eventos</h2></div>
        <form method="POST" action="{{ url('/admin/'.$modulo['moduloDetalhes']->rota.'/exportar') }}" id="form-export-to" class="pull-right" style="float: right;">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="export-to-type" id="export-to-type">
        </form>
        <div class="btn-toolbar pull-right" role="toolbar">
            <div class="btn-group mr-2" role="group">
			    <a href="{{ route('admin.eventos.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
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

		@if(count($eventos) > 0)
			<table cellpadding="0" cellspacing="0" border="0" class="table table-striped dataTable">
                <thead>
                    <tr>
                        <th>Projeto</th>
                        <th>Título</th>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th>Status</th>
                        <th>Imagem</th>
                        <th>Ações</th>
                    </tr>
                </thead>
				<tbody>
					@foreach($eventos as $evento)
					<tr>
						<td>{{ $evento->faculdade }}</td>
						<td>{{ $evento->titulo }}</td>
                        <td>{{ $evento->descricao }}</td>
                        <td>{{ $evento->categoria }}</td>
                        <td><?php echo isset($lista_status[$evento->status]) ?  $lista_status[$evento->status] : '-'; ?></td>
                        <td>
                            @if(!empty($evento->imagem))
                                <img src="{{URL::asset('files/eventos/imagem/' . $evento->imagem)}}" height="100">
                            @else
                                -
                            @endif
                        </td>
						<td nowrap>
							<a href="/admin/eventos/{{ $evento->id }}/editar" class="btn btn-default btn-sm"><i class="fa fa-fw fa-edit"></i></a>

							{{ Form::open(['method' => 'DELETE', 'route' => ['admin.eventos.deletar', $evento->id], 'style' => 'display:inline;']) }}
								<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir?')">
                                    <i class="fa fa-fw fa-trash"></i>
                                </button>
							{{ Form::close() }}

							<a href="/admin/agenda_eventos/{{ $evento->id }}/index" class="btn btn-success btn-sm">Agendas</a>
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

