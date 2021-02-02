@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>{{ $modulo['moduloDetalhes']->menu .' '.$modulo['moduloDetalhes']->modulo }}</span></h2>
        <a href="{{ route('admin.'.$modulo['moduloDetalhes']->rota)}}" class="label label-default">Voltar</a>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $obj, ['method' => 'PATCH', 'route' => ['admin.'.$modulo['moduloDetalhes']->rota.'.atualizar', $obj->id], 'files' => true] ) }}
        @else
            {{ Form::open(['url' => 'admin/'.$modulo['moduloDetalhes']->uri.'/salvar', 'files' => true]) }}
        @endif
        <div class="form-group row">
            <div class="col-sm">
                <div class="col-sm-8">
                    {{ Form::label('Projeto') }}
                    {{ Form::select('fk_faculdade_id', $faculdades, (isset($obj->fk_faculdade_id) ? $obj->fk_faculdade : null), ['class' => 'form-control']) }}
                </div>
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('Slug') }}
            {{ Form::input('text', 'slug', null, ['class' => 'form-control', '', 'placeholder' => 'Slug']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Título') }}
            {{ Form::input('text', 'titulo', null, ['class' => 'form-control', '', 'placeholder' => 'Título']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Categoria') }}
            {{ Form::input('text', 'categoria', null, ['class' => 'form-control', '', 'placeholder' => 'Categoria']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Categoria URL') }}
            {{ Form::input('text', 'categoria_url', null, ['class' => 'form-control', '', 'placeholder' => 'Categoria URL']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Posição Header/Home/Footer') }}
            {{ Form::input('text', 'posicao', null, ['class' => 'form-control', '', 'placeholder' => 'Posição Header/Home/Footer']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Altura') }}
            {{ Form::input('text', 'altura', null, ['class' => 'form-control', '', 'placeholder' => 'Altura']) }}
        </div>
        <div class="form-group">
            {{ Form::label('Cor') }}
            <div class="input-group colorpicker-component">
                {{ Form::input('text', 'cor', null, ['class' => 'form-control', '', 'placeholder' => 'cor']) }}
                <span class="input-group-addon"><i></i></span>
            </div>
        </div>
        <div class="form-group">
            {{ Form::label('Imagem Fundo URL') }}
            {{ Form::input('text', 'img_bacground_url', null, ['class' => 'form-control', '', 'placeholder' => 'Imagem Fundo URL']) }}
        </div>
        <div class="form-group">
            {{ Form::label('CSS Personalizado') }}
            {{ Form::textarea('css_personalizado', null, ['class' => 'form-control', 'id' => 'ckeditor']) }}
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
            $('.colorpicker-component').colorpicker();
        });
    </script>
@endpush
