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
                    {{ Form::label('Projeto') }} <br>
                    <small>(Projeto ao qual o termo pertence)</small>
                    {{ Form::select('fk_faculdade_id', $faculdades, (isset($obj->fk_faculdade_id) ? $obj->fk_faculdade : null), ['class' => 'form-control']) }}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-4">
                {{ Form::label('Título') }} <span id="tituloCount"></span><br>
                <small>(Título do termo)</small>
                {{ Form::input('text', 'slug', null, ['class' => 'form-control', 'maxlength' => 100, 'onkeyup' => 'countChar(this, "tituloCount", 100)', 'placeholder' => 'Título']) }}
            </div>
            <div class="form-group col-md-4">
                {{ Form::label('Descrição') }} <span id="descricaoCount"></span><br>
                <small>(Breve descrição do termo)</small>
                {{ Form::input('text', 'descricao', null, ['class' => 'form-control', 'maxlength' => 100, 'onkeyup' => 'countChar(this, "descricaoCount", 100)', 'placeholder' => 'Descricao']) }}
            </div>
            <div class="form-group col-md-4">
                {{ Form::label('URL') }} <span id="urlCount"></span><br>
                <small>(URL do termo, faculdade)</small>
                {{ Form::input('text', 'url', null, ['class' => 'form-control', 'maxlength' => 100, 'onkeyup' => 'countChar(this, "urlCount", 100)', 'placeholder' => 'URL']) }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                {{ Form::label('Texto') }} <span id="textoCount"></span><br>
                <small>(Texto para o termo)</small>
                {{ Form::textarea('texto', null, ['class' => 'form-control', 'id' => 'editor1']) }}
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-12 form-group">
                <a href="{{ route('admin.'.$modulo['moduloDetalhes']->rota)}}" class="btn btn-default">Voltar</a>
                <a href="{{ url()->current() }}" class="btn btn-default">Cancel</a>
                {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
            </div>
        </div>
        {{ Form::close() }}
    </div>
    
@endsection

@push('js')
    <script>
        countChar = function(event, tipo, max, len) {
            if (len == null) len = event.value.length;
            $('#'+tipo).empty();
            $('#'+tipo).append( len + '/' + max);
        };

        $(function () {
            CKEDITOR.replace('editor1');
            $('.textarea').wysihtml5();

            $('input').trigger('keyup');
            $('#editor1').trigger('keyup');
        })
    </script>
@endpush
