@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Lista de Questões</h2></div>
		<div class="col-md-3" style="margin-top: 20px;">
			<a href="{{ route('admin.quiz_questao.incluir', $id_quiz) }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
		</div>
		<hr class="clear hr" />
		
		@if(count($quiz_questoes) > 0)
			<table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
				<th>Titulo</th>
				<th>Qtd de Alternativas</th>
				<th>Resposta Correta</th>
				<th>Ações</th>
				<tbody>
					@foreach($quiz_questoes as $questao)
						<tr>
							<td>{{ $questao->titulo }}</td>
							<td>{{ $questao->qtd_resposta }}</td>
							<td>{{ $questao->resposta_correta }} </td>
							<td>
								<a href="javascript:;" onclick="jQuery('.alternativas_<?php echo $questao->id; ?>').slideToggle()" class="btn btn-primary btn-sm">Ver Alternativas</a>

								<a href="/admin/quiz_questao/{{ $questao->id }}/editar" class="btn btn-default btn-sm">Editar</a>
								
								{{ Form::open(['method' => 'DELETE', 'route' => ['admin.quiz_questao.deletar', $questao->id], 'style' => 'display:inline;']) }}
									<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir?')">Excluir</button>
								{{ Form::close() }}
							</td>
						</tr>

						<?php if(isset($respostas[$questao->id])) : ?>
							@foreach($respostas[$questao->id] as $resposta)
								<tr style="border-left: 6px solid #CCC; background: #EFEFEF; display: none;" class="alternativas_<?php echo $questao->id; ?>">
									<td colspan="4"> <?php echo $resposta->label; ?> - <?php echo $resposta->descricao; ?></td>
								</tr>
							@endforeach
						<?php endif; ?>
					@endforeach
				</tbody>
			</table>
		@else
			<div class="alert alert-info">Nenhum registro no banco!</div>
		@endif

	</div>
@endsection