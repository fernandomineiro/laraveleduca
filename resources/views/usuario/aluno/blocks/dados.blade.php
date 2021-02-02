@if(Request::is('*/editar'))
    {{ Form::model( $objAluno, ['method' => 'PATCH', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.atualizar', $objAluno->id], 'files' => true, 'autocomplete' => 'off'] ) }}
    <input type="hidden" name="fk_usuario_id" id="fk_usuario_id" value="<?php echo $objAluno->fk_usuario_id; ?>"/>
    <input type="hidden" name="id" value="<?php echo $objAluno->id; ?>" />
    <input type="hidden" name="fk_endereco_id" value="<?php echo $objAluno->fk_endereco_id; ?>" />
@else
    {{ Form::open(['url' => '/admin/'.$modulo['moduloDetalhes']->uri.'/salvar', 'files' => true, 'autocomplete' => 'off']) }}
@endif

<div class="row">
    <div class="form-group col-md-6">
        {{ Form::label('Faculdade *') }}
        {{ Form::select('fk_faculdade_id', $lista_faculdades,
                (isset($obj->fk_faculdade_id) ? $obj->fk_faculdade_id : null),
                ['class' => 'form-control' , 'readonly' => Request::is('*/editar')]) }}
    </div>
</div>

<div class="row">
    <div class="form-group col-md-2">
        {{ Form::label('Gênero') }}
        {{ Form::select('genero', $lista_generos, (isset($objAluno->genero) ? $objAluno->genero : ''), ['class' => 'form-control']) }}
    </div>
    <div class="form-group col-md-4">
        {{ Form::label('Nome *') }}
        <small>(Max 30 caracteres)</small>
        {{ Form::input('text', 'nome', null, ['class' => 'form-control', 'readonly' => Request::is('*/editar'), 'placeholder' => 'Nome', 'maxlength' => 30, 'autocomplete' => 'Off']) }}
    </div>
    <div class="form-group col-md-4">
        {{ Form::label('Sobrenome *') }}
        <small>(Max 60 caracteres)</small>
        {{ Form::input('text', 'sobre_nome', null, ['class' => 'form-control', 'readonly' => Request::is('*/editar'), 'placeholder' => 'Sobrenome', 'maxlength' => 60]) }}
    </div>
    @if(!Request::is('*/editar'))
        <div class="form-group  col-md-2">
            {{ Form::label('CPF *') }}
            {{ Form::input('text', 'cpf', null, ['class' => 'form-control cpf','readonly' => Request::is('*/editar'), 'onkeypress'=>'return onlyNumbers(event)', 'onblur'=>'return onlyNumbers(event)', '', 'placeholder' => 'CPF', 'maxlength' => '14']) }}
        </div>
    @endif
    @if(Request::is('*/editar'))
        <div class="form-group  col-md-2">
            {{ Form::label('CPF') }}
            {{ Form::input('text', 'cpf', null, ['class' => 'form-control cpf', 'readonly' => Request::is('*/editar'), 'onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'CPF', 'maxlength' => '14']) }}
        </div>
    @endif
    <div class="form-group  col-md-2">
        {{ Form::label('Identidade') }}
        {{ Form::input('text', 'identidade', null, ['class' => 'form-control', 'readonly' => Request::is('*/editar'), 'placeholder' => 'Identdade', 'maxlength' => '14']) }}
    </div>
</div>
<div class="row">
    <div class="form-group col-md-4">
        {{ Form::label('Data Nascimento *') }}
        {{ Form::input('text', 'data_nascimento', isset($objAluno->data_nascimento) ? implode('/', array_reverse(explode('-', $objAluno->data_nascimento))) : null, ['class' => 'form-control datepicker', '', 'placeholder' => 'Data Nascimento', 'readonly' => Request::is('*/editar')]) }}
    </div>
    <div class="form-group  col-md-4">
        {{ Form::label('Telefone Fixo') }}
        {{ Form::input('text', 'telefone_1', null, ['class' => 'form-control telefone', 'readonly' => Request::is('*/editar'),'onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'Telefone Fixo']) }}
    </div>
    <div class="form-group  col-md-4">
        {{ Form::label('Telefone Celular *') }}
        {{ Form::input('text', 'telefone_2', null, ['class' => 'form-control celular', 'readonly' => Request::is('*/editar'),'onkeypress'=>'return onlyNumbers(event)', '', 'placeholder' => 'Celular']) }}
    </div>
</div>
<hr class="hr"/>
<div class="row">
    <div class="form-group col-md-2">
        {{ Form::label('CEP *') }}
        {{ Form::input('text', 'cep', isset($objEndereco->cep) ? $objEndereco->cep : null, ['class' => 'form-control cep', 'readonly' => Request::is('*/editar'), '', 'placeholder' => 'CEP' , 'id' => 'cep']) }}
    </div>
    <div class="form-group  col-md-4">
        {{ Form::label('Logradouro *') }}
        <small>(Max 90 caracteres)</small>
        {{ Form::input('text', 'logradouro', isset($objEndereco->logradouro) ? $objEndereco->logradouro : null, ['class' => 'form-control logradouro', 'readonly' => Request::is('*/editar'), '', 'placeholder' => 'Logradouro', 'id' => 'logradouro', 'maxlength' => 90]) }}
    </div>
    <div class="form-group col-md-2">
        {{ Form::label('Nº *') }}
        {{ Form::input('text', 'numero', isset($objEndereco->numero) ? $objEndereco->numero : null, ['class' => 'form-control numero', 'readonly' => Request::is('*/editar'), '', 'placeholder' => 'Nº']) }}
    </div>
    <div class="form-group  col-md-4">
        {{ Form::label('Complemento') }}
        <small>(Max 40 caracteres)</small>
        {{ Form::input('text', 'complemento', isset($objEndereco->complemento) ? $objEndereco->complemento : null, ['class' => 'form-control complemento', 'readonly' => Request::is('*/editar'), '', 'placeholder' => 'Complemento', 'id' => 'complemento', 'maxlength' => 40]) }}
    </div>
</div>
<div class="row">
    <div class="form-group  col-md-4">
        {{ Form::label('Bairro *') }}
        <small>(Max 50 caracteres)</small>
        {{ Form::input('text', 'bairro', isset($objEndereco->bairro) ? $objEndereco->bairro : null, ['class' => 'form-control bairro', 'readonly' => Request::is('*/editar'), 'placeholder' => 'Bairro', 'id' => 'bairro', 'maxlength' => 50]) }}
    </div>
    <div class="form-group  col-md-4">
        {{ Form::label('Estado *') }}
        <div id="estados">
            {{ Form::select('fk_estado_id', ['' => 'Selecione'] + $estados, (isset($objEndereco->fk_estado_id) ? $objEndereco->fk_estado_id : null), ['class' => 'form-control', 'readonly' => Request::is('*/editar')]) }}
        </div>
    </div>
    <div class="form-group  col-md-4">
        {{ Form::label('Cidade *') }}
        <div id="cidades">
            {{ Form::select('fk_cidade_id', ['' => 'Selecione'] + $cidades, (isset($objEndereco->fk_cidade_id) ? $objEndereco->fk_cidade_id : null), ['class' => 'form-control', 'readonly' => Request::is('*/editar')]) }}
        </div>
    </div>
</div>

<hr class="hr"/>
<div class="row">
    <div class="form-group col-md-2">
        {{ Form::label('Curso Superior *') }}<br />
        {{ Form::select('curso_superior',
            ['sim' => 'SIM', 'não' => 'NÃO'],
            (isset($objAluno->curso_superior) ? strtolower($objAluno->curso_superior) : 'não'),
            ['class' => 'form-control', 'readonly' => Request::is('*/editar'), 'style' => 'text-transform: uppercase;', 'id' => 'curso_superior']) }}
    </div>
</div>
<div class="row" id="bloco_universidade" style="display: <?php echo (isset($objAluno->curso_superior) && strtolower($objAluno->curso_superior) == 'sim') ? 'block' : 'none'; ?>">
    <div class="form-group col-md-3">
        {{ Form::label('Faculdade') }}
        {{ Form::select('faculdade', $lista_faculdades,
                (isset($objAluno->universidade) ? $objAluno->universidade : null),
                ['class' => 'form-control', 'id' => 'faculdade' , 'readonly' => Request::is('*/editar')]) }}
    </div>
    <div class="form-group col-md-3">
        {{ Form::label('Curso') }}
        {{ Form::input('text', 'curso', isset($cursoAluno) ? $cursoAluno : null, ['class' => 'form-control curso', 'readonly' => Request::is('*/editar'), 'placeholder' => 'curso', 'id' => 'curso']) }}
    </div>
    <div class="form-group col-md-3">
        {{ Form::label('Semestre') }}
        {{ Form::select('semestre', $semestres, (isset($objAluno->semestre) ? $objAluno->semestre : null), ['class' => 'form-control', 'readonly' => Request::is('*/editar'), 'style' => 'text-transform: uppercase;']) }}
    </div>
    <div class="form-group  col-md-3">
        {{ Form::label('RA') }}
        {{ Form::input('text', 'matricula', isset($objAluno->matricula) ? $objAluno->matricula : null, ['class' => 'form-control', 'readonly' => Request::is('*/editar'), 'placeholder' => 'RA']) }}
    </div>
    <div class="form-group  col-md-3" id="universidade_outros" @if(!empty($objAluno->universidade) && $objAluno->universidade != 'outro') style="display: none" @endif;>
        {{ Form::label('(Outros) Universidade') }}
        {{ Form::input('text', 'universidade_outro', isset($objAluno->universidade_outro) ? $objAluno->universidade_outro : null, ['class' => 'form-control' , 'readonly' => Request::is('*/editar'), 'placeholder' => '(Outros) Universidade']) }}
    </div>
</div>

<hr class="hr"/>
<div class="row">
    <div class="form-group col-md-2">
        {{ Form::label('Faz Pós-graduação? *') }}<br />
        {{ Form::select('curso_especializacao',
            ['sim' => 'SIM', 'não' => 'NÃO'],
            (isset($objAluno->curso_especializacao) ? strtolower($objAluno->curso_especializacao) : 'não'),
            ['class' => 'form-control', 'readonly' => Request::is('*/editar'), 'style' => 'text-transform: uppercase;', 'id' => 'curso_especializacao']) }}
    </div>
</div>
<div class="row" id="bloco_especializacao" style="display: <?php echo (isset($objAluno->curso_especializacao) && strtolower($objAluno->curso_especializacao) == 'sim') ? 'block' : 'none'; ?>">
    <div class="form-group col-md-3">
        {{ Form::label('Qual modalidade?') }}
        {{ Form::select('tipo_curso_especializacao', [

            'Lato Sensu' => [
                'especializacao' => 'Especialização',
                'aperfeicoamento' => 'Aperfeiçoamento',
                'mba' => 'MBA',
            ],
            'Stricto Sensu' => [
                'mestrado' => 'Mestrado',
                'doutorado' => 'Doutorado',
            ]
        ],
                (isset($objAluno->tipo_curso_especializacao) ? $objAluno->tipo_curso_especializacao : null),
                ['class' => 'form-control' , 'readonly' => Request::is('*/editar')]) }}
    </div>
    <div class="form-group col-md-3">
        {{ Form::label('Qual Instituição?') }}
        {{ Form::select('especializacao_universidade', $lista_faculdades,
                (isset($objAluno->especializacao_universidade) ? $objAluno->especializacao_universidade : null),
                ['class' => 'form-control', 'id' => 'especializacao_universidade' , 'readonly' => Request::is('*/editar')]) }}
    </div>
    <div class="form-group col-md-3" id="instituicao_outros" @if(!empty($objAluno->especializacao_universidade) && $objAluno->especializacao_universidade != 'outro') style="display: none" @endif;>
        {{ Form::label('(Outros) Instituição *') }}
        {{ Form::input('text', 'curso', (isset($objAluno->especializacao_universidade_outro) ? $objAluno->especializacao_universidade_outro : null), ['class' => 'form-control curso', 'readonly' => Request::is('*/editar'), 'placeholder' => '(Outros) Instituição *', 'id' => 'especializacao_universidade_outro']) }}
    </div>
</div>
<hr class="hr"/>
<div class="row">
    @if(!Request::is('*/editar'))
        <div class="form-group  col-md-4">
            {{ Form::label('E-mail *') }}
            {{ Form::input('email', 'email', null, ['class' => 'form-control', '', 'placeholder' => 'E-mail']) }}
        </div>
    @endif
    @if(Request::is('*/editar'))
        <div class="form-group  col-md-4">
            <br>
            {{ Form::label('E-mail *') }}
            {{ Form::input('email', 'email', isset($objUsuario->email) ? $objUsuario->email : null, ['class' => 'form-control','readonly' => 'readonly', '', 'placeholder' => 'E-mail']) }}
        </div>
    @endif
    <div class="form-group col-md-4">
        <label>Senha *(mínimo de 8 dígitos)</label>@if(Request::is('*/editar')) (Preencher senha somente se deseja mudar!) @endif
        {{ Form::input('password', 'password', null, ['class' => 'form-control', '', 'placeholder' => 'Senha', 'value' => '']) }}
    </div>
    <div class="form-group col-md-4">
        <label>Confirma *(mínimo de 8 dígitos)</label>@if(Request::is('*/editar')) (Preencher senha somente se deseja mudar!) @endif
        {{ Form::input('password', 'password_confirmation', null, ['class' => 'form-control', '', 'placeholder' => 'Senha', 'value' => '']) }}
    </div>

    <div id="box_upload" class="form-group  col-md-3">
        @if(Request::is('*/editar') && isset($objUsuario->foto))
            <div class="form-group col-md-8">
                <img src="{{ Storage::disk('s3')->url('files/usuario/' . $objUsuario->foto) }}" height="100"/>
            </div>
        @endif
        @if(Request::is('*/editar') && !isset($objUsuario->foto))
            <div class="form-group col-md-8">
                <img src="{{URL::asset('files/default.png')}}" height="100"/>
            </div>
        @endif
        <br />
        @if(!Request::is('*/editar'))
            <div class="col-md-4">
                <div id="box_upload" class="row form-group well">
                    {{ Form::label('Foto') }}
                    {{ Form::file('foto') }}
                </div>
            </div>
        @endif
    </div>
</div>
<hr class="hr"/>
<div class="form-group">
    <a href="{{ route('admin.aluno') }}" class="btn btn-default">Voltar</a>
    {{ Form::submit('Salvar', ['class' => 'btn btn-primary', 'id' => 'botao_salvar']) }}
</div>
{{ Form::close() }}
