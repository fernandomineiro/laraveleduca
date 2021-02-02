@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>
        @if(Request::is('*/editar'))
                    Atualizar
                @else
                    Criar
                @endif

        Turma de Curso Presencial</span></h2>
        <a href="{{ route('admin.cursoturma') }}" class="label label-default">Voltar</a>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $turma, ['method' => 'PATCH', 'route' => ['admin.cursoturma.atualizar', $turma->id]] ) }}
        @else
            {{ Form::open(['url' => '/admin/curso_turma/salvar']) }}
        @endif

        <div class="form-group">
            {{ Form::label('Nome') }}
            {{ Form::input('text', 'nome', null, ['class' => 'form-control', '', 'placeholder' => 'Nome da turma']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Descrição') }}
            {{ Form::input('text', 'descricao', null, ['class' => 'form-control', '', 'placeholder' => 'Nome da turma']) }}
        </div>

        <div class="form-group">
            {{ Form::label('Curso') }}
            {{ Form::select('fk_curso', $lista_cursos, isset($turma->fk_curso) ? $turma->fk_curso : '', ['class' => 'form-control']) }}
        </div>

        <div class="form-group">
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>


@endsection
