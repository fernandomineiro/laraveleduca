@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Configurações Projeto</span></h2>
	    <a href="{{ route('admin.configuracao') }}" class="label label-default">Voltar</a>
		<hr class="hr" />

		@if(Request::is('*/editar'))
			{{ Form::model( $configuracao, ['method' => 'PATCH', 'files' => true, 'route' => ['admin.configuracao.atualizar', $configuracao->id]] ) }}
		@else
			{{ Form::open(['url' => '/admin/configuracao/salvar' , 'files' => true ]) }}
		@endif
			<div class="form-group row">
				<div class="col-sm">
					<div class="col-sm-8">
						{{ Form::label('Projeto') }}
						{{ Form::select('fk_faculdade', $lista_faculdades, (isset($configuracao->fk_faculdade) ? $configuracao->fk_faculdade : 0), ['class' => 'form-control']) }}
					</div>
					<div class="col-sm-8">
						{{ Form::label('Domínio') }}
						{{ Form::input('text', 'dominio', null, ['class' => 'form-control', '', 'placeholder' => 'Domínio']) }}
					</div>
				</div>
			</div>

			<div class="form-group row">
				<div class='col-sm'>
					<div class='col-sm-4'>
						{{Form::label('logo', 'Logo',['class' => 'control-label'])}}
						@if( Request::is('*/editar') && $configuracao->logo )
						<div class="form-group">
							<img src="{{URL::asset('files/configuracao/logo/' . $configuracao->logo)}}" height="100"/>
							<br />
							<a href="javascript:;" onclick="$('#box_upload').show();" class="label label-warning">Alterar Logo</a>
						</div>
						@endif
						<div id="box_upload" class="form-group" style="display: {{ isset($configuracao->logo) ? 'none' : 'block' }}">
							{{Form::file('logo')}}
						</div>
					</div>
					<div class='col-sm-4'>
						{{ Form::label('Banner') }}
						@if( Request::is('*/editar') && $configuracao->banner_home )
						<div class="form-group">
							<img src="{{URL::asset('files/configuracao/banner_home/' . $configuracao->banner_home)}}" height="100"/>
							<br />
							<a href="javascript:;" onclick="$('#box_upload2').show();" class="label label-warning">Alterar Banner</a>
						</div>
						@endif
						<div id="box_upload2" class="form-group" style="display: {{ isset($configuracao->banner_home) ? 'none' : 'block' }}">
							{{Form::file('banner_home')}}
						</div>
					</div>
				</div>
			</div>

			<div class="form-group row">
				<div class='col-sm'>
					<div class='col-sm-3'>
						{{ Form::label('Cor principal') }}
						{{ Form::input('color', 'cor_principal', (!empty($configuracao->cor_principal) ? $configuracao->cor_principal : '#ff0000'), ['class' => 'form-control', '', 'placeholder' => 'Cor Principal']) }}
					</div>
					<div class='col-sm-3'>
						{{ Form::label('Cor Secundária') }}
						{{ Form::input('color', 'cor_secundaria', (!empty($configuracao->cor_secundaria) ? $configuracao->cor_secundaria : '#ff0000'), ['class' => 'form-control', '', 'placeholder' => 'Cor secundário']) }}
					</div>
				</div>
			</div>

			<div class="form-group">
				{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
			</div>
		{{ Form::close() }}
	</div>
@endsection
