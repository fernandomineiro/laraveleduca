@section('styles')
    <style>
        /*______________________________________page assinaturas______________________________*/
        #page-assinaturas h2 {
            margin-top: 50px;
        }
        #page-assinaturas #description {
            max-width: 800px;
        }
        #page-assinaturas .badge {
            margin-top: 20px;
        }
        #page-assinaturas .badge p {
            white-space: normal;
            font-family: encodeSansCondensed-Medium;
            margin-top: 30px;
            color: #3e3e3e;
        }
        #page-assinaturas #meet_plans {
            margin-top: 50px;
        }
        #page-assinaturas #meet_plans h3 {
            font-family: encodeSansCondensed-Medium;
            margin-bottom: 30px;
        }
        #page-assinaturas #meet_plans .plan {
            margin-top: 30px;
        }
        #page-assinaturas #meet_plans .plan .box-shadow {
            background-color: #F2EFEF;
            padding: 25px;
            box-shadow: 0 0 5px 4px #e3e3e3 !important;
        }
        #page-assinaturas #meet_plans .plan .box-shadow h4 {
            color: #F2A628;
            font-family: encodeSansCondensed-Bold;
            margin-bottom: 40px;
            position: relative;
        }
        #page-assinaturas #meet_plans .plan .box-shadow h4 .subtitle {
            color: #F2A628;
            font-family: encodeSansCondensed-Bold;
            font-size: 14px;
            position: absolute;
        }
        #page-assinaturas #meet_plans .plan .box-shadow .feature {
            display: flex;
            margin-top: 20px;
        }
        #page-assinaturas #meet_plans .plan .box-shadow .feature .check {
            width: 10%;
            color: #f2652a;
        }
        #page-assinaturas #meet_plans .plan .box-shadow .feature .feature-text {
            width: 90%;
            font-family: encodeSansCondensed-Medium;
        }
        #page-assinaturas #meet_plans .plan .box-shadow .payment {
            background-color: #FB0616;
            margin-top: 40px;
            padding: 10px;
            font-family: encodeSansCondensed-Regular;
            text-align: center;
        }
        #page-assinaturas #meet_plans .plan .box-shadow .payment a {
            color: white !important;
        }
        #page-assinaturas #meet_trilhas {
            margin-top: 100px;
        }
        #page-assinaturas #meet_trilhas .slickear {
            display: flex;
            padding-left: 10px;
        }
        #page-assinaturas #meet_trilhas h3 {
            font-family: encodeSansCondensed-SemiBold;
            color: #F2652A;
            font-size: 20px;
            margin-bottom: 30px;
        }
        #page-assinaturas #meet_trilhas .outter {
            width: 150px;
            height: 220px;
            padding: 10px;
            display: flex !important;
            justify-content: center;
        }
        #page-assinaturas #meet_trilhas .outter a {
            height: 220px !important;
            display: flex;
            width: 150px;
        }
        #page-assinaturas #meet_trilhas .inner {
            color: white !important;
            padding: 60px 20px;
            text-align: center;
            width: 150px;
            height: 220px;
        }
        #page-assinaturas #meet_trilhas .inner .name {
            font-family: encodeSansCondensed-SemiBold;
            font-size: 20px;
            margin-top: 20px;
        }
        #page-assinaturas #meet_trilhas .inner .icon {
            font-size: 50px;
        }
        .card {
            position: relative;
            display: flex;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
            background-clip: border-box;
            border: 1px solid rgba(0, 0, 0, 0.125);
            border-radius: 0.25rem;
        }
        div.dataTables_wrapper div.dataTables_paginate {
            margin-top: 25px!important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0px;
        }
        table.dataTable tbody tr.selected td {
            background-color: #B0BED9 !important;
        }
    </style>
