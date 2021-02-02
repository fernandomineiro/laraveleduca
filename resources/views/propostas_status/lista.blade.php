@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Propostas Status</h2></div>
		<div class="col-md-3" style="margin-top: 20px;">
			<a href="{{ route('admin.propostas_status.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
		</div>
		<hr class="clear hr" />
		
		@if(count($propostas_status) > 0)
			<table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
				<th>Título</th>
				<th>Ações</th>
				<tbody>
					@foreach($propostas_status as $propostas_status)
					<tr>
						<td>{{ $propostas_status->titulo }}</td>
						<td>
							<a href="/admin/propostas_status/{{ $propostas_status->id }}/editar" class="btn btn-default btn-sm">Editar</a>
							
							{{ Form::open(['method' => 'DELETE', 'route' => ['admin.propostas_status.deletar', $propostas_status->id], 'style' => 'display:inline;']) }}
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