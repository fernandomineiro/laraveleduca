@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>{{ $modulo['moduloDetalhes']->modulo }}</span></h2>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $obj, ['method' => 'PATCH', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.atualizar', $obj->id], 'files' => true] ) }}
        @else
            {{ Form::open(['url' => '/admin/'.$modulo['moduloDetalhes']->uri.'/salvar', 'files' => true]) }}
        @endif

        <div class="form-group row">
            <div class="col-sm">
                <div class="col-sm-5">
                    {{ Form::label('Projeto') }}<br>
                    <small>(Projeto parceiro)</small>
                    {{ Form::select('fk_faculdade_id', $faculdades, (isset($obj->fk_faculdade_id) ? $obj->fk_faculdade_id : null), ['class' => 'form-control']) }}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-5">
                {{ Form::label('Descrição')}} <span id="descricaoCount"></span><br>
                <small>(Breve descrição)</small>
                {{ Form::input('text', 'descricao', null, ['class' => 'form-control', 'maxlength' => 100, 'onkeyup' => 'countChar(this, "descricaoCount", 100)', 'placeholder' => 'Descrição']) }}
            </div>
            <div class="form-group col-md-5">
                {{ Form::label('Link') }} <span id="linkCount"></span><br>
                <small>(Link para o site do parceiro)</small>
                {{ Form::input('text', 'link', null, ['class' => 'form-control', 'maxlength' => 100, 'onkeyup' => 'countChar(this, "linkCount", 100)', 'placeholder' => 'Link']) }}
            </div>
        </div>

        <div class="row">
            <div id="box_upload" class="form-group col-md-3">
                {{ Form::label('Imagem') }}<br>
                <small>(Dimensões: 150x75px)</small>
                {{ Form::file('imagem', ['id' => 'image', 'onChange' => 'previewImage(event)']) }}
            </div>
            <div class="col-md-8">
                @if(Request::is('*/editar') && $obj->imagem)
                    <img src="{{URL::asset('files/parceiros/' . $obj->imagem)}}"id="previewImg" height="75px" width="150px" />
                @else
                    <img src="" id="previewImg" height="75px" width="150px">
                @endif
            </div>
        </div>
        <div class="form-group">
            <a href="{{ route('admin.'.$modulo['moduloDetalhes']->rota)}}" class="btn btn-default">Voltar</a>
            <a href="{{ url()->current() }}" class="btn btn-default">Cancel</a>
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>
@endsection

@push('js')
    <script type="text/javascript">
        $(document).ready(function () {
            previewImage = function(event) {
                const reader = new FileReader();
                reader.onload = function() {
                    const output = document.getElementById('previewImg');
                    output.src = reader.result;
                    output.style = 'width: 150px; height: 75px; display: block;';
                };
                reader.readAsDataURL(event.target.files[0]);
            };
            countChar = function(event, tipo, max, len) {
                if (len == null) len = event.value.length;
                $('#'+tipo).empty();
                $('#'+tipo).append( len + '/' + max);
            };

            $(function () {
                $('input').trigger('keyup');
                $('#editor1').trigger('keyup');
            })
        })
    </script>
@endpush
