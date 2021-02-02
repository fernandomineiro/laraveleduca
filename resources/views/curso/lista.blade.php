@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Lista de Cursos <?php echo $lista_tipos[$tipo]; ?></h2></div>
        <form method="POST" action="{{ url('/admin/'.$modulo['moduloDetalhes']->rota.'/exportar') }}" id="form-export-to" class="pull-right" style="float: right;">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="export-to-type" id="export-to-type">
        </form>
        <div class="btn-toolbar pull-right" role="toolbar">
            <div class="btn-group mr-2" role="group">
			    <a href="/admin/curso/{{ $tipo }}/incluir" class="btn btn-success right margin-bottom-10">Adicionar</a>
            </div>
            <div class="btn-group mr-2" role="group">
                <button class="btn btn-danger dropdown-toggle" type="button" data-toggle="dropdown"> Exportar para
                    <i class="fa fa-angle-down"></i>
                </button>
                <ul class="dropdown-menu" id="dropdown-menu-export-to" role="menu">
                    <li><a href="javascript:void(0)">XLS</a></li>
                    <li><a href="javascript:void(0)">XLSX</a></li>
                    <!--<li><a href="javascript:void(0)">CSV</a></li>-->
                </ul>
            </div>
            <script type="text/javascript">
                $('#dropdown-menu-export-to li a').click(function (e) {
                    e.preventDefault();
                    var $valor = $(this).text();
                    $('#export-to-type').val($valor);
                    $("form-export-to").append($('#form-filtro').html())
                    setTimeout(() => {
                        $('#form-export-to').submit();
                    }, 300)
                });
            </script>
		</div>
		<hr class="clear hr" />

		@if(count($cursos) > 0)
			<table class="table table-bordered table-striped dataTable">
				<thead>
					<th>ID</th>
					<th>Nome do Curso</th>
					<th>Status</th>
					<th>Preço</th>
					<th>Preço com desconto</th>
					<th>Professor</th>
					<th>Curador</th>
					<th>Produtora</th>
					<th>Instituição</th>
					<th>Categorias</th>
					<th>Ações</th>
				</thead>
				<tbody>
					@foreach($cursos as $curso)
					<tr>
						<td>{{ $curso->id }}</td>
						<td>{{ $curso->titulo }}</td>
						<td><?php echo isset($lista_status[$curso->status]) ?  $lista_status[$curso->status] : '-'; ?></td>
						<td>R$ {{ number_format( $curso->valor_de , 2, ',', '.') }}</td>
						<td>R$ {{ number_format( $curso->valor , 2, ',', '.') }}</td>
						<td><?php echo isset($lista_professor[$curso->fk_professor]) ?  $lista_professor[$curso->fk_professor] : '-'; ?></td>
						<td><?php echo isset($lista_curador[$curso->fk_curador]) ?  $lista_curador[$curso->fk_curador] : '-'; ?></td>
						<td><?php echo isset($lista_produtora[$curso->fk_produtora]) ?  $lista_produtora[$curso->fk_produtora] : '-'; ?></td>
						<td><?php echo isset($lista_faculdades[$curso->fk_faculdade]) ?  $lista_faculdades[$curso->fk_faculdade] : '-'; ?></td>
						@foreach($lista_categorias as $lista)	
							@if($lista->id == $curso->id)
							@php $categ = explode(', ', $lista->categorias); @endphp
								@if(count($categ) > 1)
									@php $categories = "<a href='javascript:void(0);' title='".$lista->categorias."'>".$categ[0]." ...</a>"; @endphp
								@else 
									@php $categories = $lista->categorias; @endphp
								@endif
								<td>@php echo $categories @endphp</td>
							@endif
						@endforeach	
						<td>
							<a href="/admin/curso/{{ $curso->id }}/editar" class="btn btn-default btn-sm"><i class="fa fa-fw fa-edit"></i></a>
							{{ Form::open(['method' => 'DELETE', 'route' => ['admin.curso.deletar', $curso->id], 'style' => 'display:inline;']) }}
								<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-fw fa-trash"></i></button>
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

@endsection
