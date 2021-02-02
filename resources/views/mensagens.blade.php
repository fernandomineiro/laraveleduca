
    @section('mensagens')

	    @if (count($errors) > 0)
	        <div class="alert alert-danger">
	            <ul>
	                @foreach ($errors->all() as $error)
	                    <li>{{ $error }}</li>
	                @endforeach
	            </ul>
	        </div>
	    @endif

		@if(Session::has('mensagem_erro'))
			<div class="alert alert-danger">{{Session::get('mensagem_erro')}}</div>
		@endif

		@if(Session::has('mensagem_sucesso'))
			<div class="alert alert-success">{{Session::get('mensagem_sucesso')}}</div>
		@endif

		@if(Session::has('custom_message'))
			<div class="alert alert-success">{!! Session::get('custom_message') !!}</div>
		@endif

