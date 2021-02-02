@extends('layouts.app')
@section('content')
<a href="{{ route('admin.proposta') }}" class="btn btn-primary btn-sm"><i class="fa fa-reply"></i> Voltar para Proposta</a>
<div class="">
	<div class="col-md-9"><h2 class="table">Questionários</h2></div>
	<div class="col-md-3" style="margin-top: 20px;">
		@if($id_proposta)
		<a href="{{ route('admin.proposta_questionario') }}/{{$id_proposta}}/vincular" class="btn btn-success right margin-bottom-10">Adicionar</a>
		@else
		<a href="{{ route('admin.proposta_questionario.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
		@endif
	</div>
	@if(count($proposta_questionario) > 0)

		<div>
			<table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
				<th>Proposta</th>
				<th>Questão</th>
				<th>Ações</th>
				<tbody>
					@foreach($proposta_questionario as $item)
					<tr>
						<td>{{ isset($lista_proposta[$item->fk_proposta]) ? $lista_proposta[$item->fk_proposta] : NULL }}</td>
						<td>{{ $item->questao }}</td>
						<td>
							<a href="{{ route('admin.proposta_questionario') }}/{{$item->id}}/editar" class="btn btn-default btn-sm">Editar</a>
							{{ Form::open(['method' => 'DELETE', 'route' => ['admin.proposta_questionario.deletar', $item->id], 'style' => 'display:inline;']) }}
								<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir?')">Excluir</button>
							{{ Form::close() }}
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>

	@endif
</div>
@if(count($proposta_questionario) == 0)
<br />
<br />
<br /> 
<div class="alert alert-info">Nenhum registro no banco!</div>
@endif

@endsection