@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9"><h2 class="table">Agenda de Gravação</h2></div>
        <div class="col-md-3" style="margin-top: 20px;">
            <a href="{{ route('admin.agendamentogravacao.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
        </div>
        <hr class="clear hr"/>

        @if(count($agendamentogravacao) > 0)
            <table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
                <th>Data Início</th>
                <th>Hora Início</th>
                <th>Projeto</th>
                <th>Produtora</th>
                <th>Conteudista</th>
                <th>Local</th>
                <th>Status</th>
                <th>Material/Anexo</th>
                <th>Já enviou?</th>
                <th>Curso</th>

                <th>Ações</th>
                <tbody>
                @foreach($agendamentogravacao as $agendamentoGravacao)
                    <tr>
                        <td>{{ date('d/m/Y',strtotime($agendamentoGravacao->data)) }}</td>
                        <td>{{ date('H:i',strtotime($agendamentoGravacao->hora)) }}</td>
                        <td>{{ $agendamentoGravacao->projeto }}</td>
                        <td>{{ $agendamentoGravacao->produtora }}</td>
                        <td>Conteudista</td>
                        <td>{{ $agendamentoGravacao->local }}</td>
                        <td>{{ $agendamentoGravacao->status }}</td>
                        <td>{{ $agendamentoGravacao->possui_anexo }}</td>
                        <td>{{ $agendamentoGravacao->material_enviado }}</td>
                        <td>{{ $agendamentoGravacao->nome_curso }}</td>

                        <td>
                            <a href="{{ $agendamentoGravacao->id }}/editar"
                               class="btn btn-default btn-sm">Editar</a>

                            {{ Form::open(['method' => 'DELETE', 'route' => ['admin.agendamentogravacao.deletar', $agendamentoGravacao->id], 'style' => 'display:inline;']) }}
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

