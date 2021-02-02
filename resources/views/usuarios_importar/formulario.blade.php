@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Adicionar Usuários em Massa</span></h2>
	    <a href="{{ url('admin/download/modelo_importar_usuarios.xlsx') }}" class="label label-primary">Download do modelo de Planilha</a>
	    <a href="{{ route('admin.usuario') }}" class="label label-default">Voltar</a>
		<hr class="hr" />
		
		{{ Form::open(['method' => 'POST','url' => '/admin/usuarios_importar/salvar','files' => true]) }}
			<div class="form-group">
				{{ Form::label('Arquivo') }}
				{{ Form::file('arquivo_excel',['accept'=>'.xlsx , .xls']) }}
			</div>
			<div class="form-group">						
				{{ Form::submit('Enviar', ['class' => 'btn btn-primary']) }}
			</div>
		{{ Form::close() }}
	</div>

@endsection