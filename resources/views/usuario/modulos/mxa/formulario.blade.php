@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>{{ $modulo['moduloDetalhes']->modulo }}</span></h2>
        <a href="{{ route('admin.'.$modulo['moduloDetalhes']->rota)}}" class="label label-default">Voltar</a>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $obj, ['method' => 'PATCH', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.atualizar', $obj->id],'id' => 'formModulosAcoes'] ) }}
        @else
            {{ Form::open(['url' => '/admin/'.$modulo['moduloDetalhes']->uri.'/salvar']) }}
        @endif
        <div class="form-group">
            {{ Form::label('Módulo') }}
            <select class="form-control" id="modulesList" name="fk_modulo_id">
            </select>
        </div>
        <div class="form-group col-lg-12">
            {{ Form::label('Ação') }}<br/>
            <div class="form-group col-lg-4">
                <select class="form-control" id="actionDo" multiple style="height: 300px">
                </select>
            </div>
            <div class="form-group col-lg-2">
                <button class="btn btn-default" style="width: 75px;" id="addSelectedItem"> ></button>
                <br/>
                <button class="btn btn-default" style="width: 75px;" id="addAllItens"> >></button>
                <br/>
                <button class="btn btn-default" style="width: 75px;" id="removeSelectedItem"> <</button>
                <br/>
                <button class="btn btn-default" style="width: 75px;" id="removeAllitens"> <<</button>
                <br/>
            </div>
            <div class="form-group col-lg-4">
                <select class="form-control" multiple id="actiosSelected" style="height: 300px">
                </select>
            </div>
        </div>
        <div class="form-group">
            <input type="hidden" id="acao" name="fk_acao_id">
            {{ Form::button('Salvar', ['class' => 'btn btn-primary btnSalvar']) }}
        </div>
        {{ Form::close() }}
    </div>
@endsection

@push('js')
    <script type="text/javascript">
        var listActions = [];
        var listModules = [];
        var listModuleActionsSelected = [];
        var listSelectedItens = [];
        var listNotSelectedItens = [];

        $(document).ready(function () {

            <?php if (isset($acao)) echo ' listActions =  ' . json_encode($acao);?>;
            <?php if (isset($modulos)) echo ' listModules =  ' . json_encode($modulos);?>;
            <?php if (isset($objSelect)) echo ' listModuleActionsSelected =  ' . json_encode($objSelect);?>;

            for (var i in listModules)
                $('#modulesList').append('<option value="' + listModules[i].id + '">' + listModules[i].descricao + '</option>');

            for (var i in listModuleActionsSelected) {
                $('#modulesList').val(listModuleActionsSelected[i].fk_modulo_id);
                break;
            }

            listSelectedItens = [];
            listNotSelectedItens = [];

            for (var i in listActions) {
                var addSelected = false;

                for (var j in listModuleActionsSelected) {
                    if (listActions[i].id == listModuleActionsSelected[j].fk_acao_id) {
                        addSelected = true;
                        $("#actiosSelected").append('<option value="' + listActions[i].id + '">' + listActions[i].descricao + '(' + listActions[i].elemento + ')' + '</option>');
                        listSelectedItens.push(listActions[i]);
                    }
                }

                if (!addSelected) {
                    $("#actionDo").append('<option value="' + listActions[i].id + '">' + listActions[i].descricao + '(' + listActions[i].elemento + ')' + '</option>');
                    listNotSelectedItens.push(listActions[i]);
                }
            }

            $('.btnSalvar').click(function (e) {
                e.preventDefault();

                if ($('#actiosSelected > option').length > 0) {
                    var idsItens = [];

                    for (var i in listSelectedItens)
                        idsItens.push(listSelectedItens[i].id);

                    var a = idsItens.join(',');
                    $('#acao').val(a);

                    $('#formModulosAcoes').submit();
                }
            });

            $('#addSelectedItem').click(function (e) {
                e.preventDefault();
                if ($('#actionDo > option').length > 0) {
                    var lstAux1 = listNotSelectedItens;
                    listNotSelectedItens = [];

                    var lstAux2 = [];

                    var itensSelected = $('#actionDo').val();

                    for (var i in lstAux1) {
                        var a = true;
                        for (var j in itensSelected) {
                            if (parseInt(itensSelected[j]) == lstAux1[i].id) {
                                a = false;
                            }
                        }

                        if (a) {
                            listNotSelectedItens.push(lstAux1[i]);
                        } else {
                            lstAux2.push(lstAux1[i]);
                        }
                    }

                    if (listSelectedItens.length == 0) {
                        listSelectedItens = lstAux2;
                    } else {
                        for (var i in lstAux2) {
                            listSelectedItens.push(lstAux2[i]);
                        }
                    }

                    $('#actiosSelected').empty();
                    for (var i in listSelectedItens)
                        $("#actiosSelected").append('<option value="' + listSelectedItens[i].id + '">' + listSelectedItens[i].descricao + '(' + listSelectedItens[i].elemento + ')' + '</option>');

                    $("#actionDo").empty();
                    for (var i in listNotSelectedItens)
                        $("#actionDo").append('<option value="' + listNotSelectedItens[i].id + '">' + listNotSelectedItens[i].descricao + '(' + listSelectedItens[i].elemento + ')' + '</option>');
                }
                e.preventDefault();
            });

            $('#removeSelectedItem').click(function (e) {
                e.preventDefault();
                if ($('#actiosSelected > option').length > 0) {
                    if ($('#actiosSelected').val() != "") {
                        var lstAux1 = listSelectedItens;
                        listSelectedItens = [];

                        var lstAux2 = [];

                        var itensSelected = $('#actiosSelected').val();

                        for (var i in lstAux1) {
                            var a = true;
                            for (var j in itensSelected) {
                                if (parseInt(itensSelected[j]) == lstAux1[i].id) {
                                    a = false;
                                }
                            }

                            if (a) {
                                listSelectedItens.push(lstAux1[i]);
                            } else {
                                lstAux2.push(lstAux1[i]);
                            }
                        }

                        if (listNotSelectedItens.length == 0) {
                            listNotSelectedItens = lstAux2;
                        } else {
                            for (var i in lstAux2) {
                                listNotSelectedItens.push(lstAux2[i]);
                            }
                        }

                        $('#actiosSelected').empty();
                        for (var i in listSelectedItens)
                            $("#actiosSelected").append('<option value="' + listSelectedItens[i].id + '">' + listSelectedItens[i].descricao + '(' + listSelectedItens[i].elemento + ')' + '</option>');

                        $("#actionDo").empty();
                        for (var i in listNotSelectedItens)
                            $("#actionDo").append('<option value="' + listNotSelectedItens[i].id + '">' + listNotSelectedItens[i].descricao + '(' + listSelectedItens[i].elemento + ')' + '</option>');
                    }
                }
                e.preventDefault();
            });

            $('#addAllItens').click(function (e) {
                e.preventDefault();
                if ($('#actionDo > option').length > 0) {
                    $('#actiosSelected').empty();
                    $('#actionDo').empty();

                    for (var i in listActions)
                        $("#actiosSelected").append('<option value="' + listActions[i].id + '">' + listActions[i].descricao + '(' + listActions[i].elemento + ')' + '</option>');

                    listSelectedItens = listActions;
                    listNotSelectedItens = [];
                }
                e.preventDefault();
            });

            $('#removeAllitens').click(function (e) {
                e.preventDefault();
                if ($('#actiosSelected > option').length > 0) {
                    $('#actiosSelected').empty();
                    $('#actionDo').empty();

                    for (var i in listActions)
                        $("#actionDo").append('<option value="' + listActions[i].id + '">' + listActions[i].descricao + '(' + listActions[i].elemento + ')' + '</option>');

                    listSelectedItens = [];

                    listNotSelectedItens = listActions;
                }

                e.preventDefault();
            });

        });
    </script>
@endpush

