@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>Agendamento de Eventos</span></h2>

        @if(Request::is('*/editar'))
            <a href="/admin/agenda_eventos/{{$agenda_eventos->fk_evento}}/index" class="label label-default">Voltar</a>
            <hr class="hr"/>

            {{ Form::model( $agenda_eventos, ['method' => 'PATCH', 'route' => ['admin.agenda_eventos.atualizar', $agenda_eventos->id]] ) }}

            <div class="form-group">
                {{ Form::label('Evento') }}
                {{ Form::select('fk_evento', $eventos, (isset($agenda_eventos->fk_evento) ? $agenda_eventos->fk_evento : null), ['class' => 'form-control']) }}
            </div>
        @else
            <a href="/admin/agenda_eventos/{{$id_evento}}/index" class="label label-default">Voltar</a>
            <hr class="hr"/>
            {{ Form::open(['url' => '/admin/agenda_eventos/salvar']) }}

            <div class="form-group">
                {{ Form::label('Evento') }}
                {{ Form::select('fk_evento', $eventos, (isset($id_evento) ? $id_evento : null), ['class' => 'form-control']) }}
            </div>
        @endif

        <div class="form-group">
            {{ Form::label('Descrição') }}
            {{ Form::input('text', 'descricao', null, ['class' => 'form-control', '', 'placeholder' => 'Descrição']) }}
        </div>

        <div class="form-group">
            {{ Form::label('Data Início') }}
            @if(Request::is('*/editar'))
                {{ Form::input('text', 'data_inicio', implode('/', array_reverse(explode('-', $agenda_eventos->data_inicio))), ['class' => 'form-control datepicker', '', 'placeholder' => 'Data Início']) }}
            @else
                {{ Form::input('text', 'data_inicio', null, ['class' => 'form-control datepicker', '', 'placeholder' => 'Data Início']) }}
            @endif
        </div>
        <div class="form-group">
            {{ Form::label('Hora Início') }}
            @if(Request::is('*/editar'))
                {{ Form::input('text', 'hora_inicio', null, ['class' => 'form-control timepicker', '', 'placeholder' => 'Hora Início', 'id' => 'hora_inicio-'.$agenda_eventos->id]) }}
            @else
                {{ Form::input('text', 'hora_inicio', null, ['class' => 'form-control timepicker', '', 'placeholder' => 'Hora Início']) }}
            @endif
        </div>

        <div class="form-group">
            {{ Form::label('Data Fim') }}
            @if(Request::is('*/editar'))
                {{ Form::input('text', 'data_final', implode('/', array_reverse(explode('-', $agenda_eventos->data_final))), ['class' => 'form-control datepicker', '', 'placeholder' => 'Data Fim']) }}
            @else
                {{ Form::input('text', 'data_final', null, ['class' => 'form-control datepicker', '', 'placeholder' => 'Data Fim']) }}
            @endif
        </div>
        <div class="form-group">
            {{ Form::label('Hora Fim') }}
            @if(Request::is('*/editar'))
                {{ Form::input('text', 'hora_final', null, ['class' => 'form-control timepicker', '', 'placeholder' => 'Hora Fim', 'id' => 'hora_fim-'.$agenda_eventos->id]) }}
            @else
                {{ Form::input('text', 'hora_final', null, ['class' => 'form-control timepicker', '', 'placeholder' => 'Hora Fim']) }}
            @endif
        </div>

        <div class="form-group">
            {{ Form::label('Professor Palestrante') }}
            {{ Form::select('fk_professor', ['' => 'Selecione'] + $lista_professor, (isset($agenda_eventos->fk_professor) ? $agenda_eventos->fk_professor : null), ['class' => 'form-control']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Valor R$') }}
            {{ Form::input('text', 'valor', null, ['class' => 'form-control moeda', '', 'placeholder' => 'Valor R$']) }}
        </div>

        <div class="form-group">
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>
@endsection
@push('js')
    <script type="text/javascript">
        $(document).ready(function () {
            let timepickers = [...document.querySelectorAll('.timepicker')]
            timepickers.forEach(timepicker => {
                let id = timepicker.id
                let pathArray = window.location.pathname.split('/');
                let options = {}
                if (pathArray[4] === "editar" && $('#' + id).val()) {
                    options = {
                        twentyFour: true,
                        upArrow: 'fa fa-chevron-up fa-lg',  //The up arrow class selector to use, for custom CSS
                        downArrow: 'fa fa-chevron-down fa-lg', //The down arrow class selector to use, for custom CSS
                        now: $('#' + id).val()
                    }
                }
                $('#' + id + '.timepicker').wickedpicker(options)
            })
        })
    </script>
@endpush

