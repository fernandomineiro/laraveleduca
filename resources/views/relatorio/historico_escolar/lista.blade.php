@extends('layouts.app')
@section('styles')
    <style>
        @media print {
            body * {
                visibility: hidden;
            }

            #printable, #printable * {
                visibility: visible;
            }

            #printable {
                position: fixed;
                left: 0;
                top: 0;
            }
        }

        #page-historico-escolar table th,
        #page-historico-escolar table td  {
            padding: 5px;
        }

        select[readonly]{
            background: #eee;
            cursor:no-drop;
        }

        select[readonly] option{
            display:none;
        }
    </style>
@endsection


@section('content')
    <div class="box padding20" id="page-historico-escolar">
        <div class="col-md-9"><h2 class="table">Histórico do aluno</h2></div>
        {{--<div class="col-md-3" style="margin-top: 20px;">
            <div class="btn-group pull-right">
                @if (!empty($link_pdf))
                    <a target="_blamk" class="btn btn-primary pull-right" href="{{ $link_pdf }}">Imprimir</a>
                @endif
            </div>
        </div>--}}
        <hr class="clear hr" />

        <div class="row hide" id="sem_permissao">
            <div class="col-md-12">
                <div class="alert alert-info">Você não tem permissão utilizar esse relatório, consulte o administrador do sistema.</div>
            </div>
        </div>

        <div class="panel-group accordion scrollable" id="accordion2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#filtro">
                            <i class="fa fa-search"></i> Filtros
                            <i class="fa fa-angle-double-down"></i>
                        </a>
                    </h4>
                </div>
                <div id="filtro" class="panel-collapse">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-6 col-md-3 col-lg-3">
                                <div class="form-group">
                                    <label>Nome:</label>
                                    <input type="text" name="nome" id="nome" class="form-control" placeholder="Nome" value="{{ request()->get('nome') }}">
                                </div>
                            </div>
                            <div class="col-xs-6 col-md-3 col-lg-3">
                                <div class="form-group">
                                    <label>CPF:</label>
                                    <input type="text" name="cpf" id="cpf" class="form-control" placeholder="CPF do aluno" value="{{ request()->get('cpf') }}">
                                </div>
                            </div>
                            <div class="col-xs-6 col-md-3 col-lg-3">
                                <div class="form-group">
                                    <label>Curso:</label>
                                    <input type="text" name="curso" id="curso" class="form-control" placeholder="Nome do curso" value="{{ request()->get('curso') }}">
                                </div>
                            </div>

                            <div class="col-xs-6 col-md-3 col-lg-3">
                                <div class="form-group">
                                    <label>Instituição:</label>
                                    <select name="ies" id="ies" class="form-control "></select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-6 col-md-4 col-lg-4">
                                <div class="form-group">
                                    <label></label>
                                    <div class="btn-group">
                                        <label></label><br>
                                        <button type="button" class="btn  btn-block btn-success" id="btnFiltrar">Filtrar</button>
                                    </div>
                                    <div class="btn-group">
                                        <label></label><br>
                                        <button type="button" class="btn btn-block btn-secondary" id="btnFiltrarCancelar">Limpar filtro</button>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            {{-- Lista de usuários localizados pelo filtro --}}


            <div class="col-md-12 text-center" style="margin-top: 20px;" >
                <div class="btn-group">
                    <div class="load"><i class="fa fa-spinner fa-spin fa-5x"></i>
                        <span class="sr-only">Buscando dados...</span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">

                    <table id="table_lista_usuarios" class="table table-bordered table-striped" width="100%"></table>
                </div>
            </div>

            <hr>
            <div class="row hide" id="tableRelatorioView">
                <div class="col-md-12" style="margin-top: 20px;">
                    <div class="btn-group pull-right">
                        <a id="imprimirPDF" target="_blank" class="btn btn-primary pull-right" href="">Imprimir</a>
                    </div>
                </div>
                <div class="col-md-12">
                    <div id="printable">
                        <h1>HISTÓRICO ESCOLAR</h1>
                        <b>Nome:</b> <span id="aluno_nome"></span><br/>
                        <b>Data de Nascimento:</b> <span id="aluno_data_nascimento"></span><br/>
                        <b>CPF:</b> <span id="aluno_cpf"></span><br/>
                        <b>RG:</b> <span id="aluno_identidade"></span><br/>
                        <b>Cidade/UF:</b> <span id="aluno_descricao_cidade"></span>/<span id="aluno_uf_estado"></span><br/>
                        <b>Telefone fixo:</b> <span id="aluno_telefone_1"></span><br/>
                        <b>Telefone celular:</b> <span id="aluno_telefone_2"></span><br/>
                        <b>Instituição:</b> <span id="aluno_universidade"></span> <br/>

                        <hr/>

                        <div class="row" id="semestre_fail">
                            <div class="col-md-12">
                                <div class="alert alert-info">Para visualizar o histórico de horas assistidas é necessário que o aluno inicialize o curso.</div>
                            </div>
                        </div>

                        <div id="semestres"></div>

                    </div>
                </div>
            </div>

            {{--
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">Localize o histórico do aluno buscando por nome ou CPF.</div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">Aluno não encontrado na base!</div>
                </div>
            </div>
            --}}


        </div>
        <br>
    </div>
