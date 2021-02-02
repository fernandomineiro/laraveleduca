<!-- INICIO MODULOS -->
@if((isset($tipo) && ($tipo == 1 || $tipo == 4)) OR (isset($curso->fk_cursos_tipo) && ($curso->fk_cursos_tipo == 1 || $curso->fk_cursos_tipo == 4)))
    <hr />

    <div class="row">
        <div class="col-md-6">
            <h3>Estrutura do Curso</h3>
        </div>
        <div class="col-md-6">
            <a href="javascript:;" id="btn_incluir_secao" class="btn btn-success right">+ Seção</a>
        </div>
    </div>

    <div class="well" id="bloco_estrutura">
        @if(isset($secoes_cadastradas) && count($secoes_cadastradas))

            <?php $count_secao = 0; ?>
            @foreach($secoes_cadastradas as $id_secao => $secao)
                <?php $count_secao++; ?>
                <div class="well row secao" data-id="<?php echo $id_secao; ?>" data-contador="<?php echo $count_secao; ?>" style="margin-left: 0; background: #f2bca2;">
                    @if(Request::is('*/editar'))
                        <input type="hidden" name="<?php echo "secao[".$id_secao."]"."[id_secao]"; ?>" value="<?php echo $id_secao; ?>" />
                    @endif
                    <div class="col-md-1">
                        {{ Form::label('Seção:') }}
                        <div><strong><?php echo $id_secao; ?></strong></div>
                    </div>
                    <div class="col-md-5">
                        {{ Form::label('Descrição') }}
                        {{ Form::input('text', 'secao['.$id_secao.'][titulo]', $secao['titulo'], ['class' => 'form-control', 'maxlength' => '100']) }}
                    </div>
                    <div class="col-md-2">
                        {{ Form::label('Ordem') }}
                        {{ Form::select('secao['.$id_secao.'][ordem]', $lista_ordem, (isset($secao['ordem']) ? $secao['ordem'] : null), ['class' => 'form-control', 'style' => 'width: 50%;']) }}
                    </div>
                    <div class="col-md-2">
                        <br />
                        <a href="javascript:;" data-secao="<?php echo $id_secao; ?>" class="btn btn-success right btn_incluir_modulo">+ Aula</a>
                    </div>
                </div>

                <?php $count_modulo = 0; ?>
                <div class="well modulos s<?php echo $id_secao; ?>" style="background: #fcdfd1;" data-contador="<?php echo $count_modulo; ?>">
                    @foreach($secao['modulos'] as $id_modulo => $modulo)
                        <?php $count_modulo++; ?>
                        <div class="row modulo" data-secao="<?php echo $id_secao; ?>" data-contador="<?= $count_modulo ?>">
                            @if(Request::is('*/editar'))
                                <input type="hidden" name="<?php echo "modulos[".$id_secao."]"."[".$id_modulo."][id_modulo]"; ?>" value="<?php echo $id_modulo; ?>" />
                            @endif
                            <div class="col-md-1">
                                {{ Form::label('Aula:') }}
                                <div><strong><?php echo $id_secao; ?>.<?php echo $count_modulo; ?></strong></div>
                            </div>
                            <div class="col-md-3">
                                {{ Form::label('Nome') }}
                                {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][titulo]', $modulo['titulo'], ['class' => 'form-control nome_vimeo', 'maxlength' => '100']) }}
                            </div>
                            <div class="col-md-2">
                                {{ Form::label('Código Vimeo') }}
                                {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][url_video]', $modulo['url_video'], ['class' => 'form-control vimeoId', 'maxlength' => '50']) }}
                            </div>
                            <div class="col-md-2">
                                {{ Form::label('Duração Aula:') }}
                                {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][carga_horaria]', $modulo['carga_horaria'], ['class' => 'form-control carga_horaria', 'maxlength' => '8', 'onchange' => 'duracaoTotal()']) }}
                            </div>
                            <div class="col-md-3">
                                <div id="box_upload" class="form-group">
                                    {{ Form::label('Arquivo (PDF / Video / Podcast)') }}

                                    @if($modulo['url_arquivo'])
                                        <a href="{{ URL::asset('files/modulo/modulos/' . $modulo['url_arquivo']) }}" target="_blank">[Ver Arquivo]</a>
                                    @endif

                                    {{ Form::file('modulos['.$count_secao.']['.$count_modulo.'][url_arquivo]') }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                {{ Form::label('A aula será exibida ao vivo?') }}
                                {{ Form::select('modulos['.$id_secao.']['.$id_modulo.'][aula_ao_vivo]', $lista_check, isset($modulo['aula_ao_vivo']) ? $modulo['aula_ao_vivo'] : null, ['class' => 'form-control', 'maxlength' => '100', 'onchange' => 'abreFormAulaAoVivo(event)']) }}
                            </div>
                            @if(isset($modulo['aula_ao_vivo']) && !empty($modulo['aula_ao_vivo']))
                                    <div id="form-aula-ao-vivo{{$id_secao}}{{$id_modulo}}">
                                        <div class="col-md-3">
                                            {{ Form::label('Data da aula ao vivo') }}
                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][data_aula_ao_vivo]', (isset($modulo['data_aula_ao_vivo']) && !empty($modulo['data_aula_ao_vivo']) && $modulo['data_aula_ao_vivo'] != '0000-00-00') ?  implode('/', array_reverse(explode('-', $modulo['data_aula_ao_vivo']))) : null, ['class' => 'form-control datepicker', '', 'placeholder' => 'dd/mm/aaaa', 'id' => 'data_aula_ao_vivo-'. $id_secao . $id_modulo]) }}
                                        </div>
                                        <div class="col-md-3">
                                            {{ Form::label('Horário da aula ao vivo') }}
                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][hora_aula_ao_vivo]', isset($modulo['hora_aula_ao_vivo']) ? substr($modulo['hora_aula_ao_vivo'], 0, 2) . " : " . substr($modulo['hora_aula_ao_vivo'], 3, 2) : null, ['class' => 'form-control timepicker2', '', 'placeholder' => 'Hora Início', 'onchange' => 'calculaHoraAulaAoVivo(event)']) }}
                                        </div>
                                        <div class="col-md-3">
                                            {{ Form::label('Link da aula ao vivo') }}
                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][link_aula_ao_vivo]', isset($modulo['link_aula_ao_vivo']) ? $modulo['link_aula_ao_vivo'] : null, ['class' => 'form-control', 'maxlength' => '100']) }}
                                        </div>
                                        <div class="col-md-3">
                                            {{ Form::label('Data Final da aula ao vivo') }}
                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][data_fim_aula_ao_vivo]', (isset($modulo['data_fim_aula_ao_vivo']) && !empty($modulo['data_fim_aula_ao_vivo']) && $modulo['data_fim_aula_ao_vivo'] != '0000-00-00') ?  implode('/', array_reverse(explode('-', $modulo['data_fim_aula_ao_vivo']))) : null, ['class' => 'form-control datepicker', '', 'placeholder' => 'dd/mm/aaaa', 'id' => 'data_fim_aula_ao_vivo-'. $id_secao . $id_modulo]) }}
                                        </div>
                                        <div class="col-md-3">
                                            {{ Form::label('Horário final da aula ao vivo') }}
                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][hora_fim_aula_ao_vivo]', isset($modulo['hora_fim_aula_ao_vivo']) ? substr($modulo['hora_fim_aula_ao_vivo'], 0, 2) . " : " . substr($modulo['hora_fim_aula_ao_vivo'], 3, 2) : null, ['class' => 'form-control timepicker2', '', 'placeholder' => 'Hora Fim', 'onchange' => 'calculaHoraAulaAoVivo(event)']) }}
                                        </div>
                                    </div>
                            @else
                                    <div id="form-aula-ao-vivo{{$id_secao}}{{$id_modulo}}" class="form-aula-vivo">
                                        <div class="col-md-3">
                                            {{ Form::label('Data da aula ao vivo') }}
                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][data_aula_ao_vivo]', (isset($modulo['data_aula_ao_vivo']) && !empty($modulo['data_aula_ao_vivo']) && $modulo['data_aula_ao_vivo'] != '0000-00-00') ?  implode('/', array_reverse(explode('-', $modulo['data_aula_ao_vivo']))) : null, ['class' => 'form-control', '', 'placeholder' => 'dd/mm/aaaa', 'id' => 'data_aula_ao_vivo-'. $id_secao . $id_modulo]) }}
                                        </div>
                                        <div class="col-md-3">
                                            {{ Form::label('Horário da aula ao vivo') }}
                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][hora_aula_ao_vivo]', $modulo['hora_aula_ao_vivo'], ['class' => 'form-control timepicker2', '', 'placeholder' => 'Hora Início', 'onchange' => 'calculaHoraAulaAoVivo(event)']) }}
                                        </div>
                                        <div class="col-md-3">
                                            {{ Form::label('Link da aula ao vivo') }}
                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][link_aula_ao_vivo]', isset($modulo['link_aula_ao_vivo']) ? $modulo['link_aula_ao_vivo'] : null, ['class' => 'form-control', 'maxlength' => '100']) }}
                                        </div>
                                        <div class="col-md-3">
                                            {{ Form::label('Data final da aula ao vivo') }}
                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][data_fim_aula_ao_vivo]', (isset($modulo['data_fim_aula_ao_vivo']) && !empty($modulo['data_fim_aula_ao_vivo']) && $modulo['data_fim_aula_ao_vivo'] != '0000-00-00') ?  implode('/', array_reverse(explode('-', $modulo['data_fim_aula_ao_vivo']))) : null, ['class' => 'form-control', '', 'placeholder' => 'dd/mm/aaaa', 'id' => 'data_fim_aula_ao_vivo-'. $id_secao . $id_modulo]) }}
                                        </div>
                                        <div class="col-md-3">
                                            {{ Form::label('Horário final da aula ao vivo') }}
                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][hora_fim_aula_ao_vivo]', $modulo['hora_fim_aula_ao_vivo'], ['class' => 'form-control timepicker2', '', 'placeholder' => 'Hora Final', 'onchange' => 'calculaHoraAulaAoVivo(event)']) }}
                                        </div>
                                    </div>
                            @endif
                            <div class="col-md-12">
                                <a href="javascript:;" style="margin-top:15px;" class="btn btn-danger btn_excluir_modulo" title="Excluir Aula" onclick="return confirm('Deseja realmente excluir?')"><i class="fa fa-fw fa-trash"></i></a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="col-md-1" style="float:right; margin-right: -11px;">
                    <a href="javascript:;" style="margin-top: 25px;" class="btn btn-danger btn_excluir_secao" title="Excluir Seção" onclick="return confirm('Deseja realmente excluir?')"><i class="fa fa-fw fa-trash"></i></a>
                </div>

                <hr /><br /><br />
            @endforeach
        @else
            @if(!is_null(old('secao')))
                <?php $count_secao = 0; ?>
                @foreach(old('secao') as $id_secao => $secao)
                    @if($id_secao != "__COUNT__")
                        <?php $count_secao++; ?>
                        <div class="well row secao" data-contador="1" style="background: #f2bca2;">
                            <div class="col-md-1">
                                {{ Form::label('Seção:') }}
                                <div><strong>1</strong></div>
                            </div>
                            <div class="col-md-5">
                                {{ Form::label('Descrição') }} <small>*</small>
                                {{ Form::input('text', 'secao['. $count_secao .'][titulo]', $secao['titulo'], ['class' => 'form-control', 'data-msg-required' => 'Este campo é obrigatório', 'required' => true, '']) }}
                            </div>
                            <div class="col-md-2">
                                {{ Form::label('Ordem') }} <small>*</small>
                                {{ Form::select('secao['. $count_secao .'][ordem]', $lista_ordem, $secao['ordem'], ['class' => 'form-control', 'data-msg-required' => 'Este campo é obrigatório', 'required' => true, 'style' => 'width: 50%;']) }}
                            </div>
                            <div class="col-md-2">
                                <br />
                                <a href="javascript:;" data-secao="1" class="btn btn-success right btn_incluir_modulo">+ Aula</a>
                            </div>
                        </div>
                        @if(!is_null(old('modulos')))
                            <?php $count_modulos = 0; ?>
                            @foreach(old('modulos') as $id_modulo => $modulos)
                                @if($id_modulo != '__COUNT__' && $id_modulo != '__COUNT_SECAO__' && $id_modulo == $count_secao)
                                    @foreach($modulos as $modulo)
                                        <?php $count_modulos++; ?>
                                        <div class="well modulos s1" style="background: #fcdfd1;" data-contador="1">
                                            <div class="row modulo" data-secao="1" style="background: #fcdfd1;">
                                                <div class="col-md-1">
                                                    {{ Form::label('Aula') }}
                                                    <div><strong>{{$count_secao}}.{{$count_modulos}}</strong></div>
                                                    <input type="hidden" name="modulos[{{$count_secao}}][{{$count_modulos}}][fk_secao]" value="{{$modulo['fk_secao']}}" />
                                                </div>
                                                <div class="col-md-3">
                                                    {{ Form::label('Nome') }} <small>*</small>
                                                    {{ Form::input('text', 'modulos['. $count_secao .']['. $count_modulos .'][titulo]', $modulo['titulo'], ['class' => 'form-control nome_vimeo']) }}
                                                </div>
                                                <div class="col-md-2">
                                                    {{ Form::label('Código Vimeo') }} <small>*</small>
                                                    {{ Form::input('text', 'modulos['. $count_secao .']['. $count_modulos .'][url_video]', $modulo['url_video'], ['class' => 'form-control vimeoId']) }}
                                                </div>
                                                <div class="col-md-2">
                                                    {{ Form::label('Duração Aula:') }} <small>*</small>
                                                    {{ Form::input('text', 'modulos['. $count_secao .']['. $count_modulos .'][carga_horaria]', $modulo['carga_horaria'], ['class' => 'form-control carga_horaria']) }}
                                                </div>
                                                <div class="col-md-3">
                                                    <div id="box_upload" class="form-group">
                                                        {{ Form::label('Arquivo (PDF / Video / Podcast)') }}
                                                        {{ Form::file('modulos['. $count_secao.']['. $id_modulo .'][url_arquivo]') }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    {{ Form::label('A aula será exibida ao vivo?') }}
                                                    {{ Form::select('modulos['.$id_secao.']['.$id_modulo.'][aula_ao_vivo]', $lista_check, $modulo['aula_ao_vivo'], ['class' => 'form-control', 'maxlength' => '100', 'onchange' => 'abreFormAulaAoVivo(event)']) }}
                                                </div>
                                                @if(isset($modulo['aula_ao_vivo']) && !empty($modulo['aula_ao_vivo']))
                                                    <div id="form-aula-ao-vivo{{$id_secao}}{{$id_modulo}}">
                                                        <div class="col-md-3">
                                                            {{ Form::label('Data da aula ao vivo') }}
                                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][data_aula_ao_vivo]', (isset($modulo['data_aula_ao_vivo']) && !empty($modulo['data_aula_ao_vivo']) && $modulo['data_aula_ao_vivo'] != '0000-00-00') ? implode('/', array_reverse(explode('-', $modulo['data_aula_ao_vivo']))) : null, ['class' => 'form-control datepicker', '', 'placeholder' => 'dd/mm/aaaa', 'id' => 'data_aula_ao_vivo-'. $id_secao . $id_modulo]) }}
                                                        </div>
                                                        <div class="col-md-3">
                                                            {{ Form::label('Horário da aula ao vivo') }}
                                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][hora_aula_ao_vivo]', isset($modulo['hora_aula_ao_vivo']) ? substr($modulo['hora_aula_ao_vivo'], 0, 2) . " : " . substr($modulo['hora_aula_ao_vivo'], 3, 2) : null, ['class' => 'form-control timepicker2', '', 'placeholder' => 'Hora Início', 'onchange' => 'calculaHoraAulaAoVivo(event)']) }}
                                                        </div>
                                                        <div class="col-md-3">
                                                            {{ Form::label('Link da aula ao vivo') }}
                                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][link_aula_ao_vivo]', $modulo['link_aula_ao_vivo'], ['class' => 'form-control', 'maxlength' => '100']) }}
                                                        </div>
                                                        <div class="col-md-3">
                                                            {{ Form::label('Data final da aula ao vivo') }}
                                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][data_fim_aula_ao_vivo]', (isset($modulo['data_fim_aula_ao_vivo']) && !empty($modulo['data_fim_aula_ao_vivo']) && $modulo['data_fim_aula_ao_vivo'] != '0000-00-00') ? implode('/', array_reverse(explode('-', $modulo['data_fim_aula_ao_vivo']))) : null, ['class' => 'form-control datepicker', '', 'placeholder' => 'dd/mm/aaaa', 'id' => 'data_fim_aula_ao_vivo-'. $id_secao . $id_modulo]) }}
                                                        </div>
                                                        <div class="col-md-3">
                                                            {{ Form::label('Horário final da aula ao vivo') }}
                                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][hora_fim_aula_ao_vivo]', isset($modulo['hora_fim_aula_ao_vivo']) ? substr($modulo['hora_fim_aula_ao_vivo'], 0, 2) . " : " . substr($modulo['hora_fim_aula_ao_vivo'], 3, 2) : null, ['class' => 'form-control timepicker2', '', 'placeholder' => 'Hora Final', 'onchange' => 'calculaHoraAulaAoVivo(event)']) }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <div id="form-aula-ao-vivo{{$id_secao}}{{$id_modulo}}" class="form-aula-vivo">
                                                        <div class="col-md-3">
                                                            {{ Form::label('Data da aula ao vivo') }}
                                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][data_aula_ao_vivo]', (isset($modulo['data_aula_ao_vivo']) && !empty($modulo['data_aula_ao_vivo']) && $modulo['data_aula_ao_vivo'] != '0000-00-00') ?  implode('/', array_reverse(explode('-', $modulo['data_aula_ao_vivo']))) : null, ['class' => 'form-control', '', 'placeholder' => 'dd/mm/aaaa', 'id' => 'data_aula_ao_vivo-'. $id_secao . $id_modulo]) }}
                                                        </div>
                                                        <div class="col-md-3">
                                                            {{ Form::label('Horário da aula ao vivo') }}
                                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][hora_aula_ao_vivo]', isset($modulo['hora_aula_ao_vivo']) ? substr($modulo['hora_aula_ao_vivo'], 0, 2) . " : " . substr($modulo['hora_aula_ao_vivo'], 3, 2) : null, ['class' => 'form-control timepicker2', '', 'placeholder' => 'Hora Início', 'onchange' => 'calculaHoraAulaAoVivo(event)']) }}
                                                        </div>
                                                        <div class="col-md-3">
                                                            {{ Form::label('Link da aula ao vivo') }}
                                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][link_aula_ao_vivo]', isset($modulo['link_aula_ao_vivo']) ? $modulo['link_aula_ao_vivo'] : null, ['class' => 'form-control', 'maxlength' => '100']) }}
                                                        </div>
                                                        <div class="col-md-3">
                                                            {{ Form::label('Data inicio da aula ao vivo') }}
                                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][data_fim_aula_ao_vivo]', (isset($modulo['data_fim_aula_ao_vivo']) && !empty($modulo['data_fim_aula_ao_vivo']) && $modulo['data_fim_aula_ao_vivo'] != '0000-00-00') ?  implode('/', array_reverse(explode('-', $modulo['data_fim_aula_ao_vivo']))) : null, ['class' => 'form-control', '', 'placeholder' => 'dd/mm/aaaa', 'id' => 'data_fim_aula_ao_vivo-'. $id_secao . $id_modulo]) }}
                                                        </div>
                                                        <div class="col-md-3">
                                                            {{ Form::label('Horário final da aula ao vivo') }}
                                                            {{ Form::input('text', 'modulos['.$id_secao.']['.$id_modulo.'][hora_fim_aula_ao_vivo]', isset($modulo['hora_fim_aula_ao_vivo']) ? substr($modulo['hora_fim_aula_ao_vivo'], 0, 2) . " : " . substr($modulo['hora_fim_aula_ao_vivo'], 3, 2) : null, ['class' => 'form-control timepicker2', '', 'placeholder' => 'Hora final', 'onchange' => 'calculaHoraAulaAoVivo(event)']) }}
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="col-md-12">
                                                    <a href="javascript:;" style="margin-top:15px;" class="btn btn-danger btn_excluir_modulo" title="Excluir Aula" onclick="return confirm('Deseja realmente excluir?')"><i class="fa fa-fw fa-trash"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            @endforeach
                        @endif
                    @endif
                @endforeach
            @else
                <div class="well row secao" data-contador="1" style="background: #f2bca2;">
                    <div class="col-md-1">
                        {{ Form::label('Seção:') }}
                        <div><strong>1</strong></div>
                    </div>
                    <div class="col-md-5">
                        {{ Form::label('Descrição') }} <small>*</small>
                        {{ Form::input('text', 'secao[1][titulo]', null, ['class' => 'form-control', 'data-msg-required' => 'Este campo é obrigatório', 'required' => true]) }}
                    </div>
                    <div class="col-md-2">
                        {{ Form::label('Ordem') }} <small>*</small>
                        {{ Form::select('secao[1][ordem]', $lista_ordem, 1, ['class' => 'form-control', 'style' => 'width: 50%;', 'data-msg-required' => 'Este campo é obrigatório', 'required' => true]) }}
                    </div>
                    <div class="col-md-2">
                        <br />
                        <a href="javascript:;" data-secao="1" class="btn btn-success right btn_incluir_modulo">+ Aula</a>
                    </div>
                </div>
                <div class="well modulos s1" style="background: #fcdfd1;" data-contador="1">
                    <div class="row modulo" data-secao="1" style="background: #fcdfd1;">
                        <div class="col-md-1">
                            {{ Form::label('Aula') }}
                            <div><strong>1.1</strong></div>
                            <input type="hidden" name="modulos[1][1][fk_secao]" value="1" />
                        </div>
                        <div class="col-md-3">
                            {{ Form::label('Nome') }} <small>*</small>
                            {{ Form::input('text', 'modulos[1][1][titulo]', null, ['class' => 'form-control nome_vimeo']) }}
                        </div>
                        <div class="col-md-2">
                            {{ Form::label('Código Vimeo') }} <small>*</small>
                            {{ Form::input('text', 'modulos[1][1][url_video]', null, ['class' => 'form-control vimeoId']) }}
                        </div>
                        <div class="col-md-2">
                            {{ Form::label('Duração Aula:') }} <small>*</small>
                            {{ Form::input('text', 'modulos[1][1][carga_horaria]', null, ['class' => 'form-control carga_horaria']) }}
                        </div>
                        <div class="col-md-3">
                            <div id="box_upload" class="form-group">
                                {{ Form::label('Arquivo (PDF / Video / Podcast)') }}
                                {{ Form::file('modulos[1][1][url_arquivo]') }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            {{ Form::label('A aula será exibida ao vivo?') }}
                            {{ Form::select('modulos[1][1][aula_ao_vivo]', $lista_check, null, ['class' => 'form-control', 'maxlength' => '100', 'onchange' => 'abreFormAulaAoVivo(event)']) }}
                        </div>
                        <div id="form-aula-ao-vivo11" class="form-aula-vivo">
                            <div class="col-md-3">
                                {{ Form::label('Data da aula ao vivo') }}
                                {{ Form::input('text', 'modulos[1][1][data_aula_ao_vivo]', null, ['class' => 'form-control', 'placeholder' => 'dd/mm/aaaa', 'id' => 'data_aula_ao_vivo-11']) }}
                            </div>
                            <div class="col-md-3">
                                {{ Form::label('Horário da aula ao vivo') }}
                                {{ Form::input('text', 'modulos[1][1][hora_aula_ao_vivo]', null, ['class' => 'form-control timepicker2', '', 'placeholder' => 'Hora Início', 'onchange' => 'calculaHoraAulaAoVivo(event)']) }}
                            </div>
                            <div class="col-md-3">
                                {{ Form::label('Link da aula ao vivo') }}
                                {{ Form::input('text', 'modulos[1][1][link_aula_ao_vivo]', null, ['class' => 'form-control', 'maxlength' => '100']) }}
                            </div>
                            <div class="col-md-3">
                                {{ Form::label('Data final da aula ao vivo') }}
                                {{ Form::input('text', 'modulos[1][1][data_fim_aula_ao_vivo]', null, ['class' => 'form-control', 'placeholder' => 'dd/mm/aaaa', 'id' => 'data_fim_aula_ao_vivo-11']) }}
                            </div>
                            <div class="col-md-3">
                                {{ Form::label('Horário final da aula ao vivo') }}
                                {{ Form::input('text', 'modulos[1][1][hora_fim_aula_ao_vivo]', null, ['class' => 'form-control timepicker2', '', 'placeholder' => 'Hora final', 'onchange' => 'calculaHoraAulaAoVivo(event)']) }}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <a href="javascript:;" style="margin-top:15px;" class="btn btn-danger btn_excluir_modulo" title="Excluir Aula" onclick="return confirm('Deseja realmente excluir?')"><i class="fa fa-fw fa-trash"></i></a>
                        </div>
                    </div>
                </div>
            @endif

        @endif
    </div>
    <hr />

    <!-- modelo SECAO de curso (para clonar) -->
    <div id="default_secao" style="display: none;">
        <div class="well row secao" data-contador="__COUNT__" style="margin-left: 0; background: #f2bca2;">
            <hr />
            <div class="col-md-1">
                {{ Form::label('Seção') }}
                <div><strong>__COUNT__</strong></div>
            </div>
            <div class="col-md-5">
                {{ Form::label('Descrição') }}
                {{ Form::input('text', 'secao[__COUNT__][titulo]', null, ['class' => 'form-control', '']) }}
            </div>
            <div class="col-md-2">
                {{ Form::label('ORDEM') }}
                {{ Form::select('secao[__COUNT__][ordem]', $lista_ordem, null, ['class' => 'form-control', 'style' => 'width: 50%;']) }}
            </div>
            <div class="col-md-2">
                <br />
                <a href="javascript:;" data-secao="__COUNT__" class="btn btn-success right btn_incluir_modulo">+ Aula</a>
            </div>
        </div>

        <div class="well modulos s__COUNT__" style="background: #fcdfd1;">
            <div class="row modulo" style="background: #fcdfd1;" data-contador="1" data-secao="__COUNT__">
                <hr />
                <div class="col-md-1">
                    {{ Form::label('Aula') }}
                    <div><strong>__COUNT__.1</strong></div>
                    <input type="hidden" name="modulos[__COUNT__][1][fk_secao]" value="__COUNT__" />
                </div>
                <div class="col-md-3">
                    {{ Form::label('Nome') }}
                    {{ Form::input('text', 'modulos[__COUNT__][1][titulo]', null, ['class' => 'form-control nome_vimeo', '']) }}
                </div>
                <div class="col-md-2">
                    {{ Form::label('Código Vimeo') }}
                    {{ Form::input('text', 'modulos[__COUNT__][1][url_video]', null, ['class' => 'form-control vimeoId', '']) }}
                </div>
                <div class="col-md-2">
                    {{ Form::label('Duração Aula:') }}
                    {{ Form::input('text', 'modulos[__COUNT__][1][carga_horaria]', null, ['class' => 'form-control carga_horaria']) }}
                </div>
                <div class="col-md-3">
                    <div id="box_upload" class="form-group">
                        {{ Form::label('Arquivo (PDF / Video / Podcast)') }}
                        {{ Form::file('modulos[__COUNT__][1][url_arquivo]') }}
                    </div>
                </div>
                <div class="col-md-3">
                    {{ Form::label('A aula será exibida ao vivo?') }}
                    {{ Form::select('modulos[__COUNT__][1][aula_ao_vivo]', $lista_check, null, ['class' => 'form-control', 'maxlength' => '100', 'onchange' => 'abreFormAulaAoVivo(event)']) }}
                </div>
                <div id="form-aula-ao-vivo__COUNT__1" class="form-aula-vivo">
                    <div class="col-md-3">
                        {{ Form::label('Data da aula ao vivo') }}
                        {{ Form::input('text', 'modulos[__COUNT__][1][data_aula_ao_vivo]', null, ['class' => 'form-control', 'placeholder' => 'dd/mm/aaaa', 'id' => 'data_aula_ao_vivo-__COUNT__1']) }}
                    </div>
                    <div class="col-md-3">
                        {{ Form::label('Horário da aula ao vivo') }}
                        {{ Form::input('text', 'modulos[__COUNT__][1][hora_aula_ao_vivo]', null, ['class' => 'form-control timepicker2', '', 'placeholder' => 'Hora Início', 'onchange' => 'calculaHoraAulaAoVivo(event)']) }}
                    </div>
                    <div class="col-md-3">
                        {{ Form::label('Link da aula ao vivo') }}
                        {{ Form::input('text', 'modulos[__COUNT__][1][link_aula_ao_vivo]', null, ['class' => 'form-control', 'maxlength' => '100']) }}
                    </div>
                    <div class="col-md-3">
                        {{ Form::label('Data final da aula ao vivo') }}
                        {{ Form::input('text', 'modulos[__COUNT__][1][data_fim_aula_ao_vivo]', null, ['class' => 'form-control', 'placeholder' => 'dd/mm/aaaa', 'id' => 'data_fim_aula_ao_vivo-__COUNT__1']) }}
                    </div>
                    <div class="col-md-3">
                        {{ Form::label('Horário final da aula ao vivo') }}
                        {{ Form::input('text', 'modulos[__COUNT__][1][hora_fim_aula_ao_vivo]', null, ['class' => 'form-control timepicker2', '', 'placeholder' => 'Hora final', 'onchange' => 'calculaHoraAulaAoVivo(event)']) }}
                    </div>
                </div>
                <div class="col-md-12">
                    <a href="javascript:;" style="margin-top:15px;" class="btn btn-danger btn_excluir_modulo" title="Excluir Aula" onclick="return confirm('Deseja realmente excluir?')"><i class="fa fa-fw fa-trash"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-1" style="float:right; margin-right: -11px;">
            <a href="javascript:;" style="margin-top: 25px;" class="btn btn-danger btn_excluir_secao" title="Excluir Seção" onclick="return confirm('Deseja realmente excluir?')"><i class="fa fa-fw fa-trash"></i></a>
        </div>
        <hr /><br /><br />
    </div>
    <!-- FIM SECAO -->

    <!-- modelo MODULOS de curso (para clonar) -->
    <div id="default_modulo" style="display: none;">
        <div class="row modulo" style="background: #fcdfd1;" data-secao="__COUNT_SECAO__" data-contador="__COUNT__">
            <hr />
            <div class="col-md-1">
                {{ Form::label('Aula') }}
                <div><strong>__COUNT_SECAO__.__COUNT__</strong></div>
                <input type="hidden" name="modulos[__COUNT_SECAO__][__COUNT__][fk_secao]" value="__COUNT_SECAO__" />
            </div>
            <div class="col-md-3">
                {{ Form::label('Nome') }}
                {{ Form::input('text', 'modulos[__COUNT_SECAO__][__COUNT__][titulo]', null, ['class' => 'form-control nome_vimeo', '']) }}
            </div>
            <div class="col-md-2">
                {{ Form::label('Código Vimeo') }}
                {{ Form::input('text', 'modulos[__COUNT_SECAO__][__COUNT__][url_video]', null, ['class' => 'form-control vimeoId', '']) }}
            </div>
            <div class="col-md-2">
                {{ Form::label('Duração Aula:') }}
                {{ Form::input('text', 'modulos[__COUNT_SECAO__][__COUNT__][carga_horaria]', null, ['class' => 'form-control  carga_horaria']) }}
            </div>
            <div class="col-md-3">
                <div id="box_upload" class="form-group">
                    {{ Form::label('Arquivo (PDF / Video / Podcast)') }}
                    {{ Form::file('modulos[__COUNT_SECAO__][__COUNT__][url_arquivo]') }}
                </div>
            </div>
            
            <div class="col-md-3">
                {{ Form::label('A aula será exibida ao vivo?') }}
                {{ Form::select('modulos[__COUNT_SECAO__][__COUNT__][aula_ao_vivo]', $lista_check, null, ['class' => 'form-control', 'maxlength' => '100', 'onchange' => 'abreFormAulaAoVivo(event)']) }}
            </div>
            <div id="form-aula-ao-vivo__COUNT_SECAO____COUNT__" class="form-aula-vivo">
                <div class="col-md-3">
                    {{ Form::label('Data da aula ao vivo') }}
                    {{ Form::input('text', 'modulos[__COUNT_SECAO__][__COUNT__][data_aula_ao_vivo]', null, ['class' => 'form-control', 'placeholder' => 'dd/mm/aaaa', 'id' => 'data_aula_ao_vivo-__COUNT_SECAO____COUNT__']) }}
                </div>
                <div class="col-md-3">
                    {{ Form::label('Horário da aula ao vivo') }}
                    {{ Form::input('text', 'modulos[__COUNT_SECAO__][__COUNT__][hora_aula_ao_vivo]', null, ['class' => 'form-control timepicker2', '', 'placeholder' => 'Hora Início', 'onchange' => 'calculaHoraAulaAoVivo(event)']) }}
                </div>
                <div class="col-md-3">
                    {{ Form::label('Link da aula ao vivo') }}
                    {{ Form::input('text', 'modulos[__COUNT_SECAO__][__COUNT__][link_aula_ao_vivo]', null, ['class' => 'form-control', 'maxlength' => '100']) }}
                </div>
                <div class="col-md-3">
                    {{ Form::label('Data da aula ao vivo') }}
                    {{ Form::input('text', 'modulos[__COUNT_SECAO__][__COUNT__][data_fim_aula_ao_vivo]', null, ['class' => 'form-control', 'placeholder' => 'dd/mm/aaaa', 'id' => 'data_fim_aula_ao_vivo-__COUNT_SECAO____COUNT__']) }}
                </div>
                <div class="col-md-3">
                    {{ Form::label('Horário da aula ao vivo') }}
                    {{ Form::input('text', 'modulos[__COUNT_SECAO__][__COUNT__][hora_fim_aula_ao_vivo]', null, ['class' => 'form-control timepicker2', '', 'placeholder' => 'Hora final', 'onchange' => 'calculaHoraAulaAoVivo(event)']) }}
                </div>
            </div>
            <div class="col-md-12">
                <a href="javascript:;" style="margin-top:15px;" class="btn btn-danger btn_excluir_modulo" title="Excluir Aula" onclick="return confirm('Deseja realmente excluir?')"><i class="fa fa-fw fa-trash"></i></a>
            </div>
        </div>
    </div>
    <!-- FIM MODULOS -->
@endif

@push('js')
    <script type="text/javascript">

        $(document).ready(function () {
            function calculaHoraAulaAoVivo() {} // definido para evitar erro de não carregar nenhum js do blade formulario do curso
        })
    </script>
@endpush
