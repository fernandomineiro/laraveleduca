@extends('layouts.app')

@section('styles')
    <style>
        #lista_tags > label { margin-left: 5px; }
        #lista_tags > label > span {
            display: table-caption;
            margin-left: 4px;
            cursor: pointer;
            padding: 2px;
        }
        input.error {
            border: 1px solid;
        }
        .error {
            color: red;
        }
    </style>
@endsection

@section('content')
	<div class="box padding20">

        @isset($graduation)
        <form method="POST" action="{{ route('graduation.update', $graduation->id) }}">
                @method('PUT')
        @else
            <h2>Incluir->Graduação:</h2>
            <form method="POST" action="{{ route('graduation.store') }}">
        @endisset

            @csrf
		<hr class="hr" />

    <div class="form-group">
            <label for="exampleInputEmail1">Título * (Limite Máximo de 60 Caracteres)</label>
            <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
    </div>

    <div class="form-group">
        <label for="exampleFormControlTextarea1">Sobre o Curso * (Limite de 500 Caracteres) </label>
        <textarea class="form-control" id="exampleFormControlTextarea1" rows="10"></textarea>
    </div>

    <div class="form-group">
        <label for="exampleFormControlTextarea1">Público Alvo * (limite de 100 caracteres)</label>
        <textarea class="form-control" id="exampleFormControlTextarea1" rows="10"></textarea>
    </div>



    <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                <label for="exampleFormControlTextarea1">Teaser</label>
                <textarea class="form-control" id="preview_vimeo" rows="1"></textarea>
                <a href="javascript:;" id="preview_vimeo" class="label label-success">Preview Video</a>
                </div>
    </div>



            <div class="col-md-2">
                <label for="idioma">Idioma *</label>
                {{ Form::select('idioma', $lista_idiomas, (isset($curso->idioma) ? $curso->idioma : null),
                    [
                        'class' => 'form-control', 'data-msg-required' => 'Este campo é obrigatório',
                        'required' => true
                    ]
                ) }}
                </select>
                </div>
            </div>





        <div  class="well">
            <div id="renderizar_video"></div>
            <div id="video_description"></div>
            <div id="author_name"></div>
            <div id="video_title"></div>
            <div id="video_duration"></div>
        </div>


        @include('curso.blocks.modulos')

        @include('curso.blocks.agenda')
        @isset($curso->trabalho)
            <!-- Campo usado apenas para visualização de cursos criados no portal, não usar em outras partes e/ou operações -->

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        {{ Form::label('Curso possui trabalho?') }}
                        {{ Form::select('trabalho', ['0' => 'Não', '1' => 'Sim'], (isset($curso->trabalho) ? $curso->trabalho : null),
                        [
                            'class' => 'form-control'
                        ]
                        ) }}
                    </div>
                </div>
            </div>
        @endif



        <div class="form-group">
            {{Form::label('Categorias') }} <small>*</small>
            {{Form::select('fk_cursos_categoria[]', $categorias, !empty($lista_categorias) ? $lista_categorias : null,
                    [
                        'multiple' => 'multiple', 'id' => 'fk_cursos_categoria', 'name' => 'fk_cursos_categoria[]',
                        'class' => 'form-control myselect', 'allowClear' => true, 'data-placeholder' => 'Categorias', 'data-msg-required' => 'Este campo é obrigatório', 'required' => true
                    ]
                )}}
            <hr />
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('Preço (R$)') }} <small>*</small><br>
                    <small>&nbsp;</small>
                    {{ Form::input('text', 'valor_de', isset($dados_valor->valor_de) ? $dados_valor->valor_de : '',
                        [
                            'class' => 'form-control moeda', 'data-msg-required' => 'Este campo é obrigatório',
                            'required' => true, 'style' => 'text-align: right;'
                        ]
                    ) }}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('Preço com desconto (R$)') }}<br>
                    <small>&nbsp;</small>
                    {{ Form::input('text', 'valor', isset($dados_valor->valor) ? $dados_valor->valor : '',
                        [
                            'class' => 'form-control moeda', 'style' => 'text-align: right;'
                        ]
                    ) }}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('Duração total do curso') }} <small>*</small><br>
                    <small>(HH:MM:SS)</small>
                    {{ Form::input('text', 'duracao_total', isset($curso->duracao_total) ? $curso->duracao_total : '',
                        [
                            'class' => 'form-control', 'data-msg-required' => 'Este campo é obrigatório',
                            'required' => true, 'style' => 'text-align: right;',
                            'id' => 'duracao_total'
                        ]
                    ) }}
                </div>
            </div>

                <div class="col-md-2">
                    <div class="form-group">
                        {{ Form::label('Número Mínimo de Alunos') }} <small></small>
                        {{ Form::input('text', 'numero_minimo_alunos', isset($curso->numero_minimo_alunos) ? $curso->numero_minimo_alunos : '',
                            [
                                'class' => 'form-control', 'data-msg-required' => 'Este campo é obrigatório',
                                'required' => true, 'style' => 'width: 150px; text-align: right;'
                            ]
                        ) }}
                    </div>
                </div>




            <div class="col-md-2">
            <div class="form-group">
                {{ Form::label('Professor Principal') }} <small>*</small>
                {{ Form::select('fk_professor', ['' => 'Selecione'] + $lista_professor, (isset($curso->fk_professor) ? $curso->fk_professor : null), ['class' => 'form-control', 'data-msg-required' => 'Este campo é obrigatório', 'required' => true]) }}
            </div>
            </div>
            <div class="col-md-2">
                <div class="row">
                    <div class="col-md-12">
                        {{ Form::label('Professor Principal %') }} <small></small>
                        {{ Form::input('text', 'professorprincipal_share', (isset($curso->professorprincipal_share) ? $curso->professorprincipal_share : null), ['class' => 'form-control', 'data-msg-required' => 'Este campo é obrigatório', 'required' => true, 'style' => 'text-align: right;']) }}
                    </div>
                    <div class="col-md-12">
                        {{ Form::checkbox('professorprincipal_share_manual', 1, (isset($curso->professorprincipal_share_manual) && ($curso->professorprincipal_share_manual == 1) ? 1 : 0)) }}
                        {{ Form::label('Manual') }}
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                {{ Form::label('Curador') }}
                {{ Form::select('fk_curador', ['' => 'Selecione'] + $lista_curador->toarray(), (isset($curso->fk_curador) ? $curso->fk_curador : null), ['class' => 'form-control']) }}
            </div>
            <div class="col-md-2">
                <div class="row">
                    <div class="col-md-12">
                        {{ Form::label('Curador %') }}
                        {{ Form::input('text', 'curador_share', (isset($curso->curador_share) ? $curso->curador_share : null), ['class' => 'form-control', 'style' => 'width: 150px; text-align: right;']) }}
                    </div>
                    <div class="col-md-12">
                        {{ Form::checkbox('curador_share_manual', 1, (isset($curso->curador_share_manual) && ($curso->curador_share_manual == 1) ? 1 : 0)) }}
                        {{ Form::label('Manual') }}
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                {{ Form::label('Produtora') }}
                {{ Form::select('fk_produtora', ['' => 'Selecione'] + $lista_produtora->toArray(), (isset($curso->fk_produtora) ? $curso->fk_produtora : null), ['class' => 'form-control']) }}
            </div>
            <div class="col-md-2">
                <div class="row">
                    <div class="col-md-12">
                        {{ Form::label('Produtora %') }}
                        {{ Form::input('text', 'produtora_share', (isset($curso->produtora_share) ? $curso->produtora_share : null), ['class' => 'form-control', 'style' => 'width: 150px; text-align: right;']) }}
                    </div>
                    <div class="col-md-12">
                        {{ Form::checkbox('produtora_share_manual', 1, (isset($curso->produtora_share_manual) && ($curso->produtora_share_manual == 1) ? 1 : 0)) }}
                        {{ Form::label('Manual') }}
                    </div>
                </div>
            </div>
						<div class="col-md-12">
							<div class="row" style="margin-top: 15px;">
								<div class="col-md-4">
										<div class="form-group">
												{{ Form::label('Professor(a) responderá dúvidas ?') }} <small>*</small><br>
												{{ Form::select(
													'professor_responde_duvidas',
													['1' => 'SIM', '0' => 'NÃO'],
													(isset($curso->professor_responde_duvidas) ? $curso->professor_responde_duvidas : 1),
													[
														'class' => 'form-control',
														'data-msg-required' => 'Este campo é obrigatório',
														'required' => true
													])
												}}
										</div>
								</div>
							</div>
						</div>


        </div>

        <div class="well">
            <div id="box_upload" class="row form-group">
                {{ Form::label('Imagem do Curso') }} <small>* </small><br>
                <small>(730x377px)</small>
                {{ Form::file('imagem', [ 'id' => 'imagem', 'onchange' => 'loadFile(event)' ]) }}
                @if(Request::is('*/editar'))
                    <img id="output" style="width: 730px; height: 377px" src="{{URL::asset('files/curso/imagem/' . $curso->imagem)}}" />
                @else
                    <img id="output" style="width: 730px; height: 377px" >
                @endif
            </div>
        </div>

        <h3> tags: </h3>
        <hr />

        <div id="lista_tags">
            <?php if (isset($tags_cadastradas) && count($tags_cadastradas)) : ?>
                <?php foreach ($tags_cadastradas as $item => $key) : ?>
                    <label class="label label-success" data-id="{{ $item }}">
                        {{ $key }}
                        <span aria-hidden="true" class="removeTags">&times;</span>
                    </label>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="hidden_tags"></div>
        <div id="hidden_remove_tags"></div>

        <hr />
        <div class="form-group">
            {{ Form::label('Adicionar tags:') }}
            {{ Form::input('text', 'tags[]', null, ['class' => 'form-control', 'style' => 'width: 50%', 'id' => 'tags']) }}
            <a href="javascript:;" onclick="addTag();" class="btn btn-success">Adicionar Tag</a>
        </div>

        <hr />


        @if(Request::is('*/editar') && isset($usuariocadastro))
            <div class="form-group">
                <div class="alert alert-info">
                    <i class="fa fa-info"></i>
                    Usuário: {{$usuariocadastro->nome}} - {{isset($usuariocadastro->fk_faculdade_id) ? $faculdades[$usuariocadastro->fk_faculdade_id] . ' - ' : 'Sem projeto - '}} {{ ($curso->data_criacao) ? date('d/m/Y H:i:s', strtotime($curso->data_criacao)) : ''}}
                </div>
            </div>
        @endif
        <div class="form-group">
            <div class="col-md-1">
                <button type="button" class="btn btn-danger">Voltar
                </button>
            </div>
            <a href="{{ url()->previous() }}" class="btn btn-default">Cancel</a>
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>

		{{ Form::close() }}
    </div>
