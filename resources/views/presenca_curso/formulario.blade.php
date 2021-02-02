@extends('layouts.app')
@section('content')
	<div class="box padding20">

		<?php if(isset($agenda_curso['fk_curso'])) : ?>
			<h2 class="table"><span>Vincular à: <?php echo isset($lista_cursos[$agenda_curso['fk_curso']]) ? $lista_cursos[$agenda_curso['fk_curso']] : ''; ?></span></h2>
		<?php else : ?>
			<h2 class="table"><span>Editar</span></h2>
		<?php endif;  ?>
		
		@if(Request::is('*/editar'))
			<a href="/admin/presenca_curso/<?php echo $presenca_agenda_curso->id; ?>/listapresenca" class="label label-default">Voltar</a>
		@else
			<a href="/admin/presenca_curso/<?php echo $id_agenda_curso; ?>/listapresenca" class="label label-default">Voltar</a>
		@endif


		<hr class="hr" />
		
		@if(Request::is('*/editar'))
			{{ Form::model( $presenca_agenda_curso, ['method' => 'PATCH', 'route' => ['admin.presenca_curso.atualizar', $id], 'files' => true] ) }}
		@else
			{{ Form::open(['url' => 'admin/presenca_curso/salvar', 'files' => true]) }}
		@endif
		
			@if(Request::is('*/editar'))
				<input type="hidden" name="fk_agenda" value="<?php echo $presenca_agenda_curso->fk_agenda; ?>" />
				<input type="hidden" name="id" value="<?php echo $id; ?>" />
			@else
				<input type="hidden" name="fk_agenda" value="<?php echo $id_agenda_curso; ?>" />
			@endif		

			<div class="form-group">
				{{ Form::label('Aluno') }}
				{{ Form::select('fk_usuario', $lista_alunos, (isset($presenca_agenda_curso->fk_aluno) ? $presenca_agenda_curso->fk_aluno : null), ['class' => 'form-control']) }}
			</div>

			<div class="form-group">
				{{ Form::label('Presente') }}
				{{ Form::select('presente', ['0' => 'Não', '1' => 'Sim'], (isset($presenca_agenda_curso->presente) ? $presenca_agenda_curso->presente: null), ['class' => 'form-control']) }}
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
