@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>
        @if(Request::is('*/editar'))
            Atualizar
        @else
            Criar
        @endif

        Avaliaçāo de Curso</span></h2>
        <a href="{{ route('admin.curso_avaliacao') }}" class="label label-default">Voltar</a>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $curso_avaliacao, ['method' => 'PATCH', 'route' => ['admin.curso_avaliacao.atualizar', $curso_avaliacao->id]] ) }}
        @else
            {{ Form::open(['url' => '/admin/curso_avaliacao/salvar']) }}
        @endif

        <div class="form-group">
            {{ Form::label('Curso') }}
            {{ Form::select('fk_curso', $lista_cursos, (isset($curso_avaliacao->fk_curso) ? $curso_avaliacao->fk_curso : null), ['class' => 'form-control']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Descrição') }}
            {{ Form::textarea('descricao', null, ['class' => 'form-control', 'id' => 'ckeditor']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Qtd Estrelas') }}
            {{ Form::select('qtd_estrelas', $lista_estrelas, (isset($curso_avaliacao->qtd_estrelas) ? $curso_avaliacao->qtd_estrelas : null), ['class' => 'form-control']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Status') }}
            {{ Form::select('status', $lista_status, (isset($curso_avaliacao->status) ? $curso_avaliacao->status : null ), ['class' => 'form-control']) }}
        </div>                    

        <div class="form-group">
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>


@endsection
