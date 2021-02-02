@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9"><h2 class="table">{{$modulo['moduloDetalhes']->modulo}}</h2></div>
        <div class="col-md-3" style="margin-top: 20px;">
            <a href="{{ route('admin.'.$modulo['moduloDetalhes']->rota.'.incluir') }}"
               class="btn btn-success right margin-bottom-10">Adicionar</a>
        </div>
        <hr class="clear hr"/>

        @if(count($objlista) > 0)
            <table class="table table-bordered table-striped dataTable">
                <thead>
                    <tr>
                        <th>Projeto</th>
                        <th>Título</th>
                        <th>Descrição</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($objlista as $obj)
                    <tr>
                        <td>{{ $obj->razao_social }}</td>
                        <td>{{ $obj->slug }}</td>
                        <td>{{ $obj->descricao }}</td>
                        <td>
                        	@include('table.editarexcluir')
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <hr class="clear hr"/>
            <div class="row">
                <div class="alert alert-info">Nenhum registro no banco!</div>
            </div>
        @endif

    </div>
@endsection
