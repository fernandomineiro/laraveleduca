@extends('layouts.ajax')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h4 class="table">Conteúdo Programático dividido em Aulas</h4></div>
		<div class="col-md-3" style="margin-top: 20px;">
			<!-- <a href="{{ route('admin.proposta_modulo.incluir') }}" class="btn btn-success right margin-bottom-10 open-dialog">Adicionar</a> -->
			<a href="#" class="btn btn-success right margin-bottom-10 open-dialog">Adicionar</a>
		</div>
		<hr class="clear hr" />
		@if(count($proposta_modulo) > 0)
			<table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
				<th>Duração</th>
				<th>Ordem</th>
				<th>Ações</th>
				<tbody>
					@foreach($proposta_modulo as $item)
					<tr>
						<td>{{ $item->duracao }}</td>
						<td>{{ $item->ordem_modulo }}</td>
						<td>
							<a href="/admin/proposta_modulo/{{ $item->id }}/editar" class="btn btn-default btn-sm">Editar</a>

							{{ Form::open(['method' => 'DELETE', 'route' => ['admin.proposta_modulo.deletar', $item->id], 'style' => 'display:inline;']) }}
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
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        </div>
      </div>
    </div>


@endsection

@push('js')
    <script>
        $(document).ready(function () {
            $('.open-dialog').click(function(){
                $( ".modal-dialog" ).dialog({
                    height: 400,
                    width: 600
                });
            });
        });
    </script>
@endpush
