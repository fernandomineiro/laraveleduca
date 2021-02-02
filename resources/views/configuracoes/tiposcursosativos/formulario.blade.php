@extends('layouts.app')
@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/css/bootstrap-colorpicker.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/js/bootstrap-colorpicker.js"></script>
    <div class="box padding20">
        <h2 class="table"><span>{{ $modulo['moduloDetalhes']->menu .' '.$modulo['moduloDetalhes']->modulo }}</span></h2>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $obj, ['method' => 'PATCH', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.atualizar', $obj->id], 'files' => true] ) }}
        @else
            {{ Form::open(['url' => 'admin/'.$modulo['moduloDetalhes']->uri.'/salvar', 'files' => true]) }}
        @endif

        <div class="row">
            <div class="form-group col-md-5">
                {{ Form::label('Projeto') }}
                {{ Form::select('fk_faculdade_id', $faculdades, (isset($obj->fk_faculdade_id) ? $obj->fk_faculdade_id : null), ['class' => 'form-control']) }}
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-3">
                {{ Form::label('Cursos Online') }}
                {{ Form::select('ativar_cursos_online', $lista_status, (isset($obj->ativar_cursos_online) ? $obj->ativar_cursos_online : 1), ['class' => 'form-control']) }}
            </div>

            <div class="form-group col-md-3">
                {{ Form::label('Cursos Presenciais') }}
                {{ Form::select('ativar_cursos_presenciais', $lista_status, (isset($obj->ativar_cursos_presenciais) ? $obj->ativar_cursos_presenciais : 1), ['class' => 'form-control']) }}
            </div>

            <div class="form-group col-md-3">
                {{ Form::label('Cursos Remotos') }}
                {{ Form::select('ativar_cursos_hibridos', $lista_status, (isset($obj->ativar_cursos_hibridos) ? $obj->ativar_cursos_hibridos : 1), ['class' => 'form-control']) }}
            </div>

            <div class="form-group col-md-3">
                {{ Form::label('Eventos') }}
                {{ Form::select('ativar_eventos', $lista_status, (isset($obj->ativar_eventos) ? $obj->ativar_eventos : 1), ['class' => 'form-control']) }}
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-3">
                {{ Form::label('Trilha de conhecimento') }}
                {{ Form::select('ativar_trilha_conhecimento', $lista_status, (isset($obj->ativar_trilha_conhecimento) ? $obj->ativar_trilha_conhecimento : 1), ['class' => 'form-control']) }}
            </div>

            <div class="form-group col-md-3">
                {{ Form::label('Membership') }}
                {{ Form::select('ativar_membership', $lista_status, (isset($obj->ativar_membership) ? $obj->ativar_membership : 1), ['class' => 'form-control']) }}
            </div>

            <div class="form-group col-md-3">
                {{ Form::label('Biblioteca') }}
                {{ Form::select('ativar_biblioteca', $lista_status, (isset($obj->ativar_biblioteca) ? $obj->ativar_biblioteca : 1), ['class' => 'form-control']) }}
            </div>

            <div class="form-group col-md-3">
                {{ Form::label('Banner secundário (home)') }}
                {{ Form::select('ativar_banner_secundario', $lista_status, (isset($obj->ativar_banner_secundario) ? $obj->ativar_banner_secundario : 1), ['class' => 'form-control']) }}
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-3">
                {{ Form::label('Card (home) vantanges de ser assinante') }}
                {{ Form::select('ativar_vantagens_assinantes', $lista_status, (isset($obj->ativar_vantagens_assinantes) ? $obj->ativar_vantagens_assinantes : 1), ['class' => 'form-control']) }}
            </div>

            <div class="form-group col-md-3">
                {{ Form::label('Card (home) descubra as trilhas de conhecimento') }}
                {{ Form::select('ativar_descubra_trilhas', $lista_status, (isset($obj->ativar_descubra_trilhas) ? $obj->ativar_descubra_trilhas : 1), ['class' => 'form-control']) }}
            </div>

            <div class="form-group col-md-3">
                {{ Form::label('Card (home) seja um professor') }}<br><br>
                {{ Form::select('ativar_seja_professor', $lista_status, (isset($obj->ativar_seja_professor) ? $obj->ativar_seja_professor : 1), ['class' => 'form-control']) }}
            </div>

            <div class="form-group col-md-3">
                {{ Form::label('Card (home) autenticidade do certificado') }}
                {{ Form::select('ativar_autenticidade_certificado', $lista_status, (isset($obj->ativar_autenticidade_certificado) ? $obj->ativar_autenticidade_certificado : 1), ['class' => 'form-control']) }}
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-3">
                {{ Form::label('Faz Curso Superior') }}
                {{ Form::select('ativar_faz_curso_superior', $lista_status, (isset($obj->ativar_faz_curso_superior) ? $obj->ativar_faz_curso_superior : 1), ['class' => 'form-control']) }}
            </div>

            <div class="form-group col-md-3">
                {{ Form::label('Faz Pós-Graduação') }}
                {{ Form::select('ativar_faz_especializacao', $lista_status, (isset($obj->ativar_faz_especializacao) ? $obj->ativar_faz_especializacao : 1), ['class' => 'form-control']) }}
            </div>
            <div class="form-group col-md-3">
                {{ Form::label('Tipo Layout') }}
                {{ Form::select('tipo_layout', ['0' => 'Modelo Padrão', '1' => 'Modelo Estrutura Curricular', '2' => 'Modelo por categoria', '3' => 'Modelo Aleatório Páginado', '4' => 'Modelo Mentoria'], (isset($obj->tipo_layout) ? $obj->tipo_layout : 0), ['class' => 'form-control', 'id' => 'tipoLayout']) }}
            </div>

            <div class="form-group col-md-3">
                {{ Form::label('URl quem somos:') }}
                {{ Form::text('url_quem_somos', (isset($obj->url_quem_somos) ? $obj->url_quem_somos : ''), ['class' => 'form-control', 'id' => 'url_quem_somos']) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-3">
                {{ Form::label('Cor do header nas páginas principais:') }}
                {{ Form::text('header_primario', (isset($obj->header_primario) ? $obj->header_primario : ''), ['class' => 'form-control colorpicker-component', 'id' => 'header_primario']) }}
            </div>
            <div class="form-group col-md-3">
                {{ Form::label('Cor do header nas páginas internas:') }}<br>
                {{ Form::text('header_secundario', (isset($obj->header_secundario) ? $obj->header_secundario : ''), ['class' => 'form-control colorpicker-component', 'id' => 'header_secundario']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('Descrição do site:') }}<br><br>
                {{ Form::text('descricao', (isset($obj->descricao) ? $obj->descricao : ''), ['class' => 'form-control', 'id' => 'descricao']) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-4">
                {{ Form::label('Favicon:') }}
                {{--'onChange' => 'previewImage(event)'--}}
                {{ Form::file('favicon', ['id' => 'favicon']) }}
            </div>
        </div>
        <div id="layout_itv" @if (empty($obj->tipo_layout) || $obj->tipo_layout != 1) style="display: none;" @endif;>
            <div class="row">
                <div class="form-group col-md-3">
                    {{ Form::label('Teaser informativo (código vimeo):') }}
                    {{ Form::text('teaser', (isset($obj->teaser) ? $obj->teaser : ''), ['class' => 'form-control', 'id' => 'teaserInformativo']) }}
                </div>
                <div class="form-group col-md-3">
                    {{ Form::label('Cor do banner de login:') }}<br><br>
                    {{ Form::text('cor_banner_login', (isset($obj->cor_banner_login) ? $obj->cor_banner_login : ''), ['class' => 'form-control colorpicker-component', 'id' => 'cor_banner_login']) }}
                </div>
                <div class="form-group col-md-3">
                    {{ Form::label('Banner lateral:') }}<br>
                    <small>291x1262px</small>
                    {{--'onChange' => 'previewImage(event)'--}}
                    {{ Form::file('banner_lateral', ['id' => 'banner_lateral']) }}
                </div>
                <div class="form-group col-md-3">
                    {{ Form::label('Imagem do banner central:') }}<br>
                    <small>823x442px</small>
                    {{--'onChange' => 'previewImage(event)'--}}
                    {{ Form::file('banner_central', ['id' => 'banner_central']) }}
                </div>

            </div>
            <div class="row">
                <div class="form-group col-md-12">
                    {{ Form::label('Banner secundário:') }}
                    <span id="faltaBanner"></span>
                    {{ Form::textarea('banner_secundario', (isset($obj->banner_secundario) ? $obj->banner_secundario : ''),
                            [
                                'class' => 'form-control',
                                 'id' => 'ckeditor', 'maxlength' => '500','onkeyup' => 'countChar(this, 2, 500)'
                            ]

                    ) }}
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-12">
                    {{ Form::label('Texto do banner central:') }}
                    <span id="faltaBanner"></span>
                    {{ Form::textarea('texto_banner_central', (isset($obj->texto_banner_central) ? $obj->texto_banner_central : ''),
                            [
                                'class' => 'form-control',
                                 'id' => 'ckeditor', 'maxlength' => '500','onkeyup' => 'countChar(this, 2, 500)'
                            ]

                    ) }}
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-12">
                    {{ Form::label('Primeiro texto da seção de login:') }}
                    <span id="faltaBanner"></span>
                    {{ Form::textarea('primeiro_texto_login', (isset($obj->primeiro_texto_login) ? $obj->primeiro_texto_login : ''),
                            [
                                'class' => 'form-control',
                                 'id' => 'ckeditor', 'maxlength' => '500','onkeyup' => 'countChar(this, 2, 500)'
                            ]

                    ) }}
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-12">
                    {{ Form::label('Segundo texto da seção de login:') }}
                    <span id="faltaBanner"></span>
                    {{ Form::textarea('segundo_texto_login', (isset($obj->segundo_texto_login) ? $obj->segundo_texto_login : ''),
                            [
                                'class' => 'form-control',
                                 'id' => 'ckeditor', 'maxlength' => '500','onkeyup' => 'countChar(this, 2, 500)'
                            ]

                    ) }}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-1">
                <button type="button" class="btn btn-danger" onclick="window.location.href='{{ route('admin.'.$modulo['moduloDetalhes']->rota)}}'">Voltar</button>
            </div>
            <div class="">
                {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
            </div>
        </div>

        {{ Form::close() }}
    </div>


@endsection

@push('js')
    <script>
    $('#teaserInformativo').change(function() {
        let vimeoId = $(this).val();

        $.get('https://vimeo.com/api/oembed.json?url=https://vimeo.com/'+ vimeoId, function (response) {
        }).fail((error) => {
            $(this).val('');
            alert('Código vimeo informado não é válido');
        });
    });

    $('#tipoLayout').change(function () {
        $('#layout_itv').hide();
        if ($(this).val() == 1) {
            $('#layout_itv').show();
        }
    });

    $(document).ready(function () {
        CKEDITOR.replace('banner_secundario');
        CKEDITOR.replace('texto_banner_central');
        CKEDITOR.replace('primeiro_texto_login');
        CKEDITOR.replace('segundo_texto_login');

        CKEDITOR.instances['ckeditor'].on('change', function(event) {
            var textLimit = 500;
            var str = CKEDITOR.instances['ckeditor'].editable().getText();
            countChar(event, 2, textLimit, str.replace(/[\x00-\x1F\x7F-\x9F]/g, "").length);

            if (str.length >= textLimit) {
                countChar(event, 2, textLimit, str.slice(0, textLimit).length)
                CKEDITOR.instances['ckeditor'].setData(str.slice(0, textLimit));
                return false;
            }
        });
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
    $('.colorpicker-component').colorpicker();
</script>
@endpush
