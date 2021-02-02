@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Lista de Incrições</h2></div>
		<hr class="clear hr" />
		
		@if(count($inscricoes) > 0)
			<table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
				<th>Aluno</th>
				<th>Curso</th>
				<th>Percentual Completo</th>
				<th>Ações</th>
				<tbody>
					@foreach($inscricoes as $inscricao)
					<tr>
						<td>{{ $inscricao->nome_usuario }}</td>
						<td>{{ $inscricao->nome_curso }}</td>
						<td>{{ $inscricao->percentual_completo }}</td>
						<td>
							<a href="/admin/inscricao/{{ $inscricao->id }}/editar" class="btn btn-default btn-sm">Ver</a>
							{{ Form::open(['method' => 'DELETE', 'route' => ['admin.inscricao.deletar', $inscricao->id], 'style' => 'display:inline;']) }}
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