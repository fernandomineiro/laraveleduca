@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9">
            <h2 class="table">Módulos de Assinatura <small>(Tipo de assinatura: {{$titulo}})</small></h2>
        </div>
		<div class="col-md-3" style="margin-top: 20px;">
			<a href="/admin/assinatura/{{ $tipo_assinatura }}/incluir" class="btn btn-success right margin-bottom-10">Adicionar</a>
		</div>
		<hr class="clear hr" />

		@if(count($assinaturas) > 0)
            <table class="table table-bordered table-striped dataTable">
				<thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Periodo</th>
                        <th>Valor de Venda</th>
                        <th>Valor</th>
                        <th>Ações</th>
                    </tr>
                </thead>
				<tbody>
					@foreach($assinaturas as $assinatura)
					<tr>
						<td>{{ $assinatura->id }}</td>
						<td>{{ $assinatura->titulo }}</td>
						<td>{{ $assinatura->tipo }}</td>
						<td>{{ $assinatura->tipo_periodo ? $lista_periodos[$assinatura->tipo_periodo] : '-' }}</td>
						<td>R$ {{ $assinatura->valor_de ? number_format( $assinatura->valor_de , 2, ',', '.') : '-' }}</td>
						<td>R$ {{ $assinatura->valor ? number_format( $assinatura->valor , 2, ',', '.') : '-'}}</td>
						<td>
							<a href="/admin/assinatura/{{ $assinatura->id }}/editar" class="btn btn-default btn-sm">Editar</a>
							{{ Form::open(['method' => 'DELETE', 'route' => ['admin.assinatura.deletar', $assinatura->id], 'class' => 'form-delete', 'style' => 'display:inline;']) }}
								<button type="submit" class="btn btn-danger btn-sm deletar-item" onclick="return confirm('Deseja realmente excluir?')">Excluir</button>
							{{ Form::close() }}
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		@else
            <hr class="clear hr" />
			<div class="row">
                <div class="alert alert-info">Nenhum registro no banco!</div>
            </div>
		@endif

	</div>
@endsection
@push('js')
    <script type="text/javascript">
    // Documentação do pacote: http://craftpip.github.io/jquery-confirm, consulte em caso de dúvidas
    jQuery('.deletar-item').click(function(e){
        e.preventDefault();

        $.confirm({
            icon: 'fa fa-warning',
            title: 'Deletar Assinatura',
            content: 'Confirma a exclusão desta assinatura?',
            backgroundDismiss: true,
            closeIcon: true,
            boxWidth: '30%',
            useBootstrap: true,
            // cancelButton: 'Não, nunca!',
            type: 'red',
            typeAnimated: true,
            buttons: {
                remover: {
                    text: 'Deletar',
                    btnClass: 'btn-red',
                    action: function(){
                        $(e.target).closest('.form-delete').submit();
                    }
                },
                cancelar:{
                    text: 'Cancelar',
                    action: function(){
                        // return false;
                    }
                }
            }
        });
    });
</script>
@endpush
