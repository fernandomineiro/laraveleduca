@extends('layouts.app')
@section('styles')
    <style>
        .select2.select2-container.select2-container--default,
        .select2-selection__rendered,
        .select2-selection.select2-selection--multiple
        .select2-search.select2-search--inline,
        .select2-search__field {
            width:100% !important;
        }

    </style>
@endsection

@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Cupom</span></h2>
		<hr class="hr" />

        @if(Request::is('*/editar'))
            {{ Form::model( $cupom, ['method' => 'PATCH', 'route' => ['admin.cupom.atualizar', $cupom->id], 'id' => 'cupomform','files' => true] ) }}
        @else
            {{ Form::open(['url' => '/admin/cupom/salvar', 'id' => 'cupomform','files' => true]) }}
        @endif
        <ul class="nav nav-tabs">
            <li class="nav-item active"><a data-toggle="tab" href="#cupom">Cupom</a></li>
            @if(!Request::is('*/editar'))
                <li class="nav-item disabled"><a class="disabled" href="#gerenciar">Gerenciar Cupom</a></li>
            @else
                <li class="nav-item"><a data-toggle="tab" href="#gerenciar">Gerenciar Cupom</a></li>
            @endif
        </ul>
        <div class="tab-content" style="overflow-x:auto;">
            <div id="cupom" class="tab-pane fade in active">
                <br>
                <br>

                <div class="form-group">
                    {{ Form::label('Status') }} <small>*</small>
                    {{ Form::select('status', $lista_status, (isset($cupom->status) ? $cupom->status : 1 ), ['class' => 'form-control']) }}
                </div>
                <div class="form-group">
                    {{ Form::label('Título') }} <small>*</small>
                    {{ Form::input('text', 'titulo', null, ['class' => 'form-control', '', 'placeholder' => 'Título']) }}
                </div>
                <div class="form-group">
                    {{ Form::label('Código do Cupom') }} <small>*</small>
                    {{ Form::input('text', 'codigo_cupom', null, ['class' => 'form-control', '', 'placeholder' => 'Código do Cupom']) }}
                </div>
                <div class="form-group">
                    {{ Form::label('Descrição') }} <small>*</small>
                    {!! Form::textarea('descricao',null,['class'=>'form-control', 'rows' => 4, 'cols' => 40]) !!}
                </div>
                <div class="form-group">
                    {{ Form::label('Número Máximo de Usos') }}
                    {{ Form::input('text', 'numero_maximo_usos', isset($cupom->numero_maximo_usos) ? $cupom->numero_maximo_usos : '',
                        [
                            'class' => 'form-control', 'style' => 'width: 200px;'
                        ]
                    ) }}
                </div>
                <div class="form-group">
                    {{ Form::label('Número Máximo de Produtos') }}
                    {{ Form::input('text', 'numero_maximo_produtos', isset($cupom->numero_maximo_produtos) ? $cupom->numero_maximo_produtos : '',
                        [
                            'class' => 'form-control', 'style' => 'width: 200px;'
                        ]
                    ) }}
                </div>

                <div class="form-group">
                    {{ Form::label('Projetos') }}
                    <br />
                    {{ Form::select('fk_faculdade', ['' => 'Selecione'] + $lista_faculdades, isset($cupom->fk_faculdade) ? $cupom->fk_faculdade : '', [
                        'name' => 'fk_faculdade',
                        'class' => 'form-control myselect', 'allowClear' => true,
                        'data-placeholder' => 'Projetos (pesquise por nome do projeto)',
                        'id' => 'autocomplete-projetos',
                        'width' => '100%'
                    ]) }}
                </div>

                <div class="form-group">
                    {{ Form::label('Data Validade Inicial') }} <small>*</small>
                    @if(Request::is('*/editar'))
                        {{ Form::input('text', 'data_validade_inicial', implode('/', array_reverse(explode('-', $cupom->data_validade_inicial))), ['class' => 'form-control datepicker', '', 'placeholder' => 'Data Validade Inicial', 'style' => 'width: 200px;']) }}
                    @else
                        {{ Form::input('text', 'data_validade_inicial', null, ['class' => 'form-control datepicker', '', 'placeholder' => 'Data Validade Inicial', 'style' => 'width: 200px;']) }}
                    @endif
                </div>
                <div class="form-group">
                    {{ Form::label('Data Validade Final') }} <small>*</small>
                    @if(Request::is('*/editar'))
                        {{ Form::input('text', 'data_validade_final', implode('/', array_reverse(explode('-', $cupom->data_validade_final))), ['class' => 'form-control datepicker', '', 'placeholder' => 'Data Validade Final', 'style' => 'width: 200px;']) }}
                    @else
                        {{ Form::input('text', 'data_validade_final', null, ['class' => 'form-control datepicker', '', 'placeholder' => 'Data Validade Final', 'style' => 'width: 200px;']) }}
                    @endif
                </div>

                <div class="form-group">
                    {{ Form::label('Tipo') }} <small>*</small>
                    <br />

                    <div class="col-sm-2">
                        <input type="radio" id="marca_desconto" class="form-control radioo" name="tipo_cupom_desconto" @if (isset($cupom->tipo_cupom_desconto) && (($cupom->tipo_cupom_desconto == 1) OR !$cupom->tipo_cupom_desconto)) checked @endif value="1" style="opacity: 100; display: block;">
                        Percentual (%)
                    </div>
                    <div class="col-sm-2">
                        <input type="radio" id="marca_moeda" class="form-control radioo" name="tipo_cupom_desconto" @if (isset($cupom->tipo_cupom_desconto) && $cupom->tipo_cupom_desconto == 2) checked @endif value="2" style="opacity: 100; display: block;" />
                        Espécie (R$)
                    </div>
                </div>
                <div class="row"></div>
                <div id="box_desconto" class="form-group"
                     @if(isset($cupom->tipo_cupom_desconto) && (!$cupom->tipo_cupom_desconto OR $cupom->tipo_cupom_desconto == 1))
                     style="display: block; margin-top: 30px;"
                     @else
                     style="display: none; margin-top: 30px;"
                    @endif>

                    {{ Form::label('Percentual (%)') }} <small>*</small>
                    {{ Form::input('number', 'valor', (isset($cupom->valor)) ? $cupom->valor : null, ['class' => 'form-control', '', 'placeholder' => 'Valor', 'style' => 'width: 160px;', 'id' => 'valor_percentual', 'max' => '100', 'step' => '1']) }}
                </div>
                <div id="box_moeda" class="form-group"
                     @if(isset($cupom->tipo_cupom_desconto) && $cupom->tipo_cupom_desconto == 2)
                     style="display: block; margin-top: 30px;"
                     @else
                     style="display: none; margin-top: 30px;"
                    @endif>

                    {{ Form::label('Valor (R$)') }} <small>*</small>
                    {{ Form::input('text', 'valor', (isset($cupom->valor)) ? $cupom->valor : null, ['class' => 'form-control moeda', '', 'placeholder' => 'Valor', 'style' => 'width: 160px;', 'id' => 'valor_moeda']) }}
                </div>

                @if(Request::is('*/editar'))
                    <input type="hidden" name="valor" id="valor" value="{{$cupom->valor}}" />
                @else
                    <input type="hidden" name="valor" id="valor" value="" />
                @endif

                <div class="form-group">
                    <a href="{{ route('admin.cupom') }}" class="btn btn-default">Voltar</a>
                    @if(Request::is('*/editar'))
                        {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
                    @else
                        <a class="btn btn-primary btnNext">Avançar</a>
                    @endif
                </div>
            </div>
            <div id="gerenciar" class="tab-pane fade">
                <ul class="nav nav-tabs">
                    <li class="nav-item active"><a data-toggle="tab" href="#alunos">Alunos</a></li>
                    <li class="nav-item"><a data-toggle="tab" href="#cursos">Cursos</a></li>
                    <li class="nav-item"><a data-toggle="tab" href="#categoriasacursos">Categorias de Cursos</a></li>
                    <li class="nav-item"><a data-toggle="tab" href="#trilhas">Trilhas</a></li>
                    <!-- <li class="nav-item"><a data-toggle="tab" href="#alunos-sem-cadastro">Alunos sem Cadastro</a></li> -->
                    <!--<li class="nav-item"><a data-toggle="tab" href="#assinaturas">Assinaturas</a></li>
                    <li class="nav-item"><a data-toggle="tab" href="#eventos">Eventos</a></li>-->
                </ul>
                <div class="tab-content">
                    <div id="alunos" class="tab-pane fade in active">
                        <div id="bloco_alunos">
                            <div class="alunos">
                                <br>
                                <br>
                                <div class="row">
                                    <div class="form-group col-lg-6">
                                        {{ Form::label('Buscar Alunos') }} <br>

                                        <div class="form-group">
                                            <nobr>
                                            <input id="textBuscaAluno" type="text" class="form-control" placeholder="Alunos (pesquise por email, cpf ou nome do aluno)"><br>
                                            <button id="buscarAlunos" class="btn btn-success">Buscar</button>
                                            </nobr>
                                        </div><br>

                                        <div id="filtroAlunos" class="form-group">
                                            {{ Form::label('Alunos Filtrados') }} <br>
                                            <select id="alunosFiltrados" class="form-control"></select><br><br>
                                            <button id="adicionarAlunos" class="btn btn-success">Adiconar Aluno</button>
                                        </div><br>
                                        
                                        <div id="novosAlunos" class="form-group">
                                            {{ Form::label('Novos Alunos') }} <br>
                                            <select multiple="multiple" id="slcNovosAlunos" name="fk_alunos[]" class="form-control"></select>
                                        </div><br>
                                        
                                    </div>
                                </div>
                                <hr/>
                            </div>
                        </div>

                        @if(isset($cupom_alunos) && count($cupom_alunos) > 0)
                            <table cellpadding="0" cellspacing="0" border="0" id="table-alunos" class="table table-bordered table-striped display">
                                <thead>
                                <th>Aluno</th>
                                <th>Cpf</th>
                                <th>E-mail</th>
                                <th>Cupom</th>
                                <th>Status</th>
                                <th>Código</th>
                                <th>Tipo</th>
                                <th>Valor</th>
                                <th>Data Inicial</th>
                                <th>Data Final</th>
                                <th>Ações</th>
                                </thead>
                                <tbody>
                                @foreach($cupom_alunos as $item)
                                    <tr>
                                        <td>{{ $item->aluno_nome }}</td>
                                        <td>{{ $item->cpf }}</td>
                                        <td>{{ $item->email }}</td>
                                        <td>{{ $item->titulo }}</td>
                                        <td>{{ ($item->status == 1 ? "Ativo" : "Inativo") }}</td>
                                        <td>{{ $item->codigo_cupom }}</td>
                                        <td>{{ ($item->tipo_cupom_desconto) ? $tipo_cupom[$item->tipo_cupom_desconto] : '-'}}</td>
                                        <td>{{ ($item->tipo_cupom_desconto == 2) ? 'R$ '. number_format( $item->valor , 2, ',', '.') : (int)$item->valor}}</td>
                                        <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_inicial))) }}</td>
                                        <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_final))) }}</td>
                                        <td nowrap>
                                            <form action=""></form>
                                            {{ Form::open(['method' => 'POST', 'url' => '/admin/cupom/deletarrelacionamentos', 'style' => 'display:inline;']) }}
                                                <input type="hidden" name="id" value="{{$item->id}}">
                                                <input type="hidden" name="tipo" value="1">
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
                    <div id="cursos" class="tab-pane fade">
                        <div id="bloco_cursos">
                            <div class="cursos">
                                <br>
                                <br>
                                <div class="form-group">
                                    {{ Form::label('Selecione um Curso') }}
                                    {{ Form::select('fk_curso[]', $lista_cursos, null, [
                                        'multiple' => 'multiple', 'name' => 'fk_curso[]',
                                        'class' => 'form-control myselect', 'allowClear' => true,
                                        'data-placeholder' => 'Cursos (pesquise por nome ou tipo)',
                                        'id' => 'autocomplete-cursos'
                                    ]) }}
                                </div>
                                <hr/>
                            </div>
                        </div>

                        @if(isset($cupom_cursos) && count($cupom_cursos) > 0)
                            <table cellpadding="0" cellspacing="0" border="0" id="table-cursos" class="table table-bordered table-striped display">
                                <thead>
                                <th>Curso</th>
                                <th>Cupom</th>
                                <th>Status</th>
                                <th>Código</th>
                                <th>Tipo</th>
                                <th>Valor</th>
                                <th>Data Inicial</th>
                                <th>Data Final</th>
                                <th>Ações</th>
                                </thead>
                                <tbody>
                                @foreach($cupom_cursos as $item)
                                    <tr>
                                        <td>{{ $item->nome_curso }}</td>
                                        <td>{{ $item->titulo }}</td>
                                        <td>{{ ($item->status == 1 ? "Ativo" : "Inativo") }}</td>
                                        <td>{{ $item->codigo_cupom }}</td>
                                        <td>{{ ($item->tipo_cupom_desconto) ? $tipo_cupom[$item->tipo_cupom_desconto] : '-'}}</td>
                                        <td>{{ ($item->tipo_cupom_desconto == 2) ? 'R$ '. number_format( $item->valor , 2, ',', '.') : (int)$item->valor}}</td>
                                        <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_inicial))) }}</td>
                                        <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_final))) }}</td>
                                        <td nowrap>
                                            <form action=""></form>
                                            {{ Form::open(['method' => 'POST', 'url' => '/admin/cupom/deletarrelacionamentos', 'style' => 'display:inline;']) }}
                                            <input type="hidden" name="id" value="{{$item->id}}">
                                            <input type="hidden" name="tipo" value="2">
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
                    <div id="categoriasacursos" class="tab-pane fade">
                        <div id="bloco_categorias">
                            <div class="categorias" data-contador="1">
                                <br>
                                <br>
                                <div class="form-group">
                                    {{ Form::label('Categorias') }}
                                    {{ Form::select('fk_categoria[]', $categorias, null, [
                                        'multiple' => 'multiple', 'id' => 'fk_cursos_categoria', 'name' => 'fk_categoria[]',
                                        'class' => 'form-control myselect', 'allowClear' => true,
                                        'data-placeholder' => 'Categorias (pesquise por nome da categoria)',
                                        'id' => 'autocomplete-categorias'
                                    ]) }}
                                </div>
                                <hr/>
                            </div>
                        </div>

                        @if(isset($cupom_cursos_categorias) && count($cupom_cursos_categorias) > 0)
                            <table cellpadding="0" cellspacing="0" border="0" id="table-categoriasacursos" class="table table-bordered table-striped display">
                                <thead>
                                <th>Categoria</th>
                                <th>Cupom</th>
                                <th>Status</th>
                                <th>Código</th>
                                <th>Tipo</th>
                                <th>Valor</th>
                                <th>Data Inicial</th>
                                <th>Data Final</th>
                                <th>Ações</th>
                                </thead>
                                <tbody>
                                @foreach($cupom_cursos_categorias as $item)
                                    <tr>
                                        <td>{{ $item->nome_categoria }}</td>
                                        <td>{{ $item->titulo }}</td>
                                        <td>{{ ($item->status == 1 ? "Ativo" : "Inativo") }}</td>
                                        <td>{{ $item->codigo_cupom }}</td>
                                        <td>{{ ($item->tipo_cupom_desconto) ? $tipo_cupom[$item->tipo_cupom_desconto] : '-'}}</td>
                                        <td>{{ ($item->tipo_cupom_desconto == 2) ? 'R$ '. number_format( $item->valor , 2, ',', '.') : (int)$item->valor}}</td>
                                        <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_inicial))) }}</td>
                                        <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_final))) }}</td>
                                        <td nowrap>
                                            <form action=""></form>
                                            {{ Form::open(['method' => 'POST', 'url' => '/admin/cupom/deletarrelacionamentos', 'style' => 'display:inline;']) }}
                                            <input type="hidden" name="id" value="{{$item->id}}">
                                            <input type="hidden" name="tipo" value="3">
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
                    <div id="trilhas" class="tab-pane fade">
                        <div id="bloco_trilhas">
                            <div class="trilhas" data-contador="1">
                                <br>
                                <br>
                                <div class="form-group">
                                    {{ Form::label('Trilhas') }}
                                    {{ Form::select('fk_trilha[]', $lista_trilhas, null, [
                                        'multiple' => 'multiple', 'name' => 'fk_trilha[]',
                                        'class' => 'form-control myselect', 'allowClear' => true,
                                        'data-placeholder' => 'Trilhas (pesquise por nome da trilha)',
                                        'id' => 'autocomplete-trilha'
                                    ]) }}
                                </div>
                                <hr/>
                            </div>
                        </div>

                        @if(isset($cupom_trilhas) && count($cupom_trilhas) > 0)
                            <table cellpadding="0" cellspacing="0" border="0" id="table-trilhas" class="table table-bordered table-striped display">
                                <thead>
                                <th>Trilha</th>
                                <th>Cupom</th>
                                <th>Status</th>
                                <th>Código</th>
                                <th>Tipo</th>
                                <th>Valor</th>
                                <th>Data Inicial</th>
                                <th>Data Final</th>
                                <th>Ações</th>
                                </thead>
                                <tbody>
                                @foreach($cupom_trilhas as $item)
                                    <tr>
                                        <td>{{ $item->nome_trilha }}</td>
                                        <td>{{ $item->titulo }}</td>
                                        <td>{{ ($item->status == 1 ? "Ativo" : "Inativo") }}</td>
                                        <td>{{ $item->codigo_cupom }}</td>
                                        <td>{{ ($tipo_cupom[$item->tipo_cupom_desconto]) ? $tipo_cupom[$item->tipo_cupom_desconto] : ' - ' }}</td>
                                        <td>
                                            {{ ($item->tipo_cupom_desconto == 2) ? 'R$ '. number_format( $item->valor , 2, ',', '.') : (int)$item->valor}}
                                        </td>
                                        <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_inicial))) }}</td>
                                        <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_final))) }}</td>
                                        <td nowrap>
                                            <form action=""></form>
                                            {{ Form::open(['method' => 'POST', 'url' => '/admin/cupom/deletarrelacionamentos', 'style' => 'display:inline;']) }}
                                            <input type="hidden" name="id" value="{{$item->id}}">
                                            <input type="hidden" name="tipo" value="4">
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
                    <div id="alunos-sem-cadastro" class="tab-pane fade">
                        <div id="bloco_alunos-sem-cadastro">
                            <div class="alunos-sem-cadastro" data-contador="1">
                                <br>
                                <br>
                                <h2 class="table"><span>Adicionar Usuário sem cadastro</span></h2>
                                <a href="{{ url('admin/download/modelo_importar_usuarios_sem_cadastro.xlsx') }}" class="label label-primary">Download do modelo de Planilha</a>
                                <hr class="hr" />
                                {{ Form::label('Arquivo') }}
                                {{ Form::file('arquivo_excel',['accept'=>'.xlsx , .xls']) }}
                                <hr/>
                            </div>
                        </div>

                        @if(isset($cupom_alunos_sem_registro) && count($cupom_alunos_sem_registro) > 0)
                            <table cellpadding="0" cellspacing="0" border="0" id="table-alunos-sem-cadastro" class="table table-bordered table-striped display">
                                <thead>
                                <th>RA</th>
                                <th>CPF</th>
                                <th>Nome Aluno</th>
                                <th>E-mail</th>
                                <th>Status</th>
                                <th>Código</th>
                                <th>Tipo</th>
                                <th>Número de Usos</th>
                                <th>Valor</th>
                                <th>Data Inicial</th>
                                <th>Data Final</th>
                                <th>Ações</th>
                                </thead>
                                <tbody>
                                @foreach($cupom_alunos_sem_registro as $item)
                                    <tr>
                                        <td>{{ $item->ra }}</td>
                                        <td>{{ $item->cpf }}</td>
                                        <td>{{ $item->nome }}</td>
                                        <td>{{ $item->email }}</td>
                                        <td>{{ ($item->status == 1 ? "Ativo" : "Inativo") }}</td>
                                        <td>{{ $item->codigo_cupom }}</td>
                                        <td>{{ $tipo_cupom[$item->tipo_cupom_desconto] }}</td>
                                        <td>{{$item->numero_usos}}</td>
                                        <td>
                                            {{ ($item->tipo_cupom_desconto == 2) ? 'R$ '. number_format( $item->valor , 2, ',', '.') : (int)$item->valor}}
                                        </td>
                                        <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_inicial))) }}</td>
                                        <td>{{ implode('/', array_reverse(explode('-', $item->data_validade_final))) }}</td>
                                        <td nowrap>
                                            <form action=""></form> <!-- Único jeito pro form abaixo aparecer, sem essa tag, o form a seguir não aparece -->
                                            {{ Form::open(['method' => 'POST', 'url' => '/admin/cupom/deletarrelacionamentos', 'style' => 'display:inline;']) }}
                                                <input type="hidden" name="id" value="{{$item->id}}">
                                                <input type="hidden" name="tipo" value="5">
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
                    <div id="input-hiddens">

                    </div>
                </div>

                <hr/>

                <div class="form-group">
                    <a class="btn btn-default btnPrevious">Voltar</a>
                    {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
                </div>
            </div>
        </div>
        @if(!Request::is('*/editar'))
            <a class="btn btn-default cancel">Cancelar</a>
        @endif
        {{ Form::close() }}
	</div>
@endsection

@push('js')
	<script type="text/javascript">
		$(document).ready(function () {
		    $("#filtroAlunos").hide();
		    $("#novosAlunos").hide();
            $(document).on("click", "#buscarAlunos", function(e){
                e.preventDefault();
                $("#alunosFiltrados").html("");

                $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                $.ajax({
                    type: 'POST',
                    url: '/admin/cupom/getAlunosForm',
                    data: {
                        buscaAluno: $("#textBuscaAluno").val(),
                    },
                    dataType: 'json'
                }).done(function (data) {
                    $("#filtroAlunos").show();
                    var option = '';
                    $.each(data, function (index, item) {
                        item.cpf = item.cpf != null ? item.cpf : '';
                        option += "<option fullName='"+item.full_name+"' email='"+item.email+"' cpf='"+item.cpf+"' value='"+item.id+"'>"+item.full_name+" - "+item.email+" - "+item.cpf+"</option>";
                        
                        $("#alunosFiltrados").append(option);
                    });
                    $('#alunosFiltrados').select2({
                        allowClear: true,
                        tags: true,
                    });
                });
            });

            $(document).on("click", "#adicionarAlunos", function(e){
                e.preventDefault();

                $("#novosAlunos").show();
                var id = $("#alunosFiltrados option:selected").val();
                var fullName = $("#alunosFiltrados option:selected").attr('fullName');
                var email = $("#alunosFiltrados option:selected").attr('email');
                var cpf = $("#alunosFiltrados option:selected").attr('cpf');
                
                var option = "<option selected='selected' value='"+id+"'>"+fullName+" - "+email+" - "+cpf+"</option>";
                
                if($("#slcNovosAlunos option[value='"+id+"']").length == 0) {
                    $("#slcNovosAlunos").append(option);
                    $('#slcNovosAlunos').select2({
                        allowClear: true,
                        tags: true,
                    });
                }
            });
            
            let inputs = null;
            $('.myselect').select2({
                allowClear: true,
                tags: true,
            });
            @if(isset($cupom->tipo_cupom_desconto) && (!$cupom->tipo_cupom_desconto OR $cupom->tipo_cupom_desconto == 1))
                inputs = document.getElementById('box_moeda').getElementsByTagName('input');

                for(let i = 0; i < inputs.length ; i++)
                    inputs[i].disabled = true
            @else
                inputs = document.getElementById('box_desconto').getElementsByTagName('input');

                for(let i = 0; i < inputs.length ; i++)
                    inputs[i].disabled = true
            @endif

            $( "#table-alunos").addClass( "dataTable" );

            $('.btnNext').click(function(){
                let nextTab = $('.nav-tabs > .active').next('li').find('a')
                nextTab.attr("data-toggle", "tab").trigger('click');
            });
            $('.btnPrevious').click(function(){
                $('.nav-tabs > .active').prev('li').find('a').trigger('click');
            });

            let tabs = ['alunos'];
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
                    let target = $(e.target).attr("href") // activated tab
                    let size = tabs.length;

                    let tabela = $('.dataTable').DataTable();
                    tabela.destroy()
                    let id = target.split('#');
                    $( ".display" ).removeClass( "dataTable" );

                    $( "#table-" + id[1]).addClass( "dataTable" );

                    if (!tabs.includes(id[1])) {
                        tabs.push(id[1])
                    }

                    if (size < tabs.length) {
                        $('.dataTable thead tr').clone(true).appendTo('.dataTable thead');
                        $('.dataTable thead tr:eq(1) th').each(function () {
                            var title = $(this).text();
                            $(this).html('<input type="text" class="form-control" placeholder="' + title + '" />');
                        });

                        var table = $('.dataTable').DataTable({
                            "order": [[0, "desc"]], "language": {
                                "sEmptyTable": "Nenhum registro encontrado",
                                "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                                "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                                "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                                "sInfoPostFix": "",
                                "sInfoThousands": ".",
                                "sLengthMenu": "_MENU_ resultados por página",
                                "sLoadingRecords": "Carregando...",
                                "sProcessing": "Processando...",
                                "sZeroRecords": "Nenhum registro encontrado",
                                "sSearch": "Pesquisar por:",
                                "oPaginate": {
                                    "sNext": "Próximo",
                                    "sPrevious": "Anterior",
                                    "sFirst": "Primeiro",
                                    "sLast": "Último"
                                },
                                "oAria": {
                                    "sSortAscending": ": Ordenar colunas de forma ascendente",
                                    "sSortDescending": ": Ordenar colunas de forma descendente"
                                },
                                "select": {
                                    "rows": {
                                        "_": "Selecionado %d linhas",
                                        "0": "Nenhuma linha selecionada",
                                        "1": "Selecionado 1 linha"
                                    }
                                }
                            },
                            "orderCellsTop": true,
                            "responsive": true,
                            "fixedHeader": true,
                        });

                        if (table.columns() && table.columns().length > 0) {
                            table.columns().eq(0).each(function (colIdx) {
                                $('input', $('.dataTable thead tr:eq(1) th')[colIdx]).on('keyup change', function () {
                                    table
                                        .column(colIdx)
                                        .search(this.value)
                                        .draw();
                                });
                            });
                        }
                    }
                });
            });

			$('#marca_desconto').click(function() {
			    //console.log('teste')
				$('#box_moeda').hide();
                let inputs = document.getElementById('box_moeda').getElementsByTagName('input');

                for(let i = 0; i < inputs.length ; i++)
                    inputs[i].disabled = true


                inputs = document.getElementById('box_desconto').getElementsByTagName('input');

                for(let i = 0; i < inputs.length ; i++)
                    inputs[i].disabled = false
				$('#box_desconto').show();
				$('#valor').val($('#valor_percentual').val());
			});

			$('#marca_moeda').click(function() {
                //console.log('teste 2')
				$('#box_desconto').hide();
                let inputs = document.getElementById('box_desconto').getElementsByTagName('input');

                for(let i = 0; i < inputs.length ; i++)
                    inputs[i].disabled = true


                inputs = document.getElementById('box_moeda').getElementsByTagName('input');

                for(let i = 0; i < inputs.length ; i++)
                    inputs[i].disabled = false
				$('#box_moeda').show();
				$('#valor').val($('#valor_moeda').val());
			});

			$('.moeda').mask('#.##0,00', {reverse: true});

			$('#valor_moeda').change(function() {
				$('#valor').val($(this).val());
			});

			$('#valor_percentual').change(function() {
				$('#valor').val($(this).val());
			});

		})
        function marcarTodas() {
            $(this).prop('checked', !$(this).prop('checked'));
            $('.marcar').prop("checked", $(this).prop("checked"));
        }
	</script>
@endpush
    
