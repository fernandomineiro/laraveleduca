@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Certificados</h2></div>
		<div class="col-md-3" style="margin-top: 20px;">
			<a href="{{ route('admin.certificados.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
		</div>
		<hr class="clear hr" />

		@if(count($certificados) > 0)
			<table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped dataTable">
                <thead>
                    <th>Projeto</th>
                    <th>Usuário Alteração</th>
                    <th>Título</th>
                    <th>Status</th>
                    <th>Data Alteração</th>
                    <th>Ações</th>
                </thead>
				<tbody>
					@foreach($certificados as $certificado)
					<tr>
						<td>{{ $certificado->faculdade }}</td>
						<td>{{ $certificado->atualizador }}</td>
						<td>{{ $certificado->titulo }}</td>
						<td>{{ $lista_status[$certificado->status] }}</td>
						<td>{{ \Carbon\Carbon::parse($certificado->data_atualizacao)->format('d/m/Y H:i:s') }}</td>
						<td>
							<a href="/admin/certificados/{{ $certificado->id }}/editar" class="btn btn-defawult btn-sm">Editar</a>
							{{ Form::open(['method' => 'DELETE', 'route' => ['admin.certificados.deletar', $certificado->id], 'style' => 'display:inline;']) }}
								<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir?')">Excluir</button>
							{{ Form::close() }}
							<a target="_blank" href="/admin/certificados/{{ $certificado->id }}/generatepdf" class="btn btn-primary btn-sm"><i class="fa fa-download"></i> Gerar PDF</a>
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
