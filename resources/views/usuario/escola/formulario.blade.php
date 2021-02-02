@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>{{$modulo['moduloDetalhes']->modulo}}</span></h2>
        <hr class="hr"/>
        @if(Request::is('*/editar'))
            {{ Form::model( $objFaculdade, ['method' => 'PATCH', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.atualizar', $objFaculdade->id], 'files' => true] ) }}

            <input type="hidden" name="fk_usuario_id" value="<?php echo $objFaculdade->fk_usuario_id; ?>" />
            <input type="hidden" name="id" value="<?php echo $objFaculdade->id; ?>" />
            <input type="hidden" name="fk_endereco_id" value="<?php echo $objFaculdade->fk_endereco_id; ?>" />
        @else
            {{ Form::open(['url' => '/admin/'.$modulo['moduloDetalhes']->uri.'/salvar', 'files' => true]) }}
        @endif

        <div class="row">
            <div class="form-group col-md-6">
                {{ Form::label('Razão Social *') }}
                <small>(Max 90 caracteres)</small>
                {{ Form::input('text', 'razao_social', null, ['class' => 'form-control', '', 'placeholder' => 'Razão Social', 'maxlength' => 90]) }}
            </div>

            @if(!Request::is('*/editar'))
                <div class="form-group  col-md-3">
                    {{ Form::label('CNPJ') }}
                    {{ Form::input('text', 'cnpj', null, ['class' => 'form-control cnpj', 'onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'CNPJ']) }}
                </div>
            @endif
            @if(Request::is('*/editar'))
                <div class="form-group  col-md-3">
                    {{ Form::label('CNPJ') }}
                    {{ Form::input('text', 'cnpj', null, ['class' => 'form-control cnpj', 'onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'CNPJ']) }}
                </div>
            @endif
        </div>
        <div class="row">
            <div class="form-group col-md-5">
                {{ Form::label('Projeto') }}
                {{ Form::select('fk_faculdade', $faculdades, (isset($obj->fk_faculdade) ? $obj->fk_faculdade : null), ['class' => 'form-control']) }}
            </div>
            <div class="form-group  col-md-4">
                {{ Form::label('Diretoria de ensino') }}
                {{ Form::select('fk_diretoria_ensino', $diretoriaEnsino, (isset($obj->fk_diretoria_ensino) ? $obj->fk_diretoria_ensino : null), ['class' => 'form-control']) }}
            </div>
        </div>

        <hr class="hr"/>
        <div class="row">
            <div class="form-group col-md-2">
                {{ Form::label('CEP *') }}
                {{ Form::input('text', 'cep', isset($objEndereco->cep) ? $objEndereco->cep : null, ['class' => 'form-control cep', '', 'placeholder' => 'CEP' , 'id' => 'cep']) }}
            </div>
            <div class="form-group  col-md-4">
                {{ Form::label('Logradouro *') }}
                <small>(Max 90 caracteres)</small>
                {{ Form::input('text', 'logradouro', isset($objEndereco->logradouro) ? $objEndereco->logradouro : null, ['class' => 'form-control logradouro', '', 'placeholder' => 'Logradouro', 'id' => 'logradouro', 'maxlength' => 90]) }}
            </div>
            <div class="form-group col-md-2">
                {{ Form::label('Nº *') }}
                {{ Form::input('text', 'numero', isset($objEndereco->numero) ? $objEndereco->numero : null, ['class' => 'form-control numero', '', 'placeholder' => 'Nº', 'maxlength' => 6]) }}
            </div>
            <div class="form-group  col-md-4">
                {{ Form::label('Complemento') }}
                <small>(Max 40 caracteres)</small>
                {{ Form::input('text', 'complemento', isset($objEndereco->complemento) ? $objEndereco->complemento : null, ['class' => 'form-control complemento', '', 'placeholder' => 'Complemento', 'id' => 'complemento', 'maxlength' => 40]) }}
            </div>
            <div class="form-group  col-md-5">
                {{ Form::label('Bairro *') }}
                <small>(Max 50 caracteres)</small>
                {{ Form::input('text', 'bairro', isset($objEndereco->bairro) ? $objEndereco->bairro : null, ['class' => 'form-control bairro', '', 'placeholder' => 'Bairro', 'id' => 'bairro', 'maxlength' => 50]) }}
            </div>
            <div class="form-group  col-md-3">
                {{ Form::label('Estado *') }}
                <div id="estados">
                    {{ Form::select('fk_estado_id', ['' => 'Selecione'] + $estados, (isset($objEndereco->fk_estado_id) ? $objEndereco->fk_estado_id : null), ['class' => 'form-control']) }}
                </div>
            </div>
            <div class="form-group  col-md-4">
                {{ Form::label('Cidade *') }}
                <div id="cidades">
                    {{ Form::select('fk_cidade_id', ['' => 'Selecione'] + $cidades, (isset($objEndereco->fk_cidade_id) ? $objEndereco->fk_cidade_id : null), ['class' => 'form-control']) }}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group  col-md-2">
                {{ Form::label('Telefone Fixo') }}
                {{ Form::input('text', 'telefone_1', null, ['class' => 'form-control telefone','onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'Telefone']) }}
            </div>
            <div class="form-group  col-md-2">
                {{ Form::label('Telefone Celular *') }}
                {{ Form::input('text', 'telefone_2', null, ['class' => 'form-control celular', 'onkeypress'=>'return onlyNumbers(event)','', 'placeholder' => 'Celular']) }}
            </div>
            <div class="form-group col-md-3">
                {{ Form::label('Url da instância') }}
                {{ Form::input('url', 'url', null, ['class' => 'form-control', 'placeholder' => 'Url da instância']) }}
            </div>
        </div>

        <hr class="hr"/>

        <div class="form-group">
            <a href="{{ route('admin.escola') }}" class="btn btn-default">Voltar</a>
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary', 'id' => 'botao_salvar']) }}
        </div>
        {{ Form::close() }}
    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            $('input[name="_token"]').attr('id', 'token');
        });
    </script>
@endsection
