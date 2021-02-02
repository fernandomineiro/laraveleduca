@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9"><h2 class="table">Agenda de Cursos</h2></div>
        <div class="col-md-3" style="margin-top: 20px;">
            <a href="{{ route('admin.presenca_agenda_curso.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
        </div>
        <hr class="clear hr"/>
        @if(count($presencas) > 0)
            <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped dataTable">
                <thead>
                    <th>Aluno</th>
                    <th>Curso</th>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Ações</th>
                </thead>
                <tbody>
                @foreach($presencas as $presenca)
                    <tr>
                        <td>{{ $presenca->nome_aluno }}</td>
                        <td>{{ $presenca->titulo }}</td>
                        <td>{{ date('d/m/Y',strtotime($presenca->data_inicio)) }}</td>
                        <td>{{ date('H:i',strtotime($presenca->hora_inicio)) }}</td>
                        <td>
                            <a href="presenca_agenda_curso/{{ $presenca->id }}/editar" class="btn btn-default btn-sm">Gerenciar</a>
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

