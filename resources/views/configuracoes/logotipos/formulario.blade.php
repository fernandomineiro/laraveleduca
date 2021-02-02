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
                    {{ Form::label('Projeto') }} <br>
                    <small>(Projeto que o Logotipo pertence)</small>
                    {{ Form::select('fk_faculdade_id', $faculdades, (isset($obj->fk_faculdade_id) ? $obj->fk_faculdade : null), ['class' => 'form-control']) }}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-5">
                {{ Form::label('Título') }} <br>
                <small>(Título para o logotipo)</small>
                {{ Form::input('text', 'slug', null, ['class' => 'form-control', '', 'placeholder' => 'Título']) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-5">
                {{ Form::label('Descrição')}} <br>
                <small>(Pequena descrição para o logotipo)</small>
                {{ Form::input('text', 'descricao', null, ['class' => 'form-control', '', 'placeholder' => 'Descrição']) }}
            </div>
        </div>
        <div class="row">
            <div id="box_upload" class="form-group col-md-3">
                {{ Form::label('Logotipo') }}
                <small>(Dimensões: 96x50px)</small>
                {{ Form::file('url_logtipo', ['id' => 'image', 'onChange' => 'previewImage(event)']) }}
            </div>
            <div class="col-md-8">
                @if(Request::is('*/editar'))
                    <img src="{{URL::asset('files/logotipos/' . $obj->url_logtipo)}}" id="previewImg" width="96px" height="50px">
                @else
                    <img id="previewImg" width="96px" height="50px">
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
                    output.style = 'width: 96px; height:50px; display: block;';
                };
                reader.readAsDataURL(event.target.files[0]);
            };
        });
    </script>
@endpush
