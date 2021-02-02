@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>Configurações Banners Secundários</span></h2>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{--$modulo['moduloDetalhes']->rota--}}
            {{ Form::model( $obj, ['method' => 'PATCH', 'route' => ['admin.banner_secundario.atualizar', $obj->id], 'files' => true] ) }}
            <?php
                $dataInicio = '';
                if ($obj->data_hora_inicio)
                    $dataInicio = date('d/m/Y', strtotime($obj->data_hora_inicio));

                $horaInicio = '';
                if ($obj->data_hora_inicio)
                    $horaInicio = date('H:i', strtotime($obj->data_hora_inicio));

                $dataFim = '';
                if ($obj->data_hora_termino)
                    $dataFim = date('d/m/Y', strtotime($obj->data_hora_termino));

                $horaFim = '';
                if ($obj->data_hora_termino)
                    $horaFim = date('H:i', strtotime($obj->data_hora_termino));

            ?>
        @else
            {{--$modulo['moduloDetalhes']->uri--}}
            {{ Form::open(['url' => 'admin/banner-secundario/salvar', 'files' => true]) }}
            <?php
                $horaFim = date('H:i:s');
                $horaInicio = date('H:i:s');
            ?>
        @endif
        <div class="form-group row">

        </div>
        <div class="row">
            <div class="col-sm">
                <div class="col-sm-5">
                    {{ Form::label('Projeto') }}<br>
                    <small>(Projeto ao qual o banner pertence)</small>
                    {{ Form::select('fk_faculdade_id', $faculdades, (isset($obj->fk_faculdade_id) ? $obj->fk_faculdade_id : null), ['class' => 'form-control']) }}
                </div>
            </div>
            <div class="form-group col-md-5">
                {{ Form::label('Título do banner') }}<br> <small>(Campo aparece em cima do banner. Max 60 caracteres)</small>
                {{ Form::input('text', 'titulo', null, ['class' => 'form-control', '', 'placeholder' => 'Título', 'maxlength' => 60]) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-3">
                {{ Form::label('Link') }}<br>
                <small>(Link de direcionamento ao clicar no banner)</small>
                {{ Form::input('text', 'url_link', (isset($obj->url_link) ? $obj->url_link : null), ['class' => 'form-control', '', 'placeholder' => 'Link']) }}
            </div>
            <div class="form-group col-md-4">
                {{ Form::label('Texto') }}<br>
                <small>(Texto ao passar o mouse em cima do banner. Max 120 caracteres)</small>
                {{ Form::input('text', 'alt_text', (isset($obj->alt_text) ? $obj->alt_text : null), ['class' => 'form-control', '', 'placeholder' => 'Texto', 'maxlength' => 120]) }}
            </div>
            <div class="form-group col-md-3">
                {{ Form::label('Tipo do banner') }}<br>
                <small>(Tipo do banner)</small>
                {{ Form::select('tipo_banner',
                    [
                        '' => 'Selecione',
                        '1' => 'Banner tipo texto',
                        '2' => 'Banner tipo imagem',
                        '3' => 'Banner tipo teaser',
                    ], (isset($obj->tipo_banner) ? $obj->tipo_banner : null),
                    ['class' => 'form-control', 'id' => 'tipoBanner', 'maxlength' => 120]) }}
            </div>
        </div>

        <div class="row" id="tipo_1" @if (empty($obj->tipo_banner) || $obj->tipo_banner != 1) style="display: none" @endif>
            <div class="col-md-10">
                {{ Form::label('Banner secundário:') }}
                <small>(Texto do banner:)</small>
                {{ Form::textarea('texto', (isset($obj->texto) ? $obj->texto : ''),
                        [
                            'class' => 'form-control',
                             'id' => 'ckeditor', 'maxlength' => '500','onkeyup' => 'countChar(this, 2, 500)'
                        ]
                ) }}
            </div>
        </div>

        <div class="row" id="tipo_2" @if (empty($obj->tipo_banner) || $obj->tipo_banner != 2) style="display: none" @endif>
            <div id="box_upload" class="form-group col-md-3">
                {{ Form::label('Imagem do banner') }}<br>
                <small>(Dimensões: 1410x170px)</small>
                {{--
                <small @if (empty($obj) || $obj->pagina == 'home') style="display: none;" @endif; id="dimensoes_outros" >(Dimensões: 1920x500px)</small>--}}
                {{ Form::file('banner_url', ['id' => 'banner_url', 'onChange' => 'previewImage(event)']) }}
            </div>
            <div class="col-md-8">
                @if(Request::is('*/editar'))
                    <img src="/files/banners/{{$obj->banner_url }}" id="previewBanner" width="600px" height="200px">
                @else
                    <img id="previewBanner" width="600px" height="200px">
                @endif
            </div>
        </div>

        <div class="row" id="tipo_3" @if (empty($obj->tipo_banner) || $obj->tipo_banner != 3) style="display: none" @endif>
            <div class="col-md-3">
                {{ Form::label('Teaser 1:') }}<br>
                <small>(Teaser 1:)</small>
                {{ Form::input('text', 'codigo_vimeo_1', null, ['class' => 'form-control', '', 'placeholder' => 'Teaser 1', 'maxlength' => 120]) }}
            </div>
            <div class="col-md-3">
                {{ Form::label('Teaser 2:') }}<br>
                <small>(Teaser 2:)</small>
                {{ Form::input('text', 'codigo_vimeo_2', null, ['class' => 'form-control', '', 'placeholder' => 'Teaser 2', 'maxlength' => 120]) }}
            </div>
            <div class="col-md-3">
                {{ Form::label('Teaser 3:') }}<br>
                <small>(Teaser 3:)</small>
                {{ Form::input('text', 'codigo_vimeo_3', null, ['class' => 'form-control', '', 'placeholder' => 'Teaser 3', 'maxlength' => 120]) }}
            </div>
        </div>

        <hr/>

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
    previewImage = function(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('previewBanner');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    };
    $(document).ready(function () {

        $('#hora_inicio').wickedpicker({
            now: $('#hora_inicio').val(),
            twentyFour: true,
            upArrow: 'fa fa-chevron-up fa-lg',  //The up arrow class selector to use, for custom CSS
            downArrow: 'fa fa-chevron-down fa-lg', //The down arrow class selector to use, for custom CSS
            showSeconds: false,
        });

        $('#tipoBanner').change(function () {

            if ($(this).val() == 1) {
                $('#tipo_1').show();
                $('#tipo_2').hide();
                $('#tipo_3').hide();
            }

            if ($(this).val() == 2) {
                $('#tipo_1').hide();
                $('#tipo_2').show();
                $('#tipo_3').hide();
            }

            if ($(this).val() == 3) {
                $('#tipo_1').hide();
                $('#tipo_2').hide();
                $('#tipo_3').show();
            }
            if ($(this).val() == '') {
                $('#tipo_1').hide();
                $('#tipo_2').hide();
                $('#tipo_3').hide();
            }
        });

        $('#hora_final').wickedpicker({
            now: $('#hora_final').val(),
            twentyFour: true,
            upArrow: 'fa fa-chevron-up fa-lg',  //The up arrow class selector to use, for custom CSS
            downArrow: 'fa fa-chevron-down fa-lg', //The down arrow class selector to use, for custom CSS
            showSeconds: false,
        });

    });

    $('#teaserInformativo').change(function() {
        let vimeoId = $(this).val();

        $.get('https://vimeo.com/api/oembed.json?url=https://vimeo.com/'+ vimeoId, function (response) {
        }).fail((error) => {
            $(this).val('');
            alert('Código vimeo informado não é válido');
        });
    });

    $(document).ready(function () {
        CKEDITOR.replace('texto');

        // CKEDITOR.instances['ckeditor'].on('change', function(event) {
        //     var textLimit = 500;
        //     var str = CKEDITOR.instances['ckeditor'].editable().getText();
        //     countChar(event, 2, textLimit, str.replace(/[\x00-\x1F\x7F-\x9F]/g, "").length);
        //
        //     if (str.length >= textLimit) {
        //         countChar(event, 2, textLimit, str.slice(0, textLimit).length)
        //         CKEDITOR.instances['ckeditor'].setData(str.slice(0, textLimit));
        //         return false;
        //     }
        // });
    });

    countChar = function(event, tipo, max, len) {

        if (len == null) len = event.value.length
        let el;
        let el1;
        if (tipo == 1) {
            el = $('#faltaTitulo');
            el1 = $('#assinaturaTitle');
            el1.empty();
            el1.html(event.value);
        } else if(tipo == 2) {
            el = $('#faltaBanner');
            // el1 = $('#assinaturaDescription');
        }
        el.empty();
        el.append( len + '/' + max);

    };
    $('#aPagina').change(function(){
        if ($(this).val() == 'home') {
            $('#dimensoes_home').show();
            $('#dimensoes_outros').hide();
        } else {
            $('#dimensoes_home').hide();
            $('#dimensoes_outros').show();
        }
    })
</script>
@endpush
