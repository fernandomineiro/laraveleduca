@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Proposta Status</span></h2>
	    <a href="{{ route('admin.propostas_status') }}" class="label label-default">Voltar</a>
		<hr class="hr" />
		
		@if(Request::is('*/editar'))
			{{ Form::model( $propostas_status, ['method' => 'PATCH','route' => ['admin.propostas_status.atualizar', $propostas_status->id]] ) }}
		@else
			{{ Form::open(['url' => '/admin/propostas_status/salvar']) }}
		@endif
			<div class="form-group row">
				<div class="col-sm">
					<div class="col-sm-8">
						{{ Form::label('Título') }}
						{{ Form::input('text', 'titulo', null, ['class' => 'form-control', '', 'placeholder' => 'Título']) }}
					</div>
				</div>
			</div>

			<div class="form-group">
				{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
			</div>
		{{ Form::close() }}
	</div>
@endsection