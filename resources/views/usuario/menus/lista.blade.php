@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9"><h2 class="table">{{ $modulo['moduloDetalhes']->modulo }}</h2></div>
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
                            <th>Descrição</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($lstObj as $obj)
                            <tr>
                                <td>{{ $obj->descricao }}</td>
                                <td>{{ $lista_status[$obj->status] }}</td>
                                <td style="text-align: center">
                                    <a href="/admin/{{$modulo['moduloDetalhes']->uri}}/{{ $obj->id }}/editar" class="btn btn-default btn-sm" title="Editar">
                                    	<i class="fa fa-fw fa-edit"></i>
                                    </a>
                                    {{ Form::open(['method' => 'DELETE', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.deletar', $obj->id], 'style' => 'display:inline;']) }}
                                    	<button type="submit" class="btn btn-danger btn-sm" title="Excluir" onclick="return confirm('Deseja realmente excluir?')">
                                    		<i class="fa fa-fw fa-trash"></i>
                                    	</button>
                                    {{ Form::close() }}
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
