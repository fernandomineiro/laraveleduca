@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9"><h2 class="table">Agendas dos Cursos Presenciais</h2></div>
        <div class="col-md-3" style="margin-top: 20px;">
            <a href="{{ route('admin.agenda_curso.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
        </div>
        <hr class="clear hr"/>
        @if(count($agendas) > 0)
            <table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
                <th>Curso</th>
                <th>Descrição</th>
                <th>Data Início</th>
                <th>Hora Início</th>
                <th>Data Fim</th>
                <th>Hora Fim</th>
                <th>Ações</th>
                <tbody>
                @foreach($agendas as $agenda)
                    <tr>
                        <td>{{ $agenda->titulo }}</td>
                        <td>{{ $agenda->descricao }}</td>
                        <td>{{ date('d/m/Y',strtotime($agenda->data_inicio)) }}</td>
                        <td>{{ date('H:i',strtotime($agenda->hora_inicio)) }}</td>
                        <td>{{ date('d/m/Y',strtotime($agenda->data_final)) }}</td>
                        <td>{{ date('H:i',strtotime($agenda->hora_final)) }}</td>
                        <td>
                            <a href="/admin/agenda_curso/{{ $agenda->id }}/editar" class="btn btn-default btn-sm">Editar</a>

                            {{ Form::open(['method' => 'DELETE', 'route' => ['admin.agenda_curso.deletar', $agenda->id], 'style' => 'display:inline;']) }}
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

