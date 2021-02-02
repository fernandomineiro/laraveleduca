<div class="row">
    <div class="col-md-4">
        Projeto:
        {{Form::select('faculdade[]', $faculdades, null, ['multiple' => 'multiple', 'name' => 'faculdades[]', 'class' => 'form-control'])}}
    </div>
</div>

<div class="row">&nbsp;</div>
<div class="row">
    <div class="col-md-6">
        Nome:
        <input type="text" class="form-control" name="nome">
    </div>
    <div class="col-md-6">
        Descrição:
        <input type="text" class="form-control" name="descricao">
    </div>
</div>

<div class="row">&nbsp;</div>
<div class="row">
    <div class="col-md-6">
        Certificado:
        <select name="" id="" class="form-control"></select>
    </div>
    <div class="col-md-6">
        Opção de Liberação:
        <select name="faculdade" id="tipo-liberacao" class="form-control">
            <option value="1">Liberação Total</option>
            <option value="2">Liberação Gradativa</option>
            <option value="3">Liberação Parcial</option>
        </select>
    </div>
</div>

<div class="row">&nbsp;</div>

<div class="row" style="display: none" id="box-liberar-gradual">
    <div class="col-md-12">
        <div class="col-md-6"></div>
        <div class="col-md-6">
            <div class="" style="line-height: 40px;width: 15%;float: left;">Liberar a cada</div>
            <div class="col-md-2">
                <input type="number" class="form-control" autocomplete="Off">
            </div>
            <div class="" style="line-height: 40px;width: 5%;float: left;">dias</div>
            <div class="col-md-2">
                <input type="number" class="form-control" autocomplete="Off">
            </div>
            <div class="" style=" line-height: 40px;width: 7%;float: left;">cursos.</div>
        </div>
    </div>
</div>
<div id="box-liberar-parcial" class="row" style="display: none">
    <div class="col-md-5">
        <div class="panel">
            <div class="panel-head">Selecione os Cursos para a assinatura</div>
            <div class="panel-body">
                @for($i = 1; $i <= 10; $i++)
                    <div class="col-md-12" id="{{ $i }}">
                        Curso {{ $i }}
                    </div>
                @endfor
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="col-md-12" style="height: 130px;">
            Adicionar
            <br>
            <i class="fa fa-long-arrow-right" aria-hidden="true" style="font-size: 45px;"></i>
        </div>
        <div class="col-md-12">
            Remover
            <br>
            <i class="fa fa-long-arrow-left" aria-hidden="true" style="font-size: 45px;"></i>
        </div>
    </div>
    <div class="col-md-5">
        <div class="panel">
            <div class="panel-head">Cursos selecionados</div>
            <div class="panel-body"></div>
        </div>
    </div>
</div>

<div class="row">&nbsp;</div>
<div class="row">
    <div class="col-md-4">
        Preço:
        <input type="number" class="form-control">
    </div>
    <div class="col-md-4">
        Preço de venda:
        <input type="number" class="form-control">
    </div>
</div>
<div class="row">&nbsp;</div>
<div class="row">
    <div class="col-md-4">
        Tipo de plano:
        <select name="" id="" class="form-control">
            <option value="1">Anual</option>
            <option value="2">Semestral</option>
            <option value="3">Livre cancelamento</option>
            <option value="4">Cancelamento manual</option>
        </select>
    </div>
</div>
<div class="row">&nbsp;</div>

@push('js')
    <script>
        $('#tipo-liberacao').change(function () {
            $('#box-liberar-gradual').hide();
            $('#box-liberar-parcial').hide();
            if ($(this).val() == 2) {
                $('#box-liberar-gradual').show();
            }
            if ($(this).val() == 3) {
                $('#box-liberar-parcial').show();
            }
        });
    </script>
@endpush
