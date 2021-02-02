@extends('layouts.app')

<head>
    <meta charset="utf-8">
    ...
    {{-- ChartScript --}}
    @if($usersChart)
    {!! $usersChart->script() !!}
    @endif
</head>

@section('content')
<h1>Gráficos de Usuários</h1>

<div style="width: 50%">
    {!! $usersChart->container() !!}
</div>
@endsection

