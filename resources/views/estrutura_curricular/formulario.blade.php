@section('styles')
    <style>
        .mb-6 {
            margin-bottom: 1.5rem;
        }
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
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-8 col-sm-5 col-lg-5">
                    {{Form::label('titulo', 'Título:', array('class' => 'awesome'))}}
                    <span id="faltaTitulo">
                        @error('titulo') {{ $message }} @enderror
                    </span>
                    {{Form::text('titulo', null, [ 'class' => 'form-control', 'maxlength' => 100, 'onkeyup' => 'countChar(this, 1, 100)'])}}
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12 col-sm-12 mb-6">
                    {{Form::label('facildade[]', 'Projeto:', array('class' => 'awesome'))}}
                    {{Form::select('faculdade[]', $faculdades, $projetos,
                        [
                            'multiple' => 'multiple', 'id' => 'faculdades', 'name' => 'faculdades[]',
                            'class' => 'form-control faculdadeSelect', 'allowClear' => true,
                        ]
                    )}}
                </div>
                <div class="col-md-4 col-sm-4">
                    {{Form::label('tipo_liberacao', 'Tipo de liberação:', array('class' => 'awesome'))}}
                    {{Form::select('tipo_liberacao', [
                            1 => 'Livre',
                            2 => 'Por data',
                            3 => 'Por sequencia de liberação',
                            4 => 'Por data e sequencia de liberação'
                        ], !empty($estrutura) ? $estrutura->tipo_liberacao: null,
                        [
                            'id' => 'tipo_liberacao', 'name' => 'tipo_liberacao',
                            'class' => 'form-control tipo_liberacao', 'allowClear' => true,
                        ]
                    )}}
                </div>
                <div class="col-md-3 col-sm-3">
                    {{Form::label('estrutura_livre_cadastro', 'Estrutura liberada no cadastro?', array('class' => 'awesome'))}}
                    {{Form::select('estrutura_livre_cadastro', [
                            0 => 'Não',
                            1 => 'Sim',
                        ], !empty($estrutura) ? $estrutura->estrutura_livre_cadastro: 0,
                        [
                            'id' => 'estrutura_livre_cadastro', 'name' => 'estrutura_livre_cadastro',
                            'class' => 'form-control estrutura_livre_cadastro', 'allowClear' => true,
                        ]
                    )}}
                </div>
                <div class="col-md-5 col-sm-5">
                    {{Form::label('fk_certificado_layout', 'Layout do certificado', array('class' => 'awesome'))}}
                    {{Form::select('fk_certificado_layout', $lista_certificados, !empty($estrutura) ? $estrutura->fk_certificado_layout: null,
                        [
                            'id' => 'fk_certificado_layout', 'name' => 'fk_certificado_layout',
                            'class' => 'form-control fk_certificado_layout', 'allowClear' => true,
                        ]
                    )}}
                </div>
            </div>

            <div class="row">&nbsp;</div>

            <input type="hidden" name="status" id="status" value="1" />
            <div class="row">&nbsp;</div>
        </div>
    </div>
    <div id="box-liberar-parcial" class="col-md-12" >
        <div id="input-hiddens">

        </div>
        <div class="col-md-7">
            <div class="box" style="overflow-y: scroll; max-height: 600px;">
                <div class="box-header padding20">
                    <div class="form-group">
                        <label for="nomecurso">Nome da disciplina</label>
                        <input class="form-control" type="text" name="nomecurso" id="nomecurso">
                    </div>
                    <div class="form-group col-md-6" style="padding: 15px 5px 5px 0px;">
                        <label for="categoria">Categoria</label>
                        <select class="form-control" name="categoria" id="categoria">
                            <option value="">Todas</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6" style="padding: 15px 5px 5px 15px;"><br>
                        <span onclick="addCursosCategoria(event)" class="btn btn-primary" style="margin-top: 5px">Adicionar Todas as disciplinas da categoria</span>
                    </div>
                    <div class="form-group col-md-6" style="padding: 15px 0px 5px 0px;">
                        <label for="categoria">Professores</label>
                        <select class="form-control" name="professor" id="professor">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6" style="padding: 15px 0px 5px 15px;">
                        <label for="categoria">Modalidade</label>
                        <select class="form-control" name="modalidade" id="modalidade">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-success right" id="pesquisar" onclick="pesquisa()">Pesquisar</button>
                </div>
                <div class="box-body">
                    <div id="resultados">
                        <table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
                            <th>#ID</th>
                            <th>Nome da Disciplina</th>
                            <th>Carga Horária</th>
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
            <div class="form-group">
                <label for="tempoTotal">Tempo total:</label>
                <input type="text" class="form-control" disabled id="tempoTotal">
            </div>
        </div>
        <div class="col-md-5">
            <div class="box" style="min-height: 350px;">
                <div class="box-header padding20">
                    <h2 class="table"><span>Disciplinas Selecionadas</span></h2>
                </div>
                <div class="box-body" id="tableEstruturaCurricular">

                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script src="/js/jquery-ui.js"></script>
    <script>
        let pageURL = window.location.origin;
        let faculdades = [];
        let cursos = [];
        let assinaturaCursos = [];
        let professores = [];
        let modalidades = [];
    
        @if(Request::is('*/editar'))
            buscarCursosAssinatura({{$estrutura['id']}})
        @endif
    
        $(document).ready(function () {
    
            $('.faculdadeSelect').select2();
    
            $('#titulo').trigger('keyup');
            $('#ckeditor').trigger('keyup');
    
            $('#pesquisar').click(function (e) {
                e.preventDefault();
                pesquisa();
            });
    
            buscaCategorias();
            buscaProfessores();
            buscaModalidades();
            buscaCursos(null, null, null, null, null);
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
            buscaCursos(faculdades, $('#categoria').val(), $('#professor').val(), $('#nomecurso').val(), $('#modalidade').val())
        };
    
        function buscaCursos(fk_faculdades, categoria, professor, nome, modalidade) {
            let data = {
                faculdades: fk_faculdades,
                search: nome,
                categoria_id: categoria,
                professor_id: professor,
                trazerCategorias: true
            };
            Object.keys(data).forEach((key) => (data[key] == null) && delete data[key]); // remove propriedades vazias
    
            let url = pageURL + '/api/curso/search';
            if (modalidade) {
                url = url + '/' + modalidade;
            }
    
            $.ajax({
                url: url,
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
        function buscaModalidades() {
            $.ajax({
                url: pageURL + '/api/modalidades',
                type : 'GET',
                success : function(data) {
                    modalidades = data.items;
                    modalidades.forEach(dado => {
                        $('#modalidade').append('<option value="' + dado.id + '">' + dado.titulo + '</option>');
                    });
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
                    html += '<td>'+dado.duracao_total+'</td>'
                    html += '<td>'+  professor +'</td>'
                    html += '<td>'+dado.curso_tipo+'</td>'
                    html += '<td>'+valor+'</td>'
                    html+= '<td value="'+dado.id+'"><btn class="btn btn-default" onclick="incluirNaAssinatura('+ dado.id +')">Adicionar</btn></td>'
                    html += '</tr>'
                })
            } else {
                html += '<p class="empty-result">Nenhuma disciplina encontrada para a sua pesquisa.</p>'
            }
            $('#table_cursos').append(html);
        }
    
        function incluirNaAssinatura (dado) {
            $.ajax({
                url: pageURL + '/api/estrutura-curricular-cursos/cursosCategoria/' + dado,
                type : 'GET',
                success : function(data) {
    
                    data.items.forEach(dado => {
                        assinaturaCursos.push(dado);
                    });
    
                    calculaTotalPrecoAssinatura();
                    cursos = cursos.filter(curso => curso.id !== dado);
                    montaTabelaCursos(cursos);
                    renderizaCursoAssinatura();
                },
                error : error => console.log(error)
            });
        }
    
        function addCursosCategoria(event) {
    
            event.preventDefault();
            if (!$('#categoria').val()) {
                alert('nenhuma categoria selecionada');
                return false;
            }
    
            $.ajax({
                url: pageURL + '/api/estrutura-curricular-cursos/cursosPorCategoria/' + $('#categoria').val(),
                type : 'GET',
                success : function(data) {
    
                    data.items.forEach(dado => {
                        assinaturaCursos.push(dado);
                    });
    
                    renderizaCursoAssinatura();
                },
                error : error => console.log(error)
            });
        }
    
        function renderizaCursoAssinatura() {
            $('#tableEstruturaCurricular').empty();
            $('#input-hiddens').empty();
            let html = '';
            let html1 = '';
    
            assinaturaCursos.sort(
                firstBy("curso_tipo", { ignoreCase: true }, -1)
                .thenBy("Categoria", { ignoreCase: true }, -1)
                .thenBy("ordem", -1)
            );
    
            if (assinaturaCursos.length > 0) {
                let modalidade = '';
                let categoria = '';
                let cargaHorariaTotal = 0;
                let totalHorario = 0;
                let ordemCurso = 1;
                for (let i = assinaturaCursos.length - 1; i >= 0; i--) {
    
                    if (modalidade != assinaturaCursos[i].curso_tipo) {
                        modalidade = assinaturaCursos[i].curso_tipo;
    
                        if (cargaHorariaTotal != 0) {
                            html += '<li class="list-group-item pin"><h7> Duração Total: ' + cargaHorariaTotal + '</h7></li></ul>';
    
                            cargaHorariaTotal = 0;
                            ordemCurso = 1;
                            // categoria = '';
                        }
    
                        html += '<h2>' + modalidade + '</h2>';
                    }
    
                    if (categoria != assinaturaCursos[i].Categoria) {
                        categoria = assinaturaCursos[i].Categoria;
    
                        if (cargaHorariaTotal != 0) {
                            html += '<li class="list-group-item pin"><h7> Duração Total: ' + cargaHorariaTotal + '</h7></li></ul>';
    
                            cargaHorariaTotal = 0;
                            ordemCurso = 1;
                        }
    
                        html += '<ul class="list-group sortable" ><li class="list-group-item pin"><h5>' + categoria + '</h5></li>';
                    }
    
    
                    if (assinaturaCursos[i].duracao_total) {
                        cargaHorariaTotal = addTimes(cargaHorariaTotal, assinaturaCursos[i].duracao_total);
                        totalHorario = addTimes(totalHorario, assinaturaCursos[i].duracao_total);
                    }
    
                    let dataInicio = moment(assinaturaCursos[i].data_inicio);
    
                    html += '<li class="list-group-item" id="' + assinaturaCursos[i].id + '' + assinaturaCursos[i].categoria_id + '">'
                    html += '<a href="javascript:;" class="btn btn-danger" style="margin: 5px;" onclick="removeCursoAssinatura('+ i +')"><i class="fa fa-trash"  aria-hidden="true"></i></a>'
                    html += '<input type="hidden" name="fk_curso[' + assinaturaCursos[i].id + '' + assinaturaCursos[i].categoria_id + '][id]" value="' + assinaturaCursos[i].id + '"/>';
                    html += '<input type="hidden" name="fk_curso[' + assinaturaCursos[i].id + '' + assinaturaCursos[i].categoria_id + '][ordem]" class="ordem" value="' + ( assinaturaCursos[i].ordem ? assinaturaCursos[i].ordem : ordemCurso ) + '"/>';
                    html += '<input type="hidden" name="fk_curso[' + assinaturaCursos[i].id + '' + assinaturaCursos[i].categoria_id + '][fk_categoria]" value="' + ( assinaturaCursos[i].categoria_id ) + '"/>';
                    html += '<input type="hidden" name="fk_curso[' + assinaturaCursos[i].id + '' + assinaturaCursos[i].categoria_id + '][modalidade]" value="' + ( assinaturaCursos[i].modalidade ) + '"/>';
                    html += '<span>'+ '#' + assinaturaCursos[i].id + ' - ' + assinaturaCursos[i].titulo + ' - ' + assinaturaCursos[i].curso_tipo + '</span>'
                    html += '<div class=\'input-group date datetimepicker5\'>';
                    html += '<input type=\'text\' class="form-control" name="fk_curso[' + assinaturaCursos[i].id + '' + assinaturaCursos[i].categoria_id + '][data_inicio]" value="' + ( dataInicio.format('DD/MM/YYYY') ) + '"/>';
                    html += '<span class="input-group-addon">';
                    html += '<span class="glyphicon glyphicon-calendar"></span>';
                    html += '</span>';
                    html += '</div>';
    
                    html += '</li>';
    
                    ordemCurso++;
                }
                if (cargaHorariaTotal) {
                    html += '<li class="list-group-item pin"><h7> Duração Total: ' + cargaHorariaTotal + '</h7></li></ul>';
                }
    
                $('#tempoTotal').val(totalHorario);
            }
            $('#tableEstruturaCurricular').append(html);
    
            resetSortable();
            resetDatepicker();
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
                url: pageURL + '/api/estrutura-curricular-cursos/'+id,
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
    
        function resetDatepicker() {
            $('.datetimepicker5').datetimepicker({
                locale: 'pt-br',
                format: 'L',
                // minDate: new Date()
            });
    
            $('.datetimepicker5').each(function (index, element) {
                $(element).datetimepicker( {
                    date: $($(element).children('input')[0]).val()
                } );
            })
        }
    
        function resetSortable() {
            $('.sortable').sortable({
                items: '> li:not(.pin)',
                stop: function(event, ui) {
                    var itemOrder = $(ui.item[0]).parent().sortable("toArray");
                    for (var i = 0; i < itemOrder.length; i++) {
                        $($('#' + itemOrder[i]).children('.ordem')).val(i + 1);
                    }
    
                }
            });
            $( ".sortable" ).disableSelection();
        }
    
        /**
         * Add two string time values (HH:mm:ss) with javascript
         *
         * Usage:
         *  > addTimes('04:20:10', '21:15:10');
         *  > "25:35:20"
         *  > addTimes('04:35:10', '21:35:10');
         *  > "26:10:20"
         *  > addTimes('30:59', '17:10');
         *  > "48:09:00"
         *  > addTimes('19:30:00', '00:30:00');
         *  > "20:00:00"
         *
         * @param {String} startTime  String time format
         * @param {String} endTime  String time format
         * @returns {String}
         */
        function addTimes (startTime, endTime) {
            var times = [ 0, 0, 0 ]
            var max = times.length
    
            var a = (startTime || '').split(':')
            var b = (endTime || '').split(':')
    
            // normalize time values
            for (var i = 0; i < max; i++) {
                a[i] = isNaN(parseInt(a[i])) ? 0 : parseInt(a[i])
                b[i] = isNaN(parseInt(b[i])) ? 0 : parseInt(b[i])
            }
    
            // store time values
            for (var i = 0; i < max; i++) {
                times[i] = a[i] + b[i]
            }
    
            var hours = times[0]
            var minutes = times[1]
            var seconds = times[2]
    
            if (seconds >= 60) {
                var m = (seconds / 60) << 0
                minutes += m
                seconds -= 60 * m
            }
    
            if (minutes >= 60) {
                var h = (minutes / 60) << 0
                hours += h
                minutes -= 60 * h
            }
    
            return ('0' + hours).slice(-2) + ':' + ('0' + minutes).slice(-2) + ':' + ('0' + seconds).slice(-2)
        }
    
    </script>
@endpush
