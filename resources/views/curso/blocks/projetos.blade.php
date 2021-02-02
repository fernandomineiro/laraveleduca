<div class="row">
    <div class="col-md-12">
        {{Form::label('Projetos') }} <small>*</small>
        {{Form::select('selectCursosFaculdade', $faculdades, $projetosSelecionados,
            [
                'multiple' => 'multiple', 'id' => 'selectCursosFaculdade', 'name' => 'selectCursosFaculdade',
                'class' => 'form-control myselect', 'v-model' => 'faculdades', 'allowClear' => true,
                'data-placeholder' => 'Projetos', 'data-msg-required' => 'Este campo é obrigatório', 'required' => true
            ]
        )}}
    </div>
</div>
<script>var projetosSelecionados = [];</script>
<div id="containerProjects" style="margin-top: 30px">
    @if(!is_null(old('fk_cursos_faculdade')))
        @foreach(old('fk_cursos_faculdade') as $key => $faculdade)
            @if($key != '__IDX__')
                <div class="row" id="projeto_{{$faculdade['fk_faculdade']}}">
                    <div class="col-md-12">
                        <div class="well">
                            <div class="row">
                                <div class="col-md-12">
                                    <h4>{{ $faculdades[$faculdade['fk_faculdade']] }}</h4>
                                    <script>projetosSelecionados.push("{{$faculdade['fk_faculdade']}}")</script>
                                    {{Form::input('hidden', 'fk_cursos_faculdade['.$faculdade['fk_faculdade'].'][fk_faculdade]', $faculdade['fk_faculdade'])}}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2" style="margin-top: 30px;">
                                    <span style="font-weight:bold;">Disponibilidade</span>
                                </div>
                                <div class="col-md-10">
                                    <div class="col-lg-3 col-md-6 col-sm-6">
                                        {{ Form::label('Prazo para cursar') }}
                                        {{ Form::input('text', 'fk_cursos_faculdade['.$faculdade['fk_faculdade'].'][duracao_dias]',
                                            (isset($faculdade['duracao_dias'])) ? $faculdade['duracao_dias'] : 365,
                                            ['class' => 'form-control', '', 'placeholder' => 'Dias']
                                        ) }}
                                    </div>
                                    <div class="col-lg-3 col-md-6 col-sm-6">
                                        {{ Form::label('Prazo para venda') }}
                                        {{ Form::input('text', 'fk_cursos_faculdade['.$faculdade['fk_faculdade'].'][disponibilidade_dias]',
                                            $faculdade['disponibilidade_dias'],
                                            ['class' => 'form-control', '', 'placeholder' => 'Dias']
                                        ) }}
                                    </div>
                                </div>
                                @if(!is_null(old('conclusao_cursos_faculdades')))
                                    @isset($lista_conclusao[$faculdade['fk_faculdade']]['lista_certificados'])
                                        @php( $lista_certificados = $lista_conclusao[$faculdade['fk_faculdade']]['lista_certificados'])
                                    @endif

                                    @php($lista_conclusao = old('conclusao_cursos_faculdades'))

                                    <div class="col-md-2" style="margin-top: 30px;">
                                        <span style="font-weight:bold;">Critérios de Conclusão</span>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="col-lg-3 col-md-6 col-sm-6">
                                            {{ Form::label('Opções de Certificado') }}
                                            {{ Form::select('conclusao_cursos_faculdades[' . $faculdade['fk_faculdade'] . '][fk_certificado]',
                                                ['' => 'Este curso não emite certificado'] + $lista_certificados->toarray(),
                                                $lista_conclusao[$faculdade['fk_faculdade']]['fk_certificado'],
                                                ['class' => 'form-control']
                                            ) }}
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6">
                                            {{ Form::label('Nota de Corte Questionário') }}
                                            {{ Form::select('conclusao_cursos_faculdades['.$faculdade['fk_faculdade'].'][nota_questionario]',
                                                    $lista_percentual, $lista_conclusao[$faculdade['fk_faculdade']]['nota_questionario'],
                                                    ['class' => 'form-control']
                                                ) }}
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6">
                                            {{ Form::label('Opções de Trabalho') }}
                                            {{ Form::select('conclusao_cursos_faculdades['.$faculdade['fk_faculdade'].'][nota_trabalho]',
                                                    ['' => 'Este curso não possui trabalho', '0' => 'Possui trabalho, sem nota de corte'] +
                                                        array(
                                                            'Opções com nota de corte' => $lista_percentual_trabalho,
                                                        ),
                                                    (isset($lista_conclusao[$faculdade['fk_faculdade']]['nota_trabalho'])? $lista_conclusao[$faculdade['fk_faculdade']]['nota_trabalho'] : ''),
                                                    ['class' => 'form-control']
                                                ) }}
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6">
                                            {{ Form::label('Frequência Mínima') }}
                                            @if((isset($tipo) && $tipo == 1) OR (isset($curso->fk_cursos_tipo) && $curso->fk_cursos_tipo == 1))
                                            {{ Form::select('conclusao_cursos_faculdades['.$faculdade['fk_faculdade'].'][frequencia_minima]',
                                                    $lista_percentual, (isset($lista_conclusao[$faculdade['fk_faculdade']]['frequencia_minima'])) ? $lista_conclusao[$faculdade['fk_faculdade']]['frequencia_minima'] : null,
                                                    ['class' => 'form-control', 'disabled' => true ]
                                                ) }}
                                            @else
                                            {{ Form::select('conclusao_cursos_faculdades['.$faculdade['fk_faculdade'].'][frequencia_minima]',
                                                    $lista_percentual, $lista_conclusao[$faculdade['fk_faculdade']]['frequencia_minima'],
                                                    ['class' => 'form-control']
                                                ) }}
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @else
        @foreach($lista_faculdades as $faculdade)
            <div class="row" id="projeto_{{$faculdade['id']}}">
                <div class="col-md-12">
                    <div class="well">
                        <div class="row">
                            <div class="col-md-12">
                                <h4>{{ $faculdade['descricao'] }}</h4>
                                <script>projetosSelecionados.push("{{$faculdade['id']}}")</script>
                                {{Form::input('hidden', 'fk_cursos_faculdade['.$faculdade['id'].'][fk_faculdade]', $faculdade['id'])}}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2" style="margin-top: 30px;">
                                <span style="font-weight:bold;">Disponibilidade</span>
                            </div>
                            <div class="col-md-10">
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    {{ Form::label('Prazo para cursar') }}
                                    {{ Form::input('text', 'fk_cursos_faculdade['.$faculdade['id'].'][duracao_dias]',
                                        (isset($faculdade['duracao_dias'])) ? $faculdade['duracao_dias'] : 365,
                                        ['class' => 'form-control', '', 'placeholder' => 'Dias']
                                    ) }}
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    {{ Form::label('Prazo para venda') }}
                                    {{ Form::input('text', 'fk_cursos_faculdade['.$faculdade['id'].'][disponibilidade_dias]',
                                        $faculdade['disponibilidade_dias'],
                                        ['class' => 'form-control', '', 'placeholder' => 'Dias']
                                    ) }}
                                </div>
                            </div>
                            <div class="col-md-2" style="margin-top: 30px;">
                                <span style="font-weight:bold;">Critérios de Conclusão</span>
                            </div>
                            <div class="col-md-10">
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    {{ Form::label('Opções de Certificado') }}
                                    {{ Form::select('conclusao_cursos_faculdades[' . $faculdade['id'] . '][fk_certificado]',
                                        ['' => 'Este curso não emite certificado'] + $lista_conclusao[$faculdade['id']]['lista_certificados']->toarray(),
                                        $lista_conclusao[$faculdade['id']]['fk_certificado'],
                                        ['class' => 'form-control']
                                    ) }}
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    {{ Form::label('Nota de Corte Questionário') }}
                                    {{ Form::select('conclusao_cursos_faculdades['.$faculdade['id'].'][nota_questionario]',
                                            $lista_percentual, $lista_conclusao[$faculdade['id']]['nota_questionario'],
                                            ['class' => 'form-control']
                                        ) }}
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    {{ Form::label('Opções de Trabalho') }}
                                    {{ Form::select('conclusao_cursos_faculdades['.$faculdade['id'].'][nota_trabalho]',
                                            ['' => 'Este curso não possui trabalho', '0' => 'Possui trabalho, sem nota de corte'] +
                                                array(
                                                    'Opções com nota de corte' => $lista_percentual_trabalho,
                                                ),
                                            (isset($lista_conclusao[$faculdade['id']]['nota_trabalho'])? $lista_conclusao[$faculdade['id']]['nota_trabalho'] : ''),
                                            ['class' => 'form-control']
                                        ) }}
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    {{ Form::label('Frequência Mínima') }}
                                    <?php if((isset($tipo) && $tipo == 1) OR (isset($curso->fk_cursos_tipo) && $curso->fk_cursos_tipo == 1)) : ?>
                                    {{ Form::select('conclusao_cursos_faculdades['.$faculdade['id'].'][frequencia_minima]',
                                            $lista_percentual, $lista_conclusao[$faculdade['id']]['frequencia_minima'],
                                            ['class' => 'form-control', 'disabled' => true ]
                                        ) }}
                                    <?php else : ?>
                                    {{ Form::select('conclusao_cursos_faculdades['.$faculdade['id'].'][frequencia_minima]',
                                            $lista_percentual, $lista_conclusao[$faculdade['id']]['frequencia_minima'],
                                            ['class' => 'form-control']
                                        ) }}
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                {{ Form::label('Curso grátis') }}
                                {{ Form::checkbox('fk_cursos_faculdade['.$faculdade['id'].'][gratis]', 1, $faculdade['curso_gratis']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
<div id="default_projeto" style="display: none;">
    <div class="row" id="projeto___IDX__">
        <div class="col-md-12">
            <div class="well">
                <div class="row">
                    <div class="col-md-12">
                        <h4>__PROJECTNAME__</h4>
                        {{Form::input('hidden', 'fk_cursos_faculdade[__IDX__][fk_faculdade]', '__IDX__')}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2" style="margin-top: 30px;">
                        <span style="font-weight:bold;">Disponibilidade</span>
                    </div>
                    <div class="col-md-10">
                        <div class="col-lg-3 col-md-6 col-sm-6">
                            {{ Form::label('Prazo para cursar') }}
                            {{ Form::input('text', 'fk_cursos_faculdade[__IDX__][duracao_dias]',
                                (isset($item['duracao_dias']) ? $item['duracao_dias'] : 365),
                                ['class' => 'form-control', '', 'placeholder' => 'Dias']
                            ) }}
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6">
                            {{ Form::label('Prazo para venda') }}
                            {{ Form::input('text', 'fk_cursos_faculdade[__IDX__][disponibilidade_dias]',
                                (isset($item['disponibilidade_dias']) ? $item['disponibilidade_dias'] : ''),
                                ['class' => 'form-control', '', 'placeholder' => 'Dias']
                            ) }}
                        </div>
                    </div>
                    <div class="col-md-2" style="margin-top: 30px;">
                        <span style="font-weight:bold;">Critérios de Conclusão</span>
                    </div>
                    <div class="col-md-10">
                        <div class="col-lg-3 col-md-6 col-sm-6">
                            {{ Form::label('Opções de Certificado') }}
                            {{ Form::select('conclusao_cursos_faculdades[__IDX__][fk_certificado]', [], null,
                            ['class' => 'form-control', 'id' => 'opcaoCertificado___IDX__']) }}
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6">
                            {{ Form::label('Nota de Corte Questionário') }}
                            {{ Form::select('conclusao_cursos_faculdades[__IDX__][nota_questionario]', $lista_percentual, null,
                            ['class' => 'form-control']) }}
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6">
                            {{ Form::label('Opções de Trabalho') }}
                            {{ Form::select('conclusao_cursos_faculdades[__IDX__][nota_trabalho]',
                            ['' => 'Este curso não possui trabalho', '0' => 'Possui trabalho, sem nota de corte'] +
                            array(
                                'Opções com nota de corte' => $lista_percentual_trabalho,
                            ),
                            null,
                            ['class' => 'form-control']) }}
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-62">
                            {{ Form::label('Frequência Mínima') }}
                            <?php if((isset($tipo) && $tipo == 1) OR (isset($curso->fk_cursos_tipo) && $curso->fk_cursos_tipo == 1)) : ?>
                            {{ Form::select('conclusao_cursos_faculdades[__IDX__][frequencia_minima]', $lista_percentual, null,
                                                ['class' => 'form-control', 'disabled' => true]) }}
                            <?php else : ?>
                            {{ Form::select('conclusao_cursos_faculdades[__IDX__][frequencia_minima]', $lista_percentual, null,
                                                ['class' => 'form-control']) }}
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        {{ Form::label('Curso grátis') }}
                        {{ Form::checkbox('fk_cursos_faculdade[__IDX__][gratis]') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<hr>
<script>
    Array.prototype.diff = function (a) {
        return this.filter(function (i) { return a.indexOf(i) < 0; });
    };

    if (typeof Array.isArray === 'undefined') {
        Array.isArray = function (obj) {
            return Object.prototype.toString.call(obj) === '[object Array]';
        }
    };

    $('#selectCursosFaculdade').change(function () {
        const selectFaculdade = $(this);
        if ($(this).val() == '') {
            $('#containerProjects').html('');
            return;
        }

        $(this).val().diff(projetosSelecionados).forEach(function (element) {
            if (projetosSelecionados.indexOf(element) > -1) {
                return;
            }

            projetosSelecionados.push(element);

            let _template = $('#default_projeto').html();

            var regex = new RegExp('__IDX__', 'g');
            _template = _template.replace(regex, element);

            const regexName = new RegExp('__PROJECTNAME__', 'g');
            $(selectFaculdade).find('option').each(function () {
                if ($(this).val() == element) {
                    _template = _template.replace(regexName, $(this).text());
                }
            });

            $('#containerProjects').append(_template);

            $.getJSON("/admin/certificadosFaculdade/" + element, function (json) {
                const newCombo = $('#opcaoCertificado_' + element);
                json.forEach(function (data) {
                    $.each(data, function (key, value) {
                        if (key == 'Personalizado') {
                            if (value.length == undefined && Object.keys(value).length) {
                                //$(newCombo).append('<optgroup label="Personalizado"></optgroup>');
                                $.each(value, function (key1, value1) {
                                    //$(newCombo).find('optgroup').append('<option value="' + key1 + '">' + value1 + '</option>');
                                    $(newCombo).append('<option value="' + key1 + '">' + value1 + '</option>');
                                });
                            }
                        } else {
                            $(newCombo).append('<option value="' + key + '">' + value + '</option>');
                        }
                    })
                })
            });
        });

        projetosSelecionados.diff($(this).val()).forEach(function (element) {
            $('#projeto_' + element).remove();
            var index = projetosSelecionados.indexOf(element);
            if (index > -1) {
                projetosSelecionados.splice(index, 1);
            }
        });
    });
    $(document).ready(function () {
        $('#selectCursosFaculdade').trigger('change');
    });
</script>

<style>
@media only screen and (min-width: 1200px) and (max-width: 1300px) {
    #containerProjects label{
        height: 40px;
    }
}
.select2.select2-container.select2-container--default{
    width:100% !important;
}

</style>
