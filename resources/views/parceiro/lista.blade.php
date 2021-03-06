@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9"><h2 class="table">Pareceiros</h2></div>
        <div class="col-md-3" style="margin-top: 20px;">
            <a href="{{ route('admin.parceiros.incluir') }}"
               class="btn btn-success right margin-bottom-10">Adicionar</a>
        </div>
        <hr class="clear hr"/>

        @if(count($parceiros) > 0)
            <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped dataTable">
                <thead>
                    <th>Usuário</th>
                    <th>Tipo Parceiro</th>
                    <th>Compartilhar</th>
                    <th>Ações</th>
                </thead>
                <tbody>
                @foreach($parceiros as $parceiro)
                    <tr>
                        @if(count($lista_usuarios) > 0)
                            @foreach( $lista_usuarios  as $listaUsuarios)
                                @if($parceiro->fk_usuario == $listaUsuarios->id)
                                    <td>{{ $listaUsuarios->nome }}</td>
                                @endif
                            @endforeach
                        @endif
                        @if(count($lista_tipo_parceiro) > 0)
                            @foreach($lista_tipo_parceiro as $key => $tipoParceiro)
                                @if($parceiro->fk_tipo_parceiro == $key)
                                    <td>{{ $tipoParceiro }}</td>
                                @endif
                            @endforeach
                        @endif
                        <td>{{ $lista_status[$parceiro->compartilhar ]}}</td>
                        <td>
                            <a href="/admin/parceiro/{{ $parceiro->id }}/editar" class="btn btn-default btn-sm">Editar</a>

                            {{ Form::open(['method' => 'DELETE', 'route' => ['admin.parceiros.deletar', $parceiro->id], 'style' => 'display:inline;']) }}
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

