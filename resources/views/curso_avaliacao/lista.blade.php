@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9"><h2 class="table">Avaliação de Curso</h2></div>
        <div class="col-md-3" style="margin-top: 20px;">
            <a href="{{ route('admin.curso_avaliacao.incluir') }}" class="btn btn-success right margin-bottom-10">Adicionar</a>
        </div>

        <hr class="clear hr"/>
        @if(count($cursos_avaliacao) > 0)
            <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped dataTable">
                <thead>
                    <th>Curso</th>
                    <th>Descrição</th>
                    <th>Qtd Estrelas</th>
                    <th>Status</th>
                    <th>Ações</th>
                </thead>
                <tbody>
                @foreach($cursos_avaliacao as $item)
                    <?php if(isset($lista_cursos[$item->fk_curso])) : ?>
                        <tr>
                            <td>{{ $lista_cursos[$item->fk_curso] }}</td>
                            <td>{{ $item->descricao }}</td>
                            <td>{{ $item->qtd_estrelas }}</td>
                            <td>{{ $lista_status[$item->status] }}</td>
                            <td>
                                <a href="/admin/curso_avaliacao/{{ $item->id }}/editar" class="btn btn-default btn-sm">Editar</a>
                                {{ Form::open(['method' => 'DELETE', 'route' => ['admin.curso_avaliacao.deletar', $item->id], 'style' => 'display:inline;']) }}
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir?')">Excluir</button>
                                {{ Form::close() }}
                            </td>
                        </tr>
                    <?php endif; ?>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-info">Nenhum registro no banco!</div>
        @endif
    </div>
@endsection

