@extends('layouts.app')

@section('styles')
    <style>
        .select2.select2-container.select2-container--default,
        .select2-selection__rendered,
        .select2-selection.select2-selection--multiple
        .select2-search.select2-search--inline,
        .select2-search__field {
            width:100% !important;
        }

    </style>
@endsection

@section('content')
    <div class="box padding20">
        <h2 class="table"><span>Gerar Cupons em Massa</span></h2>
        <hr class="hr"/>

        {{ Form::open(['url' => '/admin/cupom_random/salvar']) }}

        <div class="row">
            <div class="form-group col-md-6">
                {{ Form::label('Projeto') }}<br>
                {{ Form::select('faculdade', $lista_faculdades, null, [
                    'name' => 'faculdade',
                    'class' => 'form-control myselect', 'allowClear' => true,
                    'data-placeholder' => 'Projeto (pesquise pelo nome do projeto)',
                    'id' => 'autocomplete-projeto',
                    'width' => '100%'
                ]) }}
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6">
                {{ Form::label('Total de Cupons a serem gerados') }}
                {{ Form::input('text', 'total_cupons', null, ['class' => 'form-control', 'placeholder' => 'Número de Cupons a ser gerado']) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                {{ Form::label('Número Máximo de Usos') }}
                {{ Form::input('text', 'numero_maximo_usos', isset($cupom->numero_maximo_usos) ? $cupom->numero_maximo_usos : '',
                    [
                        'class' => 'form-control'
                    ]
                ) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('Número Máximo de Produtos') }}
                {{ Form::input('text', 'numero_maximo_produtos', isset($cupom->numero_maximo_produtos) ? $cupom->numero_maximo_produtos : '',
                    [
                        'class' => 'form-control'
                    ]
                ) }}
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('Tipo') }} <small>*</small>
            <br />

            <div class="col-sm-2">
                <input type="radio" id="marca_desconto" class="form-control radioo" name="tipo_cupom_desconto" @if (isset($cupom->tipo_cupom_desconto) && (($cupom->tipo_cupom_desconto == 1) OR !$cupom->tipo_cupom_desconto)) checked @endif value="1" style="opacity: 100; display: block;">
                Percentual (%)
            </div>
            <div class="col-sm-2">
                <input type="radio" id="marca_moeda" class="form-control radioo" name="tipo_cupom_desconto" @if (isset($cupom->tipo_cupom_desconto) && $cupom->tipo_cupom_desconto == 2) checked @endif value="2" style="opacity: 100; display: block;" />
                Espécie (R$)
            </div>
        </div>
        <div class="row"></div>
        <div id="box_desconto" class="form-group"
             @if(isset($cupom->tipo_cupom_desconto) && (!$cupom->tipo_cupom_desconto OR $cupom->tipo_cupom_desconto == 1))
             style="display: block; margin-top: 30px;"
             @else
             style="display: none; margin-top: 30px;"
            @endif>

            {{ Form::label('Percentual (%)') }} <small>*</small>
            {{ Form::input('number', 'valor', (isset($cupom->valor)) ? $cupom->valor : null, ['class' => 'form-control', '', 'placeholder' => 'Valor', 'style' => 'width: 160px;', 'id' => 'valor_percentual', 'max' => '100', 'step' => '1']) }}
        </div>
        <div id="box_moeda" class="form-group"
             @if(isset($cupom->tipo_cupom_desconto) && $cupom->tipo_cupom_desconto == 2)
             style="display: block; margin-top: 30px;"
             @else
             style="display: none; margin-top: 30px;"
            @endif>

            {{ Form::label('Valor (R$)') }} <small>*</small>
            {{ Form::input('text', 'valor', (isset($cupom->valor)) ? $cupom->valor : null, ['class' => 'form-control moeda', '', 'placeholder' => 'Valor', 'style' => 'width: 160px;', 'id' => 'valor_moeda']) }}
        </div>

        @if(Request::is('*/editar'))
            <input type="hidden" name="valor" id="valor" value="{{$cupom->valor}}" />
        @else
            <input type="hidden" name="valor" id="valor" value="" />
        @endif

        <br>
        <br>
        <br>
        <div id="gerenciar">
            <ul class="nav nav-tabs">
                <li class="nav-item active"><a data-toggle="tab" href="#cursos">Cursos</a></li>
                <li class="nav-item"><a data-toggle="tab" href="#categoriasacursos">Categorias de Cursos</a></li>
                <li class="nav-item"><a data-toggle="tab" href="#trilhas">Trilhas</a></li>
                <!--<li class="nav-item"><a data-toggle="tab" href="#assinaturas">Assinaturas</a></li>
                <li class="nav-item"><a data-toggle="tab" href="#eventos">Eventos</a></li>-->
            </ul>
            <div class="tab-content">
                <div id="cursos" class="tab-pane fade in active">
                    <div id="bloco_cursos">
                        <div class="cursos">
                            <br>
                            <br>
                            <div class="form-group">
                                {{ Form::label('Selecione um Curso') }}
                                {{ Form::select('fk_curso[]', $lista_cursos, null, [
                                    'multiple' => 'multiple', 'name' => 'fk_curso[]',
                                    'class' => 'form-control myselect', 'allowClear' => true,
                                    'data-placeholder' => 'Cursos (pesquise por nome ou tipo)',
                                    'id' => 'autocomplete-cursos'
                                ]) }}
                            </div>
                            <hr/>
                        </div>
                    </div>
                </div>

                <div id="categoriasacursos" class="tab-pane fade">
                    <div id="bloco_categorias">
                        <div class="categorias" data-contador="1">
                            <br>
                            <br>
                            <div class="form-group">
                                {{ Form::label('Categorias') }}
                                {{ Form::select('fk_categoria[]', $categorias, null, [
                                    'multiple' => 'multiple', 'id' => 'fk_cursos_categoria', 'name' => 'fk_categoria[]',
                                    'class' => 'form-control myselect', 'allowClear' => true,
                                    'data-placeholder' => 'Categorias (pesquise por nome da categoria)',
                                    'id' => 'autocomplete-categorias'
                                ]) }}
                            </div>
                            <hr/>
                        </div>
                    </div>
                </div>

                <div id="trilhas" class="tab-pane fade">
                    <div id="bloco_trilhas">
                        <div class="trilhas" data-contador="1">
                            <br>
                            <br>
                            <div class="form-group">
                                {{ Form::label('Trilhas') }}
                                {{ Form::select('fk_trilha[]', $lista_trilhas, null, [
                                    'multiple' => 'multiple', 'name' => 'fk_trilha[]',
                                    'class' => 'form-control myselect', 'allowClear' => true,
                                    'data-placeholder' => 'Trilhas (pesquise por nome da trilha)',
                                    'id' => 'autocomplete-trilha'
                                ]) }}
                            </div>
                            <hr/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <a href="{{ route('admin.cupom') }}" class="btn btn-default">Voltar</a>
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function () {
            $('.myselect').select2({
                allowClear: true,
                tags: true,
            });
            $('#autocomplete-projeto').select2({
                allowClear: true,
                tags: true,
            });

            $('#marca_desconto').click(function() {
                console.log('teste')
                $('#box_moeda').hide();
                let inputs = document.getElementById('box_moeda').getElementsByTagName('input');

                for(let i = 0; i < inputs.length ; i++)
                    inputs[i].disabled = true


                inputs = document.getElementById('box_desconto').getElementsByTagName('input');

                for(let i = 0; i < inputs.length ; i++)
                    inputs[i].disabled = false
                $('#box_desconto').show();
                $('#valor').val($('#valor_percentual').val());
            });

            $('#marca_moeda').click(function() {
                console.log('teste 2')
                $('#box_desconto').hide();
                let inputs = document.getElementById('box_desconto').getElementsByTagName('input');

                for(let i = 0; i < inputs.length ; i++)
                    inputs[i].disabled = true


                inputs = document.getElementById('box_moeda').getElementsByTagName('input');

                for(let i = 0; i < inputs.length ; i++)
                    inputs[i].disabled = false
                $('#box_moeda').show();
                $('#valor').val($('#valor_moeda').val());
            });

            $('.moeda').mask('#.##0,00', {reverse: true});

            $('#valor_moeda').change(function() {
                $('#valor').val($(this).val());
            });

            $('#valor_percentual').change(function() {
                $('#valor').val($(this).val());
            });

        });
    </script>
@endpush

