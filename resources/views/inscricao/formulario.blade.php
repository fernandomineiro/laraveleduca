@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Incrições</span></h2>
	    <a href="{{ route('admin.inscricao') }}" class="label label-default">Voltar</a>
		<hr class="hr" />
		
		@if(Request::is('*/editar'))
			{{ Form::model( $inscricao, ['method' => 'PATCH', 'route' => ['admin.inscricao.atualizar', $inscricao->id]] ) }}
		@else
			{{ Form::open(['url' => '/admin/inscricao/salvar']) }}
		@endif
			<div class="form-group">
				{{ Form::label('Status') }}
				{{ Form::select('status', $lista_status, (isset($inscricao->status) ? $inscricao->status : 1), ['class' => 'form-control']) }}
			</div>	
			<div class="form-group">
				{{ Form::label('Aluno') }}
				{{ Form::select('fk_usuario', $lista_usuarios, (isset($inscricao->fk_usuario) ? $inscricao->fk_usuario : null), ['class' => 'form-control', 'style' => 'width: 50%; min-width: 120px;', 'id' => 'inscricaos_tipo']) }}
			</div>				
			<div class="form-group">
				{{ Form::label('Curso') }}
				{{ Form::select('fk_curso', $lista_cursos, (isset($inscricao->fk_curso) ? $inscricao->fk_curso : null), ['class' => 'form-control', 'style' => 'width: 50%; min-width: 120px;']) }}
			</div>
			<div class="form-group">
				{{ Form::label('% Completo') }}
				{{ Form::select('percentual_completo', $lista_percentual, (isset($inscricao->percentual_completo) ? $inscricao->percentual_completo : 0), ['class' => 'form-control', 'style' => 'width: 160px; min-width: 120px;']) }}
			</div>
			<div class="form-group">						
				<a href="{{ url()->previous() }}" class="btn btn-default">Cancel</a>
				{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
			</div>
		{{ Form::close() }}
	</div>
@endsection