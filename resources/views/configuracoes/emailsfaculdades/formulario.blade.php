@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>{{ $modulo['moduloDetalhes']->menu .' '.$modulo['moduloDetalhes']->modulo }}</span></h2>
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
                    <small>(Projeto ao qual a conta de envio de email pertence)</small>
                    {{ Form::select('fk_faculdade_id', $faculdades, (isset($obj->fk_faculdade_id) ? $obj->fk_faculdade : null), ['class' => 'form-control']) }}
                </div>
            </div>
            <div class="form-group col-md-5">
                {{ Form::label('Nome') }} <br>
                <small>(Nome da conta/assunto email. Max 60 caracteres)</small>
                {{ Form::input('text', 'nome', null, ['class' => 'form-control', '', 'placeholder' => 'Nome', 'maxlength' => 60]) }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    {{ Form::label('E-Mail') }} <br>
                    <small>(E-mail do qual a mensagem será enviado)</small>
                    {{ Form::input('email', 'email', null, ['class' => 'form-control', '', 'placeholder' => 'E-Mail', 'value' => '']) }}
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-group">
                    {{ Form::label('Senha') }}
                        @if(Request::is('*/editar'))
                            <small>(Preencher senha somente se desejar mudar!)</small>
                        @endif
                        <span id="mostrarId" style="margin-left: 20px; cursor: pointer;">Mostrar a senha</span><br>
                        <small>(Senha do e-mail ao qual a mensagem será enviada)</small>
                    {{ Form::input('password', 'senha', null, ['class' => 'form-control', '', 'placeholder' => 'Senha', 'value' => '', 'id' => 'mostrarField']) }}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-10">
                {{ Form::label('Assinatura') }}<br>
                <small>(Assinatura que aparecerá no final da mensagem)</small>
                {{ Form::textarea('assinatura', null, ['class' => 'form-control', 'id' => 'editor1']) }}
            </div>
        </div>
        <div class="form-group">
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>
@endsection

@push('js')
    <script>
    var mostrarFlag = false;
    $(function () {
        CKEDITOR.replace('editor1');
        $('.textarea').wysihtml5();
    })

    $("#mostrarId").on( "click", function() {
        if(!mostrarFlag) {
            $('#mostrarField').attr('type', 'text');
            mostrarFlag = !mostrarFlag;
        }
        else {
            $('#mostrarField').attr('type', 'password');
            mostrarFlag = !mostrarFlag;
        }
    });

</script>
@endpush
