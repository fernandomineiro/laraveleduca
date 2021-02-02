@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Nacionalidades</h2></div>
		<div class="col-md-3" style="margin-top: 20px;">
			<a href="{{ route('admin.nacionalidade.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
		</div>
		<hr class="clear hr" />
		
		@if(count($nacionalidades) > 0)
			<table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
				<th>Título</th>
				<th>Status</th>
				<th>Ações</th>
				<tbody>
					@foreach($nacionalidades as $nacionalidade)
					<tr>
						<td>{{ $nacionalidade->titulo }}</td>
						<td>{{ $lista_status[$nacionalidade->status] }}</td>
						<td>
							<a href="{{ $nacionalidade->id }}/editar" class="btn btn-default btn-sm"><strong>Editar</strong></a>
							
							{{ Form::open(['method' => 'DELETE', 'route' => ['admin.nacionalidade.deletar', $nacionalidade->id], 'style' => 'display:inline;']) }}
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