@endsection
<div class="row" id="app">
    <div class="col-md-12">
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-8 col-sm-5 col-lg-5">
                    {{Form::label('titulo', 'Título:', array('class' => 'awesome'))}}
                    <span id="faltaTitulo"></span>
                    {{Form::text('titulo', null, [ 'class' => 'form-control', 'maxlength' => 60, 'onkeyup' => 'countChar(this, 1, 60)'])}}
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    {{Form::label('facildade[]', 'Projeto:', array('class' => 'awesome'))}}
                    {{Form::select('faculdade[]', $faculdades, $projetos,
                        [
                            'multiple' => 'multiple', 'id' => 'faculdades', 'name' => 'faculdades[]',
                            'class' => 'form-control faculdadeSelect', 'allowClear' => true,
                        ]
                    )}}
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    {{Form::label('descricao', 'Descrição:', array('class' => 'awesome'))}}
                    <span id="faltaDescricao"></span>
                    {{Form::textarea('descricao', null,
                        [ 'class' => 'form-control', 'id' => 'ckeditor', 'maxlength' => 300, 'onkeyup' => 'countChar(this, 2, 300)']
                    )}}
                </div>
            </div>

            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-3 col-sm-3 col-lg-2">
                    {{Form::label('valor', 'Preço:', array('class' => 'awesome'))}}
                    {{Form::text('valor', null, [ 'class' => 'form-control moeda', 'id' => 'preco_total'])}}
                </div>
                <div class="col-md-4 col-sm-3 col-lg-2">
                    {{Form::label('valor_de', 'Preço de venda:', array('class' => 'awesome'))}}
                    {{Form::text('valor_de', null, [ 'class' => 'form-control moeda' ])}}
                </div>
                <div class="col-md-5 col-sm-5 col-lg-5">
                    {{Form::label('tipo_periodo', 'Tipo de plano:', array('class' => 'awesome'))}}
                    {{Form::select('tipo_periodo', $lista_periodos, null, ['class' => 'form-control'])}}
                </div>
            </div>
            <div class="form-group">
                {{ Form::label('Grátis para') }}
                <br />
                <div class="row">
                    <div class="col-md-12">
                        <input name="selecionar_todas_gratis" type="checkbox" id="selecionar_todas_gratis" value="" onclick="marcarGratisTodas();"> Marcar Todas <br />
                    </div>
                    @foreach($lista_faculdades as $key => $item)
                        <div class="col-md-12">
                            {{ Form::checkbox('gratis[' . $key . '][fk_faculdade]', (isset($item['id']) ? $item['id'] : ''), (isset($item['gratis']) && ($item['gratis'] == '1')) ? true : false, ['class' => 'marcar-gratis', 'id' => $key])}}
                            {{ $item['descricao'] }}
                        </div>
                    <br />
                    @endforeach
                    <hr />
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-4 col-sm-3 col-lg-3">
                    {{Form::label('tipo_liberacao', 'Opção de Liberação:', array('class' => 'awesome'))}}
                    {{Form::select('tipo_liberacao',
                            [1 => 'Liberação Total', 2 => 'Liberação Gradativa', 3 => 'Liberação Parcial'],
                            null,
                            ['class' => 'form-control', 'id' => 'tipo-liberacao']
                    )}}
                </div>

                <div style="@if (empty($assinatura) || $assinatura->tipo_liberacao != 2 ) display: none; @endif" id="box-liberar-gradual">
                    <div class="col-md-5 col-sm-3 col-lg-3">
                        {{Form::label('periodo_em_dias', 'Liberar a cada "x" dias ', array('class' => 'awesome'))}}
                        {{Form::number('periodo_em_dias', null, [ 'class' => 'form-control'])}}
                    </div>

                    <div class="col-md-3 col-sm-3 col-lg-3">
                        {{Form::label('periodo_em_dias', '"x" cursos ', array('class' => 'awesome'))}}
                        {{Form::number('qtd_cursos', null, [ 'class' => 'form-control'])}}
                    </div>
                </div>
            </div>

            <div class="row">&nbsp;</div>

            <input type="hidden" name="status" id="status" value="1" />
            <div class="row">&nbsp;</div>
        </div>
        <div class="col-md-4" id="page-assinaturas" style="display: none">
            <div id="meet_plans" >
                <div class="plan">
                    <div class="box-shadow card" style="min-height: 456px;width: 100%;display: flex;flex-direction: column;">
                        <a>
                            <h4 style="margin-bottom:0;">
                                <span id="assinaturaTitle"></span>
                               <div class="subtitle" style="display:none;"></div>
                           </h4>
                        </a>
                        <div  class="features card-body">
                            <div class="feature" id="assinaturaDescription" style="min-height: 295px">

                            </div>
                        </div>
                        <div class="payment card-bottom">
                            <a class="assinatura-link" href="javascript:void(0)">
                                <span >
                                    <b >assine agora por
                                        <span class="value">R$&nbsp;</span>
                                    </b>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="box-liberar-parcial" class="col-md-12" style="@if (empty($assinatura) || $assinatura->tipo_liberacao == 1 ) display: none; @endif">
        <div id="input-hiddens">

        </div>
        <div class="col-md-7">
            <div class="box" style="overflow-y: scroll; max-height: 600px;">
                <div class="box-header padding20">
                    <div class="form-group">
                        <label for="nomecurso">Nome Curso</label>
                        <input class="form-control" type="text" name="nomecurso" id="nomecurso">
                    </div>
                    <div class="form-group col-md-6" style="padding: 15px 5px 5px 0px;">
                        <label for="categoria">Categoria</label>
                        <select class="form-control" name="categoria" id="categoria">
                            <option value="">Todas</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6" style="padding: 15px 0px 5px 15px;">
                        <label for="categoria">Professores</label>
                        <select class="form-control" name="professor" id="professor">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <button  class="btn btn-success right" id="pesquisar" onclick="pesquisa()">Pesquisar</button>
                </div>
                <div class="box-body">
                    <div id="resultados">
                        <table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
                            <th>#ID</th>
                            <th>Nome do Curso</th>
                            <th>Professor</th>
                            <th>Tipo</th>
                            <th>Preço</th>
                            <th>Ações</th>

                            <tbody id="table_cursos">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="box" style="min-height: 350px;">
                <div class="box-header padding20">
                    <h2 class="table"><span>Cursos Selecionados</span></h2>
                </div>
                <div class="box-body">
                    <ul class="list-group" id="assinaturas" reversed>

                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        let pageURL = window.location.origin;
        let faculdades = [];
        let cursos = [];
        let assinaturaCursos = [];
        let professores = [];
    
        @if(Request::is('*/editar'))
            buscarCursosAssinatura({{$assinatura['id']}})
        @endif
    
        $(document).ready(function () {
            CKEDITOR.replace('descricao', {
                toolbar: [{ name: 'paragraph', items: ['NumberedList', 'BulletedList']}]
            });
            CKEDITOR.instances['ckeditor'].on('change',function(event){
                var textLimit = 300;
                var str = CKEDITOR.instances['ckeditor'].editable().getText();
                countChar(event, 2, textLimit, str.replace(/[\x00-\x1F\x7F-\x9F]/g, "").length);
                $('#assinaturaDescription').empty();
                $('#assinaturaDescription').html(CKEDITOR.instances['ckeditor'].getData());
                if (str.length >= textLimit) {
                    countChar(event, 2, textLimit, str.slice(0, textLimit).length)
                    CKEDITOR.instances['ckeditor'].setData(str.slice(0, textLimit));
                    return false;
                }
    
            });
    
            $('.moeda').mask('#.##0,00', { reverse: true });
    
            $('.faculdadeSelect').select2();
    
            $('#tipo-liberacao').change(function () {
                $('#box-liberar-gradual').hide();
                $('#box-liberar-parcial').hide();
                if ($(this).val() == 2) {
                    $('#box-liberar-gradual').show();
                    $('#box-liberar-parcial').show();
                }
                if ($(this).val() == 3 || $(this).val() == 2) {
                    $('#box-liberar-parcial').show();
                }
            });
    
            if ($('#fk_tipo_assinatura').val() == 2) {
                $('#tipo-liberacao').val(2);
                $('#tipo-liberacao').trigger('change');
                $('#tipo-liberacao').find('option').get(0).remove();
            }
    
            $('#titulo').trigger('keyup');
            $('#ckeditor').trigger('keyup');
    
            $('#pesquisar').click(function (e) {
                e.preventDefault()
                pesquisa()
            });
    
            buscaCategorias();
            buscaProfessores();
            buscaCursos(null, null, null, null);
        });
    
        countChar = function(event, tipo, max, len) {
    
            if (len == null) len = event.value.length
            let el;
            let el1;
            if (tipo == 1) {
                el = $('#faltaTitulo');
                el1 = $('#assinaturaTitle');
                el1.empty();
                el1.html(event.value);
            } else if(tipo == 2) {
                el = $('#faltaDescricao');
                // el1 = $('#assinaturaDescription');
            }
            el.empty();
            el.append( len + '/' + max);
    
        };
    
        function pesquisa() {
            buscaCursos(faculdades, $('#categoria').val(), $('#professor').val(), $('#nomecurso').val())
        };
    
        function buscaCursos(fk_faculdades, categoria, professor, nome) {
            let data = {
                faculdades: fk_faculdades,
                search: nome,
                categoria_id: categoria,
                professor_id: professor
            };
            Object.keys(data).forEach((key) => (data[key] == null) && delete data[key]); // remove propriedades vazias
            $.ajax({
                url: pageURL + '/api/curso/search',
                type : 'POST',
                data: data,
                success : function(data) {
                    let dados = JSON.parse(JSON.stringify(data.items));
                    dados = Object.keys(dados).map((key) => dados[key]);
                    dados = dados.filter(x => !assinaturaCursos.some(assinaturaCurso => assinaturaCurso.id === x.id));
                    cursos = dados;
                    montaTabelaCursos(dados);
                },
                error : error => console.log(error)
            });
        }
        function buscaCategorias(id) {
            $.ajax({
                url: pageURL + '/api/categorias/'+0,
                type : 'GET',
                success : (data) => montaSelectsPesquisa(data.items, 1),
                error : error => console.log(error)
            });
        }
        function buscaProfessores() {
            $.ajax({
                url: pageURL + '/api/professores',
                type : 'GET',
                success : function(data) {
                    professores = data.items;
                    montaSelectsPesquisa(data.items, 2);
                },
                error : error => console.log(error)
            })
        }
        function montaSelectsPesquisa (dados, tipo) {
            let html = '';
            if (tipo === 1) {
                if (dados.length > 0) {
                    dados.forEach(dado => {
                        html += '<option value="' + dado.id + '">' + dado.titulo + '</option>'
                    })
                } else {
                    html += '<p class="empty-categoria">Projetos não possuem categorias associadas.</p>'
                }
                $('#categoria').append(html);
            } else {
                if (dados.length > 0) {
                    dados.forEach(dado => {
                        html += '<option value="' + dado.id + '">' + dado.nome_professor + '</option>'
                    })
                } else {
                    html += '<tr class="empty-professor">Projetos não possuem professores associados.</tr>'
                }
                $('#professor').append(html);
            }
        }
    
        function montaTabelaCursos(dados) {
            $('#table_cursos').empty();
            let html = ''
            if (dados.length > 0) {
                dados.forEach(dado => {
                    let valor = (dado.valor_de != null) ? 'R$ ' + dado.valor_de.replace('.', ',') : '-'
                    let professor = (dado.nome_professor && dado.sobrenome_professor) ? dado.nome_professor + ' ' + dado.sobrenome_professor : ' - '
                    html += '<tr>'
                    html += '<td>'+dado.id+'</td>'
                    html += '<td>'+dado.titulo+'</td>'
                    html += '<td>'+  professor +'</td>'
                    html += '<td>'+dado.curso_tipo+'</td>'
                    html += '<td>'+valor+'</td>'
                    html+= '<td value="'+dado.id+'"><btn class="btn btn-default" onclick="incluirNaAssinatura('+ dado.id +')">Adicionar</btn></td>'
                    html += '</tr>'
                })
            } else {
                html += '<p class="empty-result">Nenhum curso encontrado para a sua pesquisa.</p>'
            }
            $('#table_cursos').append(html);
        }
    
        function incluirNaAssinatura (dado) {
            let curso = cursos.filter(curso => curso.id === dado);
            assinaturaCursos.push(curso[0]);
            calculaTotalPrecoAssinatura();
            cursos = cursos.filter(curso => curso.id !== dado);
            montaTabelaCursos(cursos);
            renderizaCursoAssinatura();
        }
        function renderizaCursoAssinatura() {
            $('#assinaturas').empty();
            $('#input-hiddens').empty();
            let html = ''
            let html1 = ''
            if (assinaturaCursos.length > 0) {
                for (let i = assinaturaCursos.length - 1; i >= 0; i--) {
                    html += '<li class="list-group-item">'
                    html += '<a href="javascript:;" class="btn btn-danger" style="margin: 5px;" onclick="removeCursoAssinatura('+ i +')"><i class="fa fa-trash"  aria-hidden="true"></i></a>'
                    html1 += '<input type="hidden" name="fk_curso[' + assinaturaCursos[i].id + ']" value="' + assinaturaCursos[i].id + '"/>'
                    html += '<span>'+ '#' + assinaturaCursos[i].id + ' - ' + assinaturaCursos[i].titulo + ' - ' + assinaturaCursos[i].curso_tipo + '</span>'
                    html += '</li>'
                }
            }
            $('#assinaturas').append(html);
            $('#input-hiddens').append(html1);
        }
    
        function removeCursoAssinatura(index) {
            let curso = assinaturaCursos[index];
            cursos.push(curso);
            assinaturaCursos.splice(index, 1);
    
            montaTabelaCursos(cursos);
            renderizaCursoAssinatura();
            calculaTotalPrecoAssinatura();
        }
    
        function calculaTotalPrecoAssinatura() {
            let preco_total = 0;
            assinaturaCursos.forEach(e => { if (e.valor_de) preco_total += parseFloat(e.valor_de); });
            setTimeout(() => {
                preco_total = '' + parseFloat(preco_total).toFixed(2)
                $('#preco_total').val(preco_total.replace('.', ','));
            }, 100)
        }
    
        function buscarCursosAssinatura(id) {
            $.ajax({
                url: pageURL + '/api/assinatura-cursos/'+id,
                type : 'GET',
                success : function(data) {
                    assinaturaCursos = data.items;
                    renderizaCursoAssinatura();
                    calculaTotalPrecoAssinatura();
                },
                error : error => console.log(error)
            })
        }
    
        function marcarGratisTodas() {
            if ($('#selecionar_todas_gratis').is(':checked')){
                $('.marcar-gratis').each(function() {
                    $(this).prop('checked', true);
                });
            } else {
                $('.marcar-gratis').each(function() {
                    $(this).prop('checked', false);
                });
            }
        }

    </script>
@endpush 

