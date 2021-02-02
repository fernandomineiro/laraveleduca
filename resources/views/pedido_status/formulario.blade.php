@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Pedido Status</span></h2>
	    <a href="{{ route('admin.pedido_status') }}" class="label label-default">Voltar</a>
		<hr class="hr" />
		
		@if(Request::is('*/editar'))
			{{ Form::model( $pedido_status, ['method' => 'PATCH','route' => ['admin.pedido_status.atualizar', $pedido_status->id]] ) }}
		@else
			{{ Form::open(['url' => '/admin/pedido_status/salvar']) }}

		@endif
			<div class="form-group row">
				<div class="col-sm">
					<div class="col-sm-8">
						{{ Form::label('Título') }}
						{{ Form::input('text', 'titulo', null, ['class' => 'form-control', '', 'placeholder' => 'Título']) }}
					</div>
				</div>
			</div>
			<div class="form-group row">
				<div class='col-sm'>
				<div class="col-sm-8">
						{{ Form::label('Cor') }}
						{{ Form::input('color', 'cor', (!empty($pedido_status->cor) ? $pedido_status->cor : '#ff0000'), ['class' => 'form-control', 'style' => 'width: 25%;', 'placeholder' => 'Cor']) }}
					</div>
				</div>
			</div>		

			<div class="form-group">
				{{ Form::label('Status') }}
				{{ Form::select('status', $lista_status, (isset($pedido_status->status) ? $pedido_status->status : 1), ['class' => 'form-control', 'style' => 'width: 25%;']) }}
			</div>				

			<div class="form-group">
				{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
			</div>
		{{ Form::close() }}
	</div>
@endsection