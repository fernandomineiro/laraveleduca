@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">GESTOR DE CONTEÚDOS EDUCAZ</h2></div>
		<hr class="clear hr" />

		<div class="col-md-3">
		</div>
		<div class="col-md-6">
			<a href="/admin/curso/1/lista">
				<div style="width: 300px; height: 60px; border: 2px solid #CCC; background: #EFEFEF; text-align: center;">
					<br />CONTEÚDO ONLINE
				</div>
			</a>
			<br />
			<br />

			<a href="/admin/curso/2/lista">
				<div style="width: 300px; height: 60px; border: 2px solid #CCC; background: #EFEFEF; text-align: center;">
					<br />CONTEÚDO PRESENCIAL
				</div>
			</a>
			<br />
			<br />

			<a href="/admin/curso/4/lista">
				<div style="width: 300px; height: 60px; border: 2px solid #CCC; background: #EFEFEF; text-align: center;">
					<br />CONTEÚDO REMOTO
				</div>
			</a>
			<br />
			<br />

			<a href="/admin/eventos/index">
				<div style="width: 300px; height: 60px; border: 2px solid #CCC; background: #EFEFEF; text-align: center;">
					<br />EVENTOS
				</div>
			</a>
			<br />
			<br />

			<a href="/admin/curso/5/lista">
				<div style="width: 300px; height: 60px; border: 2px solid #CCC; background: #EFEFEF; text-align: center;">
					<br />CONTEÚDO MENTORIA
				</div>
			</a>
			<br />
			<br />

		</div>
		<div class="col-md-3">
		</div>

		<hr class="clear hr" />
	</div>
@endsection
