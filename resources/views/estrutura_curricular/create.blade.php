@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>Estrutura Curricular do Curso</span></h2>
        <hr class="hr" />

        {{ Form::open(['url' => '/admin/estrutura-curricular/salvar']) }}
            @include("estrutura_curricular.formulario")
            <div class="form-group text-right">
                <a href="{{ url()->previous() }}" class="btn btn-default">Voltar</a>
                <a href="{{ url()->current() }}" class="btn btn-default">Cancel</a>
                {{ Form::submit('Salvar Rascunho', ['class' => 'btn btn-primary']) }}
                {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
            </div>
        {{ Form::close() }}
    </div>
@endsection
