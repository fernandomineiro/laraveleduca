@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-4"><h2 class="table">Lista de Cupons</h2></div>
		<div class="col-md-8" style="margin-top: 20px;">
    		<div class="btn-toolbar pull-right" role="toolbar">
              <div class="btn-group mr-2" role="group">
                <a href="{{ route('admin.cupom.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
              </div>
              <div class="btn-group mr-2" role="group">
                <a href="{{ route('admin.cupom_importar.incluir') }}" class="btn btn-success right margin-bottom-10"
                   title="Utilize essa funcionalidade para importar cupons prontos para dentro do sistema">
                    Importar Cupons em Massa
                </a>
              </div>
              <div class="btn-group mr-2" role="group">
                <a href="{{ route('admin.cupom_random.incluir') }}" class="btn btn-success right margin-bottom-10"
                title="Utilize essa funcionalidade para que o sistema gere cupons automaticamente para você">
                    Gerar Cupons Randômicos em Massa
                </a>
              </div>
            </div>
		</div>
		<hr class="clear hr" />

        <table class="table table-bordered table-striped dataTable" style="font-size: 12px; width:100%"  id="tabela-cupons">
            <thead>
            <tr>
                <th>ID</th>
                <th>Cupom</th>
                <th>Faculdade</th>
                <th>Status</th>
                <th>Código</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Data Inicial</th>
                <th>Data Final</th>
                <th>Data Criação</th>
                <th>Ações</th>
            </tr>
            </thead>
        </table>

    </div>
@endsection

@push('js')
    <script>
        $('#tabela-cupons').DataTable().destroy();

        $(document).ready(function() {
            let dataInicial, dataFinal, criacao, valor = null;

            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            $('#tabela-cupons thead tr').clone(true).appendTo( '#tabela-cupons thead' );
            $('#tabela-cupons thead tr:eq(1) th').each( function (i) {
                var title = $(this).text();
                $(this).html( '<input type="text" placeholder="'+title+'" />' );

                $( 'input', this ).on( 'keyup change', function () {
                    if ( table.column(i).search() !== this.value ) {
                        table
                            .column(i)
                            .search( this.value )
                            .draw();
                    }
                } );
            } );

            var table = $('#tabela-cupons').DataTable( {
                "scrollX": true,
                language: {
                    sEmptyTable: "Nenhum registro encontrado",
                    sInfo: "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                    sInfoEmpty: "Mostrando 0 até 0 de 0 registros",
                    sInfoFiltered: "(Filtrados de _MAX_ registros)",
                    sInfoPostFix: "",
                    sInfoThousands: ".",
                    sLengthMenu: "_MENU_ resultados por página",
                    sLoadingRecords: "Carregando...",
                    sProcessing: "Processando...",
                    sZeroRecords: "Nenhum registro encontrado",
                    sSearch: "Pesquisar por:",
                    oPaginate: {
                        sNext: "Próximo",
                        sPrevious: "Anterior",
                        sFirst: "Primeiro",
                        sLast: "Último"
                    },
                    oAria: {
                        sSortAscending: "Ordenar colunas de forma ascendente",
                        sSortDescending: "Ordenar colunas de forma descendente"
                    },
                    select: {
                        rows: {
                            "_": "Selecionado %d linhas",
                            "0": "Nenhuma linha selecionada",
                            "1": "Selecionado 1 linha"
                        }
                    }
                },
                orderCellsTop: true,
                responsive: true,
                fixedHeader: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/admin/cupom/getMultiFilterSelectDataCupom',
                    method: 'POST'
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'titulo', name: 'titulo'},
                    {data: 'fk_faculdade', name: 'fk_faculdade'},
                    {data: 'status', name: 'status'},
                    {data: 'codigo_cupom', name: 'codigo_cupom'},
                    {data: 'tipo_cupom_desconto', name: 'titulo'},
                    {data: 'valor', name: 'valor'},
                    { data: 'data_validade_inicial', name: 'data_validade_inicial', orderable: true, searchable: true, render:function(data, type, row){
                            dataInicial = `${moment(row.data_validade_inicial).format('DD/MM/YYYY')}`;
                            return dataInicial;
                        }},
                    { data: 'data_validade_final', name: 'data_validade_final', orderable: true, searchable: true, render:function(data, type, row){
                            dataFinal = `${moment(row.data_validade_final).format('DD/MM/YYYY')}`;
                            return dataFinal;
                        }},
                    { data: 'criacao', name: 'criacao', orderable: true, searchable: true, render:function(data, type, row){
                            criacao = `${moment(row.criacao).format('DD/MM/YYYY H:m:ss')}`;
                            return criacao;
                        }},

                    {data: 'actions', name: 'actions', orderable: false, searchable: false, render:function(data, type, row){
                            actions = `<nobr><a href="/admin/cupom/${row.id}/editar" class="btn btn-default btn-sm">Editar</a>
                            <form method="POST" action="/admin/cupom/${row.id}" accept-charset="UTF-8" style="display:inline;">
                                <input name="_method" type="hidden" value="DELETE" autocomplete="Off">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir?')">Excluir</button>
                            </form>
                            </nobr>`;
                            return actions;
                        }},
                ],
                fnInitComplete: function(oSettings, json) {
                    $("#tabela-cupons tfoot th").each( function ( i ) {
                        var select = $('Filter on:')
                            .appendTo( this )
                            .on( 'change', function () {
                                table.column( i )
                                    .search( $(this).val() )
                                    .draw();
                            } );

                        table.column( i ).data().unique().sort().each( function ( d, j ) {
                            select.append( ''+d+'' )
                        } );
                    } );
                }
            });
        });
    </script>
@endpush
