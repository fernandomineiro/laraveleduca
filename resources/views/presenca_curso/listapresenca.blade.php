@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9"><h2 class="table">Listas de Presença: <?php echo ($agenda_curso->fk_curso != null) ? $lista_cursos[$agenda_curso->fk_curso] : 'Curso Presencial'; ?></h2>
            <a href="{{ route('admin.presenca_curso') }}" class="label label-default">Voltar</a><br /><br />
        </div>
        <div class="col-md-3" style="margin-top: 20px;">
            <a href="/admin/presenca_curso/{{ $id_agenda_curso }}/incluir" class="btn btn-success right margin-bottom-10">Adicionar</a>
        </div>
        <hr class="clear hr"/>
        @if(count($presencas) > 0)
            <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped dataTable">
                <thead>
                    <th>Aluno</th>
                    <th>Curso</th>
                    <th>Presente</th>
                    <th>Data Início</th>
                    <th>Hora Início</th>
                    <th>Data Fim</th>
                    <th>Hora Fim</th>
                    <th>Ações</th>
                </thead>
                <tbody>
                @foreach($presencas as $presenca)
                    <tr>
                        <td>{{ $presenca->nome_aluno }}</td>
                        <td>{{ $presenca->titulo }}</td>
                        <td>{{ isset($presenca->presente) ? $lista_presente[$presenca->presente] : '-'}}</td>
                        <td>{{ date('d/m/Y',strtotime($presenca->data_inicio)) }}</td>
                        <td>{{ date('H:i',strtotime($presenca->hora_inicio)) }}</td>
                        <td>{{ date('d/m/Y',strtotime($presenca->data_final)) }}</td>
                        <td>{{ date('H:i',strtotime($presenca->hora_final)) }}</td>
                        <td>
                            <a href="/admin/presenca_curso/{{ $presenca->id }}/editar" class="btn btn-default btn-sm">Editar</a>
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

