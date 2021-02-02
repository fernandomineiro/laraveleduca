@extends('layouts.app')
@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/css/bootstrap-colorpicker.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/js/bootstrap-colorpicker.js"></script>
    <div class="box padding20">
        <h2 class="table"><span>{{ $modulo['moduloDetalhes']->menu .' '.$modulo['moduloDetalhes']->modulo }}</span></h2>

        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $obj, ['method' => 'PATCH', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.atualizar', $obj->id], 'files' => true] ) }}
        @else
            {{ Form::open(['url' => 'admin/'.$modulo['moduloDetalhes']->uri.'/salvar', 'files' => true]) }}
        @endif
        <div class="form-group row">
            <div class="col-sm-5">
                {{ Form::label('Projeto') }}
                <small>(Projeto ao qual o estilo pertence)</small>
                {{ Form::select('fk_faculdade_id', $faculdades, (isset($obj->fk_faculdade_id) ? $obj->fk_faculdade : null), ['class' => 'form-control']) }}
            </div>
            <div class="col-sm-5">
                {{ Form::label('Descrição') }}
                <small>(Breve descrição para os estilos)</small>
                {{ Form::input('text', 'descricao', null, ['class' => 'form-control', '', 'placeholder' => 'Descrição']) }}
            </div>
        </div>
        <br><br>
        <div class="form-group row">
            @foreach($configVariaveis as $variable)
                <div class="col-sm-4 col-md-4">
                    {{ Form::label($variable->descricao) }}
                    @if($variable->tipo == 'color')
                        <div class="input-group colorpicker-component">
                            {{ Form::input('text',
                                $variable->nome,
                                !empty($configEstilosVariaveis[$variable->id]) ? $configEstilosVariaveis[$variable->id] : null,
                                ['class' => 'form-control', '', 'placeholder' => $variable->descricao]) }}
                            <span class="input-group-addon"><i></i></span>
                        </div>
                    @else
                        {{ Form::input('text', $variable->nome, null, ['class' => 'form-control', '', 'placeholder' => $variable->descricao]) }}
                    @endif
                </div>
            @endforeach
        </div>
        <div class="form-group">
            <a href="{{ route('admin.'.$modulo['moduloDetalhes']->rota)}}" class="btn btn-default">Voltar</a>
            <a href="{{ url()->current() }}" class="btn btn-default">Cancel</a>
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>
@endsection

@push('js')
    <script type="text/javascript">
        $(document).ready(function () {
            $('.colorpicker-component').colorpicker();
        });
    </script>
@endpush
