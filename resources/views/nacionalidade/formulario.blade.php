@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Nacionalidade</span></h2>
	    <a href="{{ route('admin.nacionalidade') }}" class="label label-default">Voltar</a>
		<hr class="hr" />
		
		@if(Request::is('*/editar'))
			{{ Form::model( $nacionalidade, ['method' => 'PATCH', 'route' => ['admin.nacionalidade.atualizar', $nacionalidade->id]] ) }}
		@else
			{{ Form::open(['url' => '/admin/nacionalidade/salvar']) }}
		@endif
			<div class="form-group">
				{{ Form::label('Título') }}
				{{ Form::input('text', 'titulo', null, ['class' => 'form-control', '', 'placeholder' => 'Título']) }}
			</div>
			<div class="form-group">						
				{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
			</div>
		{{ Form::close() }}
	</div>
@endsection