@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9">
            <h2 class="table"><span>Conteúdo da Assinatura: {{$assinatura->titulo}}</span></h2>
            <a href="/admin/assinatura/index" class="label label-default">Voltar</a>
        </div>
        <div class="col-md-3" style="margin-top: 20px;">
            <a href="/admin/assinatura_conteudo/{{ $assinatura->id }}/incluir" class="btn btn-success right margin-bottom-10">Adicionar</a>
        </div>
        <hr class="clear hr" />
        <div
            @if(!isset($assinatura->fk_tipo_assinatura) || (isset($assinatura->fk_tipo_assinatura) && $assinatura->fk_tipo_assinatura != 3))
            style="display: none;"
            @else
            style="display: block;"
            @endif>
            <h2 class="table"><span>Trilhas de Conhecimento</span></h2>
            @if(count($assinatura_conteudo) > 0)
                <table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
                    <th>Projeto</th>
                    <th>Nome da Trilha de Conhecimento</th>
                    <th>Descrição</th>
                    <th>Assinatura</th>
                    <th>Status</th>
                    <th>Ações</th>
                    <tbody>
                    @foreach($assinatura_conteudo as $trilha)
                        <tr>
                            <td>{{ isset($lista_faculdades[$trilha->fk_faculdade]) ? $lista_faculdades[$trilha->fk_faculdade] : '-' }}</td>
                            <td>{{ $trilha->titulo }}</td>
                            <td>{{ $trilha->titulo }}</td>
                            <td>{{$assinatura->titulo}}</td>
                            <td>{{ $lista_status[$trilha->assinatura] }}</td>
                            <td>
                                <a href="/admin/assinatura_conteudo/{{ $trilha->id_conteudo }}/editar" class="btn btn-default btn-sm">Editar</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-info">Nenhuma trilha ainda foi relacionada a essa assinatura!</div>
            @endif
        </div>
        <div @if(!isset($assinatura->fk_tipo_assinatura) || (isset($assinatura->fk_tipo_assinatura) && $assinatura->fk_tipo_assinatura != 2))
             style="display: none;"
             @else
             style="display: block;"
            @endif>
            @if(count($assinatura_conteudo) > 0)
                <table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
                    <th>Nome do Curso</th>
                    <th>Descrição</th>
                    <th>Projeto</th>
                    <th>Assinatura</th>
                    <th>Status</th>
                    <th>Ações</th>
                    <tbody>
                    @foreach($assinatura_conteudo as $curso)
                        <tr>
                            <td>{{ $curso->titulo }}</td>
                            <td>{{ $curso->descricao }}</td>
                            <td>{{ isset($lista_faculdades[$curso->fk_faculdade]) ? $lista_faculdades[$curso->fk_faculdade] : '-' }}</td>
                            <td>{{$assinatura->titulo}}</td>
                            <td>{{ $lista_status[$curso->assinatura] }}</td>
                            <td>
                                <a href="/admin/assinatura_conteudo/{{ $curso->id_conteudo }}/editar" class="btn btn-default btn-sm">Editar</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-info">Nenhum curso foi relacionado a essa assinatura!</div>
            @endif
        </div>
    </div>
@endsection
