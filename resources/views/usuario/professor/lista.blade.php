@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9">
        	<h2 class="table">{{ $modulo['moduloDetalhes']->modulo }}</h2>
        </div>
		<div class="col-md-3" style="margin-top: 20px;">
			<form method="POST" action="{{ url('/admin/'.$modulo['moduloDetalhes']->rota.'/exportar') }}" id="form-export-to" class="pull-right" style="float: right;">
                @csrf
				<input type="hidden" name="export-to-type" id="export-to-type">
			</form>
			<div class="btn-toolbar pull-right" role="toolbar">
				<div class="btn-group mr-2" role="group">
    				<a href="{{ route('admin.'.$modulo['moduloDetalhes']->rota.'.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
    			</div>
    			<div class="btn-group mr-2" role="group">
    				<button class="btn btn-danger dropdown-toggle" type="button" data-toggle="dropdown"> Exportar para
    					<i class="fa fa-angle-down"></i>
    				</button>
    				<ul class="dropdown-menu" id="dropdown-menu-export-to" role="menu">
    					<li><a href="javascript:void(0)">XLS</a></li>
    					<li><a href="javascript:void(0)">XLSX</a></li>
    					<li><a href="javascript:void(0)">CSV</a></li>
    				</ul>
    			</div>
            </div>
		</div>
        <hr class="clear hr"/>

        <table class="table table-bordered table-striped dataTable" id="table-professores">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Registrado em</th>
                    <th>CPF</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Professor Ativo?</th>
                    <th>Usuário Ativo?</th>
                    <th style="width: 160px;"></th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@push('js')
    <script type="text/javascript">
        $('#dropdown-menu-export-to li a').click(function (e) {
            e.preventDefault();
            var $valor = $(this).text();
            $('#export-to-type').val($valor);
            $('#form-export-to').submit();
        });

        $('#table-professores').DataTable().destroy();

        $(document).ready(function() {
            let actions = null;
            
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            $('#table-professores').DataTable({
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
                ajax: '/admin/professor/getMultiFilterSelectDataProfessor',
                columns: [
                    {data: 'professor_id', name: 'professor_id'},
                    {data: 'registro', name: 'registro'},

                    { data: 'nome', name: 'nome', render:function(data, type, row){
                            return "<a href='/admin/professor/"+ row.professor_id +"/editar'>" + row.nome + "</a>"
                        }},

                    {data: 'cpf', name: 'cpf'},
                    {data: 'email', name: 'email'},
                    {data: 'status', name: 'status'},
                    {data: 'usuario_ativo', name: 'usuario_ativo'},

                    { data: 'actions', name: 'actions', orderable: false, searchable: false, render:function(data, type, row){
                        actions = `<a href="/admin/professor/${row.professor_id}/editar" class="btn btn-default btn-sm"><i class="fa fa-fw fa-edit"></i></a>`;
                        actions += `<button role="button" type="submit" class="btn btn-danger btn-sm" onclick="deleteRegistroProfessor(${row.professor_id})"><i class="fa fa-fw fa-trash"></i></button>`;
                        
                        if (row.usuario_ativo != 'ATIVO') {
                            actions += `<a href="/admin/professor/${row.fk_usuario_id}/aprovar" class="btn btn-success btn-sm">Aprovar</a>`;
                        }
                        
                        actions +=`<a href="/admin/usuario/${row.fk_usuario_id}/recuperarcredenciais" class="btn btn-default btn-sm" title="Enviar credenciais"><i class="fa fa-fw fa-send"></i></a>`;
    
                        return actions;
                    }},
                ]
            });
        });

        function deleteRegistroProfessor(id) {
            event.preventDefault();

            let agree= confirm("Deseja realmente excluir esse registro?");
            if(!agree){
                return false
            }

            $.ajax({
                type: 'DELETE',
                url: '/admin/professor/' + id,
                success: function(data) {
                    if(data.code === 1) {
                        toastr.success(data.message);
                    } else if(data.code === 2) {
                        toastr.error(data.message);
                    }
                    setTimeout(function(){
                        $('#table-professores').DataTable().ajax.reload();
                    },1000);
                },
                error: function(data) {
                    console.log(data);
                }
            });
        }
    </script>
@endpush
