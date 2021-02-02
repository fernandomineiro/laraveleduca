@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Tipo de Pagamento</span></h2>
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
				{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
			</div>
		{{ Form::close() }}
	</div>
@endsection