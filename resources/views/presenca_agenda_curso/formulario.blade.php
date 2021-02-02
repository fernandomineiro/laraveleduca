@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Vincular à: <?php echo $nome_trilha; ?></span></h2>

		@if(Request::is('*/editar'))
			<a href="{{ route('admin.trilha_curso', $trilha_curso->fk_trilha) }}" class="label label-default">Voltar</a>
		@else
			<a href="{{ route('admin.trilha_curso', $id_trilha) }}" class="label label-default">Voltar</a>
		@endif
	    
		<hr class="hr" />
		
		@if(Request::is('*/editar'))
			{{ Form::model( $trilha_curso, ['method' => 'PATCH', 'route' => ['admin.trilha_curso.atualizar', $id], 'files' => true] ) }}
		@else
			{{ Form::open(['url' => 'admin/trilha_curso/salvar', 'files' => true]) }}
		@endif
		
			@if(Request::is('*/editar'))
				<input type="hidden" name="fk_trilha" value="<?php echo $trilha_curso->fk_trilha; ?>" />
			@else
				<input type="hidden" name="fk_trilha" value="<?php echo $id_trilha; ?>" />
			@endif		

			<div class="form-group">
				{{ Form::label('Curso') }}
				{{ Form::select('fk_curso', $lista_cursos, (isset($trilha_curso->fk_usuario) ? $trilha_curso->fk_usuario : null), ['class' => 'form-control']) }}
			</div>

			<div class="form-group">						
				@if(Request::is('*/editar'))
					{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
				@else
					{{ Form::submit('Incluir', ['class' => 'btn btn-primary']) }}
				@endif
			</div>			
			
		{{ Form::close() }}				
	</div>
	
@endsection