@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>{{ $modulo['moduloDetalhes']->menu .' '.$modulo['moduloDetalhes']->modulo }}</span></h2>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $obj, ['method' => 'PATCH', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.atualizar', $obj->id], 'files' => true] ) }}
        @else
            {{ Form::open(['url' => 'admin/'.$modulo['moduloDetalhes']->uri.'/salvar', 'files' => true]) }}
        @endif
        <div class="form-group row">
            <div class="col-sm">
                <div class="col-sm-5">
                    {{ Form::label('Projeto') }}
                    {{ Form::select('fk_faculdade_id', $faculdades, (isset($obj->fk_faculdade_id) ? $obj->fk_faculdade : null), ['class' => 'form-control']) }}
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-12">
                {{ Form::label('Texto Padrão: Página - Trilha de Conhecimento') }}
                {{ Form::textarea('pagina_trilha_conhecimento', null, ['class' => 'form-control', 'id' => 'editor1']) }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <button type="button" class="btn btn-danger" onclick="window.location.href='{{ route('admin.'.$modulo['moduloDetalhes']->rota)}}'">Voltar</button>
            </div>
            <div class="">
                {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
            </div>
        </div>
        {{ Form::close() }}
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            CKEDITOR.replace('editor1');
            $('.textarea').wysihtml5();
        })
    </script>
@endpush
