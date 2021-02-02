@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Lista de Cupons</h2></div>
		<div class="col-md-3" style="margin-top: 20px;">
			<a href="{{ route('admin.cupom.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
			<a href="{{ route('admin.cupom_importar.incluir') }}" class="btn btn-success right margin-bottom-10"
               title="Utilize essa funcionalidade para importar cupons prontos para dentro do sistema">
                Importar Cupons em Massa
            </a>
		</div>
		<hr class="clear hr" />

		@if(count($cupons) > 0)
			<table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
				<th>Cupom</th>
				<th>Código</th>
				<th>Tipo</th>
				<th>Valor</th>
				<th>Data Inicial</th>
				<th>Data Final</th>
				<th>Ações</th>
				<tbody>
					@foreach($cupons as $item)
					<tr>
						<td>{{ $item->titulo }}</td>
						<td>{{ $item->codigo_cupom }}</td>
						<td>{{ $tipo_cupom[$item->tipo_cupom_desconto] }}</td>
						<td>{{ $item->valor }}</td>
						<td>{{ implode('/', array_reverse(explode('-', $item->data_validade_inicial))) }}</td>
						<td>{{ implode('/', array_reverse(explode('-', $item->data_validade_final))) }}</td>
						<td>
							<a href="/admin/cupom/{{ $item->id }}/editar" class="btn btn-default btn-sm">Editar</a>

							{{ Form::open(['method' => 'DELETE', 'route' => ['admin.cupom.deletar', $item->id], 'style' => 'display:inline;']) }}
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
