@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>Assinatura</span></h2>
        <hr class="hr" />

        {{ Form::model( $assinatura, ['method' => 'PATCH', 'route' => ['admin.assinatura.atualizar', $assinatura->id]] ) }}
            @include("assinatura.full.formulario")
            <div class="form-group text-right">
                <a href="{{ url()->previous() }}" class="btn btn-default">Voltar</a>
                <a href="{{ url()->current() }}" class="btn btn-default">Cancel</a>
                {{Form::hidden('fk_tipo_assinatura', null, [ 'id' => 'fk_tipo_assinatura'])}}
                {{ Form::submit('Salvar Rascunho', ['class' => 'btn btn-primary']) }}
                {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
            </div>
        {{ Form::close() }}
    </div>
@endsection
