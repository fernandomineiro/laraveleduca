@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>
        @if(Request::is('*/editar'))
            Atualizar
        @else
            Criar
        @endif

        API's</span></h2>
        <a href="{{ route('admin.api') }}" class="label label-default">Voltar</a>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $api, ['method' => 'PATCH', 'route' => ['admin.api.atualizar', $api->id]] ) }}
        @else
            {{ Form::open(['url' => '/admin/api/salvar']) }}
        @endif

        <div class="form-group">
            {{ Form::label('Status') }}
            {{ Form::select('status', $lista_status, (isset($api->status) ? $api->status : 1 ), ['class' => 'form-control', 'style' => 'width: 420px;']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Projeto') }}
			{{ Form::select('fk_faculdade', array_merge([0 => 'Selecione'], $lista_faculdades->toArray()), (isset($api->fk_faculdade) ? $api->fk_faculdade : 0 ), ['class' => 'form-control', 'style' => 'width: 420px;']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Título') }}
            {{ Form::input('text', 'titulo', null, ['class' => 'form-control', '', 'placeholder' => 'Título']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Descrição') }}
            {{ Form::textarea('descricao', null, ['class' => 'form-control', 'id' => 'ckeditor', 'style' => 'height: 100px;']) }}
        </div>
        <div class="form-group">
            {{ Form::label('URL') }}
            {{ Form::input('text', 'url', null, ['class' => 'form-control', '', 'placeholder' => 'Título']) }}
        </div>
        <div class="form-group">
            {{ Form::label('TIPO') }}
            {{ Form::select('tipo', $lista_tipos, (isset($api->tipo) ? $api->tipo : '' ), ['class' => 'form-control', 'style' => 'width: 420px;']) }}
        </div>

        <div class="row">
            <div class="col-md-6">
                <h3>Parametros de Configuraçāo da Api</h3>
            </div>
            <div class="col-md-6">
                <a href="javascript:;" id="btn_incluir_params" class="btn btn-success right">+ Params</a>
            </div>
        </div>

        <div class="well" id="bloco_estrutura">
            <?php if(isset($api['params']) && !empty($api['params'])) : ?>

                <?php $params_array = json_decode($api['params'], true); ?>

                <?php $count_param = 0; ?>
                <div class="well params" data-contador="<?php echo count($params_array); ?>">
                    @foreach($params_array as $id_conf => $param)
                        <?php $count_param++; ?>
                        <div class="row param" data-id="<?php echo $id_conf; ?>">
                            <div class="col-md-2">
                                {{ Form::label('key') }}
                                {{ Form::input('text', 'params['.$count_param.'][key]', $param['key'], ['class' => 'form-control', '']) }}
                            </div>
                            <div class="col-md-5">
                                {{ Form::label('value') }}
                                {{ Form::input('text', 'params['.$count_param.'][value]', $param['value'], ['class' => 'form-control', '']) }}
                            </div>
                            <div class="col-md-4">
                                {{ Form::label('description') }}
                                {{ Form::input('text', 'params['.$count_param.'][description]', $param['description'], ['class' => 'form-control', '']) }}
                            </div>
                            <div class="col-md-1">
                            	<a href="javascript:;" id="btn_remover_params" class="btn btn-danger" style="margin-top: 25px;"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a>
                            </div>
                        </div>
                    @endforeach
                </div>
            <?php else : ?>
                <div class="well params s1" data-contador="1">
                    <div class="row param" data-secao="1">
                        <div class="col-md-2">
                            {{ Form::label('key') }}
                            {{ Form::input('text', 'params[0][key]', null, ['class' => 'form-control', '']) }}
                        </div>
                        <div class="col-md-5">
                            {{ Form::label('value') }}
                            {{ Form::input('text', 'params[0][value]', null, ['class' => 'form-control', '']) }}
                        </div>
                        <div class="col-md-5">
                            {{ Form::label('description') }}
                            {{ Form::input('text', 'params[0][description]', null, ['class' => 'form-control', '']) }}
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>


    <!-- modelo params (para clonar) -->
    <div id="default_param" style="display: none;">
        <div class="row param" data-contador="__COUNT__">
            <div class="col-md-2">
                {{ Form::label('key') }}
                {{ Form::input('text', 'params[__COUNT__][key]', null, ['class' => 'form-control', '']) }}
            </div>
            <div class="col-md-5">
                {{ Form::label('value') }}
                {{ Form::input('text', 'params[__COUNT__][value]', null, ['class' => 'form-control', '']) }}
            </div>
            <div class="col-md-4">
                {{ Form::label('description') }}
                {{ Form::input('text', 'params[__COUNT__][description]', null, ['class' => 'form-control', '']) }}
            </div>
        	<div class="col-md-1">
				<a href="javascript:;" id="btn_remover_params" class="btn btn-danger" style="margin-top: 25px;"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a>
			</div>

        </div>
    </div>
    <!-- FIM MODULOS -->

@endsection
@push('js')
	<script type="text/javascript">
		$(document).ready(function () {

            $('#btn_incluir_params').click(function () {
				console.log('button_press');

				var qtd = $('#bloco_estrutura > .params').data('contador') + 1;
				var html = $('#default_param').html();
				var regex = new RegExp('__COUNT__', 'g');

				html = html.replace(regex, qtd);

                console.log(html);

				$('#bloco_estrutura > .params').append(html);
				$('#bloco_estrutura > .params').data('contador', qtd);
			});
        });

        $(document).on('click', '#btn_remover_params', function () {
            let contador = $(this).parent().data('contador');

            //console.log(contador);

            $(this).parent().parent().remove();

			});
    </script>
@endpush
