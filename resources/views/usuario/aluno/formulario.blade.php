@extends('layouts.app')

@section('styles')
    <style scoped>
        .tab-pane {
            margin-top: 30px;
        }
        .select2.select2-container.select2-container--default{
            width:100% !important;
        }
    </style>
@endsection

@section('content')
    <div class="box padding20">
        <h2 class="table"><span>{{ $modulo['moduloDetalhes']->modulo }}</span></h2>
        <hr class="hr"/>

        <ul class="nav nav-tabs">
            <li class="nav-item active">
                <a href="#dados" data-toggle="tab">Dados</a>
            </li>
            <li class="nav-item @if(Request::is('*/incluir')) disabled @endif" >
                <a href="#pedidos" id="tabPedidos" class="@if(Request::is('*/incluir')) disabled @endif" data-toggle="tab">Pedidos</a>
            </li>
            <li class="nav-item @if(Request::is('*/incluir')) disabled @endif">
                <a href="#tags" id="tabTags" class="@if(Request::is('*/incluir')) disabled @endif" data-toggle="tab">Tags</a>
            </li>
            <li class="nav-item @if(Request::is('*/incluir')) disabled @endif">
                <a href="#estrutraCurricular" id="estrutra" class="@if(Request::is('*/incluir')) disabled @endif" data-toggle="tab">
                    Estrutura Curricular
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div id="dados" class="tab-pane fade in active" >
                @include("usuario.aluno.blocks.dados")
            </div>
            <div id="pedidos" class="tab-pane fade" >
                @if(Request::is('*/editar')) @include("usuario.aluno.blocks.pedidos") @endif
            </div>
            <div id="tags" class="tab-pane fade" >
                @if(Request::is('*/editar')) @include("usuario.aluno.blocks.tags") @endif
            </div>
            <div id="estrutraCurricular" class="tab-pane fade" >
                @if(Request::is('*/editar')) @include("usuario.aluno.blocks.estruturaCurricular") @endif
            </div>
        </div>
    </div>

    
@endsection


@push('js')
    <script type="text/javascript">

        $('#tabTags').click(function () {
            buscarListaTags()
        });

        buscarListaTags = function() {
            $.ajax({
                url: "/admin/tags-aluno/listar",
                method: "GET",
                data: {
                    id : '<?php echo isset($objAluno->id) ? $objAluno->id : null; ?>' ,
                    _token: '{{ csrf_token() }}'
                },
                dataType: "html"
            }).done(function (html) {
                $('#tags').html(html);
                resetSelect();
            });
        };

        inserirTags = function(el) {
            $(el).html('Processando requisição ...');
            $.ajax({
                url: "/admin/tags-aluno",
                method: "POST",
                data: {
                    id : '<?php echo isset($objAluno->id) ? $objAluno->id : null; ?>' ,
                    tags: $('#selectedTags').val(),
                    _token: '{{ csrf_token() }}'
                },
                dataType: "json"
            }).done(function (data) {
                if (data.success == true) {
                    buscarListaTags();
                }
            });
        };

        deletarTag = function(id, button) {
            $(button).html('Processando requisição ...');
            $.ajax({
                url: "/admin/tags-aluno/deletar",
                method: "POST",
                data: {
                    id : id ,
                    _token: '{{ csrf_token() }}'
                },
                dataType: "json",
                success: function (data) {
                    console.log(data)
                    if (data.success == true || data.success == 'true') {
                        buscarListaTags();
                    }
                }
            }).done(function (data) {
                console.log(data)
                if (data.success == true || data.success == 'true') {
                    buscarListaTags();
                }
            });
        }

        $('#tabPedos').click(function () {
            console.log('test');
        });

        resetSelect = function() {
            $('.myselect').select2({
                allowClear: true,
                tags: true,
            });
        };

        resetSelect();

        $('#curso_superior').change(function () {
            $('#bloco_universidade').hide();
            if ($(this).val() == 'sim') {
                $('#bloco_universidade').show();
            }
        });

        $('#curso_especializacao').change(function () {
            $('#bloco_especializacao').hide();
            if ($(this).val() == 'sim') {
                $('#bloco_especializacao').show();
            }
        });

        $('#especializacao_universidade').change(function () {
            $('#instituicao_outros').hide();
            if ($(this).val() == 'outro') {
                $('#instituicao_outros').show();
            }
        });

        $('#faculdade').change(function () {
            $('#universidade_outros').hide();
            if ($(this).val() == 'outro') {
                $('#universidade_outros').show();
            }
        });

        $(document).ready(function() {
            $('input[name="_token"]').attr('id', 'token');
        });
        $(".nav-tabs a[data-toggle=tab]").on("click", function(e) {
            if ($(this).hasClass("disabled")) {
                e.preventDefault();
                return false;
            }
        });
    </script>
@endpush
