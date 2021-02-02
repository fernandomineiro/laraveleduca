@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="row">
			<div class="col-md-6">
				<h2>Repasses/Detalhes - {{ $usuario['nome'] }}</h2>
			</div>
		</div>
		<div class="row">
			<div class="hidden">
				<div class="col-md-2">
					<label for="data-inicio">Início:</label>
					<div class="input-group date" data-provide="datepicker">
						<input  class="datepicker form-control" id="data-inicio" type="text">
						<div class="input-group-addon">
							<span class="glyphicon glyphicon-th"></span>
						</div>
					</div>
				</div>
				<div class="col-md-2">	
					<label for="data-inicio">Fim:</label>
					<div class="input-group date" data-provide="datepicker">
						<input class="datepicker form-control" id="data-fim" type="text">
						<div class="input-group-addon">
							<span class="glyphicon glyphicon-th"></span>
						</div>
					</div>
				</div>
				<div class="col-md-2">
					<label for="data-inicio">&nbsp;</label>
					<div class="form-group">
						<button class="btn btn-primary" id="filtrar-detalhes">Filtrar</button>
					</div>
				</div>
			</div>
			<div class="col-md-6"></div>
			<div class="col-md-4 col-md-offset-2">
				<div class="pull-right">				
					<button type="button" class="btn btn-primary" aria-label="Left Align"  id="transfer-manual">REGISTRAR REPASSE MANUAL</button>
					<button type="button" class="btn btn-success" aria-label="Left Align" id="transfer-wirecard">TRANSFERÊNCIA VIA WIRECARD</button>
				</div>
				<div id="form-transfer-wirecard" style="display: none;">		
					<div class="input-group"> 
						<span class="input-group-addon">R$</span>
						<input class="form-control valor valor-transferencia-wirecard"> 
						<span class="input-group-btn">
							<button class="btn btn-success" id="execute-transfer-wirecard" type="button">Transferir via Wirecard</button>
							<button class="btn btn-danger cancelar-transfer" type="button">Cancelar</button>
						</span>
					</div>
				</div>
				<div id="form-registrar-transfererencia-manual" style="display: none;">		
					<div class="input-group">
						<span class="input-group-addon">R$</span>
						<input class="form-control valor valor-transferencia-manual">
						<span class="input-group-btn">
							<button class="btn btn-success" id="execute-transfer-manual" type="button">Registrar transferência</button>
							<button class="btn btn-danger cancelar-transfer" type="button">Cancelar</button>
						</span>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="input-group pull-right"> 
							<div class="alert alert-success" style="margin-top: 10px; display: none;" id="transfer-return" role="alert">Transferência realizada com sucesso...</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<hr class="clear hr" />
		<div class="row">
			<div class="col-md-6">
				<h3>Extrato - Últimos repasses</h3>
				@if ($repasses)
					<table class="table">
						<thead>
							<tr>
								<td><b>Valor</b></td>
								<td><b>Data</b></td>
							</tr>
						</thead>
						<tbody>
							@foreach ($repasses as $repasse)
								<tr>
									<td>{{ 'R$ ' . number_format( $repasse['valor'], 2 , ',', '.') }}</td>
									<td>{{ $repasse['criacao'] }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				@else	
					<center><h3>Sem registros...</h3></center>
				@endif

				<h4><b><br/>Total:</b>&nbsp;<span>{{ 'R$ ' . number_format($total_repasse, 2 , ',', '.') }}</span></h4>
			</div>
			<div class="col-md-6">
				<h3>Últimos pedidos</h3>
				@if ($pedidos)
					<table class="table">
						<thead>
							<tr>
								<td><b>ID</b></td>
								<td><b>Produtos</b></td>
								<td><b>Total</b></td>
								<td><b>Data</b></td>
							</tr>
						</thead>
						<tbody>
							@foreach ($pedidos as $pedido)
								<tr>
									<td>{{ $pedido['pid'] }}</td>
									<td>
										@if ($pedido['produtos'])
											@foreach ($pedido['produtos'] as $produto)
												{{ $produto['titulo'] }}<br/>
											@endforeach
										@endif
									</td>
									<td>{{ $pedido['valor_bruto'] }}</td>
									<td>{{ $pedido['data'] }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				@else	
					<center><h3>Sem registros...</h3></center>
				@endif
			</div>
		</div>
		<hr class="hr"/>
			<div class="row">
				<div class="col-md-6">
					<h3>Dados bancários</h3>
				</div>
				<div class="col-md-6">
					<div class="pull-right">
					<h4><b>Saldo Wirecard disponível:</b>&nbsp;<span class="pull-right">{{ $extrato['valor_disponivel'] }}</span></h4>
					<h4><b>Saldo Wirecard futuro:</b>&nbsp;<span class="pull-right">{{ $extrato['valor_futuro'] }}</span></h4>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="form-group col-md-4">
					{{ Form::label('Titular') }}
					{{ Form::input('text', 'titular', isset($objConta->titular) ? $objConta->titular : null, ['disabled' => 'disabled', 'class' => 'form-control', '', 'placeholder' => 'Titular']) }}
				</div>
				<div class="form-group  col-md-2">
					{{ Form::label('CPF/CNPJ') }}
					{{ Form::input('text', 'documento', isset($objConta->documento) ? $objConta->documento : null, ['disabled' => 'disabled', 'class' => 'form-control cpf', 'onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'CPF/CNPJ do Titular']) }}
				</div>
				<div class="form-group col-md-2">
					{{ Form::label('Perfil') }}
					{{ Form::input('text', 'titular', isset($usuario['perfil']) ? $usuario['perfil'] : null, ['disabled' => 'disabled', 'class' => 'form-control']) }}
				</div>
			</div>
			<hr/>
			<div class="row">
				<div class="form-group col-md-2">
					{{ Form::label('Banco') }}
					{{ Form::select('fk_banco_id', $lista_bancos, (isset($objConta->fk_banco_id) ? $objConta->fk_banco_id : 1), ['disabled' => 'disabled', 'class' => 'form-control']) }}
				</div>
				<div class="form-group  col-md-2">
					{{ Form::label('Tipo de conta') }}
					@if ($objConta->tipo_conta === 'cc')
						{{ Form::input('text', 'tipo_conta', 'Conta corrente', ['disabled' => 'disabled', 'class' => 'form-control']) }}
					@else
						{{ Form::input('text', 'tipo_conta', 'Conta poupança', ['disabled' => 'disabled', 'class' => 'form-control']) }}
					@endif
				</div>
				<div class="form-group col-md-1">
					{{ Form::label('Agência') }}
					{{ Form::input('text', 'agencia', isset($objConta->agencia) ? $objConta->agencia : null, ['disabled' => 'disabled', 'class' => 'form-control', '', 'placeholder' => 'Agência']) }}
				</div>
				<div class="form-group col-md-1">
					{{ Form::label('Dígito') }}
					{{ Form::input('text', 'digita_agencia', isset($objConta->digita_agencia) ? $objConta->digita_agencia : null, ['disabled' => 'disabled', 'class' => 'form-control',]) }}
				</div>
				<div class="form-group col-md-2">
					{{ Form::label('Conta') }}
					{{ Form::input('text', 'conta_corrente', isset($objConta->conta_corrente) ? $objConta->conta_corrente : null, ['disabled' => 'disabled', 'class' => 'form-control','', 'placeholder' => 'Conta Corrente']) }}
				</div>
				<div class="form-group col-md-1">
					{{ Form::label('Dígito') }}
					{{ Form::input('text', 'digita_conta', isset($objConta->digita_conta) ? $objConta->digita_conta : null, ['disabled' => 'disabled', 'class' => 'form-control',]) }}
				</div>
				<div class="form-group col-md-1">
					{{ Form::label('Operação') }}
					{{ Form::input('text', 'operacao', isset($objConta->operacao) ? $objConta->operacao : null, ['disabled' => 'disabled', 'class' => 'form-control','', 'placeholder' => 'Operação']) }}
				</div>
			</div>
	{{ csrf_field() }}
	<input type="hidden" id="type-user" value="{{ $tipo }}" />
	<input type="hidden" id="user-id" value="{{ $usuario['id'] }}" />

@endsection

@push('js')
    <script type="text/javascript">
                $(document).ready(function () {
                    $('#transfer-wirecard').on('click', function(){
                        $('#form-transfer-wirecard').fadeIn();
                        $('#transfer-wirecard').hide();
                        $('#transfer-manual').hide();
                    });

                    $('#transfer-manual').on('click', function(){
                        $('#form-registrar-transfererencia-manual').fadeIn();
                        $('#transfer-wirecard').hide();
                        $('#transfer-manual').hide();
                    });

                    $('.cancelar-transfer').on('click', function(){
                        $('#form-transfer-wirecard').hide();
                        $('#form-registrar-transfererencia-manual').hide();
                        $('#transfer-wirecard').fadeIn();
                        $('#transfer-manual').fadeIn();
                        $('#transfer-return').hide();
                    });

                    $(".valor").maskMoney({
                        decimal: ",",
                        thousands: "."
                    });

                    $('#execute-transfer-wirecard').on('click', function(e){
                        e.preventDefault();

                        var cents = $('.valor-transferencia-wirecard').maskMoney('unmasked')[0] * 100;
                        var type = $('#type-user').val();

                        $.ajax({
                            type: 'POST',
                            dataType: 'json',
                            url: '/api/wirecard-transfer/execute',
                            data: { 'cents' : cents, 'type' : type, 'user_id' : $('#user-id').val() },
                            async: true,
                            beforeSend: function(response) {
                                $('#execute-transfer-wirecard').text('Processando...').prop('disabled', true);
                            },
                            success: function (response) {
                                console.log(response);
                                if (response['success']){
                                    $('#transfer-return').text(response['success']).addClass('alert-success').removeClass('alert-warning').fadeIn();

                                    window.location.reload();
                                } else if(response['error']){
                                    $('#transfer-return').text(response['error']).removeClass('alert-success').addClass('alert-warning').fadeIn();
                                }
                            },
                            complete: function(){
                                $('#execute-transfer-wirecard').text('Transferir via Wirecard').prop('disabled', false);
                            },
                            error: function(response){
                                if (response.responseJSON.message){
                                    $('#transfer-return').text(response.responseJSON.message).removeClass('alert-success').addClass('alert-warning').fadeIn();
                                }
                            }
                        });
                    });

                    $('#execute-transfer-manual').on('click', function(e){
                        e.preventDefault();

                        var cents = $('.valor-transferencia-manual').maskMoney('unmasked')[0] * 100;
                        var type = $('#type-user').val();

                        $.ajax({
                            type: 'POST',
                            dataType: 'json',
                            url: '/admin/register-transfer-manual',
                            data: { 'cents' : cents, 'type' : type, 'user_id' : $('#user-id').val(), '_token': $('meta[name="csrf-token"]').attr('content') },
                            async: true,
                            beforeSend: function(response) {
                                $('#execute-transfer-manual').text('Processando...').prop('disabled', true);
                            },
                            success: function (response) {
                                if (response['success']){
                                    $('#transfer-return').text(response['success'] + ' Atualizando...').addClass('alert-success').removeClass('alert-warning').fadeIn();

                                    window.location.reload();
                                } else if(response['error']){
                                    $('#transfer-return').text(response['error']).removeClass('alert-success').addClass('alert-warning').fadeIn();
                                }
                            },
                            complete: function(){
                                $('#execute-transfer-manual').text('Registrar transferência').prop('disabled', false);
                            },
                        });
                    });
                });

                $('.datepicker').datepicker();

                $('#filtrar-detalhes').on('click', function(){
                    var data_inicio = $('#data-inicio').val().replace("/", "-").replace("/", "-");
                    var data_fim = $('#data-fim').val().replace("/", "-").replace("/", "-");

                    window.location = window.location.href + '/' + data_inicio + '/' + data_fim;
                });

            </script>
@endpush
