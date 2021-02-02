@extends('layouts.app')

@section('content')
<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Graduações:</h2></div>
		<div class="col-md-3" style="margin-top: 20px;">
			<a href="{{ route('graduation.create') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
		</div>
		<hr class="clear hr" />

@endsection
