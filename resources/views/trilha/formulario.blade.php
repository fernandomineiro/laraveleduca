@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Trilha de Conhecimento</span></h2>
        @if(Request::is('*/editar'))
            <!--<a class="label label-default primary" onclick="abrirPreviaTrilha('$trilha->id')">Preview</a>-->
        @endif
		<hr class="hr" />

		@if(Request::is('*/editar'))
			{{ Form::model( $trilha, ['method' => 'PATCH', 'files' => true, 'route' => ['admin.trilha.atualizar', $trilha->id], 'id' => 'trilhaform'] ) }}
		@else
			{{ Form::open(['url' => '/admin/trilha/salvar', 'files' => true, 'id' => 'trilhaform']) }}
		@endif

        <ul class="nav nav-tabs">
            <li class="nav-item active"><a data-toggle="tab" href="#trilha">Trilha</a></li>
            @if(!Request::is('*/editar'))
                <li class="nav-item disabled"><a class="disabled" href="#trilhacurso">Cursos da Trilha</a></li>
            @else
                <li class="nav-item"><a data-toggle="tab" href="#trilhacurso">Cursos da Trilha</a></li>
            @endif
        </ul>
        <div class="tab-content">
            <div id="trilha" class="tab-pane fade in active">
                <br>
                <br>
                @if(Request::is('*/editar'))
                    <div class="form-group">
                        {{ Form::label('Status (workflow)') }}
                        {{ Form::select('status', $lista_status, (isset($trilha->status) ? $trilha->status : 1), ['class' => 'form-control']) }}
                    </div>
                @endif
                <div class="form-group">
                    {{ Form::label('Título') }} <span id="faltaTitulo"></span>
                    {{ Form::input('text', 'titulo', null, ['class' => 'form-control', 'maxlength' => 70, 'onkeyup' => 'countChar(this, 1, 70)']) }}
                    <span id=""></span>
                </div>
                <div class="form-group">
                    {{ Form::label('Descrição') }} <span id="faltaDescricao"></span>
                    {{ Form::textarea('descricao', null, ['class' => 'form-control', 'id' => 'ckeditor', 'maxlength' => 900, 'onkeyup' => 'countChar(this, 2, 900)']) }}
                </div>
                <div class="well" id="show-video">
                    <div id="renderizar_video"></div>
                    <div id="video_description"></div>
                    <div id="author_name"></div>
                    <div id="video_title"></div>
                    <div id="video_duration"></div>
                </div>

                <div class="form-group">
                    {{ Form::label('Teaser') }}
                    {{ Form::textarea('teaser', null, ['class' => 'form-control', 'style' => 'height: 40px;']) }} <a href="javascript:;" id="preview_vimeo" class="label label-success">Preview Video</a>
                </div>
                <div class="form-group">
                    {{ Form::label('Projetos') }}
                    <br />
                    <div class="row">
                        <div class="col-md-12">
                            <input name="selecionar_todas" type="checkbox" id="selecionar_todas" value="" onclick="marcarTodas();"> Marcar Todas <br />
                        </div>
                        @foreach($lista_faculdades as $key => $item)

                            <div class="col-md-12">
                                {{ Form::checkbox('fk_faculdade[' . $key . '][fk_faculdade]', (isset($item['id']) ? $item['id'] : ''), (isset($item['ativo']) && ($item['ativo'] == '1')) ? true : false, ['class' => 'marcar', 'id' => $key])}}
                                {{ $item['descricao'] }}
                            </div>
                        <br />
                        @endforeach
                        <hr />
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
                <div class="form-group">
                    {{ Form::label('Categorias') }}
                    <br />
                    @foreach($lista_categorias as $key => $item)
                    {{ Form::checkbox('fk_categoria[' . $key . ']', (isset($item['descricao']) ? $item['descricao'] : ''), (isset($item['ativo']) && ($item['ativo'] == '1')) ? true : false) }}
                    {{ $item['descricao'] }}
                    <br />
                    @endforeach
                    <hr />
                </div>
                <div class="form-group campos_full certificados" id="certificados">
                </div>
                @if(isset($trilha->imagem))
                <img src="{{URL::asset('files/trilha/imagem/' . $trilha->imagem)}}" height="100"/>
                @endif
                <br />
                <div class="well">
                    <div id="box_upload" class="row form-group">
                        {{ Form::label('Imagem da Trilha') }}
                        {{ Form::file('imagem', ['id' => 'imagem', 'onchange' => 'loadFile(event)' ]) }}
                        <img id="output"/>
                    </div>
                </div>
                <div class="form-group">
                    <a href="{{ route('admin.trilha') }}" class="btn btn-default">Voltar</a>
                    @if(Request::is('*/editar'))
                        {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
                    @else
                        <a class="btn btn-primary btnNext">Avançar</a>
                    @endif
                </div>
            </div> 
            <!-- FIM quiz --> 
            <div id="input-hiddens">

            </div>
            <div id="trilhacurso" class="tab-pane fade">
                <div class="form-group col-md-12 cursos">
                    <div class="col-md-7"  style="overflow-y: scroll; max-height: 600px;">
                        <div class="form-group col-md-12">
                            <label for="nomecurso">Nome Curso</label>
                            <input class="form-control" type="text" name="nomecurso" id="nomecurso">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="categoria">Categoria</label>
                            <select class="form-control" name="categoria" id="categoria">
                                <option value="">Todas</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="categoria">Professores</label>
                            <select class="form-control" name="professor" id="professor">
                                <option value="">Todos</option>
                            </select>
                        </div>
                        <button  class="btn btn-success right" id="pesquisar" onclick="pesquisa()">Pesquisar</button>
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
                    <div class="col-md-5">
                        <h2 class="table"><span>Cursos Selecionados</span></h2>
                        <ul class="list-group" id="trilhas" reversed>

                        </ul>
                    </div>
                </div>
                <div class="form-group">
                    {{ Form::label('Duração total da trilha') }}
                    {{ Form::input('text', 'duracao_total', isset($trilha->duracao_total) ? $trilha->duracao_total : '', ['class' => 'form-control', 'id'=> 'duracao_total', 'style' => 'width: 150px; text-align: right;']) }}
                </div>
                <div class="form-group">
                    {{ Form::label('Valor (R$)') }}
                    {{ Form::input('text', 'valor', isset($trilha->valor) ? $trilha->valor : '', ['class' => 'form-control moeda', 'style' => 'width: 150px; text-align: right;', 'id'=> 'preco_total']) }}
                </div>
                <div class="form-group">
                    {{ Form::label('Valor Venda (R$)') }}
                    {{ Form::input('text', 'valor_venda', isset($trilha->valor_venda) ? $trilha->valor_venda : '', ['class' => 'form-control moeda', 'style' => 'width: 150px; text-align: right;']) }}
                </div>
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
        let pageURL = window.location.origin;
        let portalUrl = 'http://18.215.45.177';
        let faculdades = []
        let cursos = []
        let trilhacursos = []
        let professores = []
        @if(Request::is('*/editar'))
        @foreach($lista_faculdades as $faculdade)
        @if(isset($faculdade['ativo']) && ($faculdade['ativo'] == '1'))
        pushValueFaculdades({{$faculdade['id']}})
        @endif
        @endforeach
        buscarCursosTrilha({{$trilha['id']}})
        @endif

        buscaCategorias()
        buscaProfessores()
        buscaCursos(faculdades, null, null, null)
        $(document).ready(function () {
            $('.cancel').click(function () {
                //trilhaform
                $("#trilhaform")[0].reset();
                $('#renderizar_video').empty();
                $('#video_description').empty();
                $('#author_name').empty();
                $('#video_title').empty();
                $('#trilhas').empty();
                $('#output').remove();
                $('#box_upload').append('<img id="output"/>')
                $('#show-video').hide();
                //ckeditor
                CKEDITOR.instances['ckeditor'].setData('');
                trilhacursos = []
            })
            $('.btnNext').click(function(){
                let nextTab = $('.nav-tabs > .active').next('li').find('a')
                nextTab.attr("data-toggle", "tab").trigger('click');
            });
            $('.btnPrevious').click(function(){
                $('.nav-tabs > .active').prev('li').find('a').trigger('click');
            });
            $('.moeda').mask('#.##0,00', {reverse: true});
            $('.multipla-escolha').hide();
            $('#show-video').hide();
            @if(!Request::is('*/editar'))
            $('#bloco_quiz').hide();
            @endif
            if($('#tipoquest').val() == 'ME') $('.multipla-escolha').show()
            if($('#questionario').val() == 'S') $('#bloco_quiz').show()
            if (trilhacursos.length > 0) {
                renderizaCursoTrilha()
            }

            /* buscar o curso da trilha se necessário adicionar essa funcionalidade na edição*/

            CKEDITOR.replace('descricao');
            CKEDITOR.instances['ckeditor'].on('change',function(event){
                var textLimit = 900;
                var str = CKEDITOR.instances['ckeditor'].editable().getText();
                countChar(event, 2, textLimit, str.replace(/[\x00-\x1F\x7F-\x9F]/g, "").length)
                if (str.length >= textLimit) {
                    countChar(event, 2, textLimit, str.slice(0, textLimit).length)
                    CKEDITOR.instances['ckeditor'].setData(str.slice(0, textLimit));
                    return false;
                }
            });
            $('.marcar').change(function () {
                let id = $(this).attr('id')
                let el = document.getElementById(id);
                if (el.checked == true) {
                    faculdades.push(parseInt($('#'+id).val()));
                    buscaCategorias()
                    buscaProfessores()
                } else {
                    faculdades.splice(faculdades.indexOf(parseInt($('#'+id).val())), 1);
                }
                buscaCursos(faculdades, null, null, null)
            })
            $('#tipoquest').change(function () {
                let id = $(this).val()
                if (id == 'D') {
                    $('.multipla-escolha').hide()
                } else {
                    $('.multipla-escolha').show()
                }
            })
            $('#questionario').change(function () {
                let id = $(this).val()
                if (id == 'S') {
                    $('#bloco_quiz').show()
                } else {
                    $('#bloco_quiz').hide()
                }
            })
            $('#pesquisar').click(function (e) {
                e.preventDefault()
                pesquisa()
            })
            $('#btn_incluir_quiz').click(function () {
                var qtd = $('#bloco_quiz').data('contador') + 1;
                $('#bloco_quiz').data('contador', qtd);

                var regex = new RegExp('__X__', 'g');
                var html = $('#default_quiz').html().replace(regex, qtd);

                $('#bloco_quiz').append(html);

                // call_datepicker();
            });

            $('a#preview_vimeo').click(function() {
                let vimeoId = $('textarea[name="teaser"]').val().split('/');

                vimeoId = vimeoId.filter(function (el) {
                    return el != null && el != '';
                });

                $('textarea[name="teaser"]').val('https://vimeo.com/'+ vimeoId[vimeoId.length - 1]);
                $('#show-video').show();
                $.get('https://vimeo.com/api/oembed.json?url=https://vimeo.com/'+ vimeoId[vimeoId.length - 1], function (response) {
                    $('#renderizar_video').html(response.html);
                    $('#video_description').html('Descrição: ' + response.description);
                    $('#author_name').html('Autor: ' +response.author_name);
                    $('#video_title').html('Título: ' +response.title);

                    var hours   = Math.floor(response.duration / 3600);
                    var minutes = Math.floor((response.duration - (hours * 3600)) / 60);
                    var seconds = response.duration - (hours * 3600) - (minutes * 60);

                    $('#video_duration').html('Duração: ' + [hours,minutes,seconds]
                        .map(v => v < 10 ? "0" + v : v)
                        .filter((v,i) => v !== "00" || i > 0)
                        .join(":"));
                });
            });
        })

        function pesquisa() {
            buscaCursos(faculdades, $('#categoria').val(), $('#professor').val(), $('#nomecurso').val())
        }
        function buscaCursos(fk_faculdades, categoria, professor, nome) {
            console.log(fk_faculdades)
            let data = {
                faculdades: fk_faculdades,
                search: nome,
                categoria_id: categoria,
                professor_id: professor
            }
            Object.keys(data).forEach((key) => (data[key] == null) && delete data[key]); // remove propriedades vazias
            console.log(data)
            $.ajax({
                url: pageURL + '/api/curso/search',
                type : 'POST',
                data: data,
                success : function(data) {
                    let dados = JSON.parse(JSON.stringify(data.items))
                    dados = Object.keys(dados).map((key) => dados[key])
                    dados = dados.filter(x => !trilhacursos.some(trilhacurso => trilhacurso.id === x.id));
                    cursos = dados
                    montaTabelaCursos(dados)
                },
                error : function(error)
                {
                    console.log(error)
                }
            })
        }
        function buscaCategorias(id) {
            $.ajax({
                url: pageURL + '/api/categorias/'+0,
                type : 'GET',
                success : function(data) {
                    montaSelectsPesquisa(data.items, 1);
                },
                error : function(error)
                {
                    console.log(error)
                }
            })
        }
        function buscaProfessores() {
            $.ajax({
                url: pageURL + '/api/professores',
                type : 'GET',
                success : function(data) {
                    professores = data.items;
                    montaSelectsPesquisa(data.items, 2);
                },
                error : function(error)
                {
                    console.log(error)
                }
            })
        }
        function montaSelectsPesquisa (dados, tipo) {
            let html = ''
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
        function montaSelect(dados) {
            $('#certificados').empty();
            let html = '<label>Certificados</label>'
            if (dados.length > 0) {
                html += '<select name="fk_certificado" class="form-control">'
                dados.forEach(dado => {
                    html+= '<option value="'+dado.id+'">' + dado.titulo + '</option>'
                })
                html += '</select>'
            } else {
                html += '<p class="select">Projetos não possuem certificados associados.</p>'
            }
            //   $('#certificados').append(html);
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
                    html+= '<td value="'+dado.id+'"><btn class="btn btn-default" onclick="incluirNaTrilha('+ dado.id +')">Adicionar</btn></td>'
                    html += '</tr>'
                })
            } else {
                html += '<p class="empty-result">Nenhum curso encontrado para a sua pesquisa.</p>'
            }
            $('#table_cursos').append(html);
        }
        function abrirPreviaTrilha(id) {
            window.open(portalUrl+'/#/trilha-sobre/'+id);
        }
        function incluirNaTrilha (dado) {
            let curso = cursos.filter(curso => curso.id === dado)
            trilhacursos.push(curso[0])
            calculaTotalTrilha()
            calculaTotalPrecoTrilha()
            cursos = cursos.filter(curso => curso.id !== dado)
            montaTabelaCursos(cursos)
            renderizaCursoTrilha()
        }
        function renderizaCursoTrilha() {
            $('#trilhas').empty();
            $('#input-hiddens').empty();
            let html = ''
            let html1 = ''
            if (trilhacursos.length > 0) {
                for (let i = trilhacursos.length - 1; i >= 0; i--) {
                    html += '<li class="list-group-item">'
                    html += '<a href="javascript:;" class="btn btn-danger" style="margin: 5px;" onclick="removeCursoTrilha('+ i +')"><i class="fa fa-trash"  aria-hidden="true"></i></a>'
                    html1 += '<input type="hidden" name="fk_curso[' + trilhacursos[i].id + ']" value="' + trilhacursos[i].id + '"/>'
                    html += '<span>'+ '#' + trilhacursos[i].id + ' - ' + trilhacursos[i].titulo + ' - ' + trilhacursos[i].curso_tipo + '</span>'
                    html += '</li>'
                }
            }
            $('#trilhas').append(html);
            $('#input-hiddens').append(html1);
        }
        function removeCursoTrilha(index) {
            let curso = trilhacursos[index]
            cursos.push(curso)
            trilhacursos.splice(index, 1)
            montaTabelaCursos(cursos)
            renderizaCursoTrilha()
            calculaTotalTrilha()
            calculaTotalPrecoTrilha()
        }
        function calculaTotalTrilha() {
            let duracao_total = 0
            trilhacursos.forEach(e => {
                if (e.duracao_total) {
                    let tempo = e.duracao_total.split(':')
                    if (tempo[0] && tempo[1] && tempo[2]) {
                        let segundos = parseInt(tempo[2])/60
                        duracao_total = duracao_total + parseInt(tempo[0])* 60 + parseInt(tempo[1]) + (Math.round( segundos * 1e2 ) / 1e2);
                    }
                }
            })

            setTimeout(() => {
                let horas = parseInt(duracao_total / 60)
                horas = (horas > 9) ? horas : "0" + horas
                let minutos = duracao_total % 60
                let segundos = (minutos - Math.floor(minutos)) * 60
                segundos = (segundos > 0) ? parseInt(segundos) : "00"
                minutos = (minutos > 9) ? parseInt(minutos) : "0" + parseInt(minutos)
                duracao_total = horas + ":" + minutos + ":" + segundos;
                $('#duracao_total').val(duracao_total);
            }, 100)
        }
        function calculaTotalPrecoTrilha() {
            let preco_total = 0;
            trilhacursos.forEach(e => {
                if (e.valor_de) preco_total += parseFloat(e.valor_de);
            })
            setTimeout(() => {
                preco_total = '' + parseFloat(preco_total).toFixed(2)
                $('#preco_total').val(preco_total.replace('.', ','));
            }, 100)
        }
        let loadFile = function(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('output');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        };
        function marcarTodas() {
            $(this).prop('checked', !$(this).prop('checked'));
            $('.marcar').prop("checked", $(this).prop("checked"));
            if ($(this).prop('checked')) {
                faculdades = $('.marcar').map((e, f) => {
                    return parseInt(f.value);
                })
            } else {
                faculdades = []
            }
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

        function buscarCursosTrilha(id) {
            $.ajax({
                url: pageURL + '/api/trilha_cursos/'+id,
                type : 'GET',
                success : function(data) {
                    trilhacursos = data.items;
                    renderizaCursoTrilha()
                    calculaTotalTrilha()
                    calculaTotalPrecoTrilha()
                },
                error : function(error)
                {
                    console.log(error)
                }
            })
        }
        function countChar(event, tipo, max, len) {
            // console.log(event)
            if (len == null) len = event.value.length
            let el;
            if (tipo == 1) {
                el = $('#faltaTitulo')
            } else if(tipo == 2) {
                el = $('#faltaDescricao')
            }
            el.empty()
            el.append( len + '/' + max)
        }
        function pushValueFaculdades(faculdade_id) {
            faculdades.push(faculdade_id)
            console.log(faculdades)
        }
    </script>
@endpush
