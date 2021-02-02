@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>{{  $modulo['moduloDetalhes']->modulo }}</span></h2>
        <a href="{{ route('admin.'.$modulo['moduloDetalhes']->rota)}}" class="label label-default">Voltar</a>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $obj, ['method' => 'PATCH', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.atualizar', $obj->id]] ) }}
        @else
            {{ Form::open(['url' => '/admin/'.$modulo['moduloDetalhes']->uri.'/salvar']) }}
        @endif
        <div class="form-group">
            {{ Form::label('Descrição') }}
            {{ Form::input('text', 'descricao', null, ['class' => 'form-control', '', 'placeholder' => 'Título']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Menu') }}
            {{ Form::select('fk_menu_id', $menus, (isset($obj->fk_menu_id) ? $obj->fk_menu_id : null), ['class' => 'form-control']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Controller') }}
            {{ Form::input('text', 'controller', null, ['class' => 'form-control', '', 'placeholder' => 'Controller']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Nome rota') }}
            {{ Form::input('text', 'route_name', null, ['class' => 'form-control', '', 'placeholder' => 'Nome rota']) }}
        </div>
        <div class="form-group">
            {{ Form::label('URI') }}
            {{ Form::input('text', 'route_uri', null, ['class' => 'form-control', '', 'placeholder' => 'URI']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Caminho View') }}
            {{ Form::input('text', 'view_caminho', null, ['class' => 'form-control', '', 'placeholder' => 'Caminho View']) }}
        </div>
        <div class="form-group">
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>

    @if(Request::is('*/editar'))
        <div class="box padding20">
            <h2 class="table"><span>Ações para o Módulo</span>
            </h2>
            <div class="form-group col-lg-12">
                <div class="form-group col-lg-4">
                    {{ Form::label('Açõs que o módulo deve conter') }}<br/>
                    <?php
                    if (isset($mxa)) {
                        echo '<select class="form-control" id="modulesActionsList">';
                        foreach ($mxa as $a) {
                            echo '<option value="' . $a->id . '">' . $a->descricao . ' ( ' . $a->elemento . ')' . '</option>';
                        }
                        echo '</select>';
                    }
                    ?>
                </div>
                <div class="form-group col-lg-2">
                    {{ Form::label('Tipo de rota') }}<br/>
                    <select class="form-control" id="methodUse">
                        <option value="--" selected>Selecione</option>
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="PATCH">PATCH</option>
                        <option value="DELETE">DELETE</option>
                    </select>
                </div>
                <div class="form-group col-lg-2">
                    {{ Form::label('Aceita parâmetros') }}<br/>
                    <select class="form-control" id="acceptParameter">
                        <option value="-1" selected>Selecione</option>
                        <option value="0">Não</option>
                        <option value="1">Sim</option>
                    </select>
                </div>

                <div class="form-group col-lg-2">
                    {{ Form::label('Nome middleware') }}<br/>
                    <select class="form-control" id="middlewareName">
                        <option value="-1" selected>Selecione</option>
                        <option value="0">Não</option>
                        <option value="1">Sim</option>
                    </select>
                </div>
                <div class="form-group col-lg-2">
                    <br/>
                    <button class="btn btn-default" type="button" style="width: 75px;" id="addActionToModel">
                        Adicionar
                    </button>
                </div>

            </div>

            <table class="table" id="moduleActions" cellpadding="0" cellspacing="0" border="0"
                   class="table table-striped">
                <th>Ação</th>
                <th>Elemento</th>
                <th>Status</th>
                <th>Method</th>
                <th>Parâmetro</th>
                <th>MiddlewareName</th>
                <th>Ações</th>
                <tbody>
                <?php $arrayItensPermited = array(); ?>
                @foreach($moduloMxA as $objActions)
                    <tr>
                        <td>{{ $objActions->acao }}</td>
                        <td>{{ $objActions->elemento }}</td>
                        <td>{{ $lista_status[$objActions->status] }}</td>
                        @if($objActions->elemento == 'Rota')
                            @if($objActions->tipo_rota != '')
                                <td>{{ $objActions->tipo_rota }}</td>
                            @else
                                <td></td>
                            @endif
                        @else
                            <td> --</td>
                        @endif
                        @if($objActions->elemento == 'Rota')
                            @if($objActions->parametro == 1)
                                <td>SIM</td>
                            @else
                                <td>NÃO</td>
                            @endif
                        @else
                            <td> --</td>
                        @endif
                        @if($objActions->elemento == 'Rota')
                            @if($objActions->name_middle_ware == 1)
                                <td>SIM</td>
                            @else
                                <td>NÃO</td>
                            @endif
                        @else
                            <td> --</td>
                        @endif
                        <td>
                            <button type="button" class="btn btn-danger btn-sm removeAction" onclick="return confirm('Deseja realmente excluir?')"
                                    id="{{ $objActions->id }}">Excluir
                            </button>
                        </td>
                    </tr>
                    <?php $arrayItensPermited[] = $obj->id; ?>
                @endforeach
                </tbody>
            </table>
        </div>
        <meta name="csrf-token" content="{{ csrf_token() }}"/>

    @endif
@endsection

@push('js')
    <script>
        var baseUrl = '<?= URL::to('/');?>/admin/usuarios_modulos/';
        var listActionModule = [];
        var idModulo = <?= isset($obj) ? $obj->id : ''?>;
        <?php if (isset($arrayItensPermited) && count($arrayItensPermited) > 0) echo 'listActionModule=' . json_encode($arrayItensPermited) . ' ;'?>

        $(document).ready(function (e) {

            $(document).on('click', '.removeAction', function (e) {
                console.log($(this).attr('id'));
                var idItem = $(this).attr('id');
                var formData = new FormData();
                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

                formData.append('_token', CSRF_TOKEN);
                formData.append('idModulo', idModulo);
                formData.append('id', idItem);
                $('#moduleActions > tbody').html('');
                $.ajax({
                    url: baseUrl + 'removemxa',
                    type: 'POST',
                    crossDomain: true,
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function (ret, textStatus, jqXHR) {
                        location.reload(true);
                        // $('#moduleActions > tbody').html('');
                        // montarTabela(ret.dataView);

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
                    formData.append('idModulo', idModulo);
                    formData.append('idAction', $('#modulesActionsList').val());
                    formData.append('methodUse', $('#methodUse').val());
                    formData.append('middlewareName', $('#middlewareName').val());
                    formData.append('acceptParameter', $('#acceptParameter').val());
                    $('#moduleActions > tbody').html('');
                    $.ajax({
                        url: baseUrl + 'addmxa',
                        type: 'POST',
                        crossDomain: true,
                        data: formData,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function (ret, textStatus, jqXHR) {
                            location.reload(true);
                            // $('#moduleActions > tbody').html('');
                            // montarTabela(ret.dataView);
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            alert('Não foi possível realizar a ação');
                        }
                    });
                }
            });

            function montarTabela(tabela) {
                var linha = '';
                $('#moduleActions > tbody').html('');

                for (var i in tabela) {
                    linha += '<tr><td>' + tabela[i].acao + '</td>';
                    linha += '<td>' + tabela[i].elemento + '</td>';
                    linha += '<td>' + tabela[i].status + '</td>';
                    linha += '<td><button type="button" class="btn btn-danger btn-sm removeAction" onclick="return confirm(\'Deseja realmente excluir?\')" id="' + tabela[i].id + '">Excluir</button></td></tr>';
                }
                $('#moduleActions > tbody').append(linha);
            }

        });
    </script>
@endpush
