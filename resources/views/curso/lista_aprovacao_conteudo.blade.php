@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Análse de Conteúdo do Curso</h2></div>
		<hr class="clear hr" />
		@if(count($cursos) > 0)
			<table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
				<th>Título</th>
				<th>Projeto</th>
				<th>Tipo</th>
				<th>Valor</th>
				<th>Status</th>
				<th>Ações</th>
				<tbody>
					@foreach($cursos as $curso)
					<tr>
						<td>{{ $curso->titulo }}</td>
						<td>{{ $curso->faculdade }}</td>
						<td>{{ $curso->curso_tipo }}</td>
						<td>R$ {{ number_format( $curso->valor , 2, ',', '.') }}</td>
						<td>{{ $curso->descricao }}</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		@else
			<div class="alert alert-info">Nenhum registro no banco!</div>
		@endif

	</div>
@endsection
