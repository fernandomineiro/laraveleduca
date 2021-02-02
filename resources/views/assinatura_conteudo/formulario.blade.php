@extends('layouts.app')

@section('content')
    <div class="box padding20">
        @if(Request::is('*/editar'))
            <h2 class="table"><span>Conteúdo da Assinatura: {{$assinatura->titulo}}</span></h2>
        @else
            <h2 class="table"><span>Vincular Conteúdo à Assinatura: {{$assinatura->titulo}}</span></h2>
        @endif
        <a href="/admin/assinatura_conteudo/{{ $assinatura->id }}/index" class="label label-default">Voltar</a>
        <hr class="hr" />
        <div class="form-group campos-trilha"
             @if(!isset($assinatura->fk_tipo_assinatura) || (isset($assinatura->fk_tipo_assinatura) && $assinatura->fk_tipo_assinatura != 3))
             style="display: none;"
             @else
             style="display: block;"
            @endif>
            @if(Request::is('*/editar'))
                {{ Form::model( $assinatura_conteudo, ['method' => 'PATCH', 'route' => ['admin.assinatura_conteudo.atualizar', $assinatura_conteudo->id]] ) }}
            @else
                {{ Form::open(['method' => 'POST', 'url' => '/admin/assinatura_conteudo/salvar']) }}
            @endif
            {{ Form::label('Trilha de Conhecimento') }}
            {{ Form::select('fk_conteudo', $trilhas, (isset($assinatura_conteudo->fk_conteudo) ? $assinatura_conteudo->fk_conteudo : null), ['class' => 'form-control']) }}
            <div class="form-group">
                {{ Form::label('Status') }}
                {{ Form::select('assinatura', $lista_status, (isset($assinatura_conteudo->assinatura) ? $assinatura_conteudo->assinatura : 1), ['class' => 'form-control', 'style' => 'width: 50%;']) }}
            </div>
            <input type="hidden" name="fk_assinatura" value="{{$assinatura->id}}">
            <div class="form-group">
                <a href="{{ url()->previous() }}" class="btn btn-default">Cancelar</a>
                {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
            </div>
            {{ Form::close() }}
        </div>
        <div class="form-group campos-curso"
             @if(!isset($assinatura->fk_tipo_assinatura) || (isset($assinatura->fk_tipo_assinatura) && $assinatura->fk_tipo_assinatura != 2))
             style="display: none;"
             @else
             style="display: block;"
            @endif>
            @if(Request::is('*/editar'))
                {{ Form::model( $assinatura_conteudo, ['method' => 'PATCH', 'route' => ['admin.assinatura_conteudo.atualizar', $assinatura_conteudo->id]] ) }}
            @else
                {{ Form::open(['method' => 'POST', 'url' => '/admin/assinatura_conteudo/salvar']) }}
            @endif
            {{ Form::label('Cursos')}}
            <div class="form-group">
                {{ Form::label('Selecione um Curso') }}
                {{ Form::select('fk_conteudo', $cursos, (isset($assinatura_conteudo->fk_conteudo) ? $assinatura_conteudo->fk_conteudo : null), ['class' => 'form-control']) }}
            </div>
            <div class="form-group">
                {{ Form::label('Status') }}
                {{ Form::select('assinatura', $lista_status, (isset($assinatura_conteudo->assinatura) ? $assinatura_conteudo->assinatura : 1), ['class' => 'form-control', 'style' => 'width: 50%;']) }}
            </div>
            <input type="hidden" name="fk_assinatura" value="{{$assinatura->id}}">
            <div class="form-group">
                <a href="{{ url()->previous() }}" class="btn btn-default">Cancelar</a>
                {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
            </div>
            {{ Form::close() }}
        </div>
    </div>
@endsection
