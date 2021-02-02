@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Status de Pedidos</h2></div>
		<hr class="clear hr" />
		
		@if(count($pedido_status) > 0)
			<table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
				<th>Título</th>
				<th>Ações</th>
				<tbody>
					@foreach($pedido_status as $pedido_status)
					<tr style="background: {{ $pedido_status->cor }};">
						<td>{{ $pedido_status->titulo }}</td>
						<td>
							<a href="/admin/pedido_status/{{ $pedido_status->id }}/editar" class="btn btn-default btn-sm" style="margin-right: 10px;">Editar</a>
							
							{{ Form::open(['method' => 'DELETE', 'route' => ['admin.pedido_status.deletar', $pedido_status->id], 'style' => 'display:inline;']) }}
								<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir?')">Excluir</button>
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
@endsection