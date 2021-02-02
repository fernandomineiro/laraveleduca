        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('Título') }} <span><b>(Limite máximo de 60 caracteres)</b></span><small>*</small>
                    {{ Form::input('text', 'titulo', null,
                        [
                            'class' => 'form-control', 'data-msg-required' => 'Este campo é obrigatório',
                            'required' => true, 'maxlength' => '60'
                        ]
                    ) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    @if(Request::is('*/editar'))
                        <div class="form-group">
                            {{ Form::label('Status (workflow)') }}
                            {{ Form::select('status', $lista_status, (isset($curso->status) ? $curso->status : 1), ['class' => 'form-control']) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('Sobre o Curso') }}<small>*</small><span><b> (limite de 800 caracteres)</b></span>
                    {{ Form::textarea('descricao', null,
                        [
                            'class' => 'form-control', 'data-msg-required' => 'Este campo é obrigatório',
                            'required' => true, 'id' => 'descricao', 'maxlength' => '800'
                        ]
                    ) }}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    {{ Form::label('Teaser') }}
                    {{ Form::textarea('teaser', null,
                        [
                            'class' => 'form-control',
                            'style' => 'height: 40px;'
                        ])
                     }}
                    <a href="javascript:;" id="preview_vimeo" class="label label-success">Preview Video</a>
                </div>
            </div>
        </div>

        <div  class="well">
            <div id="renderizar_video"></div>
            <div id="video_description"></div>
            <div id="author_name"></div>
            <div id="video_title"></div>
            <div id="video_duration"></div>
        </div>
        @include("curso.blocks.projetos")

        <div class="row form-group">
            <div class="col-md-2">
                {{ Form::label('Professor Principal') }} <small>*</small>
                {{ Form::select('fk_professor', ['' => 'Selecione'] + $lista_professor, (isset($curso->fk_professor) ? $curso->fk_professor : null),
                [
                    'name' => 'fk_professor',
                    'class' => 'form-control myselect', 'allowClear' => true,
                    'data-placeholder' => 'Projetos (pesquise por nome do projeto)',
                    'id' => 'autocomplete-projetos',
                    'data-msg-required' => 'Este campo é obrigatório',
                    'required' => true
                ]) }}
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
            <?php if(isset($tags_cadastradas) && count($tags_cadastradas)) : ?>
            <?php foreach($tags_cadastradas as $item => $key) : ?>
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

        @if(Request::is('*/editar') && isset($usuariocadastro))
            <div class="form-group">
                <div class="alert alert-info">
                    <i class="fa fa-info"></i>
                    Usuário: {{$usuariocadastro->nome}} - {{isset($usuariocadastro->fk_faculdade_id) ? $faculdades[$usuariocadastro->fk_faculdade_id] . ' - ' : 'Sem projeto - '}} {{ ($curso->data_criacao) ? date('d/m/Y H:i:s', strtotime($curso->data_criacao)) : ''}}
                </div>
            </div>
        @endif
    </div>
