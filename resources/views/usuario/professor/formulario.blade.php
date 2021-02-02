@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>{{ $modulo['moduloDetalhes']->modulo }}</span></h2>
        <hr class="hr"/>
        @if(Request::is('*/editar'))
            {{ Form::model( $objProfessor, ['method' => 'PATCH', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.atualizar', $objProfessor->id], 'files' => true, 'autocomplete' => 'off'] ) }}

            <input type="hidden" name="fk_usuario_id" value="<?php echo $objProfessor->fk_usuario_id; ?>"/>
            <input type="hidden" name="id" value="<?php echo $objProfessor->id; ?>" />
            <input type="hidden" name="fk_endereco_id" value="<?php echo $objProfessor->fk_endereco_id; ?>" />
            <input type="hidden" name="fk_conta_bancaria_id" value="<?php echo $objProfessor->fk_conta_bancaria_id; ?>" />
        @else
            {{ Form::open(['url' => '/admin/'.$modulo['moduloDetalhes']->uri.'/salvar', 'files' => true, 'autocomplete' => 'off']) }}
        @endif
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">

        <div class="row">
            <div class="form-group col-md-2">
                {{ Form::label('Gênero') }}
                {{ Form::select('genero', $lista_generos, (isset($objProfessor->genero) ? $objProfessor->genero : ''), ['class' => 'form-control']) }}
            </div>
            <div class="form-group col-md-3">
                {{ Form::label('Nome *') }}
                <small>(Max 30 caracteres)</small>
                {{ Form::input('text', 'nome', isset($objProfessor->nome) ? $objProfessor->nome : null, ['class' => 'form-control', '', 'placeholder' => 'Nome', 'maxlength' => 30]) }}
            </div>
            <div class="form-group col-md-3">
                {{ Form::label('Sobrenome *') }}
                <small>(Max 60 caracteres)</small>
                {{ Form::input('text', 'sobrenome', isset($objProfessor->sobrenome) ? $objProfessor->sobrenome : null, ['class' => 'form-control', '', 'placeholder' => 'Sobrenome', 'maxlength' => 60]) }}
            </div>
            @if(!Request::is('*/editar'))
                <div class="form-group  col-md-3">
                    {{ Form::label('CPF *') }} <span>(Digitar sem pontuação)</span>
                    {{ Form::input('text', 'cpf', null, ['class' => 'form-control cpf', 'onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'CPF', 'maxlength' => 20]) }}
                </div>
            @else
                <div class="form-group  col-md-3">
                    {{ Form::label('CPF *') }} <span>(Digitar sem pontuação)</span>
                    {{ Form::input('text', 'cpf', $objProfessor->cpf, ['class' => 'form-control cpf', 'readonly' => 'readonly', 'onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'CPF/CNPJ', 'maxlength' => 20]) }}
                </div>
            @endif
            <div class="form-group col-md-3">
                {{ Form::label('Data Nascimento') }}
                {{ Form::input('text', 'data_nascimento', isset($objProfessor->data_nascimento) ? implode('/', array_reverse(explode('-', $objProfessor->data_nascimento))) : null, ['class' => 'form-control datepicker', '', 'placeholder' => 'Data Nascimento', 'maxlength' => 10]) }}
            </div>
        </div>
        <hr class="hr"/>
        <div class="row">
            <div class="form-group col-md-4">
                {{ Form::label('% Share *') }}
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
                {{ Form::input('text', 'logradouro', isset($objEndereco->logradouro) ? $objEndereco->logradouro : null, ['class' => 'form-control logradouro', '', 'placeholder' => 'Logradouro', 'id' => 'logradouro', 'maxlength' => 100]) }}
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
                {{ Form::label('Bairro') }}
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
            <div class="form-group col-md-5">
                {{ Form::label('Profissão *') }}
                {{ Form::input('text', 'profissao', isset($objProfessor->profissao) ? $objProfessor->profissao : null, ['class' => 'form-control', '', 'placeholder' => 'Professor']) }}
            </div>
            <div class="form-group  col-md-2">
                {{ Form::label('Telefone Fixo') }}
                {{ Form::input('text', 'telefone_1', isset($objProfessor->telefone_1) ? $objProfessor->telefone_1 : null, ['class' => 'form-control telefone','onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'Telefone']) }}
            </div>
            <div class="form-group  col-md-2">
                {{ Form::label('Telefone Celular *') }}
                {{ Form::input('text', 'telefone_2', isset($objProfessor->telefone_2) ? $objProfessor->telefone_2 : null, ['class' => 'form-control celular','onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'Telefone']) }}
            </div>
            <div class="col-md-12">
                {{ Form::label('Mini Currículo *') }}
                {{ Form::textarea('mini_curriculum', isset($objProfessor->mini_curriculum) ? $objProfessor->mini_curriculum : null, ['id' => 'mini_curriculum', 'rows' => 7, 'style' => 'width: 100%;']) }}
            </div>
        </div>

        <hr class="hr"/>
        <div class="row">
            <div class="form-group col-md-4">
                {{ Form::label('Facebook') }}
                {{ Form::input('text', 'facebook_link', isset($objProfessor->facebook_link) ? $objProfessor->facebook_link : null, ['class' => 'form-control', '', 'placeholder' => 'Facebook']) }}
            </div>
            <div class="form-group col-md-4">
                {{ Form::label('Instagram') }}
                {{ Form::input('text', 'insta_link', isset($objProfessor->insta_link) ? $objProfessor->insta_link : null, ['class' => 'form-control', '', 'placeholder' => 'Instagram']) }}
            </div>
            <div class="form-group col-md-4">
                {{ Form::label('Twitter') }}
                {{ Form::input('text', 'twitter_link', isset($objProfessor->twitter_link) ? $objProfessor->twitter_link : null, ['class' => 'form-control', '', 'placeholder' => 'Twitter']) }}
            </div>
            <div class="form-group col-md-4">
                {{ Form::label('LinkedIn') }}
                {{ Form::input('text', 'linkedin_link', isset($objProfessor->linkedin_link) ? $objProfessor->linkedin_link : null, ['class' => 'form-control', '', 'placeholder' => 'LinkedIn']) }}
            </div>

            <div class="form-group col-md-4">
                {{ Form::label('Canal no Youtube') }}
                {{ Form::input('text', 'youteber_link', isset($objProfessor->youteber_link) ? $objProfessor->youteber_link : null, ['class' => 'form-control', '', 'placeholder' => 'Canal no Youtube']) }}
            </div>
            <div class="col-md-4"></div>
        </div>
        <hr class="hr"/>
        <div class="row">
            <div class="form-group col-md-4">
                {{ Form::label('Repasse manual') }}
                {{ Form::checkbox('repasse_manual', 1, $repasse_manual) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-4">
                {{ Form::label('Titular') }}
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
                    <!-- SE USUARIO AINDA NAO TEM CONTA WIRECARD -->
                        <div class="form-group  col-md-3">
                            {{ Form::label('Digito') }}
                            {{ Form::input('text', 'digita_agencia', isset($objConta->digita_agencia) ? $objConta->digita_agencia : null, ['class' => 'form-control',]) }}
                        </div>
                    @if (!is_null($repasse_manual))
                    @endif
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
            	<label>Senha *(mínimo de 8 dígitos)</label>@if(Request::is('*/editar')) (Preencher senha somente se deseja mudar!) @endif
                {{ Form::input('password', 'password', null, ['class' => 'form-control', '', 'placeholder' => 'Senha', 'value' => '']) }}
            </div>
            <div class="form-group col-md-4">
                <label>Confirma *(mínimo de 8 dígitos)</label>@if(Request::is('*/editar')) (Preencher senha somente se deseja mudar!) @endif
                {{ Form::input('password', 'password_confirmation', null, ['class' => 'form-control', '', 'placeholder' => 'Senha', 'value' => '']) }}
            </div>
        </div>
        <div class="row">
            <div id="box_upload" class="form-group  col-md-3">
                @if(Request::is('*/editar') && isset($objUsuario->foto))
                    <div class="form-group">
                        <img src="{{ Storage::disk('s3')->url('files/usuario/' . $objUsuario->foto) }}" height="100"/>
                    </div>
                @endif
                @if(Request::is('*/editar') && !isset($objUsuario->foto))
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
            <span> Ao clicar em salvar, você declara que as informações acima prestadas são verdadeiras, e assume a inteira responsabilidade por elas.</span>
            <br/>
            <br/>
            <a href="{{ route('admin.professor') }}" class="btn btn-default">Voltar</a>
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
