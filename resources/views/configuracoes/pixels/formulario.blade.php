@extends('layouts.app')
@section('content')

    <div class="box padding20">
        <h2 class="table"><span>{{ $modulo['moduloDetalhes']->modulo }}</span></h2>
        <a href="{{ route('admin.'.$modulo['moduloDetalhes']->rota)}}" class="label label-default">Voltar</a>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $obj, ['method' => 'PATCH', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.atualizar', $obj->id], 'files' => true] ) }}
        @else
            {{ Form::open(['url' => 'admin/'.$modulo['moduloDetalhes']->uri.'/salvar', 'files' => true]) }}
        @endif
        <div class="form-group row">
            <div class="col-sm">
                <div class="col-sm-5">
                    {{ Form::label('Projeto') }}<br>
                    <small>(Projeto ao qual o banner pertence)</small>
                    {{ Form::select('fk_faculdade_id', $faculdades, (isset($obj->fk_faculdade_id) ? $obj->fk_faculdade_id : null), ['class' => 'form-control']) }}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-5">
                {{ Form::label('ID View') }}<br>
                <small>(ID de visualização vinculado a conta do Analytics. Max 32 caracteres)</small>
                {{ Form::input('text', 'id_visualizacao', null, ['class' => 'form-control', '', 'placeholder' => 'ID View', 'maxlength' => 32]) }}
            </div>
            <div class="form-group col-md-5">
                {{ Form::label('Tipo') }}<br>
                <small>(Tipo de pixel utilizado)</small>
                {{ Form::select('tipo_pixel', ["facebook"=>"Facebook", "google"=>"Google"],(isset($obj->tipo_pixel) ? $obj->tipo_pixel : 'facebook'), ['class' => 'form-control']) }}
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-10">
                {{ Form::label('Pixel') }}<br>
                <small>(Código da conta do google analytics)</small>
                {{ Form::textarea('pixel', null, ['class' => 'form-control', 'id' => 'ckeditor']) }}
            </div>
        </div>

        <div class="form-group">
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>
@endsection
