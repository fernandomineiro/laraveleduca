@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Aulas dos Cursos</span></h2>
	    <a href="{{ route('admin.cursos_modulo') }}" class="label label-default">Voltar</a>
		<hr class="hr" />

		@if(Request::is('*/editar'))
			{{ Form::model( $cursos_modulo, ['method' => 'PATCH', 'route' => ['admin.cursos_modulo.atualizar', $cursos_modulo->id]] ) }}
		@else
			{{ Form::open(['url' => '/admin/cursos_modulo/salvar']) }}
		@endif
			<div class="form-group">
				{{ Form::label('Curso') }}
				{{ Form::select('fk_curso', ['' => 'Selecionar'] + $lista_curso, (isset($cursos_modulo->fk_curso) ? $cursos_modulo->fk_curso : '' ), ['class' => 'form-control']) }}
			</div>
			<div class="form-group">
				{{ Form::label('Professor') }}
				{{ Form::select('fk_professor', $lista_professor, (isset($cursos_modulo->fk_professor) ? $cursos_modulo->fk_professor : null), ['class' => 'form-control']) }}
			</div>
			<div class="form-group">
				{{ Form::label('Título') }}
				{{ Form::input('text', 'titulo', null, ['class' => 'form-control', '', 'placeholder' => 'Título']) }}
			</div>
			<div class="form-group">
				{{ Form::label('Descrição') }}
				{{ Form::textarea('descricao', null, ['class' => 'form-control', '', 'placeholder' => 'Descrição']) }}
			</div>
			<div class="form-group">
				{{ Form::label('Tipo') }}
				{{ Form::radio('tipo_modulo', '1') }} Video
				{{ Form::radio('tipo_modulo', '2') }} Arquivo
			</div>
			<div class="form-group">
				{{ Form::label('Url Vídeo') }}
				{{ Form::input('text', 'url_video', null, ['class' => 'form-control', '', 'placeholder' => 'Url Vídeo']) }}
			</div>
			<div id="box_upload" class="form-group" style="display: ;">
				{{ Form::label('Arquivo') }}
				{{ Form::file('url_arquivo') }}
			</div>
			<div class="form-group">
				{{ Form::label('Carga Horária') }}
				{{ Form::input('text', 'carga_horaria', null, ['class' => 'form-control hora', 'style' => 'width: 120px;']) }}
			</div>
			<div class="form-group">
				<a href="{{ url()->previous() }}" class="btn btn-default">Cancel</a>
				{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
			</div>
		{{ Form::close() }}
	</div>
@endsection
