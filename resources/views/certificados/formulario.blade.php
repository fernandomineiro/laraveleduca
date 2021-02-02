@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table">
			@if(Request::is('*/editar'))
			<span>Editar Certificado</span>
			@else
			<span>Cria novo Design</span>
			@endif
		</h2>
	    <a href="{{ route('admin.certificados') }}" class="label label-default">Voltar</a>
		<hr class="hr" />

		@if(Request::is('*/editar'))
			{{ Form::model( $certificado, ['method' => 'PATCH','files' => true, 'route' => ['admin.certificados.atualizar', $certificado->id]] ) }}
		@else
			{{ Form::open(['url' => '/admin/certificados/salvar','files' => true]) }}
		@endif
			<div class="form-group">
				{{ Form::label('Título') }}
				{{ Form::input('text', 'titulo', null, ['class' => 'form-control', '', 'placeholder' => 'Título']) }}
			</div>
			<div class="form-group">
				{{ Form::label('Projeto') }}
				{{ Form::select('fk_faculdade', $faculdades, (isset($certificado->fk_faculdade) ? $certificado->fk_faculdade : 0), ['class' => 'form-control']) }}
			</div>
			<div class="form-group">
				{{ Form::label('Status') }}
				{{ Form::select('status', $lista_status, (isset($certificado->status) ? $certificado->status : 2 ), ['class' => 'form-control']) }}
			</div>

			<div class="form-group">
				{{ Form::label('Tipo de Certificado') }}
				{{ Form::select('tipo', $tipos, (isset($certificado->tipo) ? $certificado->tipo : 1), ['class' => 'form-control']) }}
			</div>

			<div class="form-group">
				{{Form::label('layout', 'Layout do Certificado',['class' => 'control-label'])}}
				@if( Request::is('*/editar') && $certificado->layout )
				<div>
					<img src="{{URL::asset('files/certificado/' . $certificado->layout)}}" height="300"/>
					<br />
					<a href="javascript:;" onclick="$('#box_upload').show();" class="label label-warning">Alterar Layout do Certificado</a>
				</div>
				@endif
				<div id="box_upload" class="form-group" style="display: {{ isset($certificado->layout) ? 'none' : 'block' }}">
					{{Form::file('layout', ['accept' => 'image/jpeg , image/jpg, image/gif, image/png' ])}}
				</div>
			</div>

			<div class="form-group">
				{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
			</div>
		{{ Form::close() }}
	</div>
@endsection
