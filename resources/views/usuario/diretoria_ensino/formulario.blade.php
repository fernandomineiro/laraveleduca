@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>{{ $modulo['moduloDetalhes']->menu .' '.$modulo['moduloDetalhes']->modulo }}</span></h2>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $obj, ['method' => 'PATCH', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.atualizar', $obj->id], 'files' => true] ) }}
        @else
            {{ Form::open(['url' => 'admin/'.$modulo['moduloDetalhes']->uri.'/salvar', 'files' => true]) }}
        @endif

        <div class="form-group">
            {{ Form::label('Projeto') }}
            {{ Form::select('fk_faculdade', $faculdades, (isset($obj->fk_faculdade) ? $obj->fk_faculdade : null), ['class' => 'form-control']) }}
        </div>
        <div class="row">
            <div class="form-group  col-md-6">
                {{ Form::label('Nome') }}
                {{ Form::input('text', 'nome', null, ['class' => 'form-control', 'autofocus', 'placeholder' => 'Nome']) }}
            </div>
        </div>
        <div class="form-group">
            <a href="{{ route('admin.'.$modulo['moduloDetalhes']->rota)}}" class="btn btn-default">Voltar</a>
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>
@endsection
