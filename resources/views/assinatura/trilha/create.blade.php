@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>Assinatura</span></h2>
        <a href="admin/assinatura/2/lista" class="label label-default">Voltar</a>
        <hr class="hr" />

        {{ Form::open(['url' => '/admin/assinatura/salvar']) }}
            @include("assinatura.trilha.formulario")
            <div class="form-group text-right">
                <a href="{{ url()->previous() }}" class="btn btn-default">Cancel</a>
                {{ Form::submit('Salvar Rascunho', ['class' => 'btn btn-primary']) }}
                {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
            </div>
        {{ Form::close() }}
    </div>
@endsection
