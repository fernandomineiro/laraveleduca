@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9"><h2 class="table">Gerenciamento de API's</h2></div>
        <div class="col-md-3" style="margin-top: 20px;">
            <a href="{{ route('admin.api.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
        </div>

        <hr class="clear hr"/>
        @if(count($apis) > 0)
            <table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
                <th>Título</th>
                <th>Tipo</th>
                <th>URL</th>
                <th>Status</th>
                <th>Ações</th>
                <tbody>
                @foreach($apis as $obj)
                    <tr>
                        <td>{{ $obj->titulo }}</td>
                        <td>{{ $lista_tipos[$obj->tipo] }}</td>
                        <td>{{ $obj->url }}</td>
                        <td>{{ $lista_status[$obj->status] }}</td>
                        <td style="text-align: center">
							@include('table.editarexcluir')
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

