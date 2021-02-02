@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Bancos</h2></div>
		<div class="col-md-3" style="margin-top: 20px;">
			<a href="{{ route('admin.banco.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
		</div>
		<hr class="clear hr" />
		
		@if(count($bancos) > 0)
			<table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
				<th>Código do Banco</th>
				<th>Título</th>
				<th>Status</th>
				<th>Ações</th>
				<tbody>
					@foreach($bancos as $banco)
					<tr>
						<td>{{ $banco->numero }}</td>
						<td>{{ $banco->titulo }}</td>
						<td>{{ $lista_status[$banco->status] }}</td>
						<td>
							<a href="/admin/banco/{{ $banco->id }}/editar" class="btn btn-default btn-sm">Editar</a>
							
							{{ Form::open(['method' => 'DELETE', 'route' => ['admin.banco.deletar', $banco->id], 'style' => 'display:inline;']) }}
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