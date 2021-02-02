@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9"><h2 class="table">Agenda de Cursos</h2></div>
        <div class="col-md-3" style="margin-top: 20px;">
            <a href="{{ route('admin.agenda_curso.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
        </div>
        <hr class="clear hr"/>
        @if(count($agendas) > 0)
            <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped dataTable">
                <thead>
                    <th>Curso</th>
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
                        <td>{{ $agenda->descricao }}</td>
                        <td>{{ date('d/m/Y',strtotime($agenda->data_inicio)) }}</td>
                        <td>{{ date('H:i',strtotime($agenda->hora_inicio)) }}</td>
                        <td>{{ date('d/m/Y',strtotime($agenda->data_final)) }}</td>
                        <td>{{ date('H:i',strtotime($agenda->hora_final)) }}</td>
                        <td>
                            <a href="presenca_agenda_curso/{{ $agenda->id }}/lista_presenca" class="btn btn-default btn-sm">Gerenciar</a>
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

