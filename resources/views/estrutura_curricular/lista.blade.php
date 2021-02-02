@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Estrutura Curricular do Curso</h2></div>
		<div class="col-md-3" style="margin-top: 20px;">
			<a href="/admin/estrutura-curricular/incluir" class="btn btn-success right margin-bottom-10">Adicionar</a>
		</div>
		<hr class="clear hr" />

		@if(count($estruturas_curriculares) > 0)
            <table class="table table-bordered table-striped dataTable">
				<thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Ações</th>
                    </tr>
                </thead>
				<tbody>
					@foreach($estruturas_curriculares as $estrutura)
					<tr>
						<td>{{ $estrutura->id }}</td>
						<td>{{ $estrutura->titulo }}</td>
						<td>
							<a href="/admin/estrutura-curricular/{{ $estrutura->id }}/editar" class="btn btn-default btn-sm">Editar</a>
							{{ Form::open(['method' => 'DELETE', 'route' => ['admin.estrutura_curricular.deletar', $estrutura->id], 'class' => 'form-delete', 'style' => 'display:inline;']) }}
								<button type="submit" class="btn btn-danger btn-sm deletar-item">Excluir</button>
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
    <script src="/js/jquery-confirm.min.js"></script>
    <script type="text/javascript">
        // Documentação do pacote: http://craftpip.github.io/$-confirm, consulte em caso de dúvidas
        $('.deletar-item').click(function(e){
            e.preventDefault();

            $.confirm({
                icon: 'fa fa-warning',
                title: 'Deletar Estrutura curricular',
                content: 'Confirma a exclusão desta estrutura curricular?',
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
