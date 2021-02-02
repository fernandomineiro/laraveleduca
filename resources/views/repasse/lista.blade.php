@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="row">
			<div class="col-md-9"><h2 class="table">Repasses</h2></div>
		</div>
		<div class="box-header">
			<h3 class="box-title">Lista de registros encontrados</h3>
		</div>
		@if(count($usuarios) > 0)
			<table cellpadding="0" cellspacing="0" border="0" class="table table-striped dataTable">
				<thead>
					<tr>
						<th >Nome</th>
						<th>Tipo</th>
						<th>Status</th>
						<th>Ações</th>
					</tr>
				</thead>
				<tbody>
					@foreach($usuarios as $usuario)
					<tr>
                        <td>{{ $usuario['nome'] }}</td>
                        <td>{{ $usuario['tipo'] }}</td>
                        <td>{{ $usuario['status'] }}</td>
						<td>
							<a href="/admin/repasse/{{ $usuario['id'] }}/detalhes" class="btn btn-primary btn-sm">Detalhes</a>
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
