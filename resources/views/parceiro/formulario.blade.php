@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>Parceiro</span></h2>
        <a href="{{ route('admin.parceiros') }}" class="label label-default">Voltar</a>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $parceiros, ['method' => 'PATCH', 'route' => ['admin.parceiros.atualizar', $eventos->id]] ) }}
        @else
            {{ Form::open(['url' => '/admin/parceiro/salvar']) }}
        @endif

        <div class="form-group">
            {{ Form::label('Usuário') }}
            {{ Form::select('fk_usuario', $lista_usuarios, (isset($parceiros->fk_usuario) ? $parceiros->fk_usuario : null), ['class' => 'form-control']) }}
        </div>

        <div class="form-group">
            {{ Form::label('Tipo Parceiro') }}
            {{ Form::select('fk_tipo_parceiro', $lista_tipo_parceito, (isset($parceiros->tipo_parceiro) ? $parceiros->tipo_parceiro : null), ['class' => 'form-control']) }}
        </div>

        <div class="form-group">
            {{ Form::label('Compartilhar') }}
            {{ Form::select('compartilhar', $lista_status, (isset($parceiros->compartilhar) ? $parceiros->compartilhar : null), ['class' => 'form-control']) }}
        </div>

        <div class="form-group">
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>
@endsection
