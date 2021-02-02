@extends('layouts.app')
@section('content')
	<div class="box padding20">
        <a href="{{ route('admin.pedido') }}" class="label label-default">Voltar</a>
	    <h2 class="table"><span>Detalhes do Pedido: #{{ $pedido->pid }}</span></h2>
		<hr class="hr" />

		@if(Request::is('*/editar'))
			{{ Form::model( $pedido, ['method' => 'PATCH', 'route' => ['admin.pedido.atualizar', $pedido->id]] ) }}
		@endif
		<div class="row">
			<div class="col-sm-5">
                <div class="well">
                    <b>Histórico de Status:</b> <br>
                    <hr />
                    <table class="table">
                        <thead>
                            <th>Data</th>
                            <th>Número</th>
                            <th>Status</th>
                            <th><center>NFe<center></th>
                        </thead>
                        <tr>
                            <td>{{ $pedido->criacao }}</td>
                            <td>{{ $pedido->pid }}</td>
                            <td>{{ $pedido->pedido_status['titulo'] }}</td>
                            
                            @if ($nfe)
                                <td>
                                    <center>
                                        <a href="/api/nfe/download-invoice?pedido_id={{ $pedido->id }}" target="_blamk" download >Download</a><br/><br/>
                                        <button class="btn btn-success" id="resent-invoice" >Reenviar por e-mail</button>
                                    </center>
                                </td>
                            @else 
                                <td>Pendente</td>
                            @endif
                        </tr>
                    </table>
                </div>
                <div class="well">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                {{ Form::label('Alterar Status:') }}
                                {{ Form::select('status', $lista_status, (isset($pedido->status) ? $pedido->status : 1 ), ['class' => 'form-control']) }}
                            </div>
                            <div class="form-group" id="notificar-cliente" style="display: none;">
                                {{ Form::label('Notificar cliente') }}
                                {{ Form::checkbox('notificar', '', false) }}
                            </div>
                        </div>
                        <div class="col-sm-1">
                            <br />
                            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
                        </div>
                        <script type="text/javascript">
                            $(document).ready(function () {
                                $('.moeda').mask('#.##0,00', {reverse: true});
                            })
                        </script>
                    </div>
                </div>
                
                @if (!empty($pedido->link_boleto) && $pedido->status != 2)
                    <div class="row">
                        <div class="input-group"> 
                            <div class="alert alert-success" style="margin-top: 10px; display: none;"  id="reenvio-boleto-return" role="alert">MENSAGEM TESTE</div>
                        </div>
                    </div>
                    <div class="well">
                        <div class="row">
                            <div class="col-sm-12">
                                {{ Form::label('Boleto:') }}
                                <div class="form-group">
                                    {{ Form::input('text', 'link_boleto', $pedido->link_boleto, ['class' => 'form-control', 'placeholder' => 'Titular', 'id' => 'inputLinkBoleto', 'readonly' => 'readonly']) }}
                                </div>
                                <div class="form-group">
                                    {{ Form::button('Copiar link', array('class' => 'btn btn-primary pull-right', 'id' => 'btnCopyLink')) }}
                                    {{ Form::button('Reenviar boleto por e-mail', array('class' => 'btn btn-success', 'id' => 'reenviar-boleto')) }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($pedido->status == 2)
                    <div class="row">
                        <div class="input-group"> 
                            <div class="alert alert-success" style="margin-top: 10px; display: none;"  id="enviar-comprovante-pagamento-return" role="alert">MENSAGEM TESTE</div>
                        </div>
                    </div>
                    <div class="well">
                        <div class="row">
                            <div class="col-sm-12">
                                {{ Form::label('Outras opções: ') }}
                                <div class="form-group">
                                    {{ Form::button('Enviar comprovante de pagamento para o cliente, por e-mail', array('class' => 'btn btn-success', 'id' => 'btn-enviar-comprovante-pagamento')) }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="well">
                    <div class="row">
                        <div class="col">
                            <b>Informações do Aluno</b> <br>
                            <p>Nome: {{ $aluno->nome }}</p>
                            <p>CPF: {{ $aluno->cpf ? $aluno->cpf : '-' }}</p>
                            <p>RG: {{ $aluno->identidade ? $aluno->identidade : '-' }}</p>
                            <p>Data de Nascimento: {{ $aluno->data_nascimento ? $aluno->data_nascimento : '-' }}</p>
                            <p>IES: {{ $lista_faculdades[$aluno->fk_faculdade_id] }}</p>
                        </div>
                        <div>
                            <b>Contato</b><br>
                            <p>Telefone 01: {{ $aluno->telefone_1 ? $aluno->telefone_1 : '-' }}</p>
                            <p>Telefone 02: {{ $aluno->telefone_2 ? $aluno->telefone_2 : '-' }}</p>
                            <p>Email: {{ $pedido->usuario['email'] }}</p>
                        </div>
                        <?php if (isset($pedido->metodo_pagamento)) : ?>
                            <div>
                                <b>Método de Pagamento</b><br>
                                <p><?php echo $pedido->metodo_pagamento; ?></p>
                                <p><?php echo !empty($pedido->link_boleto) ? ' - <a target="_blank" href="'.$pedido->link_boleto.'">Baixar Boleto</a>' : ''; ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="well">
                    <table class="table">
                        <thead>
                        <th>Formato</th>
                        <th>Nome do Curso</th>
                        <th>Preço</th>
                        <th>Total</th>
                        </thead>
                        @php
                            $total_valor = 0;
                            $total_imposto = 0;
                        @endphp
                        @foreach ($lista_items as $item)
                        <tr>
                            <td>
                                @if (!empty($item->evento))
                                    <?= 'Evento' ?>
                                @elseif (!empty($item->curso))
                                    <?= 'Curso' ?>
                                @elseif (!empty($item->trilha))
                                    <?= 'Trilha' ?>
                                @elseif (!empty($item->assinatura))
                                    <?= 'Assinatura' ?>
                                @endif
                            </td>
                            <td>
                                @if (!empty($item->evento))
                                    <?= isset($item->evento->titulo) ? $item->evento->titulo : ''; ?>
                                @elseif (!empty($item->curso))
                                    <?= isset($item->curso->titulo) ? $item->curso->titulo : ''; ?>
                                @elseif (!empty($item->trilha))
                                    <?= isset($item->trilha->titulo) ? $item->trilha->titulo : ''; ?>
                                @elseif (!empty($item->assinatura))
                                    <?= isset($item->assinatura->titulo) ? $item->assinatura->titulo : ''; ?>
                                @endif
                            </td>
                            <td>{{ 'R$ ' . number_format( str_replace(',', '.', $item->valor_bruto) , 2, ',', '.') }}</td>
                            <?php $vlr_liquido = floatval($item->valor_bruto) - (floatval($item->valor_desconto) + floatval($item->valor_imposto)) ?>
                            <td>{{ 'R$ ' . number_format( str_replace(',', '.', $vlr_liquido) , 2, ',', '.') }}</td>
                            <?php
                                $total_valor += doubleval($item->valor_bruto);
                                $total_imposto += doubleval($item->valor_imposto);
                            ?>
                        </tr>
                        @endforeach
                        <tr>
                            <td colspan="4"></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td>Subtotal:</td>
                            <td>R$ {{ number_format( str_replace(',', '.', $total_valor) , 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td>Taxas:</td>
                            <td>R$ {{ number_format( str_replace(',', '.', $total_imposto) , 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="3"></td>
                            <td>R$ {{ number_format( str_replace(',', '.', strval($total_valor - $total_imposto)) , 2, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
			</div>
		{{ Form::close() }}
	</div>
    <input type="hidden" value="{{ $pedido->id }}" id="pedido-id" disabled>
@endsection

@push('js')        
    <script type="text/javascript">

        $(document).ready(function () {    
            $('#resent-invoice').on('click', function(e){
                e.preventDefault();
                jQuery.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: '/api/nfe/resent-invoice',
                    data: { 'pedido_id' :  $('#pedido-id').val() },
                    async: true,
                    success: function (response) {
                        if (response['success']){
                            alert(response['success']);
                        } else if(response['error']){
                            alert(response['error']);
                        }
                    }
                });
            });
        });

        if ($('select[name="status"]').val() == '2'){
            $('#notificar-cliente').show();
        }

        $('select[name="status"]').on('change', function(){
            $('input[name="notificar"]').prop('checked', false);

            if ($(this).val() == '2'){
                $('#notificar-cliente').show();
            } else {
                $('#notificar-cliente').hide();
            }
        });

        if (document.getElementById("btnCopyLink")){
            document.getElementById("btnCopyLink").addEventListener("click", function() {
                copyToClipboard(document.getElementById("inputLinkBoleto"));

                $('#btnCopyLink').text('Copiado!');

                window.setTimeout(function(){
                    $('#btnCopyLink').text('Copiar link');
                }, 800);
            });
        }

        function copyToClipboard(elem) {
            // create hidden text element, if it doesn't already exist
            var targetId = "_hiddenCopyText_";
            var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
            var origSelectionStart, origSelectionEnd;
            if (isInput) {
                // can just use the original source element for the selection and copy
                target = elem;
                origSelectionStart = elem.selectionStart;
                origSelectionEnd = elem.selectionEnd;
            } else {
                // must use a temporary form element for the selection and copy
                target = document.getElementById(targetId);
                if (!target) {
                    var target = document.createElement("textarea");
                    target.style.position = "absolute";
                    target.style.left = "-9999px";
                    target.style.top = "0";
                    target.id = targetId;
                    document.body.appendChild(target);
                }
                target.textContent = elem.textContent;
            }
            // select the content
            var currentFocus = document.activeElement;
            target.focus();
            target.setSelectionRange(0, target.value.length);
            
            // copy the selection
            var succeed;
            try {
                succeed = document.execCommand("copy");
            } catch(e) {
                succeed = false;
            }
            // restore original focus
            if (currentFocus && typeof currentFocus.focus === "function") {
                currentFocus.focus();
            }
            
            if (isInput) {
                // restore prior selection
                elem.setSelectionRange(origSelectionStart, origSelectionEnd);
            } else {
                // clear temporary content
                target.textContent = "";
            }
            return succeed;
        }

            $('#reenviar-boleto').on('click', function(e){
				e.preventDefault();

				jQuery.ajax({
					type: 'POST',
					dataType: 'json',
					url: '/admin/pedido/'+$('#pedido-id').val()+'/reenviar-boleto',
					data: { '_token': $('meta[name="csrf-token"]').attr('content') },
					async: true,
					beforeSend: function(response) {
						$('#reenviar-boleto').text('Processando...').prop('disabled', true);
					},
					success: function (response) {
						if (response['success']){
							$('#reenvio-boleto-return').text(response['success']).addClass('alert-success').removeClass('alert-warning').fadeIn();
						} else if(response['error']){
							$('#reenvio-boleto-return').text(response['error']).removeClass('alert-success').addClass('alert-warning').fadeIn();
						}
					},
					complete: function(){
						$('#reenviar-boleto').text('Reenviar boleto por e-mail').prop('disabled', false);
					},
				});

                window.setTimeout(function(){
                    $('#reenvio-boleto-return').fadeOut();
                }, 9600)
			});

            $('#btn-enviar-comprovante-pagamento').on('click', function(e){
				e.preventDefault();

				jQuery.ajax({
					type: 'POST',
					dataType: 'json',
					url: '/admin/pedido/'+$('#pedido-id').val()+'/enviar-comprovante-pagamento',
					data: { '_token': $('meta[name="csrf-token"]').attr('content') },
					async: true,
					beforeSend: function(response) {
						$('#btn-enviar-comprovante-pagamento').text('Processando...').prop('disabled', true);
					},
					success: function (response) {
						if (response['success']){
							$('#enviar-comprovante-pagamento-return').text(response['success']).addClass('alert-success').removeClass('alert-warning').fadeIn();
						} else if(response['error']){
							$('#enviar-comprovante-pagamento-return').text(response['error']).removeClass('alert-success').addClass('alert-warning').fadeIn();
						}
					},
					complete: function(){
						$('#btn-enviar-comprovante-pagamento').text('Enviar comprovante de pagamento, por e-mail').prop('disabled', false);
					},
				});

                window.setTimeout(function(){
                    $('#enviar-comprovante-pagamento-return').fadeOut();
                }, 9600)
			});

    </script>
@endpush
