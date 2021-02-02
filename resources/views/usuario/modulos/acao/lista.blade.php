@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9"><h2
                class="table">{{ $modulo['moduloDetalhes']->modulo }}</h2></div>
        <div class="col-md-3" style="margin-top: 20px;">
            <a href="{{ route('admin.'.$modulo['moduloDetalhes']->rota.'.incluir') }}"
               class="btn btn-success right margin-bottom-10">Adicionar</a>
        </div>
        <hr class="clear hr"/>

        @if(count($lstObj) > 0)
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Lista de registros encontrados</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered table-striped dataTable">
                        <thead>
                        <tr>
                            <th>Ação</th>
                            <th>Elemento</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($lstObj as $obj)
                            <tr>
                                <td>{{ $obj->descricao }}</td>
                                <td>{{ $obj->elemento }}</td>
                                <td>{{ $lista_status[$obj->status] }}</td>
                                <td style="text-align: center">
                                    @include('table.editarexcluir')
                                </td>
                            </tr>
                        @endforeach
                        </tbody>

                    </table>
                </div>
            </div>
        @else
            <div class="alert alert-info">Nenhum registro no banco!</div>
        @endif

    </div>
@endsection
