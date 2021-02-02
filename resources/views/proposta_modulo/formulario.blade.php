@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Proposta Modulo</span></h2>
	    <a href="{{ route('admin.proposta_modulo') }}/{{$id_proposta}}/carregar" class="label label-default">Voltar</a>
		<hr class="hr" />
		
		@if(Request::is('*/editar'))
			{{ Form::model( $proposta_modulo, ['method' => 'PATCH', 'files' => true, 'route' => ['admin.proposta_modulo.atualizar', $proposta_modulo->id]] ) }}
		@else
			{{ Form::open(['url' => '/admin/proposta_modulo/salvar', 'files' => true]) }}
		@endif
			<div class="form-group">
				{{ Form::label('Proposta') }}
				{{ Form::select('fk_proposta', $lista_proposta, (isset($proposta_modulo->fk_proposta) ? $proposta_modulo->fk_proposta : 1 ), ['class' => 'form-control']) }}
			</div>

			<div class="form-group">						
				{{ Form::label('Ordem') }}
				{{ Form::input('text', 'ordem_modulo', null, ['class' => 'form-control', '', 'placeholder' => 'Ordem']) }}
			</div>

			<div class="form-group">						
				{{ Form::label('URL Vídeo') }}
				{{ Form::input('text', 'url_video', null, ['class' => 'form-control', '', 'placeholder' => 'URL Vídeo']) }}
			</div>
			
			<div class='form-group'>
				{{ Form::label('Arquivo') }}
				@if( Request::is('*/editar') && $proposta_modulo->arquivo )
				<div class="form-group">
					<img src="{{URL::asset('files/proposta_modulo/arquivo/' . $proposta_modulo->arquivo)}}" height="100"/>
					<br />
					<a href="javascript:;" onclick="$('#box_upload2').show();" class="label label-warning">Alterar Arquivo</a>
				</div>
				@endif
				<div id="box_upload2" class="form-group" style="display: {{ isset($proposta_modulo->arquivo) ? 'none' : 'block' }}">
					{{Form::file('arquivo')}}
				</div>
			</div>

			<div class="form-group">						
				{{ Form::label('Duração') }}
				{{ Form::input('text', 'duracao', null, ['class' => 'form-control', '', 'placeholder' => 'Duração', 'data-mask'=> "00:00:00" ]) }}
			</div>

			<div class="form-group">						
				{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
			</div>
		{{ Form::close() }}
	</div>
@endsection