@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9"><h2 class="table">Turma de Cursos Presenciais</h2></div>
        <div class="col-md-3" style="margin-top: 20px;">
            <a href="{{ route('admin.cursoturma.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
        </div>
        <hr class="clear hr"/>
        @if(count($turmas) > 0)
            <table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
                <th>Turma</th>
                <th>Descrição</th>
                <th>Curso</th>
                <th>IES</th>
                <th>Status</th>
                <th>Ações</th>
                <tbody>
                @foreach($turmas as $obj)
                    <tr>
                        <td>{{ $obj->nome }}</td>
                        <td>{{ $obj->descricao }}</td>
                        <td>{{ $obj->titulo }}</td>
                        <td>{{ $obj->ies }}</td>
                        <td>{{ ($obj->status === 0) ? 'Inativo' : 'Ativo' }}</td>
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

