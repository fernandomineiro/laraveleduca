@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Configurações de pagamento</h2></div>
		<hr class="clear hr" />
		
		@if(count($tipo_pagamento) > 0)
			<table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
				<th>Título</th>
				<th>Status</th>
				<th>Ações</th>
				<tbody>
					@foreach($tipo_pagamento as $item)
					<tr>
						<td>{{ $item->titulo }}</td>
						<td>{{ $lista_status[$item->status] }}</td>
						<td>
							<a href="/admin/tipo_pagamento/{{ $item->id }}/editar" class="btn btn-default btn-sm">Editar</a>
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
