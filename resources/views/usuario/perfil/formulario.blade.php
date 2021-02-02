@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>{{ $modulo['moduloDetalhes']->modulo }}</span></h2>
        <a href="{{ route('admin.'.$modulo['moduloDetalhes']->rota)}}" class="label label-default">Voltar</a>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $perfil, ['method' => 'PATCH', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.atualizar', $perfil->id]] ) }}
        @else
            {{ Form::open(['url' => '/admin/'.$modulo['moduloDetalhes']->uri.'/salvar']) }}
        @endif
        <div class="form-group">
            {{ Form::label('Descrição') }}
            {{ Form::input('text', 'titulo', null, ['class' => 'form-control', '', 'placeholder' => 'Título']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Tipo de Perfil') }}
            {{ Form::select('fk_parceiro_id', $lista_parceiro, (isset($perfil->fk_parceiro_id) ? $perfil->fk_parceiro_id : 1), ['class' => 'form-control']) }}
        </div>
        <div class="form-group">
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}

    </div>
    @if(Request::is('*/editar'))
    	{{ Form::model( $perfil, ['method' => 'POST', 'url' => '/admin/usuarios_perfil/addpxmxa'] ) }}
    	<div class="box padding20">
    		<div class="box-header">
                <h3 class="box-title">Adicionar: módulos e ações permitidas</h3>
            </div>
            <div class="box-body">
            	<input type="hidden" name="idPerfil" value="{{ $perfil->id }}">
            	<?php if (isset($mxa)) {
                    foreach ($mxa as $a2) {
                        ?>
                        <div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="modulesAction[]" id="modulesAction_{{ $a2->id }}" value="{{ $a2->id }}">
                            <label class="custom-control-label" for="modulesAction_{{ $a2->id }}">
                            	Módulo: {{ $a2->modulo }} - Ação: {{ $a2->acao }} ({{ $a2->elemento }})
                            </label>
                        </div>
                        <?php
                    }
                } ?>
                <br/>
                <button class="btn btn-default" type="submit" style="width: 75px;">
                    Salvar
                </button>
            </div>
    	</div>
    	{{ Form::close() }}
    
        <div class="box padding20">
            <div class="box-header">
                <h3 class="box-title">Lista de registros encontrados</h3>
            </div>
            <div class="box-body">
                <table id="dataTableDados" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>Módulo</th>
                        <th>Ação</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($perfilModulosAcoes as $objActions)
                        <tr>
                            <td>{{ $objActions->modulo }}</td>
                            <td>{{ $objActions->acoes }} - {{ $objActions->elemento }}</td>
                            <td>{{ $lista_status[$objActions->status] }}</td>
                            <td style="text-align: center">
                                <button type="button" class="btn btn-danger btn-sm removeAction"
                                        id="{{ $objActions->id }}"><i class="fa fa-fw fa-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <?php $arrayItensPermited = array(); ?>
        <meta name="csrf-token" content="{{ csrf_token() }}"/>
        
    @endif
@endsection

@push('js')
    <script>
        var baseUrl = '<?= URL::to('/');?>/admin/usuarios_perfil/';
        var listActionModule = [];
        var idPerfil = <?= $perfil->id ?>;

        <?php if (count($arrayItensPermited) > 0) echo 'listActionModule=' . json_encode($arrayItensPermited) . ' ;'?>
        $(document).ready(function (e) {
            $('#dataTableDados').DataTable();
            $(document).on('click', '.removeAction', function (e) {
                console.log($(this).attr('id'));
                var idItem = $(this).attr('id');
                var formData = new FormData();
                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

                formData.append('_token', CSRF_TOKEN);
                formData.append('idPerfil', idPerfil);
                formData.append('id', idItem);
                $('#moduleActions > tbody').html('');
                $.ajax({
                    url: baseUrl + 'removepxmxa',
                    type: 'POST',
                    crossDomain: true,
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function (ret, textStatus, jqXHR) {
                        location.reload();
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        alert('Não foi possível realizar a ação');
                    }
                });

            });

            $('#addActionToModel').click(function (e) {
                var add = true;
                for (var i in listActionModule) {
                    var itenActionAdd = $('#modulesActionsList').val();
                    if (itenActionAdd == listActionModule[i])
                        addAction = false;
                }

                if (add) {
                    var formData = new FormData();
                    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

                    formData.append('_token', CSRF_TOKEN);
                    formData.append('idPerfil', idPerfil);
                    formData.append('idAction', $('#modulesActionsList').val());

                    $('#moduleActions > tbody').html('');
                    $.ajax({
                        url: baseUrl + 'addpxmxa',
                        type: 'POST',
                        crossDomain: true,
                        data: formData,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function (ret, textStatus, jqXHR) {
                            location.reload();
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            alert('Não foi possível realizar a ação');
                        }
                    });
                }
            });
        });
    </script>
@endpush    
