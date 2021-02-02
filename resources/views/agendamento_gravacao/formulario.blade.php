@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>Agendar Gravação</span></h2>
        <a href="{{ route('admin.agendamentogravacao') }}" class="label label-default">Voltar</a>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $agendamentogravacao, ['method' => 'PATCH', 'route' => ['admin.agendamentogravacao.atualizar', $agendamentogravacao->id]] ) }}
        @else
            {{ Form::open(['url' => '/admin/agendamentogravacao/salvar']) }}
        @endif
        <div class="form-group">
            {{ Form::label('Parceiro') }}
            {{ Form::select('fk_parceiro', $lista_parceiro, (isset($agendamentogravacao->fk_parceiro) ? $agendamentogravacao->fk_parceiro : null), ['class' => 'form-control']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Data') }}
            {{ Form::input('text', 'data', null, ['class' => 'form-control datepicker', '', 'placeholder' => 'Data']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Hora') }}
            {{ Form::input('text', 'hora', null, ['class' => 'form-control timepicker', '', 'placeholder' => 'Hora']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Local') }}
            {{ Form::input('text', 'local', null, ['class' => 'form-control', '', 'placeholder' => 'Local']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Status') }}
            {{ Form::select('status', $lista_status , (isset($agendamentogravacao->status) ? $agendamentogravacao->status : null), ['class' => 'form-control']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Nome Curso') }}
            {{ Form::input('textarea', 'nome_curso', null, ['class' => 'form-control', '', 'placeholder' => 'Nome Curso']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Possui Anexo') }}
            {{ Form::select('possui_anexo', $lista_check , (isset($agendamentogravacao->possui_anexo) ? $agendamentogravacao->possui_anexo : null), ['class' => 'form-control']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Material Enviado') }}
            {{ Form::select('material_enviado', $lista_check , (isset($agendamentogravacao->material_enviado) ? $agendamentogravacao->material_enviado : null), ['class' => 'form-control']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Produtora') }}
            {{ Form::select('fk_produtora', $lista_produtora, (isset($agendamentogravacao->fk_produtora) ? $agendamentogravacao->fk_produtora : null), ['class' => 'form-control']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Projeto') }}
            {{ Form::select('fk_projeto', $lista_projetos, (isset($agendamentogravacao->fk_projeto_id) ? $agendamentogravacao->fk_projeto_id : null), ['class' => 'form-control']) }}
        </div>
        <div class="form-group">
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>
@endsection
