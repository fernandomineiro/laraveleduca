@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9"><h2 class="table">Lista de Pedidos</h2></div>
        <hr class="clear hr"/>
        
        <table class="table table-bordered table-striped dataTable" style="font-size: 12px; width:100%"  id="tabela-pedidos">
            <thead>
            <tr>
                <th>Data Venda</th>
                <th>Id Pedido</th>
                <th>IES</th>
                <th>NFE</th>
                <th>Formato</th>
                <th>Curso</th>
                <th>Status</th>
                <th>Assinante</th>
                <th>Email</th>
                <th>CPF</th>
                <th>Valor</th>
                <th>Ações</th>
            </tr>
            </thead>
        </table>
        
    </div>
@endsection

@push('js')
    <script>
        $('#tabela-pedidos').DataTable().destroy();

        $(document).ready(function() {
            let criacao, valor = null;

            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            $('#tabela-pedidos').DataTable( {
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
                    url: '/admin/pedido/getMultiFilterSelectDataPedido',
                    method: 'POST'
                },
                columns: [
                    { data: 'criacao', name: 'criacao', orderable: true, searchable: true, render:function(data, type, row){
                            criacao = `${moment(row.criacao).format('DD/MM/YYYY H:m:ss')}`;
                            return criacao;
                        }},

                    {data: 'pid', name: 'pid'},
                    {data: 'faculdade', name: 'faculdades.fantasia', searchable: true},

                    { data: 'nfe', name: 'nfe', orderable: false, searchable: false, render:function(data, type, row){
                            return 'Nota Fiscal';
                        }},

                    {data: 'formato', name: 'formato', orderable: false, searchable: false},

                    { data: 'lista_cursos', name: 'lista_cursos', orderable: false, searchable: false, render:function(data, type, row){
                            if(data) {
                                return data.split(" __ ").join('<br/><i class="fa fa-angle-double-right mr-2"></i>');
                            } else {
                                return '';
                            }
                        }},

                    {data: 'status_titulo', name: 'pedidos_status.titulo', searchable: true},
                    {data: 'usuario', name: 'usuarios.nome', searchable: true},
                    {data: 'email', name: 'usuarios.email', searchable: true},
                    {data: 'cpf', name: 'alunos.cpf', searchable: true},

                    {data: 'valor_liquido', name: 'valor_liquido', orderable: true, searchable: true, render:function(data, type, row){
                            valor = (parseFloat(row.valor_bruto) - parseFloat(row.valor_desconto) - parseFloat(row.valor_imposto)).toFixed(2)
                            return valor;
                        }},

                    {data: 'actions', name: 'actions', orderable: false, searchable: false, render:function(data, type, row){
                            actions = `<a href="/admin/pedido/${row.id}/editar" class="btn btn-primary" role="button"><i class="fa fa-edit"></i></a>`;
                            return actions;
                        }},
                ]
            });
        });
    </script>
@endpush
