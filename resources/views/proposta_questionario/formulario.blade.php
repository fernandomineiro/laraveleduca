@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Questionário</span></h2>
	    <a href="{{ route('admin.proposta_questionario') }}/{{$id_proposta}}/carregar" class="label label-default">Voltar</a>
		<hr class="hr" />
		
		@if(Request::is('*/editar'))
			{{ Form::model( $proposta_questionario, ['method' => 'PATCH', 'files' => true, 'route' => ['admin.proposta_questionario.atualizar', $proposta_questionario->id]] ) }}
		@else
			{{ Form::open(['url' => '/admin/proposta_questionario/salvar', 'files' => true]) }}
		@endif
			<div class="form-group">
				<p>
					{{ Form::label('Selecionar o tipo de questionário:') }}
				</p>
				@foreach($tipo_questionario as $key => $item )
				<span>
					{{ Form::checkbox('tipo_questionario[]', $key, (isset($proposta_questionario->tipo_questionario) ? in_array( $key, json_decode($proposta_questionario->tipo_questionario) ) : false ) ) }}
					{{ Form::label( $item ) }}
				</span>
				@endforeach
			</div>

			<div class="form-group">
				{{ Form::label('Proposta') }}
				{{ Form::select('fk_proposta', $lista_proposta, (isset($proposta_questionario->fk_proposta) ? $proposta_questionario->fk_proposta : 1 ), ['class' => 'form-control']) }}
			</div>

			<div class="form-group">						
				{{ Form::label('Questão') }}
				{{ Form::input('text', 'questao', null, ['class' => 'form-control', '', 'placeholder' => 'Questão']) }}
			</div>

			@for ($i = 1; $i <= 6; $i++)
			<span>
				<div class="form-group">						
					{{ Form::label('Opção') }} {{$i}}
					{{$value = NULL}}
					@if( null != $proposta_questionario_opcoes  )
						@foreach( $proposta_questionario_opcoes as $pqo => $opcao )
							@if( $opcao->ordem == $i )
								{{$value = $opcao->descricao}}
								break
							@endif
						@endforeach
					@endif
					{{ Form::input('text', 'PropostaQuestionarioOpcao.descricao[]', $value, ['class' => 'form-control', '', 'placeholder' => 'Opção '.$i]) }}
				</div>
			</span>
			@endfor
		<!-- proposta_questionario_opcoes -->

			<div class="form-group">						
				{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
			</div>
		{{ Form::close() }}
	</div>
@endsection