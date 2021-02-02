<a href="/admin/{{$modulo['moduloDetalhes']->uri}}/{{ $obj->id }}/editar" class="btn btn-default btn-sm" title="Editar">
	<i class="fa fa-fw fa-edit"></i>
</a>
{{ Form::open(['method' => 'DELETE', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.deletar', $obj->id], 'style' => 'display:inline;']) }}
	<button type="submit" class="btn btn-danger btn-sm" title="Excluir" onclick="return confirm('Deseja realmente excluir?')">
		<i class="fa fa-fw fa-trash"></i>
	</button>
{{ Form::close() }}