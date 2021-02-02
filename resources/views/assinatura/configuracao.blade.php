@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <h2 class="table"><span>Assinatura</span></h2>
        <div class="form-group campos-trilha"
             @if(!isset($assinatura->fk_tipo_assinatura) || (isset($assinatura->fk_tipo_assinatura) && $assinatura->fk_tipo_assinatura != 3))
             style="display: none;"
             @else
             style="display: block;"
            @endif>
            {{ Form::open(['method' => 'POST', 'url' => '/admin/assinatura/salvarcurso']) }}
                {{ Form::label('Trilha de Conhecimento') }}
                <select name="trilha" id="trilha" class="form-control" style="text-align: right; width: 50%;">
                    @foreach($trilhas as $trilha)
                        <option value="{{$trilha['id']}}">{{$trilha['titulo']}}</option>
                    @endforeach
                </select>
                <input type="hidden" name="assinatura" value="{{$assinatura}}">
                <div class="form-group">
                    <a href="{{ url()->previous() }}" class="btn btn-default">Cancelar</a>
                    {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
                </div>
            {{ Form::close() }}
        </div>
        <div
            @if(!isset($assinatura->fk_tipo_assinatura) || (isset($assinatura->fk_tipo_assinatura) && $assinatura->fk_tipo_assinatura != 3))
                style="display: none;"
            @else
                style="display: block;"
            @endif>
            <h2 class="table"><span>Trilhas de Conhecimento</span></h2>
                @if(count($assinatura->trilhas) > 0)
                    <table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
                        <th>Categoria</th>
                        <th>Projeto</th>
                        <th>Nome da Trilha de Conhecimento</th>
                        <th>Status</th>
                        <th>Ações</th>
                        <tbody>
                        @foreach($assinatura->trilhas as $trilha)
                            <tr>
                                <td>{{ isset($lista_categorias[$trilha->fk_categoria]) ? $lista_categorias[$trilha->fk_categoria] : '-' }}</td>
                                <td>{{ isset($lista_faculdades[$trilha->fk_faculdade]) ? $lista_faculdades[$trilha->fk_faculdade] : '-' }}</td>
                                <td>{{ $trilha->titulo }}</td>
                                <td>{{ $lista_status[$trilha->status] }}</td>
                                <td>
                                    {{ Form::open(['method' => 'DELETE', 'route' => ['admin.trilha.deletar', $trilha->id], 'style' => 'display:inline;']) }}
                                        <button type="submit" class="btn btn-danger btn-sm">Inativar</button>
                                    {{ Form::close() }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info">Nenhuma trilha ainda foi relacionada a essa assinatura!</div>
                @endif
        </div>
        <div class="form-group campos-curso"
             @if(!isset($assinatura->fk_tipo_assinatura) || (isset($assinatura->fk_tipo_assinatura) && $assinatura->fk_tipo_assinatura != 2))
             style="display: none;"
             @else
             style="display: block;"
            @endif>
            {{ Form::open(['method' => 'POST', 'url' => '/admin/assinatura/salvarcurso']) }}
            {{ Form::label('Cursos')}}
            @for($i = 0; $i < $assinatura->qtd_cursos; $i++ )
                <select name="curso[]" id="curso" class="form-control" style="width: 50%;">
                    @foreach($cursos as $curso)
                        <option value="{{$curso['id']}}">{{$curso['nome_curso']}}</option>
                    @endforeach
                </select>
            @endfor
            <input type="hidden" name="assinatura" value="{{$assinatura}}">
            <div class="form-group">
                <a href="{{ url()->previous() }}" class="btn btn-default">Cancelar</a>
                {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
            </div>
            {{ Form::close() }}
        </div>
        <div @if(!isset($assinatura->fk_tipo_assinatura) || (isset($assinatura->fk_tipo_assinatura) && $assinatura->fk_tipo_assinatura != 2))
            style="display: none;"
        @else
            style="display: block;"
        @endif>
            @if(count($assinatura->cursos) > 0)
                <table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
                    <th>Nome do Curso</th>
                    <th>Certificado</th>
                    <th>Professor</th>
                    <th>Curador</th>
                    <th>Produtora</th>
                    <th>Status</th>
                    <th>Ações</th>
                    <tbody>
                    @foreach($$assinatura->cursos as $curso)
                        <tr>
                            <td>{{ $curso->titulo }}</td>
                            <td><?php echo isset($lista_status[$curso->status]) ?  $lista_status[$curso->status] : '-'; ?></td>
                            <td><?php echo isset($lista_certificados[$curso->fk_certificado]) ?  $lista_certificados[$curso->fk_certificado] : '-'; ?></td>
                            <td><?php echo isset($lista_professor[$curso->fk_professor]) ?  $lista_professor[$curso->fk_professor] : '-'; ?></td>
                            <td><?php echo isset($lista_curador[$curso->fk_curador]) ?  $lista_curador[$curso->fk_curador] : '-'; ?></td>
                            <td><?php echo isset($lista_produtora[$curso->fk_produtora]) ?  $lista_produtora[$curso->fk_produtora] : '-'; ?></td>
                            <td>
                                {{ Form::open(['method' => 'DELETE', 'route' => ['admin.curso.deletar', $curso->id], 'style' => 'display:inline;']) }}
                                <button type="submit" class="btn btn-danger btn-sm">Inativar</button>
                                {{ Form::close() }}
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
