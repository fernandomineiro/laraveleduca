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

        @if(Request::is('*/editar') && $obj->foto)
            <div class="form-group">
                <img src="{{ Storage::disk('s3')->url('files/usuario/' . $obj->foto) }}" height="100"/>
                <br/>
                {{--<a href="javascript:;" onclick="$('#box_upload').show();" class="label label-warning">Mudar Imagem</a>--}}
            </div>
        @endif
        <div class="form-group">
            {{ Form::label('Perfil') }}
            {{ Form::select('fk_perfil', $lista_perfis, (isset($obj->fk_perfil) ? $obj->fk_perfil : null), ['class' => 'form-control']) }}
        </div>
        <div class="form-group">
				{{ Form::label('Projeto') }}
				{{ Form::select('fk_faculdade_id', $lista_faculdades, (isset($obj->fk_faculdade_id) ? $obj->fk_faculdade_id : null), ['class' => 'form-control']) }}
			</div>
        <div class="row">
            <div class="form-group  col-md-4">
                {{ Form::label('Nome') }}
                {{ Form::input('text', 'nome', null, ['class' => 'form-control', 'autofocus', 'placeholder' => 'Nome']) }}
            </div>
            @if(!Request::is('*/editar'))
                <div class="form-group  col-md-4">
                    {{ Form::label('E-mail') }}
                    {{ Form::input('email', 'email', null, ['class' => 'form-control', '', 'placeholder' => 'E-mail']) }}
                </div>
            @endif
            @if(Request::is('*/editar'))
                <div class="form-group  col-md-4">
                    {{ Form::label('E-mail') }}
                    {{ Form::input('email', 'email', null, ['class' => 'form-control','readonly' => 'readonly', '', 'placeholder' => 'E-mail']) }}
                </div>
            @endif
        </div>

        <div class="row">
            <div class="form-group col-md-4">
            	<label>Senha (mínimo de 8 dígitos)</label>@if(Request::is('*/editar')) (Preencher senha somente se deseja mudar!) @endif
                {{ Form::input('password', 'password', null, ['class' => 'form-control', '', 'placeholder' => 'Senha', 'value' => '']) }}
            </div>
            <div class="form-group col-md-4">
                <label>Confirma (mínimo de 8 dígitos)</label>@if(Request::is('*/editar')) (Preencher senha somente se deseja mudar!) @endif
                {{ Form::input('password', 'password_confirmation', null, ['class' => 'form-control', '', 'placeholder' => 'Senha', 'value' => '']) }}
            </div>

            <div id="box_upload" class="form-group  col-md-3">
                {{ Form::label('') }}
                {{ Form::label('Imagem') }}
                {{ Form::file('foto') }}
            </div>
        </div>
        <div class="form-group">
            <a href="{{ route('admin.'.$modulo['moduloDetalhes']->rota)}}" class="btn btn-default">Voltar</a>
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>
@endsection
