@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9"><h2
                class="table">{{ $modulo['moduloDetalhes']->menu .' '.$modulo['moduloDetalhes']->modulo }}s</h2></div>
        <div class="col-md-3" style="margin-top: 20px;">
            <a href="{{ route('admin.'.$modulo['moduloDetalhes']->rota.'.incluir') }}"
               class="btn btn-success right margin-bottom-10">Adicionar</a>
        </div>
        <hr class="clear hr"/>

        @if(count($lstObj) > 0)
            <table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
                <th>Perfil</th>
                <th>Módulo - Ação</th>
                <th>Elemento</th>
                <th>Ações</th>
                <tbody>
                <?php $perf = '' ?>
                @foreach($lstObj as $obj)
                    <tr>
                        @if($perf != $obj->perfil)
                            <td>{{ $obj->perfil }}</td>
                        @else
                            <td></td>
                        @endif
                        <td>{{ $obj->modulo }} - {{ $obj->acoes }}</td>
                        <td>{{ $obj->elemento }}</td>
                        @if($perf != $obj->perfil)
                            <td>
                                <a href="{{ $obj->fk_perfil_id }}/editar" class="btn btn-default btn-sm">Editar</a>
                                {{ Form::open(['method' => 'DELETE', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.deletar', $obj->fk_perfil_id], 'style' => 'display:inline;']) }}
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir?')">Excluir</button>
                                {{ Form::close() }}
                            </td>
                            <?php $perf = $obj->perfil;?>
                        @else
                            <td></td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-info">Nenhum registro no banco!</div>
        @endif

    </div>
@endsection
