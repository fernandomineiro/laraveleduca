@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Assinatura</span></h2>
	    <a href="{{ route('admin.assinatura') }}" class="label label-default">Voltar</a>
		<hr class="hr" />

		@if(Request::is('*/editar'))
			{{ Form::model( $assinatura, ['method' => 'PATCH', 'route' => ['admin.assinatura.atualizar', $assinatura->id]] ) }}
		@else
			{{ Form::open(['url' => '/admin/assinatura/salvar']) }}
		@endif
			<div class="form-group">
				{{ Form::label('Status*') }}
				{{ Form::select('status', $lista_status, (isset($assinatura->status) ? $assinatura->status : 1), ['class' => 'form-control', 'style' => 'width: 50%;']) }}
			</div>
			<div class="form-group">
				{{ Form::label('Nome*') }}
				{{ Form::input('text', 'titulo', null, ['class' => 'form-control', '']) }}
			</div>

			<div class="form-group">
				{{ Form::label('Tipo de Assinatura*') }}
				{{ Form::select('fk_tipo_assinatura', $lista_tipos, (isset($assinatura->fk_assinaturas_tipo) ? $assinatura->fk_assinaturas_tipo : null), ['class' => 'form-control', 'style' => 'width: 50%; min-width: 120px;', 'id' => 'assinaturas_tipo']) }}
			</div>
             <div class="form-group campos_full"
                 @if(!isset($assinatura->fk_tipo_assinatura) || (isset($assinatura->fk_tipo_assinatura) && $assinatura->fk_tipo_assinatura != 1))
                 style="display: none;"
                 @else
                 style="display: block;"
                @endif>
                @if(Request::is('*/editar'))
                    <div class="form-group">
                        @if (isset($assinatura->fk_faculdade))
                            {{ Form::label('Projeto:') }}
                            <h3 style="margin-top: 5px;">{{$lista_faculdades[$assinatura->fk_faculdade]}}</h3>
                            <hr/>
                        @endif
                    </div>
                @else
                    <div class="form-group">
                        {{ Form::label('Projetos') }}
                        <br />
                        <input name="selecionar_todas" type="checkbox" id="selecionar_todas" value="" onclick="marcarTodas();"> Marcar Todas <br />
                        @foreach($lista_faculdades as $key => $item)
                        {{ Form::checkbox('fk_assinatura_faculdade[' . $key . ']', (isset($item['descricao']) ? $item['descricao'] : ''), (isset($item['ativo']) && ($item['ativo'] == '1')) ? true : false, ['class' => 'marcar', 'id' => $key])}}
                        {{ $item['descricao'] }}
                        <br />
                        @endforeach
                        <hr />
                    </div>
                @endif
             </div>
            <div class="form-group campos_full certificados" id="certificados"
                 @if(!isset($assinatura->fk_tipo_assinatura) || (isset($assinatura->fk_tipo_assinatura) && $assinatura->fk_tipo_assinatura != 1))
                 style="display: none;"
                 @else
                 style="display: block;"
                @endif>
                {{ Form::label('Certificados:')}}
            </div>
            <div class="form-group certificado"
                @if(!isset($assinatura->fk_tipo_assinatura) || (isset($assinatura->fk_tipo_assinatura) && $assinatura->fk_tipo_assinatura === 1))
                    style="display: none;"
                @else
                    style="display: block;"
                @endif>
                {{ Form::label('Certificado:')}}
                {{ Form::select('fk_certificado', $lista_certificados, (isset($curso->fk_certificado) ? $curso->fk_certificado : null), ['class' => 'form-control', 'style' => 'width: 50%;']) }}
            </div>

			<div class="form-group campos-cursos_por_periodo"
				@if(!isset($assinatura->fk_tipo_assinatura) || (isset($assinatura->fk_tipo_assinatura) && $assinatura->fk_tipo_assinatura != 2))
					style="display: none;"
				@else
					style="display: block;"
				@endif>

				{{ Form::label('Quantidade de Cursos') }}
				{{ Form::input('text', 'qtd_cursos', isset($assinatura->qtd_cursos) ? $assinatura->qtd_cursos : '', ['class' => 'form-control moeda', 'style' => 'text-align: right; width: 120px;']) }}
			</div>

			<div class="form-group campos-cursos_por_periodo"
				@if(!isset($assinatura-> fk_tipo_assinatura) || (isset($assinatura->fk_tipo_assinatura) && $assinatura->fk_tipo_assinatura != 2))
					style="display: none;"
				@else
					style="display: block;"
				@endif>

				{{ Form::label('Prazo Novos Cursos (em dias)') }}
				{{ Form::input('text', 'periodo_em_dias', isset($assinatura->periodo_em_dias) ? $assinatura->periodo_em_dias : '', ['class' => 'form-control moeda', 'style' => 'text-align: right; width: 120px;']) }}
			</div>

			<div class="form-group">
				{{ Form::label('Preço: (Somente informativo)*') }}
				{{ Form::input('text', 'valor_de', isset($assinatura->valor_de) ? $assinatura->valor_de : '', ['class' => 'form-control moeda', 'style' => 'width: 150px; text-align: right;']) }}
			</div>
			<div class="form-group">
				{{ Form::label('Preço de Venda (R$)*') }}
				{{ Form::input('text', 'valor', isset($assinatura->valor) ? $assinatura->valor : '', ['class' => 'form-control moeda', 'style' => 'width: 150px; text-align: right;']) }}
			</div>
			<div class="form-group">
				{{ Form::label('Período da Assinatura*') }}
				{{ Form::select('tipo_periodo', $lista_periodos, (isset($assinatura->tipo_periodo) ? $assinatura->tipo_periodo : null), ['class' => 'form-control', 'style' => 'width: 50%; min-width: 120px;', 'id' => 'assinaturas_tipo']) }}
			</div>
            <hr/>
            <div class="row form-group">
                <div class="col-md-3">
                    {{ Form::label('Professor Principal') }}
                    {{ Form::select('fk_professor', ['' => 'Selecione'] + $lista_professor, (isset($assinatura->fk_professor) ? $assinatura->fk_professor : null), ['class' => 'form-control']) }}
                </div>
                <div class="col-md-3">
                    {{ Form::label('Professor Principal %') }}
                    {{ Form::input('text', 'professorprincipal_share', (isset($assinatura->professorprincipal_share) ? $assinatura->professorprincipal_share : null), ['class' => 'form-control moeda', 'style' => 'width: 150px; text-align: right;']) }}
                </div>
            </div>
			<div class="form-group">
				<a href="{{ url()->previous() }}" class="btn btn-default">Cancel</a>
				{{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
			</div>
		{{ Form::close() }}
	</div>
@endsection
@push('js')
    <script type="text/javascript">
    $(document).ready(function () {
        $('.moeda').mask('#.##0,00', {reverse: true});
        if ($('#assinaturas_tipo').val() == '1') {
            $('.campos_full').show();
            $('.certificados').hide();
        }
        $('#assinaturas_tipo').change(function() {
            if($(this).val() == '2') {
                $('.campos-cursos_por_periodo').show();
                $('.certificado').show();
                $('.campos-trilha').hide();
                $('.campos_full').hide();
            } else if ($(this).val() == '3'){
                $('.campos-trilha').show();
                $('.certificado').show();
                $('.campos-cursos_por_periodo').hide();
                $('.campos_full').hide();
            } else if ($(this).val() == '1') {
                $('.campos_full').show();
                $('.certificado').hide();
                $('.campos-trilha').hide();
                $('.campos-cursos_por_periodo').hide();
            }
            else {
                $('.campos-cursos_por_periodo').hide();
                $('.campos_full').hide();
            }
        });

        $('.marcar').click(function () {
            let pageURL = window.location.origin;
            let id = $(this).prop('id')
            if ($(this).prop('checked')) {
                $.ajax({
                    url: pageURL + '/api/certificado/'+id+'/lista',
                    type : 'GET',
                    success : function(data) {
                        montaSelect(data.items, id);
                    },
                    error : function(error)
                    {
                        console.log(error)
                    }
                })
            } else {
                $( ".select"+ id).remove();
            }
        })
    })
    function montaSelect(dados, id) {
        let html = ''
        if (dados.length > 0) {
            html += '<div class="form-group select'+ id +'"><label for="fk_certificados['+id+']">Certificado Projeto '+ id +'</label><br/>'
            html += '<select name="fk_certificados['+ id +']" class="form-control">'
            html += '<option value="0">Selecione</option>'
            dados.forEach(dado => {
                html+= '<option value="'+dado.id+'">' + dado.titulo + '</option>'
            })
            html += '</select></div>'
        } else {
            html += '<p class="select'+ id +'">Projeto '+ id +' não possui certificados associados.</p>'
        }
        $('#certificados').append(html);
    }
    function marcarTodas() {
        $(this).prop('checked', !$(this).prop('checked'));
        $('.marcar').prop("checked", $(this).prop("checked"));
    }
</script>
@endpush
