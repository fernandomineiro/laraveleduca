@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Lista de Cursos da Trilha: <br /></h2><h3>{{ $nome_trilha }}</h3>
		<a href="{{ route('admin.trilha') }}" class="label label-default">Voltar</a><br /><br />
		</div>
		<div class="col-md-3" style="margin-top: 20px;">
			<a href="/admin/trilha_curso/{{ $id_trilha }}/incluir" class="btn btn-success right margin-bottom-10">Adicionar</a>
		</div>
		<hr class="clear hr" />
		
		@if(count($trilha_cursos))
			<table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
				<th>Nome</th>
				<th>Ações</th>
				<tbody>
					@foreach($trilha_cursos as $curso)
					<tr>
						<td>{{ $lista_faculdades[$curso->fk_faculdade] }} - {{ $curso->nome_curso }}</td>
						<td>
							<a href="/admin/trilha_curso/{{ $curso['id'] }}/editar" class="btn btn-default btn-sm">Editar</a>
							{{ Form::open(['method' => 'DELETE', 'route' => ['admin.trilha_curso.deletar', $curso['id']], 'style' => 'display:inline;']) }}
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







