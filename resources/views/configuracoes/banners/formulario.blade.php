@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>{{ $modulo['moduloDetalhes']->menu .' '.$modulo['moduloDetalhes']->modulo }}</span></h2>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $obj, ['method' => 'PATCH', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.atualizar', $obj->id], 'files' => true] ) }}
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
            {{ Form::open(['url' => 'admin/'.$modulo['moduloDetalhes']->uri.'/salvar', 'files' => true]) }}
            <?php
                $horaFim = date('H:i:s');
                $horaInicio = date('H:i:s');
            ?>
        @endif
        <div class="form-group row">

        </div>
        <div class="row">
{{--            <div class="form-group col-md-4">--}}
{{--                {{ Form::label('Slug') }}--}}
{{--                {{ Form::input('text', 'slug', null, ['class' => 'form-control', '', 'placeholder' => 'Slug']) }}--}}
{{--            </div>--}}
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
                {{ Form::label('Página') }}<br>
                <small>(Página em que será exibido o banner)</small>
                {{ Form::select('pagina', $paginas, (isset($obj->pagina) ? $obj->pagina : ''), ['id' => 'aPagina', 'class' => 'form-control']) }}
            </div>
            <div class="form-group col-md-3">
                {{ Form::label('Link') }}<br>
                <small>(Link de direcionamento ao clicar no banner)</small>
                {{ Form::input('text', 'url_link', null, ['class' => 'form-control', '', 'placeholder' => 'Link']) }}
            </div>
            <div class="form-group col-md-4">
                {{ Form::label('Texto') }}<br>
                <small>(Texto ao passar o mouse em cima do banner. Max 120 caracteres)</small>
                {{ Form::input('text', 'alt_text', null, ['class' => 'form-control', '', 'placeholder' => 'Texto', 'maxlength' => 120]) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-2">
                {{ Form::label('Data Início') }}<br>
                <small>(Período de aparecimento)</small>
                {{ Form::input('text', 'data_inicio', isset($dataInicio) ? $dataInicio : null, ['class' => 'form-control datepicker', 'id' => 'data_inicio', 'placeholder' => 'Data Início']) }}
            </div>
            <div class="form-group col-md-1">
                {{ Form::label('Hora Início') }}<br><br>
                {{ Form::input('text', 'hora_inicio', $horaInicio, ['class' => 'form-control', 'id' =>'hora_inicio', 'placeholder' => 'Hora Início']) }}
            </div>
            <div class="form-group col-md-2">
                {{ Form::label('Data Fim') }}<br>
                <small>(Fim do período)</small>
                {{ Form::input('text', 'data_final', isset($dataFim) ? $dataFim : null, ['class' => 'form-control datepicker', 'id' => 'data_final', 'placeholder' => 'Data Fim']) }}
            </div>
            <div class="form-group col-md-1">
                {{ Form::label('Hora Fim') }}<br><br>
                {{ Form::input('text', 'hora_final', $horaFim, ['class' => 'form-control', 'id' => 'hora_final', 'placeholder' => 'Hora Fim']) }}
            </div>
            <div class="form-group col-md-2">
                {{ Form::label('Padrão') }}<br>
                <small>(O banner é default?)</small>
                {{ Form::select('banner_default', $lista_sim_nao, (isset($obj->ordem) ? $obj->ordem : null), ['class' => 'form-control']) }}
            </div>
            <div class="form-group col-md-2">
                {{ Form::label('Ordem') }}<br>
                <small>(Sequência do banner)</small>
                {{ Form::input('text', 'banner_ordem', null, ['class' => 'form-control','onkeypress'=>'return onlyNumbers(event)', 'id' => 'banner_ordem', 'placeholder' => '1,2,3']) }}
            </div>
        </div>

        <div class="row">
{{--            <div class="form-group col-md-2">--}}
{{--                {{ Form::label('Largura') }} <br>--}}
{{--                <small>(Largura padrão do banner)</small>--}}
{{--                {{ Form::input('number', 'banner_largura', null, ['class' => 'form-control ', 'id' => 'banner_largura', 'placeholder' => '500']) }}--}}
{{--            </div>--}}
{{--            <div class="form-group col-md-2">--}}
{{--                {{ Form::label('Altura') }}<br>--}}
{{--                <small>(Altura padrão do banner)</small>--}}
{{--                {{ Form::input('number', 'banner_altura', null, ['class' => 'form-control', 'id' => 'banner_altura', 'placeholder' => '200']) }}--}}
{{--            </div>--}}
            <div class="form-group col-md-6">
                {{ Form::label('Tempo Transição') }}<br>
                <small>(Transições entre os banner em segundos caso esteja cadastrado mais de um banner no mesmo período.)</small>
                <div class="row">
                    <div class="col-md-4">
                        {{ Form::input('text', 'tempo_transicao_seg', null, ['class' => 'form-control','onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'segundos']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-2" style="display: none">
                {{ Form::label('Mostrar Pesquisa?') }}
                {{ Form::select('mostrar_pesquisa', $lista_sim_nao, (isset($obj->mostrar_pesquisa) ? $obj->mostrar_pesquisa : 0), ['class' => 'form-control']) }}
            </div>
        </div>
        <div class="row">
            <div id="box_upload" class="form-group col-md-3">
                {{ Form::label('Imagem do banner') }}<br>
                <small @if (!empty($obj) && $obj->pagina != 'home') style="display: none;" @endif; id="dimensoes_home">(Dimensões: 1920x800px)</small>
                <small @if (empty($obj) || $obj->pagina == 'home') style="display: none;" @endif; id="dimensoes_outros" >(Dimensões: 1920x500px)</small>
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

        $('#hora_final').wickedpicker({
            now: $('#hora_final').val(),
            twentyFour: true,
            upArrow: 'fa fa-chevron-up fa-lg',  //The up arrow class selector to use, for custom CSS
            downArrow: 'fa fa-chevron-down fa-lg', //The down arrow class selector to use, for custom CSS
            showSeconds: false,
        });

    });
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
