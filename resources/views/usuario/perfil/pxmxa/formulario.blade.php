@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>{{ $modulo['moduloDetalhes']->menu .' '.$modulo['moduloDetalhes']->modulo }}</span></h2>
        <a href="{{ route('admin.'.$modulo['moduloDetalhes']->rota) }}" class="label label-default">Voltar</a>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $obj, ['method' => 'PATCH', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.atualizar', $obj->id]] ) }}
        @else
            {{ Form::open(['url' => '/admin/'.$modulo['moduloDetalhes']->uri.'/salvar']) }}
        @endif
        <div class="form-group">
            {{ Form::label('Perfil') }}
            {{ Form::select('fk_perfil_id', $perfil, (isset($obj->fk_perfil_id) ? $obj->fk_perfil_id : 1), ['class' => 'form-control']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Módulo - Ação') }}
            {{ Form::select('fk_modulo_acoes_id', $modulosAcoes, (isset($obj->fk_modulo_acoes_id) ? $obj->fk_modulo_acoes_id : 1), ['class' => 'form-control']) }}
        </div>
        <div class="form-group">
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>
@endsection
