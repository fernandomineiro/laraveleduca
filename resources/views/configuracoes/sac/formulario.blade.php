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
        <div class="form-group row">
            <div class="col-sm">
                <div class="col-sm-5">
                    {{ Form::label('Projeto') }}
                    {{ Form::select('fk_faculdade_id', $faculdades, (isset($obj->fk_faculdade_id) ? $obj->fk_faculdade : null), ['class' => 'form-control']) }}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-5">
                {{ Form::label('Título') }}
                {{ Form::input('text', 'slug', null, ['class' => 'form-control', '', 'placeholder' => 'Título']) }}
            </div>
            <div class="form-group col-md-5">
                {{ Form::label('Descrição') }}
                {{ Form::input('text', 'descricao', null, ['class' => 'form-control', '', 'placeholder' => 'Descrição']) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-3">
                {{ Form::label('Contato') }}
                {{ Form::input('text', 'remetente', null, ['class' => 'form-control', '', 'placeholder' => 'Contato']) }}
            </div>
            <div class="form-group col-md-3">
                {{ Form::label('E-mail') }}
                {{ Form::input('email', 'email', null, ['class' => 'form-control', '', 'placeholder' => 'email@email.com']) }}
            </div>
            <div class="form-group  col-md-4">
                {{ Form::label('URL SAC') }}
                {{ Form::input('text', 'url_sac', null, ['class' => 'form-control', '', 'placeholder' => 'URL SAC']) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group  col-md-3">
                {{ Form::label('Telegram') }}
                {{ Form::input('text', 'telegram', null, ['class' => 'form-control telefone','onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'Telefone']) }}
            </div>
            <div class="form-group  col-md-3">
                {{ Form::label('Skype') }}
                {{ Form::input('text', 'skype', null, ['class' => 'form-control', '', 'placeholder' => 'Skype']) }}
            </div>
            <div class="form-group  col-md-4">
                {{ Form::label('Hangouts') }}
                {{ Form::input('text', 'hangouts', null, ['class' => 'form-control','', 'placeholder' => 'Hangouts']) }}
            </div>

        </div>
        <div class="row">
            <div class="form-group  col-md-3">
                {{ Form::label('Telefone Fixo *') }}
                {{ Form::input('text', 'telefone_1', null, ['class' => 'form-control telefone','onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'Telefone']) }}
            </div>
            <div class="form-group  col-md-2">
                {{ Form::label('Telefone Celular *') }}
                {{ Form::input('text', 'telefone_2', null, ['class' => 'form-control celular','onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'Telefone']) }}
            </div>
            <div class="form-group  col-md-2">
                {{ Form::label('FAX') }}
                {{ Form::input('text', 'telefone_3', null, ['class' => 'form-control telefone','onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'Telefone']) }}
            </div>
            <div class="form-group  col-md-3">
                {{ Form::label('WhastApp') }}
                {{ Form::input('text', 'whatsapp', null, ['class' => 'form-control telefone','onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'Telefone']) }}
            </div>
        </div>

        <div class="row">
            <div class="col-md-1">
                <button type="button" class="btn btn-danger" onclick="window.location.href='{{ route('admin.'.$modulo['moduloDetalhes']->rota)}}'">Voltar</button>
            </div>
            <div class="">
                {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
            </div>
        </div>
        {{ Form::close() }}
    </div>
@endsection
