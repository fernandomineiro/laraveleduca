@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Questionário</span></h2>
	    <a href="{{ route('admin.quiz') }}" class="label label-default">Voltar</a>
		<hr class="hr" />
		
		@if(Request::is('*/editar'))
			{{ Form::model( $quiz, ['method' => 'PATCH', 'route' => ['admin.quiz.atualizar', $quiz->id]] ) }}
		@else
			{{ Form::open(['url' => '/admin/quiz/salvar']) }}
		@endif
			<div class="form-group">
				{{ Form::label('Status') }}
				{{ Form::select('status', $lista_status, (isset($quiz->status) ? $quiz->status : 1), ['class' => 'form-control', 'style' => 'width: 50%; min-width: 120px;']) }}
			</div>		
			<div class="form-group">
				{{ Form::label('Curso') }}
				{{ Form::select('fk_curso', $lista_cursos, (isset($quiz->fk_curso) ? $quiz->fk_curso : null), ['class' => 'form-control', 'id' => 'cursoSelection', 'style' => 'width: 50%; min-width: 120px;']) }}
			</div>
			<div class="form-group">
				{{ Form::label('Percentual p/ Aprovação') }}
				{{ Form::select('percentual_acerto', $lista_percentual, (isset($quiz->percentual_acerto) ? $quiz->percentual_acerto : null), ['class' => 'form-control', 'style' => 'width: 160px; min-width: 120px;']) }}
			</div>
			<div class="form-group">						
				<a href="{{ url()->previous() }}" class="btn btn-default">Cancel</a>
				{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
			</div>
		{{ Form::close() }}
	</div>
@endsection

@push('js')
{{--<script src="{{ asset('js/jquery.js') }}"></script>--}}
    <script type="text/javascript">
        $(document).ready(function() {
            document.getElementById('cursoSelection').options[0].setAttribute("disabled", true);
        })
    </script>
@endpush