@endsection

@push('js')
		<script type="text/javascript">

			$(document).ready(function () {
				if($('#respostaSelection').length){
					document.getElementById('respostaSelection').options[0].setAttribute("disabled", true);
				}
				$('.resposta-correta').each(function(){
					this.options[0].setAttribute("disabled", true);
				})

				$('textarea[name="teaser"]').change(function() {
					// $('#renderizar_video').html($(this).val());
				});

				$('a#preview_vimeo').click(function() {
						let vimeoId = $('textarea[name="teaser"]').val();

						$.get('https://vimeo.com/api/oembed.json?url=https://vimeo.com/'+ vimeoId, function (response) {
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

				bindButtonIncluirModulo();

				$('body').on('click', '.btn_excluir_modulo', function() {
					var secao = this.parentElement.parentElement.dataset.secao;
					var contador = this.parentElement.parentElement.dataset.contador;
					$('[data-secao='+secao+'][data-contador='+contador+']').remove();
				});

				$('body').on('click', '.btn_excluir_secao', function() {
					this.parentElement.previousElementSibling.previousElementSibling.remove();
					this.parentElement.previousElementSibling.remove();
					this.parentElement.remove()
				});

				$('body').on('click', '.btn_excluir_questao', function() {
									@if(Request::is('*/editar'))
											this.parentNode.parentNode.parentElement.remove()
									@else
											this.parentNode.parentNode.parentNode.parentNode.parentElement.remove()
									@endif
				});

				$('#btn_incluir_secao').click(function () {
					var secao = $('.secao').length;

					var html = $('#default_secao').html();

					var regex = new RegExp('__COUNT__', 'g');
					html = html.replace(regex, secao);

					$('#bloco_estrutura').append(html);

									call_datepicker();
									bindButtonIncluirModulo();
				});
							checkVimeo();


				$('#btn_incluir_agenda').click(function () {
					var qtd = $('#bloco_agenda').children().last().data('id');

					var regex = new RegExp('__X__', 'g');
					var html = $('#default_agenda').html().replace(regex, qtd + 1);
					$('#bloco_agenda').append(html);
									call_timepicker();
									//call_datepicker();
									let dataAtual = $('input[name="agenda[' + qtd + '][data_inicio]"]').val()
									if (dataAtual) {
											dataAtual = dataAtual.split("/")
											let dia = new Date(dataAtual[2], parseInt(dataAtual[1])-1, parseInt(dataAtual[0]))
											dia.setDate(dia.getDate() + 1)
											$('input[name="agenda[' + (qtd + 1) + '][data_inicio]"]').val(("0" + dia.getDate()).slice(-2) + '/' + ("0"+(dia.getMonth()+1)).slice(-2) + '/' + dia.getFullYear())
											$('input[name="agenda[' + (qtd + 1) + '][data_inicio]"]').datepicker({
													defaultDate: dia,
													format: "dd-mm-yyyy",
													dayNames: ["Domingo","Segunda","Terça","Quarta","Quinta","Sexta","Sábado"],
													dayNamesMin: ["D","S","T","Q","Q","S","S","D"],
													dayNamesShort: ["Dom","Seg","Ter","Qua","Qui","Sex","Sáb","Dom"],
													monthNames: ["Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"],
													monthNamesShort: ["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"],
													minDate: dia,
													maxDate: '+30Y',
													nextText: "Próximo",
													prevText: "Anterior"
											})
									}
				});


				$('#btn_incluir_quiz').click(function () {
									var qtd = $('.quiz').length;
					// var qtd = $('#bloco_quiz').data('contador') + 1;
					$('#bloco_quiz').data('contador', qtd);

					var regex = new RegExp('__X__', 'g');
					var html = $('#default_quiz').html().replace(regex, qtd);

					console.log(html);
					$('#bloco_quiz').append(html);

					// call_datepicker();
				});

				$('.moeda').mask('#.##0,00', {reverse: true});

				//CKEDITOR.replace('descricao');
				//CKEDITOR.replace('objetivo_descricao');
				//CKEDITOR.replace('publico_alvo');
				let timepickers = [...document.querySelectorAll('.timepicker')]
							if (timepickers) {
									timepickers.forEach(timepicker => {
											let id = timepicker.name
											let pathArray = window.location.pathname.split('/');
											let options = {}
											if (pathArray[4] === "editar" && $('input[name="' + id + '"]').val()) {
													options = {
															twentyFour: true,
															upArrow: 'fa fa-chevron-up fa-lg',  //The up arrow class selector to use, for custom CSS
															downArrow: 'fa fa-chevron-down fa-lg', //The down arrow class selector to use, for custom CSS
															now: $('input[name="' + id + '"]').val()
													}
													$('input[name="' + id + '"]').wickedpicker(options)
											}
									})
							}
			});

					$('.myselect').select2({
							allowClear: true,
							tags: true,
					});

					$('body').on('click', '.clear', function() {
							console.log(this.previousElementSibling)
							$(this.previousElementSibling).val('');
					});

					$('body').on('focus', '.datepicker', function() {
							let valor = $(this).val()
							$(this).removeClass('hasDatepicker').datepicker({
									defaultDate: (valor) ? valor : new Date(),
									minDate: (valor) ? valor : new Date(),
									maxDate: '+30Y',
									format: "dd-mm-yyyy",
									dayNames: ["Domingo","Segunda","Terça","Quarta","Quinta","Sexta","Sábado"],
									dayNamesMin: ["D","S","T","Q","Q","S","S","D"],
									dayNamesShort: ["Dom","Seg","Ter","Qua","Qui","Sex","Sáb","Dom"],
									monthNames: ["Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"],
									monthNamesShort: ["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"],
									nextText: "Próximo",
									prevText: "Anterior"
							});
					})

			function marcarTodas() {
				$(this).prop('checked', !$(this).prop('checked'));
				$('.marcar').prop("checked", $(this).prop("checked"));
			}

			function bindButtonIncluirModulo() {
				console.log('button');
				$('.btn_incluir_modulo').click(function () {
					var secao = $(this).data('secao');
					var qtd = $('.s' + secao).find('.modulo').length + 1;

					var html = $('#default_modulo').html();

					var regex_secao = new RegExp('__COUNT_SECAO__', 'g');
					var regex_modulo = new RegExp('__COUNT__', 'g');

					html = html.replace(regex_secao, secao).replace(regex_modulo, qtd);

					$('.s' + secao).append(html);
					call_datepicker();
									checkVimeo();

					$('.s' + secao).data('contador', qtd);
				});
							call_datepicker();
							checkVimeo();
			}

				 function checkVimeo() {
							jQuery('.vimeoId').on('change', function (event) {
									// 350027983
									// 349107013

									var url = $(this).val();
									if (url != undefined || url != '') {
											var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=|\?v=)([^#\&\?]*).*/;
											var match = url.match(regExp);
											if (match && match[2].length == 11) {
													// Do anything for being valid
													// if need to change the url to embed url then use below line
												 // $('#videoObject').attr('src', 'https://www.youtube.com/embed/' + match[2] + '?autoplay=1&enablejsapi=1');
												 jQuery('.nome_vimeo').val('YouTube video');
												 jQuery('.carga_horaria').val('00:00');
												 return;
											} else {
													console.log('buscando')
													jQuery('.nome_vimeo').val('');
													$.get('https://vimeo.com/api/oembed.json?url=https://vimeo.com/'+ $(event.currentTarget).val(), function (response) {
															var hours   = Math.floor(response.duration / 3600);
															var minutes = Math.floor((response.duration - (hours * 3600)) / 60);
															var seconds = response.duration - (hours * 3600) - (minutes * 60);

															$($(event.currentTarget).parent().next('div').children('input')).val([hours,minutes,seconds]
																	.map(v => v < 10 ? "0" + v : v)
																	.join(":"));

															if ($($(event.currentTarget).parent().prev('div').children('input')).val().trim() == '') {
																	$($(event.currentTarget).parent().prev('div').children('input')).val(response.title);
															}
															duracaoTotal()
													}).fail(function() {
															alert('Nenhum vídeo encontrado');
															jQuery('.nome_vimeo').val('');
															jQuery('.carga_horaria').val('');
													});
													// Do anything for not being valid
											}
									}


							});
					}

			function call_datepicker() {
				jQuery(".datepicker").datepicker({
									defaultDate: new Date,
						format: "dd-mm-yyyy",
						dayNames: ["Domingo","Segunda","Terça","Quarta","Quinta","Sexta","Sábado"],
						dayNamesMin: ["D","S","T","Q","Q","S","S","D"],
						dayNamesShort: ["Dom","Seg","Ter","Qua","Qui","Sex","Sáb","Dom"],
						monthNames: ["Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"],
						monthNamesShort: ["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"],
						nextText: "Próximo",
						prevText: "Anterior"
				})
			}
			function call_timepicker() {
							jQuery('.timepicker').wickedpicker({
									twentyFour: true,
									upArrow: 'fa fa-chevron-up fa-lg',  //The up arrow class selector to use, for custom CSS
									downArrow: 'fa fa-chevron-down fa-lg', //The down arrow class selector to use, for custom CSS
							});
					}

			function addTag() {
				$('#lista_tags').append(
						'<label class="label label-success" data-text="' + $('#tags').val() + '">' + $('#tags').val() + '<span aria-hidden="true" class="removeTags" onclick="removeTag(this)">&times;</span></label>');
				$('#hidden_tags').append('<input type="hidden" data-hidden-tag="' + $('#tags').val() + '" name="tags[]" value="' + $('#tags').val() + '" />');
				$('#tags').val('');
			}

			$('.removeTags').click(function () {
							removeTag($(this));
					});

					function removeTag(element) {
							if ($(element).parent().data('id')) {
									$('#hidden_remove_tags').append('<input type="hidden" name="removeTags[]" value="' + $(element).parent().data('id') + '" />');
									$(element).parent().remove();
							} else {
									$('#hidden_tags').find('[data-hidden-tag="'+$(element).parent().data('text')+'"]').remove();
									$(element).parent().remove();
							}
					}

					let loadFile = function(event) {
							var reader = new FileReader();
							reader.onload = function(){
									var output = document.getElementById('output');
									output.src = reader.result;
							};
							reader.readAsDataURL(event.target.files[0]);
					};

					$('#formCurso').validate({
							ignore: [],
							rules: {
									duracao_total: { step: false },
									descricao: {
											required: function(textarea) {
													console.log(textarea)
													//CKEDITOR.instances[textarea.id].updateElement(); // update textarea
													var editorcontent = textarea.value.replace(/<[^>]*>/gi, ''); // strip tags
													return editorcontent.length === 0;
											}
									},
									objetivo_descricao: {
											required: function(textarea) {
													//CKEDITOR.instances[textarea.id].updateElement(); // update textarea
													var editorcontent = textarea.value.replace(/<[^>]*>/gi, ''); // strip tags
													return editorcontent.length === 0;
											}
									},
									publico_alvo: {
											required: function(textarea) {
													//CKEDITOR.instances[textarea.id].updateElement(); // update textarea
													var editorcontent = textarea.value.replace(/<[^>]*>/gi, ''); // strip tags
													return editorcontent.length === 0;
											}
									}
							}
					});
					function duracaoPresencial(event) {
							let key = event.target.name.split('[')
							key = key[1].split(']')
							key = key[0]
							let hora_inicio = $('input[name="agenda[' + key + '][hora_inicio]"]').val()
							let hora_final = $('input[name="agenda[' + key + '][hora_fim]"]').val()

							if (hora_inicio && hora_final) {
									hora_inicio = hora_inicio.split(' ')
									hora_final = hora_final.split(' ')

									let start = parseInt(hora_inicio[0])* 60+ parseInt(hora_inicio[2]);
									let end = parseInt(hora_final[0])*60 + parseInt(hora_final[2]);
									if (end > start) {
											let hora_total = end - start;
											let horas = parseInt(hora_total / 60)
											horas = (horas > 9) ? horas : "0" + horas
											let minutos = hora_total % 60
											minutos = (minutos > 9) ? minutos : "0" + minutos
											hora_total = horas + ":" + minutos;
											$('#duracao_aula-' + key).val(hora_total+':00')
									} else {
											if (start > end) {
													$('input[name="agenda[' + key + '][hora_fim]"]').val(hora_inicio[0] + " : " + hora_inicio[2])
											}
											$('#duracao_aula-' + key).val('00:00:00')
									}
							}
							duracaoTotal()
					}
					function duracaoTotal() {
							let duracao_total = 0
							let duracao_modulos = [...document.querySelectorAll('.carga_horaria')]

							let duracao_agendas = [...document.querySelectorAll('.duracao_aula')]
							if (duracao_agendas) {
									duracao_agendas.forEach(e => {
											let tempo = e.value.split(':')
											duracao_total = duracao_total + parseInt(tempo[0]) * 60 + parseInt(tempo[1]);
									})
							}

							if (duracao_modulos) {
									duracao_modulos.forEach(e => {
											let tempo = e.value.split(':')
											if (tempo[0] && tempo[1] && tempo[2]) {
													let segundos = parseInt(tempo[2]) / 60
													duracao_total = duracao_total + parseInt(tempo[0]) * 60 + parseInt(tempo[1]) + (Math.round(segundos * 1e2) / 1e2);
											}
									})
							}

							if (duracao_total > 0) {
									let horas = parseInt(duracao_total / 60)
									horas = (horas > 9) ? horas : "0" + horas
									let minutos = duracao_total % 60
									let segundos = (minutos - Math.floor(minutos)) * 60
									segundos = (segundos > 0) ? parseInt(segundos) : "00"
									minutos = (minutos > 9) ? parseInt(minutos) : "0" + parseInt(minutos)
									duracao_total = horas + ":" + minutos + ":" + segundos;
									$('#duracao_total').val(duracao_total)
							} else {
									$('#duracao_total').val("00:00:00")
							}
					}
					function ytVidId(url) {
							var p = /^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?(?=.*v=((\w|-){11}))(?:\S+)?$/;
							return (url.match(p)) ? RegExp.$1 : false;
					}
		</script>
@endpush