@endsection


@push('js')
    <script>
        $('#print').on('click', function(){
            window.print();
        });
        let access_token = null;
        let faculdades = {!! $faculdades !!}

        $(document).ready( function() {
            let data = {};
            let userLogged = @json(\Illuminate\Support\Facades\Session::all());
            
            
            $('#nome').keypress(function(event){
                let keycode = (event.keyCode ? event.keyCode : event.which);
                if(keycode == '13'){
                    $('#btnFiltrar').click();
                }
            });

            $('#cpf').keypress(function(event){
                let keycode = (event.keyCode ? event.keyCode : event.which);
                if(keycode == '13'){
                    $('#btnFiltrar').click();
                }
            });
            
            $('#curso').keypress(function(event){
                let keycode = (event.keyCode ? event.keyCode : event.which);
                if(keycode == '13'){
                    $('#btnFiltrar').click();
                }
            });
            

            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }});

            access_token = userLogged.user.access_token;

            listaFaculdades();

            $('.load').hide();
            
            $('#btnFiltrar').click(function () {
                data.token = "{{\Illuminate\Support\Facades\Session::token()}}"
                data.nome = $('#nome').val();
                data.cpf = $('#cpf').val();
                data.curso = $('#curso').val();
                data.ies = $('#ies').val();
                data.token = access_token;

                // console.log(data)

                if(data.nome.length < 3 && !data.cpf && !data.curso  && !data.ies) {
                    toastr.error('Informe um nome com no mínimo 3 caracteres ou um cpf')
                    return false;
                }

                if(data.nome.length < 3 && !data.cpf && !data.curso && data.ies) {
                    toastr.error('Para pesquisar na instituição, informe um nome (pelo menos 3 caracteres) ou cpf.')
                    return false;
                }

                if ( $.fn.DataTable.isDataTable('#table_lista_usuarios') ) {
                    $('#table_lista_usuarios').DataTable().destroy();
                }
                $('#table_lista_usuarios').html('');

                applyFilter(data);
            });

            $('#btnFiltrarCancelar').click(function () {
                $('#curso').val('').removeAttr('readonly');
                $('#nome').val('').removeAttr('readonly');
                $('#cpf').val('').removeAttr('readonly');
                $('#ies').val('');

                if ( $.fn.DataTable.isDataTable('#table_lista_usuarios') ) {
                    $('#table_lista_usuarios').DataTable().destroy();
                }
                $('#table_lista_usuarios').html('');
                $('#tableRelatorioView').addClass('hide');

                toastr.success('Filtro removido com sucesso.');
            });


            //$('#cpf').mask('999.999.999-99');

            $('#semestre_fail').hide();
        });

        function applyFilter(data) {
            // console.group('Aplicar Filtro')
            // console.log(data)
            // console.groupEnd();

            // console.log('vou carregar o spnnier')
            $('.load').show();
            if ( $.fn.DataTable.isDataTable('#table_lista_usuarios') ) {
                $('#table_lista_usuarios').DataTable().destroy();
            }
            $('#table_lista_usuarios').html('');
            $('#tableRelatorioView').addClass('hide')


            $.ajax({
                type: 'POST',
                url: '/admin/relatorios/historico-escolar/get_alunos',
                headers: { Authorization: `Bearer ${data.token}` },
                data: data,
                success: function(data) {
                    // console.log(data)
                    // return;
                    let dataSet = [];
                    let nome_completo = null;
                    let cpf_aluno = null;
                    if(data.length > 0) {
                        data.map(function (item, index) {
                            nome_completo = item.nome + ' ' + item.sobre_nome;
                            !item.cpf ? item.cpf = 'Não informado' : null;

                            dataSet.push([
                                item.id,
                                item.cpf,
                                `<a href="javascript:void(0)" onclick="exibeRelatorioUsuario('${item.fk_usuario_id}')"> ${nome_completo}</a>`,
                                item.faculdade_fantasia
                            ])
                        });
                    }

                    if ( $.fn.DataTable.isDataTable('#table_lista_usuarios') ) {
                        $('#table_lista_usuarios').DataTable().destroy();
                    }
                    $('#table_lista_usuarios').dataTable({
                        "language": {
                            "url": "https://cdn.datatables.net/plug-ins/1.10.20/i18n/Portuguese-Brasil.json"
                        },
                        data: dataSet,
                        columns: [
                            { title: 'ID Aluno' },
                            { title: "CPF" },
                            { title: "Nome" },
                            { title: "IES" }
                        ]
                    });
                    $('.load').hide();
                },
                error: function(data) {
                    console.log(data);
                    $("#overlay").fadeOut(300);
                }
            });

        }

        function listaFaculdades() {
            if(faculdades.length > 0) {
                let lista_faculdades = '<option value="">Selecione um item...</option>';
                faculdades.map(function (item, index) {
                    lista_faculdades += `<option value="${item.id}">${item.fantasia}</option>`;
                });
                $('#ies').html('').html(lista_faculdades);
            } else {
                $('#ies').html('');
            }
        }

        function exibeRelatorioUsuario(fk_usuario_id) {
            // console.group('Relatorio Usuário')
            // console.log(fk_usuario_id);
            // console.groupEnd();
            $.ajax({
                type: 'POST',
                url: '/admin/relatorios/historico-escolar/get_relatorio',
                data: {'fk_usuario_id': fk_usuario_id},
                success: function(data) {
                    // console.group('Dados do relatorio')
                    // console.log(data)
                    // console.groupEnd()
                    setDataRelatorio(data)
                },
                error: function(data) {
                    console.log(data);
                }
            });
        }
        
        function setDataRelatorio(data) {
            let divSemestresHTML = '';
            let cursos_online_carga_horaria_total = 0;
            let cursos_remotos_carga_horaria_total = 0;
            let cursos_presenciais_carga_horaria_total = 0;
            let cursos_trilha_do_conhecimento_carga_horaria_total = 0;
            let aluno = data.aluno;
            let semestres = null;
            let nao_informado = 'Não informado'

            $('#imprimirPDF').attr('href', data.link_pdf)

            $('#tableRelatorioView').removeClass('hide');

            $('#semestres').html('');

            // console.group('Carregar dados do Relatório');
            // console.log(data);
            // console.groupEnd();

            if(data.semestres) {
                semestres = data.semestres;
                $('#semestre_fail').hide();
            } else {
                $('#semestre_fail').show();
                // console.log('mostrar erro')
            }

            cleanDataRelatorio();

            // Valida dados para o cabeçalho do relatório
            !aluno.data_nascimento ?  aluno.data_nascimento = nao_informado : null;
            !aluno.cpf ?  aluno.cpf = nao_informado : null;
            !aluno.identidade ?  aluno.identidade = nao_informado : null;
            !aluno.descricao_cidade ?  aluno.descricao_cidade = nao_informado : null;
            !aluno.uf_estado ?  aluno.uf_estado = nao_informado : null;
            !aluno.telefone_1 ?  aluno.telefone_1 = nao_informado : null;
            !aluno.telefone_2 ?  aluno.telefone_2 = nao_informado : null;

            $('#aluno_nome').text(aluno.nome + ' ' + aluno.sobre_nome);
            $('#aluno_data_nascimento').text(aluno.data_nascimento);
            $('#aluno_cpf').text(aluno.cpf);
            $('#aluno_identidade').text(aluno.identidade);
            $('#aluno_descricao_cidade').text(aluno.descricao_cidade);
            $('#aluno_uf_estado').text(aluno.uf_estado);
            $('#aluno_telefone_1').text(aluno.telefone_1);
            $('#aluno_telefone_2').text(aluno.telefone_2);

            // if(aluno.curso_superior === 'sim') {
            //     if(aluno.universidade === 'outro') {
            //         $('#aluno_universidade').text(aluno.universidade_outro)
            //     } else {
            //         $('#aluno_universidade').text(aluno.universidade)
            //     }
            // }
            $('#aluno_universidade').text(aluno.faculdade_instituicao)

            if(semestres) {
                $.map( semestres, function( cursos, semestre ) {
                    if(cursos['online'] || cursos['remoto'] || cursos['presencial'] || cursos['trilha_do_conhecimento']) {
                        divSemestresHTML += '<center><h2>Extracurricular</h2></center>';
                        divSemestresHTML += `<center><h4><b>SEMESTRE ${semestre}</b></h4></center>`;

                        if(cursos['online']) {
                            divSemestresHTML += `<b><h4>CURSOS ONLINE</h4></b>`;
                            divSemestresHTML += `<table width="100%" border="1">`;
                            divSemestresHTML += `<thead>`;
                            divSemestresHTML += `<tr> <th>DISCIPLINA</th>`;
                            divSemestresHTML += `<th>PROFESSOR</th>`;
                            divSemestresHTML += `<th>NOTA - QUESTIONÁRIO</th>`;
                            divSemestresHTML += `<th>NOTA - TRABALHO</th>`;
                            divSemestresHTML += `<th>MÉDIA</th>`;
                            divSemestresHTML += `<th>C/H</th>`;
                            divSemestresHTML += `<th>SITUAÇÃO</th>`;
                            divSemestresHTML += `<th>DT. INÍCIO</th>`;
                            divSemestresHTML += `<th>DT. CONCLUSÃO</th></tr>`;
                            divSemestresHTML += `</thead>`;
                            divSemestresHTML += `<tbody>`;

                            if(cursos['online']) {
                                $.map(cursos['online'], function (curso, index) {
                                    cursos_online_carga_horaria_total += curso.carga_horaria;
                                    !curso.professor_nome ? curso.professor_nome = '--' : null;

                                    divSemestresHTML += `<tr>`;
                                    divSemestresHTML += `<th>${curso.nome}</th>`;
                                    divSemestresHTML += `<th>${curso.professor_nome}</th>`;
                                    divSemestresHTML += `<th>${curso.nota_quiz}</th>`;
                                    divSemestresHTML += `<th>${curso.nota_trabalho}</th>`;
                                    divSemestresHTML += `<th>${curso.media}</th>`;

                                    if (curso.carga_horaria > 0) {
                                        divSemestresHTML += `<th>${curso.carga_horaria} hora(s)</th>`;
                                    } else {
                                        divSemestresHTML += `<th>--</th>`;
                                    }

                                    if (curso.data_conclusao === '--') {
                                        divSemestresHTML += `<th>Em andamento</th>`;
                                    } else {
                                        divSemestresHTML += `<th>Concluído</th>`;
                                    }
                                    divSemestresHTML += `<th>${curso.data_inicio}</th>`;
                                    divSemestresHTML += `<th>${curso.data_conclusao}</th>`;
                                    divSemestresHTML += `</tr>`;
                                });

                                divSemestresHTML += `</tbody>`;
                                divSemestresHTML += `</table>`;

                                if (cursos_online_carga_horaria_total > 0) {
                                    divSemestresHTML += `<table width="100%" style="margin-top: 10px">`;
                                    divSemestresHTML += `<thead>`;
                                    divSemestresHTML += `<tr>`;
                                    divSemestresHTML += `<th><span class="pull-right">CARGA HORÁRIA TOTAL</span></th>`;
                                    divSemestresHTML += `</tr>`;
                                    divSemestresHTML += `</thead>`;
                                    divSemestresHTML += `<tbody>`;
                                    divSemestresHTML += `<tr>`;
                                    divSemestresHTML += `<th><span class="pull-right">${cursos_online_carga_horaria_total} hora(s)</span></th>`;
                                    divSemestresHTML += `</tr>`;
                                    divSemestresHTML += `</tbody>`;
                                    divSemestresHTML += `</table>`;
                                }
                            }
                        }
                        if(cursos['remoto']) {
                            divSemestresHTML += `<hr>`;
                            divSemestresHTML += `<b><h4>CURSOS REMOTOS</h4></b>`;
                            divSemestresHTML += `<table width="100%" border="1">`;
                            divSemestresHTML += `<thead><tr>`;
                            divSemestresHTML += `<th>DISCIPLINA</th>`;
                            divSemestresHTML += `<th>PROFESSOR</th>`;
                            divSemestresHTML += `<th>NOTA - QUESTIONÁRIO</th>`;
                            divSemestresHTML += `<th>NOTA - TRABALHO</th>`;
                            divSemestresHTML += `<th>MÉDIA</th>`;
                            divSemestresHTML += `<th>C/H</th>`;
                            divSemestresHTML += `<th>FREQUÊNCIA</th>`;
                            divSemestresHTML += `<th>SITUAÇÃO</th>`;
                            divSemestresHTML += `<th>DT. INÍCIO</th>`;
                            divSemestresHTML += `<th>DT. CONCLUSÃO</th>`;
                            divSemestresHTML += `</tr>`;
                            divSemestresHTML += `</thead>`;
                            divSemestresHTML += `<tbody>`;

                            $.map(cursos['remoto'], function (curso, index) {
                                cursos_remotos_carga_horaria_total += curso.carga_horaria;
                                !curso.professor_nome ? curso.professor_nome = '--' : null;

                                divSemestresHTML += `<tr>`;
                                divSemestresHTML += `<th>${curso.nome }</th>`;
                                divSemestresHTML += `<th>${curso.professor_nome }</th>`;
                                divSemestresHTML += `<th>${curso.nota_quiz }</th>`;
                                divSemestresHTML += `<th>${curso.nota_trabalho }</th>`;
                                divSemestresHTML += `<th>${curso.media }</th>`;

                                if (curso.carga_horaria > 0) {
                                    divSemestresHTML += `<th>${curso.carga_horaria} hora(s)</th>`;
                                } else {
                                    divSemestresHTML += `<th>--</th>`;
                                }

                                divSemestresHTML += `<th>${curso.frequencia}</th>`;

                                if (curso.data_conclusao === '--') {
                                    divSemestresHTML += `<th>Em andamento</th>`;
                                } else {
                                    divSemestresHTML += `<th>Concluído</th>`;
                                }
                                divSemestresHTML += `<th>${curso.data_inicio}</th>`;
                                divSemestresHTML += `<th>${curso.data_conclusao}</th>`;
                                divSemestresHTML += `</tr>`;
                            });

                            divSemestresHTML += `</tbody>`;
                            divSemestresHTML += `</table>`;

                            if (cursos_remotos_carga_horaria_total > 0) {
                                divSemestresHTML += `<table width="100%" style="margin-top: 10px">`;
                                divSemestresHTML += `<thead>`;
                                divSemestresHTML += `<tr>`;
                                divSemestresHTML += `<th><span class="pull-right">CARGA HORÁRIA TOTAL</span></th>`;
                                divSemestresHTML += `</tr>`;
                                divSemestresHTML += `</thead>`;
                                divSemestresHTML += `<tbody>`;
                                divSemestresHTML += `<tr>`;
                                divSemestresHTML += `<th><span class="pull-right">${cursos_remotos_carga_horaria_total} hora(s)</span></th>`;
                                divSemestresHTML += `</tr>`;
                                divSemestresHTML += `</tbody>`;
                                divSemestresHTML += `</table>`;
                            }
                        }
                    }

                    if(cursos['presencial']) {
                        divSemestresHTML += `<hr>`;
                        divSemestresHTML += `<b><h4>CURSOS PRESENCIAIS</h4></b>`;
                        divSemestresHTML += `<table width="100%" border="1">`;
                        divSemestresHTML += `<thead>`;
                        divSemestresHTML += `<tr>`;
                        divSemestresHTML += `<th>DISCIPLINA</th>`;
                        divSemestresHTML += `<th>PROFESSOR</th>`;
                        divSemestresHTML += `<th>NOTA - QUESTIONÁRIO</th>`;
                        divSemestresHTML += `<th>NOTA - TRABALHO</th>`;
                        divSemestresHTML += `<th>MÉDIA</th>`;
                        divSemestresHTML += `<th>C/H</th>`;
                        divSemestresHTML += `<th>FREQUÊNCIA</th>`;
                        divSemestresHTML += `<th>SITUAÇÃO</th>`;
                        divSemestresHTML += `<th>DT. INÍCIO</th>`;
                        divSemestresHTML += `<th>DT. CONCLUSÃO</th>`;
                        divSemestresHTML += `</tr>`;
                        divSemestresHTML += `</thead>`;
                        divSemestresHTML += `<tbody>`;

                        $.map(cursos['presencial'], function (curso, index) {
                            cursos_presenciais_carga_horaria_total += curso.carga_horaria;
                            !curso.professor_nome ? curso.professor_nome = '--' : null;

                            divSemestresHTML += `<tr>`;
                            divSemestresHTML += `<th>${curso.nome}</th>`;
                            divSemestresHTML += `<th>${curso.professor_nome}</th>`;
                            divSemestresHTML += `<th>${curso.nota_quiz}</th>`;
                            divSemestresHTML += `<th>${curso.nota_trabalho}</th>`;
                            divSemestresHTML += `<th>${curso.media}</th>`;

                            if (curso.carga_horaria > 0) {
                                divSemestresHTML += `<th>${curso.carga_horaria} hora(s)</th>`;
                            } else {
                                divSemestresHTML += `<th>--</th>`;
                            }

                            divSemestresHTML += `<th>${curso.frequencia}</th>`;

                            if (curso.data_conclusao === '--') {
                                divSemestresHTML += `<th>Em andamento</th>`;
                            } else {
                                divSemestresHTML += `<th>Concluído</th>`;
                            }

                            divSemestresHTML += `<th>${curso.data_inicio}</th>`;
                            divSemestresHTML += `<th>${curso.data_conclusao}</th>`;
                            divSemestresHTML += `</tr>`;

                        });

                        divSemestresHTML += `</tbody>`;
                        divSemestresHTML += `</table>`;

                        if (cursos_presenciais_carga_horaria_total > 0) {
                            divSemestresHTML += `<table width="100%" style="margin-top: 10px">`;
                            divSemestresHTML += `<thead>`;
                            divSemestresHTML += `<tr>`;
                            divSemestresHTML += `<th><span class="pull-right">CARGA HORÁRIA TOTAL</span></th>`;
                            divSemestresHTML += `</tr>`;
                            divSemestresHTML += `</thead>`;
                            divSemestresHTML += `<tbody>`;
                            divSemestresHTML += `<tr>`;
                            divSemestresHTML += `<th><span class="pull-right">$cursos_presenciais_carga_horaria_total }} hora(s)</span></th>`;
                            divSemestresHTML += `</tr>`;
                            divSemestresHTML += `</tbody>`;
                            divSemestresHTML += `</table>`;
                        }
                    }

                    if(cursos['trilha_do_conhecimento']) {
                        divSemestresHTML += `<hr>`;
                        divSemestresHTML += `<b><h4>CURSOS - TRILHA DO CONHECIMENTO</h4></b>`;
                        divSemestresHTML += `<table width="100%" border="1">`;
                        divSemestresHTML += `<thead>`;
                        divSemestresHTML += `<tr>`;
                        divSemestresHTML += `<th>DISCIPLINA</th>`;
                        divSemestresHTML += `<th>PROFESSOR</th>`;
                        divSemestresHTML += `<th>NOTA - QUESTIONÁRIO</th>`;
                        divSemestresHTML += `<th>NOTA - TRABALHO</th>`;
                        divSemestresHTML += `<th>MÉDIA</th>`;
                        divSemestresHTML += `<th>C/H</th>`;
                        divSemestresHTML += `<th>FREQUÊNCIA</th>`;
                        divSemestresHTML += `<th>SITUAÇÃO</th>`;
                        divSemestresHTML += `<th>DT. INÍCIO</th>`;
                        divSemestresHTML += `<th>DT. CONCLUSÃO</th>`;
                        divSemestresHTML += `</tr>`;
                        divSemestresHTML += `</thead>`;
                        divSemestresHTML += `<tbody>`;

                        $.map(cursos['trilha_do_conhecimento'], function (curso, index) {
                            cursos_trilha_do_conhecimento_carga_horaria_total += curso.carga_horaria;
                            !curso.professor_nome ? curso.professor_nome = '--' : null;

                            divSemestresHTML += `<tr>`;
                            divSemestresHTML += `<th>${curso.nome}</th>`;
                            divSemestresHTML += `<th>${curso.professor_nome}</th>`;
                            divSemestresHTML += `<th>${curso.nota_quiz}</th>`;
                            divSemestresHTML += `<th>${curso.nota_trabalho}</th>`;
                            divSemestresHTML += `<th>${curso.media}</th>`;

                            if (curso.carga_horaria > 0) {
                                divSemestresHTML += `<th>${curso.carga_horaria} hora(s)</th>`;
                            } else {
                                divSemestresHTML += `<th>--</th>`;
                            }

                            divSemestresHTML += `<th>${curso.frequencia}</th>`;

                            if (curso.data_conclusao === '--') {
                                divSemestresHTML += `<th>Em andamento</th>`;
                            } else {
                                divSemestresHTML += `<th>Concluído</th>`;
                            }
                            divSemestresHTML += `<th>${curso.data_inicio}</th>`;
                            divSemestresHTML += `<th>${curso.data_conclusao}</th>`;
                            divSemestresHTML += `</tr>`;
                        });

                        divSemestresHTML += `</tbody>`;
                        divSemestresHTML += `</table>`;

                        if (cursos_trilha_do_conhecimento_carga_horaria_total > 0) {
                            divSemestresHTML += `<table width="100%" style="margin-top: 10px">`;
                            divSemestresHTML += `<thead>`;
                            divSemestresHTML += `<tr>`;
                            divSemestresHTML += `<th><span class="pull-right">CARGA HORÁRIA TOTAL</span></th>`;
                            divSemestresHTML += `</tr>`;
                            divSemestresHTML += `</thead>`;
                            divSemestresHTML += `<tbody>`;
                            divSemestresHTML += `<tr>`;
                            divSemestresHTML += `<th><span class="pull-right">${cursos_trilha_do_conhecimento_carga_horaria_total} hora(s)</span></th>`;
                            divSemestresHTML += `</tr>`;
                            divSemestresHTML += `</tbody>`;
                            divSemestresHTML += `</table>`;
                        }
                    }
                });
            }
            if(data['cursos_nao_iniciados'] && Object.keys(data['cursos_nao_iniciados']).length > 0) {
                // console.log('o conteúdo é : ' + Object.keys(data['cursos_nao_iniciados']).length)
                // console.log(data['cursos_nao_iniciados'])
                divSemestresHTML += `<hr>`;
                divSemestresHTML += `<b><h4>CURSOS NÃO INICIADOS</h4></b>`;
                divSemestresHTML += `<table width="100%" border="1">`;
                divSemestresHTML += `<thead>`;
                divSemestresHTML += `<tr>`;
                divSemestresHTML += `<th>DISCIPLINA</th>`;
                divSemestresHTML += `<th>PROFESSOR</th>`;
                divSemestresHTML += `<th>NOTA - QUESTIONÁRIO</th>`;
                divSemestresHTML += `<th>NOTA - TRABALHO</th>`;
                divSemestresHTML += `<th>MÉDIA</th>`;
                divSemestresHTML += `<th>C/H</th>`;
                divSemestresHTML += `<th>FREQUÊNCIA</th>`;
                divSemestresHTML += `<th>SITUAÇÃO</th>`;
                divSemestresHTML += `<th>DT. INÍCIO</th>`;
                divSemestresHTML += `<th>DT. CONCLUSÃO</th>`;
                divSemestresHTML += `</tr>`;
                divSemestresHTML += `</thead>`;
                divSemestresHTML += `<tbody>`;

                $.map(data['cursos_nao_iniciados'], function (curso, index) {
                    divSemestresHTML += `<tr>`;
                    divSemestresHTML += `<th>${curso.titulo}</th>`;
                    divSemestresHTML += `<th>---</th>`;
                    divSemestresHTML += `<th>---</th>`;
                    divSemestresHTML += `<th>---</th>`;
                    divSemestresHTML += `<th>---</th>`;
                    divSemestresHTML += `<th>---</th>`;
                    divSemestresHTML += `<th>---</th>`;
                    divSemestresHTML += `<th>---</th>`;
                    divSemestresHTML += `<th>---</th>`;
                    divSemestresHTML += `<th>---</th>`;
                    divSemestresHTML += `</tr>`;
                });

                divSemestresHTML += `</tbody>`;
                divSemestresHTML += `</table>`;
            }
            $('#semestres').html(divSemestresHTML);

            toastr.success('Dados carregados com sucesso.')

        }

        function cleanDataRelatorio() {
            // let arr = $("#tableRelatorioView [id]").map(function() {
            //     if(this.id !== 'printable') {
            //         $('#' + this.id).text('')
            //     }
            //     return this.id;
            // });
            $('#aluno_nome').text('');
            $('#aluno_data_nascimento').text('');
            $('#aluno_cpf').text('');
            $('#aluno_descricao_cidade').text('');
            $('#aluno_uf_estado').text('');
            $('#aluno_telefone_1').text('');
            $('#aluno_telefone_2').text('');

        }
    </script>
@endpush
