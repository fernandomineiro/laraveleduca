@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Proposta</span></h2>
		<a href="{{ route('admin.proposta') }}" class="btn btn-default">Voltar</a>

		<hr class="hr" />
		
		@if(Request::is('*/editar'))
			{{ Form::model( $proposta, ['method' => 'PATCH', 'route' => ['admin.proposta.atualizar', $proposta->id]] ) }}
		@else
			{{ Form::open(['url' => '/admin/proposta/salvar']) }}
		@endif
			<div class="form-group">						
				{{ Form::label('Título') }}
				{{ Form::input('text', 'titulo', null, ['class' => 'form-control', '', 'placeholder' => 'Título']) }}
			</div>

			<div class="form-group">						
				{{ Form::label('Descrição') }}
				{!! Form::textarea('descricao',null,['class'=>'form-control', 'rows' => 4, 'cols' => 40]) !!}

			</div>

			<div class="form-group">						
				{{ Form::label('Local') }}
				{{ Form::input('text', 'local', null, ['class' => 'form-control', '', 'placeholder' => 'Local']) }}
			</div>

			<div class="form-group">
				{{ Form::label('Status') }}
				{{ Form::select('fk_proposta_status', $lista_status, (isset($proposta->fk_proposta_status) ? $proposta->fk_proposta_status : 1 ), ['class' => 'form-control']) }}
			</div>

			<div class="form-group">
				{{ Form::label('Professor') }}
				{{ Form::select('fk_professor', $lista_professor, (isset($proposta->fk_professor) ? $proposta->fk_professor : null), ['class' => 'form-control']) }}
			</div>

			<div class="form-group">						
				{{ Form::label('URL Vídeo') }}
				{{ Form::input('text', 'url_video', null, ['class' => 'form-control', '', 'placeholder' => 'URL Vídeo']) }}
			</div>
			

			<div class="form-group">						
				{{ Form::label('Duração') }}
				{{ Form::input('text', 'duracao_total', null, ['class' => 'form-control', '', 'placeholder' => 'Duração', 'data-mask'=> "00:00:00"]) }}
			</div>

			<div class="form-group">						
				{{ Form::label('Sugestão de preço') }}
				{{ Form::input('text', 'sugestao_preco', null, ['class' => 'form-control money', '', 'placeholder' => 'Duração']) }}
			</div>

			<div class="form-group">						
				{{ Form::label('Sugestão de Categoria') }}
				{{ Form::input('text', 'sugestao_categoria', null, ['class' => 'form-control', '', 'placeholder' => 'Sugestão de Categoria']) }}
			</div>

			<div class="form-group">						
				{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
				<a href="{{ route('admin.proposta') }}" class="btn btn-default btn-sm">Voltar</a>
			</div>
		{{ Form::close() }}
	</div>
@endsection