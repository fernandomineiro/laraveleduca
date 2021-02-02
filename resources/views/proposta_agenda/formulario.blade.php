@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Agenda de Proposta</span></h2>
	    <a href="{{ route('admin.proposta_agenda') }}" class="label label-default">Voltar</a>
		<hr class="hr" />
		
		@if(Request::is('*/editar'))
			{{ Form::model( $proposta_agenda, ['method' => 'PATCH', 'route' => ['admin.proposta_agenda.atualizar', $proposta_agenda->id]] ) }}
		@else
			{{ Form::open(['url' => '/admin/proposta_agenda/salvar']) }}
		@endif

			<div class="form-group">
				{{ Form::label('Proposta') }}
				{{ Form::select('fk_proposta', $lista_propostas, (isset($proposta_agenda->fk_proposta) ? $proposta_agenda->fk_proposta : null), ['class' => 'form-control']) }}
			</div>

			<div class="form-group">
				{{ Form::label('Data da Aula') }}
				{{ Form::input('text', 'data_aula', null, ['class' => 'form-control datepicker', '', 'placeholder' => 'Data Aula']) }}
			</div>
			<div class="form-group">
				{{ Form::label('Hora Início') }}
				{{ Form::input('text', 'inicio', null, ['class' => 'form-control', '', 'placeholder' => 'Hora Início']) }}
			</div>
			<div class="form-group">
				{{ Form::label('Hora Fim') }}
				{{ Form::input('text', 'termino', null, ['class' => 'form-control', '', 'placeholder' => 'Hora Fim']) }}
			</div>
			<div class="form-group">						
				{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
			</div>
		{{ Form::close() }}
	</div>
@endsection