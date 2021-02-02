@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>{{  $modulo['moduloDetalhes']->modulo }}</span></h2>
        <a href="{{ route('admin.'.$modulo['moduloDetalhes']->rota)}}" class="label label-default">Voltar</a>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $tipoParceiro, ['method' => 'PATCH', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.atualizar', $tipoParceiro->id]] ) }}
        @else
            {{ Form::open(['url' => '/admin/'.$modulo['moduloDetalhes']->uri.'/salvar']) }}
        @endif
        <div class="form-group">
            {{ Form::label('Descrição') }}
            {{ Form::input('text', 'descricao', null, ['class' => 'form-control', '', 'placeholder' => 'Descrição']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Status') }}
            {{ Form::select('status', $lista_status, (isset($tipoParceiro->status) ? $tipoParceiro->status : null), ['class' => 'form-control']) }}
        </div>

        <div class="form-group">
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>
@endsection
