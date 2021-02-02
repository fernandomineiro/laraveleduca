@extends('layouts.app')

@section('content')
	<div class="box padding20">
		<div class="col-md-9"><h2 class="table">Lista de Perguntas (módulo de tutoria)</h2></div>
		<hr class="clear hr" />

		@if(count($perguntas) > 0)
			<table class="table" cellpadding="0" cellspacing="0" border="0" class="table table-striped">
				<th>IES</th>
				<th>Nome do Curso</th>
				<th>Pergunta</th>
				<th>Professor</th>
				<th>Ações</th>
				<tbody>
					@foreach($perguntas as $pergunta)
						<tr>
							<td>{{ $pergunta['faculdade_nome'] }}</td>
								<th>{{ $pergunta['titulo'] }}</th>
								<th>{{ $pergunta['pergunta'] }}</th>
								<th>{{ $pergunta['nome_professor']}} {{$pergunta['sobrenome_professor']}}</th>
								<th>
									<a href="javascript:;" onclick="showRespostas({{ $pergunta['pergunta_id'] }})" class="btn btn-default btn-sm">Ver Respostas</a>
									<a href="javascript:;" onclick="showRespoder({{ $pergunta['pergunta_id'] }})" class="btn btn-default btn-sm">Responder</a>
								</th>
							</td>
						</tr>

						@if(isset($pergunta['resposta']) && count($pergunta['resposta']))
							@foreach($pergunta['resposta'] as $resposta)
								<tr id="resposta_{{ $pergunta['pergunta_id'] }}" class="resposta" style="display: none;">
									<td colspan="5">
										<div class="alert alert-warning" style="margin-left: 0;">
											<strong>Resposta:</strong> {{ $resposta['resposta'] }}
										</div>
									</td>
								</tr>
							@endforeach
						@endif

						<tr id="form_{{ $pergunta['pergunta_id'] }}" class="form_resposta" style="display: none;">
							<td colspan="5">
								{{ Form::open(['url' => '/admin/tutoria/salvar','files' => true]) }}
									<input type="hidden" name="fk_pergunta" value="{{$pergunta['pergunta_id']}}" />
									<div class="form-group">
										{{ Form::label('Resposta') }}
										{{ Form::textarea('resposta', null, ['class' => 'form-control', 'id' => 'ckeditor']) }}
									</div>
									<div class="form-group">
										{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
                                        <button class="btn btn-default" onclick="cancelarResposta({{$pergunta['pergunta_id']}})">Cancelar</button>
									</div>
								{{ Form::close() }}
							</td>
						</tr>
						<tr id="separacao_{{ $pergunta['pergunta_id'] }}" class="separacao" style="display: none;">
							<td colspan="5"><hr /></td>
						</tr>
					@endforeach
				</tbody>
			</table>
		@else
			<div class="alert alert-info">Nenhum registro no banco!</div>
		@endif
	</div>

@endsection

@push('js')
    <script type="text/javascript">
        $(document).ready(function () {
        })
        function showRespostas(id) {
            if($('#resposta_'+id).is(":visible")) {
                $('#resposta_'+id).hide();
                $('#separacao_'+id).hide();
            } else {
                $('.resposta').hide();
                $('.separacao').hide();
                $('#resposta_'+id).show();
                $('#separacao_'+id).show();
                $('#form_'+id).hide();
            }
        }
        function showRespoder(id) {
            if($('#form_'+id).is(":visible")) {
                $('#form_'+id).hide();
                $('#separacao_'+id).hide();
            } else {
                $('.form_resposta').hide();
                $('.separacao').hide();
                $('#form_'+id).show();
                $('#separacao_'+id).show();
                $('#resposta_'+id).hide();
            }
        }
        function cancelarResposta(id) {
            $('#separacao_'+id).hide();
            $('#form_'+id).hide();
        }
    </script>
@endpush
