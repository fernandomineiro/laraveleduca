@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>{{ $modulo['moduloDetalhes']->modulo }}</span></h2>
        <a href="{{ route('admin.'.$modulo['moduloDetalhes']->rota)}}" class="label label-default">Voltar</a>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $obj, ['method' => 'PATCH', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.atualizar', $obj->id]] ) }}
        @else
            {{ Form::open(['url' => '/admin/'.$modulo['moduloDetalhes']->uri.'/salvar']) }}
        @endif
        <div class="form-group">
            {{ Form::label('Ação') }}
            {{ Form::input('text', 'descricao', null, ['class' => 'form-control', '', 'placeholder' => 'Título']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Elemento') }}
            {{ Form::select('fk_elemento_id', $elemento, (isset($obj->fk_elemento_id) ? $obj->fk_elemento_id : null), ['class' => 'form-control']) }}
        </div>
        <div class="form-group">
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>
@endsection
