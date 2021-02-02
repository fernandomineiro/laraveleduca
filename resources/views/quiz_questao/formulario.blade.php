@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Questionário</span></h2>

		<hr class="hr" />
		
		@if(Request::is('*/editar'))
			{{ Form::model( $quiz_questao, ['method' => 'PATCH', 'route' => ['admin.quiz_questao.atualizar', $quiz_questao->id]] ) }}
		@else
			{{ Form::open(['url' => '/admin/quiz_questao/salvar']) }}
		@endif

			@if(Request::is('*/editar'))
				<a href="/quiz_questao/{{ $quiz_questao->fk_quiz }}" class="label label-default">Voltar</a>
				<input type="hidden" name="fk_quiz" value="<?php echo $quiz_questao->fk_quiz; ?>" />
			@else
				<a href="/quiz_questao/{{ $id_quiz }}" class="label label-default">Voltar</a>
				<input type="hidden" name="fk_quiz" value="<?php echo $id_quiz; ?>" />
			@endif		

			<div class="form-group">
				{{ Form::label('Status') }}
				{{ Form::select('status', $lista_status, (isset($quiz_questao->status) ? $quiz_questao->status : 1), ['class' => 'form-control', 'style' => 'width: 50%; min-width: 120px;']) }}
			</div>		
			<div class="form-group">
				{{ Form::label('Questão') }}
				{{ Form::input('text', 'titulo', null, ['class' => 'form-control', '', 'placeholder' => 'Questão']) }}
			</div>

			<hr />
			<div class="well">
				<h3> Alternativas: </h3>
				<?php foreach($alternativas as $k => $alternativa) : ?>
					<div class="form-group">
						{{ Form::label($alternativa) }}
						{{ Form::input('text', 'op.' . $k, isset($lista_respostas[$k]) ? $lista_respostas[$k] : '', ['class' => 'form-control', '', 'placeholder' => $alternativa]) }}
					</div>
					<hr />
				<?php endforeach; ?>
			</div>
			<div class="well" style="background: #acba93;">
				<div class="form-group">
					{{ Form::label('Resposta Correta: ') }}
					{{ Form::select('resposta_correta', $alternativas, (isset($quiz_questao->resposta_correta) ? $quiz_questao->resposta_correta : 1), ['class' => 'form-control', 'style' => 'width: 50%; min-width: 120px;']) }}
				</div>
			</div>

			<div class="form-group">						
				<a href="{{ url()->previous() }}" class="btn btn-default">Cancel</a>
				{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
			</div>
			
		{{ Form::close() }}
	</div>
@endsection