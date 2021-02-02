@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Agenda de Propostas</h2></div>
		<div class="col-md-3" style="margin-top: 20px;">
			<a href="{{ route('admin.proposta_agenda.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
		</div>
		<hr class="clear hr" />
		
		@if(count($proposta_agenda) > 0)
			<table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
				<th>Proposta</th>
				<th>Data</th>
				<th>Hora Início</th>
				<th>Hora Fim</th>
				<tbody>
					@foreach($proposta_agenda as $item)
					<tr>
						<td>{{ $item->titulo }}</td>
						<td>{{ $item->data_aula }}</td>
						<td>{{ $item->inicio }}</td>
						<td>{{ $item->terminal }}</td>
						<td>
							<a href="proposta_agenda/{{ $item->id }}/editar" class="btn btn-default btn-sm">Editar</a>
							{{ Form::open(['method' => 'DELETE', 'route' => ['admin.proposta_agenda.deletar', $item->id], 'style' => 'display:inline;']) }}
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
