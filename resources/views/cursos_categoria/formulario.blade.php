@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Categorias de Cursos</span></h2>
	    <a href="{{ route('admin.cursos_categoria') }}" class="label label-default">Voltar</a>
		<hr class="hr" />

		@if(Request::is('*/editar'))
			{{ Form::model( $cursos_categoria, ['method' => 'PATCH', 'files' => true, 'route' => ['admin.cursos_categoria.atualizar', $cursos_categoria->id]] ) }}
		@else
			{{ Form::open(['url' => '/admin/cursos_categoria/salvar', 'files' => true]) }}
		@endif
			<div class="form-group">
				{{ Form::label('Status') }}
				{{ Form::select('status', $lista_status, (isset($cursos_categoria->status) ? $cursos_categoria->status : 1), ['class' => 'form-control']) }}
			</div>

			<div class="form-group">
				{{ Form::label('Título') }}
				{{ Form::input('text', 'titulo', null, ['class' => 'form-control', '', 'placeholder' => 'Título']) }}
			</div>
			<?php if(isset($cursos_categoria->icone) && !empty($cursos_categoria->icone)) : ?>
				<img src="{{Storage::disk('s3')->url('files/categoria/icone/' . $cursos_categoria->icone)}}" height="80" class="icone_categora"/>
				<input type="hidden" name="icone_categora" value="0" class="icone_categora">
				<p class="icone_categora">
					Tamanho: {{ $cursos_categoria_icone[0].' X '.$cursos_categoria_icone[1] }} px<br>
					<a href="#" id="close"><span class="glyphicon glyphicon-trash"></span></a>
				</p>
			<?php endif; ?>
			<div class="well">
				<div id="box_upload" class="row form-group">
					{{ Form::label('Ícone da Categoria') }}
					{{ Form::file('icone') }}
                    <small>Dimensões: 400x400</small>
				</div>
			</div>
			<div class="form-group">
				{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
			</div>
		{{ Form::close() }}
	</div>
@endsection

@push('js')
    <script type="text/javascript">
        $(document).ready(function () {
            $('a#close').on('click', function(){
                if(confirm('Deseja realmente excluir?')){
                    $(".icone_categora").remove();
                }
            });
        });
    </script>
@endpush
