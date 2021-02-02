@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Propostas</h2></div>
		<div class="col-md-3" style="margin-top: 20px;">
			<a href="{{ route('admin.proposta.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
		</div>
		<hr class="clear hr" />
		
		@if(count($propostas) > 0)
			<table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
				<th>Título</th>
				<th>Professor</th>
				<th>Status</th>
				<th>Duração</th>
				<th>Ações</th>
				<tbody>
					@foreach($propostas as $item)
					<tr>
						<td>{{ $item->titulo }}</td>
						<td>{{ $lista_professor[$item->fk_professor] }}</td>
						<td>{{ isset($lista_status[$item->fk_proposta_status]) ? $lista_status[$item->fk_proposta_status] : NULL }}</td>
						<td>{{ $item->duracao_total }}</td>
						<td>
							<a href="/admin/proposta_modulo/{{ $item->id }}/carregar" class="btn btn-primary btn-sm"><i class="fa fa-wrench"></i> Modulos</a>
							<a href="/admin/proposta_questionario/{{ $item->id }}/carregar" class="btn btn-primary btn-sm"><i class="fa fa-wrench"></i> Questionário</a>
							<a href="/admin/proposta/{{ $item->id }}/editar" class="btn btn-default btn-sm">Editar</a>
							{{ Form::open(['method' => 'DELETE', 'route' => ['admin.proposta.deletar', $item->id], 'style' => 'display:inline;']) }}
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
