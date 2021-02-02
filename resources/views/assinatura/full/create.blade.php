@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>Assinatura</span></h2>
        <hr class="hr" />

        {{ Form::open(['url' => '/admin/assinatura/salvar']) }}
            @include("assinatura.full.formulario")
            <div class="form-group text-right">
                <input type="hidden" name="fk_tipo_assinatura" id="fk_tipo_assinatura" value="<?php echo $tipo_assinatura; ?>" />
                <a href="{{ url()->previous() }}" class="btn btn-default">Voltar</a>
                <a href="{{ url()->current() }}" class="btn btn-default">Cancel</a>
                {{ Form::submit('Salvar Rascunho', ['class' => 'btn btn-primary']) }}
                {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
            </div>
        {{ Form::close() }}
    </div>
@endsection
