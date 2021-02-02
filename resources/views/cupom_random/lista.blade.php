@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9"><h2 class="table">Lista de Cupons Gerados Randomicamente</h2></div>
        <a href="{{ route('admin.cupom') }}" class="label label-default">Voltar</a>

        @if(count($lista_inseridos) > 0)
            <table cellpadding="0" cellspacing="0" border="0" class="table table-striped dataTable">
                <th>Cupom</th>
                <th>Código</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Data Inicial</th>
                <th>Data Final</th>
                <th>Ações</th>
                <tbody>
                @foreach($lista_inseridos as $item)
                    <tr>
                        <td>{{ $item->titulo }}</td>
                        <td>{{ ($item->status == 1 ? "Ativo" : "Inativo") }}</td>
                        <td>{{ $item->codigo_cupom }}</td>
                        <td>{{ $tipo_cupom[$item->tipo_cupom_desconto] }}</td>
                        <td>{{ $item->valor }}</td>
                        <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_inicial))) }}</td>
                        <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_final))) }}</td>
                        <td>{{ implode('/', array_reverse(explode('-', explode(" ",$item->criacao)[0]))) }}</td>
                        <td nowrap>
                            <a href="/admin/cupom/{{ $item->id }}/editar" class="btn btn-default btn-sm">Editar</a>
                            {{ Form::open(['method' => 'DELETE', 'route' => ['admin.cupom.deletar', $item->id], 'style' => 'display:inline;']) }}
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir?')">Excluir</button>
                            {{ Form::close() }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-info">Nenhum registro no banco!</div>
        @endif

    </div>
@endsection
