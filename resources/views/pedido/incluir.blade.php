@extends('layouts.app')

@section('content')
<form action="/admin/pedido/salvar" method="post">
    {{ csrf_field() }}
    <div class="form-group" style="width: 200px;">
        <label for="valorBruto">Valor Bruto</label>
        <input type="text" class="form-control" id="valorBruto" name="valor_bruto">
    </div>

    <div class="form-group" style="width: 200px;">
        <label for="valorBruto">Valor Desconto</label>
        <input type="text" class="form-control" id="valorBruto" name="valor_desconto">
    </div>

    <div class="form-group" style="width: 200px;">
        <label for="valorBruto">Valor Imposto</label>
        <input type="text" class="form-control" id="valorBruto" name="valor_imposto">
    </div>

    {{--<div class="form-group" style="width: 200px;">
        <label for="valorBruto">Status</label>
        <select name="status" class="form-control">
            <option value="">Selecione</option>
            @foreach($status as $s)
                <option value="{{$s->id}}">{{$s->titulo}}</option>
            @endforeach
        </select>
    </div>--}}

    <div class="form-group" style="width: 200px;">
        <input type="submit" class="form-control" value="Salvar">
    </div>
</form>

@endsection
