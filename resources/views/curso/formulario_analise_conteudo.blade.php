@extends('layouts.app')
@section('content')
	<div class="box padding20">
	    <h2 class="table"><span>Cursos</span></h2>
	    <a href="{{ route('admin.curso') }}" class="label label-default">Voltar</a>
		<hr class="hr" />

		@if(Request::is('*/editar'))
			{{ Form::model( $curso, ['method' => 'PATCH', 'route' => ['admin.curso.atualizar', $curso->id], 'files' => true] ) }}
		@else
			{{ Form::open(['url' => '/admin/curso/salvar']) }}
		@endif

			<?php if(isset($curso->imagem)) : ?>
				<img src="{{URL::asset('files/curso/imagem/' . $curso->imagem)}}" height="100"/>
			<?php endif; ?>
			<br />
			<div id="box_upload" class="form-group">
				{{ Form::label('Imagem') }}
				{{ Form::file('imagem') }}
			</div>

			<div class="form-group">
				{{ Form::label('Status') }}
				{{ Form::select('status', $lista_status, (isset($curso->status) ? $curso->status : 1), ['class' => 'form-control']) }}
			</div>
			<div class="form-group">
				{{ Form::label('Título do Curso') }}
				{{ Form::input('text', 'titulo', null, ['class' => 'form-control', '']) }}
			</div>
			<div class="form-group">
				{{ Form::label('Teaser') }}
				{{ Form::input('text', 'teaser', null, ['class' => 'form-control', '']) }}
			</div>
			<div class="form-group">
				{{ Form::label('Descrição do Curso') }}
				{{ Form::textarea('descricao', null, ['class' => 'form-control', 'id' => 'ckeditor']) }}
			</div>
			<div class="form-group">
				{{ Form::label('Tipo de Curso') }}
				{{ Form::select('fk_cursos_tipo', $lista_tipos, (isset($curso->fk_cursos_tipo) ? $curso->fk_cursos_tipo : null), ['class' => 'form-control', 'style' => 'width: 50%; min-width: 120px;', 'id' => 'cursos_tipo']) }}
			</div>

			<div class="form-group campos-presencial"
				@if(!isset($curso-> fk_cursos_tipo) || (isset($curso->fk_cursos_tipo) && $curso->fk_cursos_tipo == 1))
					style="display: none;"
				@else
					style="display: block;"
				@endif>
				{{ Form::label('Endereço (das aulas presenciais)') }}
				{{ Form::input('text', 'endereco_presencial', isset($curso->endereco_presencial) ? $curso->endereco_presencial : '', ['class' => 'form-control moeda', 'style' => 'text-align: right;']) }}

			</div>
			<div class="form-group campos-presencial"
				@if(!isset($curso->fk_cursos_tipo) || (isset($curso->fk_cursos_tipo) && $curso->fk_cursos_tipo == 1))
					style="display: none;"
				@else
					style="display: block;"
				@endif>
				{{ Form::label('Datas e Horários (das aulas presenciais)') }}
				{{ Form::textarea('descricao_datas_presencial', null, ['class' => 'form-control', 'id' => 'ckeditor', 'style' => 'height: 70px;']) }}
			</div>

			<div class="form-group">
				{{ Form::label('Categorias') }}
				<br />
				<?php foreach($lista_categorias as $key => $item) : ?>
					{{ Form::checkbox('fk_cursos_categoria_' . $key, (isset($item['descricao']) ? $item['descricao'] : ''), (isset($item['ativo']) && ($item['ativo'] == '1')) ? true : false) }}
					{{ $item['descricao'] }}
					<br />
				<?php endforeach; ?>
				<hr />
			</div>

			<div class="form-group">
				{{ Form::label('Projeto') }}
				{{ Form::select('fk_faculdade', $lista_faculdades, (isset($curso->fk_faculdade) ? $curso->fk_faculdade : null), ['class' => 'form-control', 'style' => 'width: 50%; min-width: 120px;']) }}
			</div>
			<div class="form-group">
				{{ Form::label('De: (Somente informativo)') }}
				{{ Form::input('text', 'valor_de', isset($dados_valor->valor_de) ? $dados_valor->valor_de : '', ['class' => 'form-control moeda', 'style' => 'width: 150px; text-align: right;']) }}
			</div>
			<div class="form-group">
				{{ Form::label('Valor (R$)') }}
				{{ Form::input('text', 'valor', isset($dados_valor->valor) ? $dados_valor->valor : '', ['class' => 'form-control moeda', 'style' => 'width: 150px; text-align: right;']) }}
			</div>

			<div class="form-group">
				{{ Form::label('Número Máximo de Alunos') }}
				{{ Form::input('text', 'numero_maximo_alunos', isset($curso->numero_maximo_alunos) ? $curso->numero_maximo_alunos : '', ['class' => 'form-control', 'style' => 'width: 150px; text-align: right;']) }}
			</div>
			<div class="form-group">
				{{ Form::label('Número Mínimo de Alunos') }}
				{{ Form::input('text', 'numero_minimo_alunos', isset($curso->numero_minimo_alunos) ? $curso->numero_minimo_alunos : '', ['class' => 'form-control', 'style' => 'width: 150px; text-align: right;']) }}
			</div>

			<h3> tags: </h3>
			<hr />
			<?php foreach($lista_tags as $key => $item) : ?>
				<a href="javascript:void(0);" class="label label-warning">{{ $key }}</a>  <a href="javascript:void(0);" class="label label-danger">x</a>
				<br /><br />
			<?php endforeach; ?>
			<hr />
			<div class="form-group">
				{{ Form::label('Adicionar tags:') }}
				{{ Form::input('text', 'tags', null, ['class' => 'form-control', 'style' => 'width: 50%']) }}
				<label> Separe as tag por (;) ponto e vingula. </label>
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

            $('#cursos_tipo').change(function() {
                if($(this).val() == '1') {
                    $('.campos-presencial').hide();
                } else {
                    $('.campos-presencial').show();
                }
            });

            $('.moeda').mask('#.##0,00', {reverse: true});
            CKEDITOR.replace('descricao');
        })
    </script>
@endpush
