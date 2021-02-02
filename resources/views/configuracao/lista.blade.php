@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Configurações Projeto</h2></div>
		<div class="col-md-3" style="margin-top: 20px;">
			<a href="{{ route('admin.configuracao.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
		</div>
		<hr class="clear hr" />

		@if(count($configuracoes) > 0)
			<table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
				<th>Projeto</th>
				<th>Domínio</th>
				<th>Ações</th>
				<tbody>
					@foreach($configuracoes as $configuracao)
					<tr>
						<td>{{ $lista_faculdades[$configuracao->fk_faculdade] }}</td>
						<td>{{ $configuracao->dominio }}</td>
						<td>
							<a href="/admin/configuracao/{{ $configuracao->id }}/editar" class="btn btn-default btn-sm">Editar</a>

							{{ Form::open(['method' => 'DELETE', 'route' => ['admin.configuracao.deletar', $configuracao->id], 'style' => 'display:inline;']) }}
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
