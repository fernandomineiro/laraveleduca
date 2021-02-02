@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Banco</span></h2>
	    <a href="{{ route('admin.banco') }}" class="label label-default">Voltar</a>
		<hr class="hr" />
		
		@if(Request::is('*/editar'))
			{{ Form::model( $banco, ['method' => 'PATCH', 'route' => ['admin.banco.atualizar', $banco->id]] ) }}
		@else
			{{ Form::open(['url' => '/admin/banco/salvar']) }}
		@endif
			<div class="form-group">
				{{ Form::label('Status') }}
				{{ Form::select('status', $lista_status, (isset($banco->status) ? $banco->status : 1), ['class' => 'form-control']) }}
			</div>
			<div class="form-group">						
				{{ Form::label('Título') }}
				{{ Form::input('text', 'titulo', null, ['class' => 'form-control', '', 'placeholder' => 'Título']) }}
			</div>
			<div class="form-group">						
				{{ Form::label('Número') }}
				{{ Form::input('text', 'numero', null, ['class' => 'form-control', '', 'placeholder' => 'Número']) }}
			</div>			
			<div class="form-group">						
				{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
			</div>
		{{ Form::close() }}
	</div>
@endsection