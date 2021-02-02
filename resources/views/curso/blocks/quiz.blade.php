<div class="row">
    <div class="col-md-6">
        <h3>Questionário</h3>
    </div>
    <div class="col-md-6">
        <a href="javascript:;" id="btn_incluir_quiz" class="btn btn-success right">+ Questão</a>
    </div>
</div>

<div class="row" id="bloco_quiz" data-contador="1">
    @if(Request::is('*/editar') && isset($quiz))
    <input type="hidden" name="quiz[fk_quiz]" value="<?php echo $quiz->id; ?>" />
    @endif
    <div class="well" id="quizes">
    <?php if(isset($quiz) && isset($quiz_questao) && count($quiz_questao)) : ?>
            <?php foreach($quiz_questao as $key => $questao) : ?>
                <div class="row quiz" data-id="<?php echo $key + 1; ?>">
                    @if(Request::is('*/editar'))
                        <input type="hidden" name="<?php echo "quiz[questao][".($key)."][id_questao]"; ?>"
                               value="<?php echo $questao->id; ?>" />
                    @endif
                    <div class="form-group">
                        <div class="col-md-1" style="float:right; margin-right: -11px;">
                            <a href="javascript:;" style="margin-bottom: 25px;" class="btn btn-danger btn_excluir_questao" title="Excluir Questão" onclick="return confirm('Deseja realmente excluir?')"><i class="fa fa-fw fa-trash"></i></a>
                        </div><br />
                        {{ Form::label('Questão') }}
                        <div class="input-outer">
                            {{ Form::input('text', 'quiz[questao]['.($key).'][titulo]', isset($questao['titulo']) ? $questao['titulo'] : null,
                                ['class' => 'form-control', '', 'placeholder' => 'Questão']
                            ) }}
                            <i class="clear fa fa-times-circle"></i>
                        </div>
                    </div>
                    <hr />
                    <div class="well">
                        <h3> Alternativas: </h3>
                        @foreach($quiz_resposta[$key] as $chave => $alternativa)
                            @if(Request::is('*/editar'))
                                <input type="hidden" name="<?php echo "quiz[op][".$key."][".$chave."][id_alternativa]"; ?>"
                                        value="<?php echo $alternativa->id; ?>" />
                            @endif
                            <div class="form-group">
                                {{ Form::label($alternativa->label) }}
                                <div class="input-outer">
                                    {{ Form::input('text', 'quiz[op]['.($key).']['.$chave.'][alternativa]',
                                        isset($quiz_resposta[$key][$chave]) ? $quiz_resposta[$key][$chave]['descricao'] : '',
                                        ['class' => 'form-control', '', 'placeholder' => $opcoes[$chave+1]]
                                    ) }}
                                    <i class="clear fa fa-times-circle"></i>
                                </div>
                            </div>
                            <hr />
                        @endforeach
                    </div>
                    <div class="well" style="background: #acba93;">
                        <div class="form-group">
                            {{ Form::label('Resposta Correta: ') }}
                            {{ Form::select('quiz[questao]['.($key) .'][resposta_correta]', [null	=>'Selecione'] + $opcoes, (isset($questao->resposta_correta) ? $questao->resposta_correta : 1), ['class' => 'form-control resposta-correta', 'style' => 'width: 50%; min-width: 120px;']) }}
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
    <?php else : ?>
        <div class="row quiz" data-id="1">
                    <div class="form-group">
                        {{ Form::label('Questão') }} #1
                        <div class="input-outer">
                            {{ Form::input('text', 'quiz[questao][1][titulo]', null, ['class' => 'form-control', '', 'placeholder' => 'Questão']) }}
                            <i class="clear fa fa-times-circle"></i>
                        </div>
                    </div>
                    <hr />
                    <div class="well">
                        <h3> Alternativas: </h3>
                        <?php foreach($opcoes as $chave => $alternativa) : ?>
                        <div class="form-group">
                            {{ Form::label($alternativa) }}
                            <div class="input-outer">
                                {{ Form::input('text', 'quiz[op][1]['.$chave.'][alternativa]', null, ['class' => 'form-control', '', 'placeholder' => $alternativa]) }}
                                <i class="clear fa fa-times-circle"></i>
                            </div>
                        </div>
                        <hr />
                        <?php endforeach; ?>
                    </div>
                    <div class="well" style="background: #acba93;">
                        <div class="form-group">
                            {{ Form::label('Resposta Correta: ') }}
                            {{ Form::select('quiz[questao][1][resposta_correta]', [null	=>'Selecione'] + $opcoes, (isset($quiz_questao['resposta_correta']) ? $quiz_questao['resposta_correta'] : 1), ['class' => 'form-control resposta-correta', 'id' => 'respostaSelection', 'style' => 'width: 50%; min-width: 120px;']) }}
                        </div>
                    </div>
                </div>
    <?php endif; ?>
</div>
<hr />

<!-- modelo quiz (para clonar)  -->
<div id="default_quiz" style="display: none;" data-id="__X__">
    <br />
    <div class="row quiz" data-id="__X__">
        <div class="form-group">
            <div class="col-md-1" style="float:right; margin-right: -11px;">
                <a href="javascript:;" style="margin-bottom: 25px;" class="btn btn-danger btn_excluir_questao" title="Excluir Questão" onclick="return confirm('Deseja realmente excluir?')"><i class="fa fa-fw fa-trash"></i></a>
            </div><br />
            {{ Form::label('Questão') }} #__X__
            <div class="input-outer">
                {{ Form::input('text', 'quiz[questao][__X__][titulo]', null, ['class' => 'form-control', '', 'placeholder' => 'Questão']) }}
                <i class="clear fa fa-times-circle"></i>
            </div>
        </div>
        <hr />
        <div class="well">
            <h3> Alternativas: </h3>
            <?php foreach($opcoes as $chave => $alternativa) : ?>
            <div class="form-group">
                {{ Form::label($alternativa) }}
                <div class="input-outer">
                    {{ Form::input('text', 'quiz[op][__X__][' . $chave . '][alternativa]', null, ['class' => 'form-control', '', 'placeholder' => $alternativa]) }}
                    <i class="clear fa fa-times-circle"></i>
                </div>
            </div>
            <hr />
            <?php endforeach; ?>
        </div>
        <div class="well" style="background: #acba93;">
            <div class="form-group">
                {{ Form::label('Resposta Correta: ') }}
                {{ Form::select('quiz[questao][__X__][resposta_correta]', [null	=>'Selecione'] + $opcoes, 1, ['class' => 'form-control resposta-correta', 'style' => 'width: 50%; min-width: 120px;']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 
<style>
    .input-outer {
        display: flex;
    }
    .input-outer input {
        flex: 1;
    }
    .clear {
        position: relative;
        float: right;
        height: 10px;
        width: 10px;
        top: 10px;
        right: 2%;
        color: red;
        font-weight: bold;
        background-color: white;
        text-align: center;
        cursor: pointer;
    }
</style>
<!-- FIM quiz -->
