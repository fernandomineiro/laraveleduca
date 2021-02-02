@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Config. Wirecard</span></h2>
	    <a href="{{ route('admin.tipo_pagamento') }}" class="label label-default">Voltar</a>
		<hr class="hr" />
		
		@if(Request::is('*/editar'))
			{{ Form::model( $tipo_pagamento, ['method' => 'PATCH', 'route' => ['admin.tipo_pagamento.atualizar', $tipo_pagamento->id]] ) }}
		@else
			{{ Form::open(['url' => '/admin/tipo_pagamento/salvar']) }}
		@endif
			<div class="form-group">
				{{ Form::label('Status') }}
				{{ Form::select('status', $lista_status, (isset($tipo_pagamento->status) ? $tipo_pagamento->status : 1), ['class' => 'form-control']) }}
			</div>
			
			<div class="form-group">						
				{{ Form::label('Título') }}
				{{ Form::input('text', 'titulo', null, ['class' => 'form-control', '', 'placeholder' => 'Título']) }}
			</div>
            <div class="form-group">
				{{ Form::label('Ambiente') }}
				{{ Form::select('ambiente', array('teste' => 'Testes', 'producao' => 'Produção'), $tipo_pagamento->ambiente, ['class' => 'form-control']) }}
			</div>
            <hr class="hr" />
            <div class="form-group">						
				{{ Form::label('Chave de teste') }}
				{{ Form::input('text', 'key_teste', $tipo_pagamento->key_teste, ['class' => 'form-control', '', 'placeholder' => 'Chave de teste']) }}
			</div>
            <div class="form-group">						
				{{ Form::label('Token de teste') }}
				{{ Form::input('text', 'token_teste', $tipo_pagamento->token_teste, ['class' => 'form-control', '', 'placeholder' => 'Token de teste']) }}
			</div>
            <div class="form-group">						
				{{ Form::label('APP - Access Token (TESTE)') }}
				{{ Form::input('text', 'app_teste', $tipo_pagamento->app_teste, ['class' => 'form-control', '', 'placeholder' => 'Access Token']) }}
			</div>
            <hr class="hr" />
            <div class="form-group">						
				{{ Form::label('Chave de produção') }}
				{{ Form::input('text', 'key_producao', $tipo_pagamento->key_producao, ['class' => 'form-control', '', 'placeholder' => 'Chave de produção']) }}
			</div>
            <div class="form-group">						
				{{ Form::label('Token de producao') }}
				{{ Form::input('text', 'token_producao', $tipo_pagamento->token_producao, ['class' => 'form-control', '', 'placeholder' => 'Token de producao']) }}
			</div>
            <div class="form-group">			
				{{ Form::label('APP - Access Token (TESTE)') }}
				{{ Form::input('text', 'app_producao', $tipo_pagamento->app_producao, ['class' => 'form-control', '', 'placeholder' => 'Access Token']) }}
			</div>
			<hr class="hr" />
			<div class="form-group">			
				{{ Form::label('Webhook - URL para retorno') }}
				{{ Form::input('text', 'url_retorno', $tipo_pagamento->url_retorno, ['class' => 'form-control', 'id' => 'url-retorno', 'placeholder' => 'Webhook - URL para retorno']) }}
			</div>
			<div class="form-group">						
				{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
			</div>
		{{ Form::close() }}
	</div>
@endsection