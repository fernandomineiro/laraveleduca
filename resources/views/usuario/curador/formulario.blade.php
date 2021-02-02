@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>{{ $modulo['moduloDetalhes']->modulo }}</span></h2>
        <hr class="hr"/>
        @if(Request::is('*/editar'))
            {{ Form::model( $objCurador, ['method' => 'PATCH', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.atualizar', $objCurador->id], 'files' => true, 'autocomplete' => 'off'] ) }}

            <input type="hidden" name="id" value="<?php echo $objCurador->id; ?>" />
            <input type="hidden" name="fk_usuario_id" value="<?php echo $objCurador->fk_usuario_id; ?>" />
            <input type="hidden" name="fk_endereco_id" value="<?php echo $objCurador->fk_endereco_id; ?>" />
            <input type="hidden" name="fk_conta_bancaria_id" value="<?php echo $objCurador->fk_conta_bancaria_id; ?>" />
        @else
            {{ Form::open(['url' => '/admin/'.$modulo['moduloDetalhes']->uri.'/salvar', 'files' => true, 'autocomplete' => 'off']) }}
        @endif
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">

        <div class="row">
            <div class="form-group col-md-4">
                {{ Form::label('Titular *') }}
                <small>(Max 60 caracteres)</small>
                {{ Form::input('text', 'titular_curador', isset($objCurador->titular_curador) ? $objCurador->titular_curador : null, ['class' => 'form-control', '', 'placeholder' => 'Titular', 'maxlength' => 60]) }}
            </div>
            <div class="form-group col-md-4">
                {{ Form::label('Data Nascimento *') }}
                {{ Form::input('text', 'data_nascimento', isset($objCurador->data_nascimento) ? implode('/', array_reverse(explode('-', $objCurador->data_nascimento))) : null, ['class' => 'form-control datepicker', '', 'placeholder' => 'Data Nascimento', 'maxlength' => 10]) }}
            </div>
            <div class="form-group col-md-4">
                {{ Form::label('CPF do Titular *') }}
                {{ Form::input('text', 'cpf', isset($objCurador->cpf) ? $objCurador->cpf : null, ['class' => 'form-control cpf', 'onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'CPF', 'maxlength' => 20]) }}
            </div>
        </div>
        <hr class="hr"/>

        <div class="row">
            <div class="form-group col-md-2">
                {{ Form::label('Gênero') }}
                {{ Form::select('genero', $lista_generos, (isset($objCurador->genero) ? $objCurador->genero : ''), ['class' => 'form-control']) }}
            </div>
            <div class="form-group col-md-3">
                {{ Form::label('Razão social') }}
                <small>(Max 90 caracteres)</small>
                {{ Form::input('text', 'razao_social', isset($objCurador->razao_social) ? $objCurador->razao_social : null, ['class' => 'form-control', '', 'placeholder' => 'Nome', 'maxlength' => 90]) }}
            </div>
            <div class="form-group col-md-3">
                {{ Form::label('Nome Fantasia') }}
                <small>(Max 90 caracteres)</small>
                {{ Form::input('text', 'nome_fantasia', isset($objCurador->nome_fantasia) ? $objCurador->nome_fantasia : null, ['class' => 'form-control', '', 'placeholder' => 'Sobrenome', 'maxlength' => 90]) }}
            </div>
            @if(!Request::is('*/editar'))
                <div class="form-group  col-md-3">
                    {{ Form::label('CNPJ') }}
                    {{ Form::input('text', 'cnpj', null, ['class' => 'form-control cpf', 'onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'CNPJ', 'maxlength' => 20]) }}
                </div>
            @endif
            @if(Request::is('*/editar'))
                <div class="form-group  col-md-3">
                    {{ Form::label('CNPJ *') }}
                    {{ Form::input('text', 'cnpj', $objCurador->cnpj, ['class' => 'form-control cpf', 'readonly' => 'readonly', 'onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'CPF/CNPJ', 'maxlength' => 20]) }}
                </div>
            @endif
        </div>
        <hr class="hr"/>
        <div class="row">
            <div class="form-group col-md-4">
                {{ Form::label('% Share') }}
                {{ Form::input('number', 'share', isset($objCurador->share) ? $objCurador->share : null, ['class' => 'form-control',  'min'=>'0,00', 'max'=>'100', 'step'=>'0.01', '', 'placeholder' => '0,00%']) }}
            </div>
        </div>
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
            <div class="form-group  col-md-4">
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
        <hr class="hr"/>
        <div class="row">

            <div class="form-group  col-md-4">
                {{ Form::label('Telefone Fixo ') }}
                {{ Form::input('text', 'telefone_1', isset($objCurador->telefone_1) ? $objCurador->telefone_1 : null, ['class' => 'form-control telefone','onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'Telefone']) }}
            </div>
            <div class="form-group  col-md-4">
                {{ Form::label('Telefone Celular *') }}
                {{ Form::input('text', 'telefone_2', isset($objCurador->telefone_2) ? $objCurador->telefone_2 : null, ['class' => 'form-control celular','onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'Telefone']) }}
            </div>
        </div>
        <hr class="hr"/>
        <!-- SE USUARIO AINDA NAO TEM CONTA WIRECARD -->
        @if (!is_null($repasse_manual))
            <div class="row">
                <div class="form-group col-md-4">
                    {{ Form::label('Repasse manual') }}
                    {{ Form::checkbox('repasse_manual', 1, $repasse_manual) }}
                </div>
            </div>
        @endif
        <div class="row">
            <div class="form-group col-md-4">
                {{ Form::label('Titular da conta') }}
                <small>(Max 60 caracteres)</small>
                {{ Form::input('text', 'titular', isset($objConta->titular) ? $objConta->titular : null, ['class' => 'form-control', '', 'placeholder' => 'Titular', 'maxlength' => 60]) }}
            </div>
            <div class="form-group  col-md-3">
                {{ Form::label('CPF/CNPJ do Titular') }}
                {{ Form::input('text', 'documento', isset($objConta->documento) ? $objConta->documento : null, ['class' => 'form-control cpf', 'onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'CPF/CNPJ do Titular']) }}
            </div>

            <div class="form-group col-md-4">
                {{ Form::label('Banco') }}
                {{ Form::select('fk_banco_id', $lista_bancos, (isset($objConta->fk_banco_id) ? $objConta->fk_banco_id : 1), ['class' => 'form-control']) }}
            </div>
            <div class="form-group  col-md-2">
                {{ Form::label('Tipo de conta') }}
                <br/>
                {{ Form::radio('tipo_conta' , 'cc', (isset($objConta->fk_banco_id) && $objConta->tipo_conta === 'cc') ? 'checked' : '') }}
                {{ Form::label('Conta Corrente') }}
                <br/>
                {{ Form::radio('tipo_conta', 'cp', (isset($objConta->fk_banco_id) && $objConta->tipo_conta === 'cp') ? 'checked' : '') }}
                {{ Form::label('Conta Poupança') }}
            </div>
            <div class="form-group  col-md-4">
                <div class="row">
                    <div class="form-group  col-md-8">
                        {{ Form::label('Agência') }}
                        {{ Form::input('text', 'agencia', isset($objConta->agencia) ? $objConta->agencia : null, ['class' => 'form-control', '', 'placeholder' => 'Agência']) }}
                    </div>
                    <div class="form-group  col-md-3">
                        {{ Form::label('Digito') }}
                        {{ Form::input('text', 'digita_agencia', isset($objConta->digita_agencia) ? $objConta->digita_agencia : null, ['class' => 'form-control',]) }}
                    </div>
                </div>
            </div>
            <div class="form-group  col-md-4">
                <div class="row">
                    <div class="form-group  col-md-8">
                        {{ Form::label('Conta Corrente') }}
                        {{ Form::input('text', 'conta_corrente', isset($objConta->conta_corrente) ? $objConta->conta_corrente : null, ['class' => 'form-control','', 'placeholder' => 'Conta Corrente']) }}
                    </div>
                    <div class="form-group  col-md-3">
                        {{ Form::label('Digito') }}
                        {{ Form::input('text', 'digita_conta', isset($objConta->digita_conta) ? $objConta->digita_conta : null, ['class' => 'form-control',]) }}
                    </div>
                </div>
            </div>
            <div class="form-group  col-md-2">
                {{ Form::label('Operação') }}
                {{ Form::input('text', 'operacao', isset($objConta->operacao) ? $objConta->operacao : null, ['class' => 'form-control','', 'placeholder' => 'Operação']) }}
            </div>
        </div>

        <hr class="hr"/>
        <div class="row">
            @if(!Request::is('*/editar'))
                <div class="form-group  col-md-4">
                    {{ Form::label('E-mail *') }}
                    {{ Form::input('email', 'email', null, ['class' => 'form-control', '', 'placeholder' => 'E-mail']) }}
                </div>
            @endif
            @if(Request::is('*/editar'))
                <div class="form-group  col-md-4">
                    {{ Form::label('E-mail *') }}
                    {{ Form::input('email', 'email', isset($objUsuario->email) ? $objUsuario->email : null, ['class' => 'form-control','readonly' => 'readonly', '', 'placeholder' => 'E-mail']) }}
                </div>
            @endif
            <div class="form-group col-md-4">
            	<label>Senha * (mínimo de 8 dígitos)</label>@if(Request::is('*/editar')) (Preencher senha somente se deseja mudar!) @endif
                {{ Form::input('password', 'password', null, ['class' => 'form-control', '', 'placeholder' => 'Senha', 'value' => '']) }}
            </div>
            <div class="form-group col-md-4">
                <label>Confirma * (mínimo de 8 dígitos)</label>@if(Request::is('*/editar')) (Preencher senha somente se deseja mudar!) @endif
                {{ Form::input('password', 'password_confirmation', null, ['class' => 'form-control', '', 'placeholder' => 'Senha', 'value' => '']) }}
            </div>
        </div>
        <div class="row">
            <div id="box_upload" class="form-group  col-md-3">
                @if(Request::is('*/editar') && $objUsuario->foto)
                    <div class="form-group">
                        <img src="{{ Storage::disk('s3')->url('files/usuario/' . $objUsuario->foto) }}" height="100"/>
                    </div>
                @endif
                @if(Request::is('*/editar') && !$objUsuario->foto)
                    <div class="form-group">
                        <img src="{{URL::asset('files/default.png')}}" height="100"/>
                    </div>
                @endif
                <br />
                <div class="well">
                    <div id="box_upload" class="row form-group">
                        {{ Form::label('Foto') }}
                        {{ Form::file('foto') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a href="{{ route('admin.curador') }}" class="btn btn-default">Voltar</a>
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary', 'id' => 'botao_salvar']) }}
        </div>
        {{ Form::close() }}
    </div>
    
@endsection

@push('js')
    <script type="text/javascript">
        $(document).ready(function () {
            $('input[name="_token"]').attr('id', 'token');
        });
    </script>
@endpush    
