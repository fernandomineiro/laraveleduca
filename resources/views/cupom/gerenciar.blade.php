@extends('layouts.app')
@section('styles')
    <style>
        table.display thead th, table.display thead td {
            border-bottom: 2px solid #ddd;
        }
    </style>
@endsection

@section('content')
    <div class="box padding20">
        <h2 class="table"><span>Gerenciando o cupom: {{$cupom->titulo}}</span></h2>
        <a href="/admin/cupom/index" class="label label-default">Voltar</a>
        <hr class="hr" />

        {{ Form::open(['method' => 'POST', 'url' => '/admin/cupom/salvarrelacionamentos', 'id' => 'cupomrform']) }}

        <ul class="nav nav-tabs">
            <li class="nav-item active"><a data-toggle="tab" href="#alunos">Alunos</a></li>
            <li class="nav-item"><a data-toggle="tab" href="#cursos">Cursos</a></li>
            <li class="nav-item"><a data-toggle="tab" href="#categoriasacursos">Categorias de Cursos</a></li>
            <li class="nav-item"><a data-toggle="tab" href="#trilhas">Trilhas</a></li>
        </ul>
        <div class="tab-content">
            <div id="alunos" class="tab-pane fade in active">
                <div id="bloco_alunos">
                    <div class="alunos">
                        <br>
                        <br>
                        <div class="form-group">
                            <a href="javascript:;" id="btn_incluir_aluno-1" class="btn btn-success right btn_incluir_aluno"><i class="fa fa-plus"></i></a>
                        </div>
                        <div class="form-group">
                            {{ Form::label('Alunos') }}
                            {{ Form::select('fk_alunos[1]', ['' => 'Selecionar'] + $lista_alunos, null, ['class' => 'form-control']) }}
                        </div>
                        <hr/>
                    </div>
                </div>

                <div id="default_alunos" style="display: none;" data-id="__X__">
                    <div class="alunos" data-contador="__X__">
                        <div class="form-group">
                            <a href="javascript:;" id="btn_remover_aluno-__X__" class="margin-bottom-10 btn btn-success right btn_remover_aluno"><i class="fa fa-minus"></i></a>
                        </div>
                        <div class="form-group">
                            {{ Form::label('Alunos') }}
                            {{ Form::select('fk_alunos[__X__]', ['' => 'Selecionar'] + $lista_alunos, null, ['class' => 'form-control']) }}
                        </div>
                        <hr/>
                    </div>
                </div>

                @if(count($cupom_alunos) > 0)
                    <table cellpadding="0" cellspacing="0" border="0" id="table-alunos" class="table table-bordered table-striped dataTable display">
                        <thead>
                        <th>Aluno</th>
                        <th>Cpf</th>
                        <th>E-mail</th>
                        <th>Cupom</th>
                        <th>Projeto</th>
                        <th>Status</th>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Data Inicial</th>
                        <th>Data Final</th>
                        <th>Ações</th>
                        </thead>
                        <tbody>
                        @foreach($cupom_alunos as $item)
                            <tr>
                                <td>{{ $item->aluno_nome }}</td>
                                <td>{{ $item->cpf }}</td>
                                <td>{{ $item->email }}</td>
                                <td>{{ $item->titulo }}</td>
                                <td>{{ $lista_faculdades[$item->fk_faculdade]['descricao'] }}</td>
                                <td>{{ ($item->status == 1 ? "Ativo" : "Inativo") }}</td>
                                <td>{{ $item->codigo_cupom }}</td>
                                <td>{{ $tipo_cupom[$item->tipo_cupom_desconto] }}</td>
                                <td>{{ ($item->tipo_cupom_desconto == 2) ? 'R$ '. number_format( $item->valor , 2, ',', '.') : (int)$item->valor}}</td>
                                <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_inicial))) }}</td>
                                <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_final))) }}</td>
                                <td nowrap>
                                    {{ Form::open(['method' => 'POST', 'url' => '/admin/cupom/deletarrelacionamentos', 'style' => 'display:inline;']) }}
                                        <input type="hidden" name="id" value="{{$item->id}}">
                                        <input type="hidden" name="tipo" value="1">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir?')">Excluir</button>
                                    {{ Form::close() }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info">Nenhum registro no banco!</div>
                @endif
            </div>
            <div id="cursos" class="tab-pane fade">
                <div id="bloco_cursos">
                    <div class="cursos">
                        <br>
                        <br>
                        <div class="form-group">
                            <a href="javascript:;" id="btn_incluir_secao-1" class="btn btn-success right btn_incluir_curso"><i class="fa fa-plus"></i></a>
                        </div>
                        <div class="form-group">
                            {{ Form::label('Selecione um Curso') }}
                            {{ Form::select('fk_curso[1]', ['' => 'Selecionar'] + $lista_cursos, null, ['class' => 'form-control']) }}
                        </div>
                        <hr/>
                    </div>
                </div>

                <div id="default_cursos" style="display: none;" data-id="__X__">
                    <div class="cursos" data-contador="__X__">
                        <div class="form-group">
                            <a href="javascript:;" id="btn_remover_curso-__X__" class="margin-bottom-10 btn btn-success right btn_remover_curso"><i class="fa fa-minus"></i></a>
                        </div>
                        <div class="form-group">
                            {{ Form::label('Selecione um Curso') }}
                            {{ Form::select('fk_curso[__X__]', ['' => 'Selecionar'] + $lista_cursos, null, ['class' => 'form-control']) }}
                        </div>
                        <hr/>
                    </div>
                </div>

                @if(count($cupom_cursos) > 0)
                    <table cellpadding="0" cellspacing="0" border="0" id="table-cursos" class="table table-bordered table-striped display">
                        <thead>
                        <th>Curso</th>
                        <th>Cupom</th>
                        <th>Projeto</th>
                        <th>Status</th>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Data Inicial</th>
                        <th>Data Final</th>
                        <th>Ações</th>
                        </thead>
                        <tbody>
                        @foreach($cupom_cursos as $item)
                            <tr>
                                <td>{{ $item->nome_curso }}</td>
                                <td>{{ $item->titulo }}</td>
                                <td>{{ $lista_faculdades[$item->fk_faculdade]['descricao'] }}</td>
                                <td>{{ ($item->status == 1 ? "Ativo" : "Inativo") }}</td>
                                <td>{{ $item->codigo_cupom }}</td>
                                <td>{{ $tipo_cupom[$item->tipo_cupom_desconto] }}</td>
                                <td>{{ ($item->tipo_cupom_desconto == 2) ? 'R$ '. number_format( $item->valor , 2, ',', '.') : (int)$item->valor}}</td>
                                <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_inicial))) }}</td>
                                <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_final))) }}</td>
                                <td nowrap>
                                    {{ Form::open(['method' => 'POST', 'url' => '/admin/cupom/deletarrelacionamentos', 'style' => 'display:inline;']) }}
                                        <input type="hidden" name="id" value="{{$item->id}}">
                                        <input type="hidden" name="tipo" value="2">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir?')">Excluir</button>
                                    {{ Form::close() }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info">Nenhum registro no banco!</div>
                @endif
            </div>
            <div id="categoriasacursos" class="tab-pane fade">
                <div id="bloco_categorias">
                    <div class="categorias" data-contador="1">
                        <br>
                        <br>
                        <div class="form-group">
                            <a href="javascript:;" id="btn_incluir_categoria-1" class="btn btn-success right btn_incluir_categoria"><i class="fa fa-plus"></i></a>
                        </div>
                        <div class="form-group">
                            {{ Form::label('Categorias') }}
                            {{ Form::select('fk_categoria[1]', ['' => 'Selecionar'] + $categorias, null, ['class' => 'form-control']) }}
                        </div>
                        <hr/>
                    </div>
                </div>

                <div id="default_categorias" style="display: none;" data-id="__X__">
                    <div class="categorias" data-contador="__X__">
                        <div class="form-group">
                            <a href="javascript:;" id="btn_remover_categoria-__X__" class="margin-bottom-10 btn btn-success right btn_remover_categoria"><i class="fa fa-minus"></i></a>
                        </div>
                        <div class="form-group">
                            {{ Form::label('Categorias') }}
                            {{ Form::select('fk_categoria[__X__]', ['' => 'Selecionar'] + $categorias, null, ['class' => 'form-control']) }}
                        </div>
                        <hr/>
                    </div>
                </div>

                @if(count($cupom_cursos_categorias) > 0)
                    <table cellpadding="0" cellspacing="0" border="0" id="table-categoriasacursos" class="table table-bordered table-striped display">
                        <thead>
                        <th>Categoria</th>
                        <th>Cupom</th>
                        <th>Status</th>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Data Inicial</th>
                        <th>Data Final</th>
                        <th>Ações</th>
                        </thead>
                        <tbody>
                        @foreach($cupom_cursos_categorias as $item)
                            <tr>
                                <td>{{ $item->nome_categoria }}</td>
                                <td>{{ $item->titulo }}</td>
                                <td>{{ ($item->status == 1 ? "Ativo" : "Inativo") }}</td>
                                <td>{{ $item->codigo_cupom }}</td>
                                <td>{{ $tipo_cupom[$item->tipo_cupom_desconto] }}</td>
                                <td>{{ ($item->tipo_cupom_desconto == 2) ? 'R$ '. number_format( $item->valor , 2, ',', '.') : (int)$item->valor}}</td>
                                <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_inicial))) }}</td>
                                <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_final))) }}</td>
                                <td nowrap>
                                    {{ Form::open(['method' => 'POST', 'url' => '/admin/cupom/deletarrelacionamentos', 'style' => 'display:inline;']) }}
                                        <input type="hidden" name="id" value="{{$item->id}}">
                                        <input type="hidden" name="tipo" value="3">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir?')">Excluir</button>
                                    {{ Form::close() }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info">Nenhum registro no banco!</div>
                @endif
            </div>
            <div id="trilhas" class="tab-pane fade">
                <div id="bloco_trilhas">
                    <div class="trilhas" data-contador="1">
                        <br>
                        <br>
                        <div class="form-group">
                            <a href="javascript:;" id="btn_incluir_trilha-1" class="btn btn-success right btn_incluir_trilha" title="Adicionar Trilha"><i class="fa fa-plus"></i></a>
                        </div>
                        <div class="form-group">
                            {{ Form::label('Selecione uma Trilha') }}
                            {{ Form::select('fk_trilha[1]', ['' => 'Selecionar'] + $lista_trilhas, null, ['class' => 'form-control']) }}
                        </div>
                        <hr/>
                    </div>
                </div>

                <div id="default_trilhas" style="display: none;" data-id="__X__">
                    <div class="trilhas" data-contador="__X__">
                        <div class="form-group">
                            <a href="javascript:;" id="btn_remover_trilha-__X__" class="margin-bottom-10 btn btn-success right btn_remover_trilha"><i class="fa fa-minus"></i></a>
                        </div>
                        <div class="form-group">
                            {{ Form::label('Selecione uma Trilha') }}
                            {{ Form::select('fk_trilha[__X__]', ['' => 'Selecionar'] + $lista_trilhas, null, ['class' => 'form-control']) }}
                        </div>
                        <hr/>
                    </div>
                </div>

                @if(count($cupom_trilhas) > 0)
                    <table cellpadding="0" cellspacing="0" border="0" id="table-trilhas" class="table table-bordered table-striped display">
                        <thead>
                            <th>Trilha</th>
                            <th>Cupom</th>
                            <th>Projeto</th>
                            <th>Status</th>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Data Inicial</th>
                            <th>Data Final</th>
                            <th>Ações</th>
                        </thead>
                        <tbody>
                        @foreach($cupom_trilhas as $item)
                            <tr>
                                <td>{{ $item->nome_trilha }}</td>
                                <td>{{ $item->titulo }}</td>
                                <td>{{ $lista_faculdades[$item->fk_faculdade]['descricao'] }}</td>
                                <td>{{ ($item->status == 1 ? "Ativo" : "Inativo") }}</td>
                                <td>{{ $item->codigo_cupom }}</td>
                                <td>{{ $tipo_cupom[$item->tipo_cupom_desconto] }}</td>
                                <td>
                                    {{ ($item->tipo_cupom_desconto == 2) ? 'R$ '. number_format( $item->valor , 2, ',', '.') : (int)$item->valor}}
                                </td>
                                <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_inicial))) }}</td>
                                <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_final))) }}</td>
                                <td nowrap>
                                    {{ Form::open(['method' => 'POST', 'url' => '/admin/cupom/deletarrelacionamentos', 'style' => 'display:inline;']) }}
                                        <input type="hidden" name="id" value="{{$item->id}}">
                                        <input type="hidden" name="tipo" value="4">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir?')">Excluir</button>
                                    {{ Form::close() }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info">Nenhum registro no banco!</div>
                @endif
            </div> 
            <!-- FIM quiz --> 
            <div id="input-hiddens">

            </div>
        </div>

        <hr/>

        <div class="form-group">
            {{ Form::label('Projetos') }}
            <br />
            <div class="row">
                <div class="col-md-12">
                    <input name="selecionar_todas" type="checkbox" id="selecionar_todas" value="" onclick="marcarTodas();"> Marcar Todas <br />
                </div>
                @foreach($lista_faculdades as $key => $item)
                    <div class="col-md-12">
                        {{ Form::checkbox('fk_faculdade[' . $key . ']', (isset($item['id']) ? $item['id'] : ''), (isset($item['ativo']) && ($item['ativo'] == '1')) ? true : false, ['class' => 'marcar', 'id' => $key])}}
                        {{ $item['descricao'] }}
                    </div>
                    <br />
                @endforeach
                <hr />
            </div>
        </div>
        <input type="hidden" name="fk_cupom" value="{{$cupom->id}}">

        <div class="form-group">
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>
@endsection

@push('js')
    <script type="text/javascript">
        $(document).ready(function () {
            let tabs = [];
            $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
                let target = $(e.target).attr("href") // activated tab
                let size = tabs.length;

                let tabela = $('.dataTable').DataTable();
                tabela.destroy()
                let id = target.split('#');
                $( ".display" ).removeClass( "dataTable" );

                $( "#table-" + id[1]).addClass( "dataTable" );

                if (!tabs.includes(id[1])) {
                    tabs.push(id[1])
                }

                if (size < tabs.length && id[1] != 'alunos') {
                    $('.dataTable thead tr').clone(true).appendTo('.dataTable thead');
                    $('.dataTable thead tr:eq(1) th').each(function () {
                        var title = $(this).text();
                        $(this).html('<input type="text" class="form-control" placeholder="' + title + '" />');
                    });

                    var table = $('.dataTable').DataTable({
                        "order": [[0, "desc"]], "language": {
                            "sEmptyTable": "Nenhum registro encontrado",
                            "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                            "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                            "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                            "sInfoPostFix": "",
                            "sInfoThousands": ".",
                            "sLengthMenu": "_MENU_ resultados por página",
                            "sLoadingRecords": "Carregando...",
                            "sProcessing": "Processando...",
                            "sZeroRecords": "Nenhum registro encontrado",
                            "sSearch": "Pesquisar por:",
                            "oPaginate": {
                                "sNext": "Próximo",
                                "sPrevious": "Anterior",
                                "sFirst": "Primeiro",
                                "sLast": "Último"
                            },
                            "oAria": {
                                "sSortAscending": ": Ordenar colunas de forma ascendente",
                                "sSortDescending": ": Ordenar colunas de forma descendente"
                            },
                            "select": {
                                "rows": {
                                    "_": "Selecionado %d linhas",
                                    "0": "Nenhuma linha selecionada",
                                    "1": "Selecionado 1 linha"
                                }
                            }
                        },
                        "orderCellsTop": true,
                        "responsive": true,
                        "fixedHeader": true,
                    });

                    table.columns().eq(0).each(function (colIdx) {
                        $('input', $('.dataTable thead tr:eq(1) th')[colIdx]).on('keyup change', function () {
                            table
                                .column(colIdx)
                                .search(this.value)
                                .draw();
                        });
                    });
                }
            });

            $('.btn_incluir_aluno').click(function () {
                let cont = $('.alunos').length;

                console.log(cont)
                let html = $('#default_alunos').html();

                let regex = new RegExp('__X__', 'g');
                html = html.replace(regex, cont);


                $('#bloco_alunos').append(html);
            });

            $('body').on('click', '.btn_remover_aluno', function() {
                let cont = $(this).attr('id');

                cont = cont.split('-')
                cont = parseInt(cont[1])
                console.log(cont)
                $('[data-contador='+cont+']').remove();
            });

            $('.btn_incluir_curso').click(function () {
                let cont = $('.cursos').length;

                console.log(cont)
                let html = $('#default_cursos').html();

                let regex = new RegExp('__X__', 'g');
                html = html.replace(regex, cont);


                $('#bloco_cursos').append(html);
            });

            $('body').on('click', '.btn_remover_curso', function() {
                let cont = $(this).attr('id');

                cont = cont.split('-')
                cont = parseInt(cont[1])
                console.log(cont)
                $('[data-contador='+cont+']').remove();
            });

            $('.btn_incluir_categoria').click(function () {
                let cont = $('.categorias').length;

                console.log(cont)
                let html = $('#default_categorias').html();

                let regex = new RegExp('__X__', 'g');
                html = html.replace(regex, cont);


                $('#bloco_categorias').append(html);
            });

            $('body').on('click', '.btn_remover_categoria', function() {
                let cont = $(this).attr('id');

                cont = cont.split('-')
                cont = parseInt(cont[1])
                console.log(cont)
                $('[data-contador='+cont+']').remove();
            });

            $('.btn_incluir_trilha').click(function () {
                let cont = $('.trilhas').length;

                console.log(cont)
                let html = $('#default_trilhas').html();

                let regex = new RegExp('__X__', 'g');
                html = html.replace(regex, cont);


                $('#bloco_trilhas').append(html);
            });

            $('body').on('click', '.btn_remover_trilha', function() {
                let cont = $(this).attr('id');

                cont = cont.split('-')
                cont = parseInt(cont[1])
                console.log(cont)
                $('[data-contador='+cont+']').remove();
            });
        })
    </script>
@endpush

