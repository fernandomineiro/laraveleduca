@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Lista de Questionários</h2></div>
		<div class="col-md-3" style="margin-top: 20px;">
			<a href="{{ route('admin.quiz.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
		</div>
		<hr class="clear hr" />

		@if(count($quizes) > 0)
			<table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
				<th>Projeto</th>
				<th>Curso</th>
				<th>Quantidade de Questões</th>
				<th>Percentual de Acertos</th>
				<th>Ações</th>
				<tbody>
					@foreach($quizes as $quiz)
					<tr>
						<td>{{ $quiz->faculdade_nome }}</td>
						<td>{{ $quiz->curso_nome }}</td>
						<td>{{ $quiz->qtd_questao }}</td>
						<td>{{ $quiz->percentual_acerto }}% </td>
						<td>
							<a href="/admin/quiz/{{ $quiz->id }}/editar" class="btn btn-default btn-sm">Editar</a>
							<a href="/admin/quiz_questao/{{ $quiz->id }}/index" class="btn btn-primary btn-sm">Questões</a>

							{{ Form::open(['method' => 'DELETE', 'route' => ['admin.quiz.deletar', $quiz->id], 'style' => 'display:inline;']) }}
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
