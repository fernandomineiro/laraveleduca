@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>
        @if(Request::is('*/editar'))
            Atualizar
        @else
            Criar
        @endif

        Agenda de Curso Presencial</span></h2>
        <a href="{{ route('admin.agenda_curso') }}" class="label label-default">Voltar</a>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $agenda_curso, ['method' => 'PATCH', 'route' => ['admin.agenda_curso.atualizar', $agenda_curso->id]] ) }}
        @else
            {{ Form::open(['url' => '/admin/agenda_curso/salvar']) }}
        @endif

        <div class="form-group">
            {{ Form::label('Nome') }}
            {{ Form::input('text', 'nome', null, ['class' => 'form-control', '', 'placeholder' => 'Nome da agenda']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Turma') }}
            {{ Form::select('fk_turma', $lista_cursos, (isset($agenda_curso->fk_turma) ? $agenda_curso->fk_turma : null), ['class' => 'form-control']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Descrição') }}
            {{ Form::textarea('descricao', null, ['class' => 'form-control', 'id' => 'ckeditor']) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('Data Início') }}
            {{ Form::input('text', 'data_inicio', null, ['class' => 'form-control datepicker', '', 'placeholder' => 'Data Início']) }}
        </div>
        @if(Request::is('*/editar'))
            <div class="form-group col-md-6">
                {{ Form::label('Hora Início') }}
                {{ Form::input('text', 'hora_inicio', null, ['class' => 'form-control timepicker', '', 'placeholder' => 'Hora Início', 'id' => 'hora_inicio-'.$agenda_curso->id]) }}
            </div>
        @else
            <div class="form-group col-md-6">
                {{ Form::label('Hora Início') }}
                {{ Form::input('text', 'hora_inicio', null, ['class' => 'form-control timepicker', '', 'placeholder' => 'Hora Início']) }}
            </div>
        @endif
        <div class="form-group col-md-6">
            {{ Form::label('Data Fim') }}
            {{ Form::input('text', 'data_final', null, ['class' => 'form-control datepicker', '', 'placeholder' => 'Data Fim']) }}
        </div>
        @if(Request::is('*/editar'))
            <div class="form-group col-md-6">
                {{ Form::label('Hora Fim') }}
                {{ Form::input('text', 'hora_final', null, ['class' => 'form-control timepicker', '', 'placeholder' => 'Hora Fim', 'id' => 'hora_fim-'.$agenda_curso->id]) }}
            </div>
        @else
            <div class="form-group col-md-6">
                {{ Form::label('Hora Fim') }}
                {{ Form::input('text', 'hora_final', null, ['class' => 'form-control timepicker', '', 'placeholder' => 'Hora Fim']) }}
            </div>
        @endif
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
            timepickers.forEach( timepicker => {
                let id = timepicker.id
                let pathArray = window.location.pathname.split( '/' );
                let options = {}
                if (pathArray[4] === "editar" && $('#' + id).val()) {
                    options = {
                        twentyFour: true,
                        upArrow: 'fa fa-chevron-up fa-lg',  //The up arrow class selector to use, for custom CSS
                        downArrow: 'fa fa-chevron-down fa-lg', //The down arrow class selector to use, for custom CSS
                        now: $('#' + id).val()
                    }
                }
                $('#'+ id + '.timepicker').wickedpicker(options)
            })
            jQuery(".datepicker").datepicker({
                minDate: new Date(),
                format: "dd-mm-yyyy",
                dayNames: ["Domingo","Segunda","Terça","Quarta","Quinta","Sexta","Sábado"],
                dayNamesMin: ["D","S","T","Q","Q","S","S","D"],
                dayNamesShort: ["Dom","Seg","Ter","Qua","Qui","Sex","Sáb","Dom"],
                monthNames: ["Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"],
                monthNamesShort: ["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"],
                nextText: "Próximo",
                prevText: "Anterior"
            })
        })
    </script>
@endpush

