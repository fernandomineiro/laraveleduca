@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9"><h2 class="table">Lista de Cursos Presencias</h2></div>
        <hr class="clear hr"/>
        @if(count($agendas) > 0)
            <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped dataTable">
                <thead>
                    <th>Curso</th>
                    <th>Turma</th>
                    <th>Descrição</th>
                    <th>Data Início</th>
                    <th>Hora Início</th>
                    <th>Data Fim</th>
                    <th>Hora Fim</th>
                    <th>Ações</th>
                </thead>

                <tbody>
                @foreach($agendas as $agenda)
                    <tr>
                        <td>{{ $agenda->titulo }}</td>
                        <td>{{ $agenda->turma }}</td>
                        <td>{{ $agenda->descricao }}</td>
                        <td>{{ date('d/m/Y',strtotime($agenda->data_inicio)) }}</td>
                        <td>{{ date('H:i',strtotime($agenda->hora_inicio)) }}</td>
                        <td>{{ date('d/m/Y',strtotime($agenda->data_final)) }}</td>
                        <td>{{ date('H:i',strtotime($agenda->hora_final)) }}</td>
                        <td>
                            <a href="/admin/presenca_curso/{{ $agenda->id }}/listapresenca" class="btn btn-primary btn-sm">Lista de Presença</a>
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

