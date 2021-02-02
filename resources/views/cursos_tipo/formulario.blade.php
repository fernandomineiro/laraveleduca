@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Tipos de Cursos</span></h2>
	    <a href="{{ route('admin.cursos_tipo') }}" class="label label-default">Voltar</a>
		<hr class="hr" />
		
		@if(Request::is('*/editar'))
			{{ Form::model( $cursos_tipo, ['method' => 'PATCH', 'route' => ['admin.cursos_tipo.atualizar', $cursos_tipo->id]] ) }}
		@else
			{{ Form::open(['url' => '/admin/cursos_tipo/salvar']) }}
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