@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>{{ $modulo['moduloDetalhes']->modulo }}</span></h2>
        <hr class="hr"/>
        @if(Request::is('*/editar'))
            {{ Form::model( $objGestao, ['method' => 'PATCH', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.atualizar', $objGestao->id], 'files' => true, 'autocomplete' => 'off'] ) }}

            <input type="hidden" name="fk_usuario_id" value="<?php echo $objGestao->fk_usuario_id; ?>" />
            <input type="hidden" name="id" value="<?php echo $objGestao->id; ?>" />
        @else
            {{ Form::open(['url' => '/admin/'.$modulo['moduloDetalhes']->uri.'/salvar', 'files' => true, 'autocomplete' => 'off']) }}
        @endif
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">

        <div class="row">
            <div class="form-group col-md-4">
                {{ Form::label('Nome *') }}
                {{ Form::input('text', 'nome', isset($objGestao->nome) ? $objGestao->nome : null, ['class' => 'form-control', '', 'placeholder' => 'Nome', 'maxlength' => 30]) }}
            </div>
            <div class="form-group col-md-4">
                {{ Form::label('CPF *') }} <span>(Digitar sem pontuação)</span>
                {{ Form::input('text', 'cpf', isset($objGestao->cpf) ? $objGestao->cpf : null, ['class' => 'form-control cpf', 'onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'CPF', 'maxlength' => 20]) }}
            </div>
        </div>
        <hr class="hr"/>
        <div class="row">
            <div class="form-group  col-md-2">
                {{ Form::label('Telefone Fixo ') }}
                {{ Form::input('text', 'telefone_1', isset($objGestao->telefone_1) ? $objGestao->telefone_1 : null, ['class' => 'form-control telefone','onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'Telefone']) }}
            </div>
            <div class="form-group  col-md-2">
                {{ Form::label('Telefone Celular *') }}
                {{ Form::input('text', 'telefone_2', isset($objGestao->telefone_2) ? $objGestao->telefone_2 : null, ['class' => 'form-control celular','onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'Telefone']) }}
            </div>
            <div class="form-group  col-md-2">
                {{ Form::label('Perfil *') }}
                {{ Form::select('fk_perfil', $lista_perfis, (isset($objUsuario) && isset($objUsuario->fk_perfil) ? $objUsuario->fk_perfil : null), ['class' => 'form-control']) }}
            </div>
            <div class="form-group  col-md-3">
                {{ Form::label('Projeto *') }}
                {{ Form::select('fk_faculdade_id', $lista_faculdades, (isset($objUsuario) && isset($objUsuario->fk_faculdade_id) ? $objUsuario->fk_faculdade_id : null), ['class' => 'form-control']) }}
            </div>
            <div class="form-group  col-md-3">
                {{ Form::label('Diretoria Ensino ') }}
                {{ Form::select('fk_diretoria_ensino', $lista_diretoria, (isset($objUsuario) && isset($objUsuario->fk_diretoria_ensino) ? $objUsuario->fk_diretoria_ensino : null), ['class' => 'form-control']) }}
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
                    {{ Form::input('email', 'email', isset($objUsuario) && isset($objUsuario->email) ? $objUsuario->email : null, ['class' => 'form-control', 'placeholder' => 'E-mail']) }}
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
        <div class="form-group">
            <a href="{{ route('admin.gestao') }}" class="btn btn-default">Voltar</a>
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
