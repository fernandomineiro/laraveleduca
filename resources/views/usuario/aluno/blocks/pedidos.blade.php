<div class="padding20">
    {{ Form::open(['url' => '/admin/'.$modulo['moduloDetalhes']->uri.'/'.$objAluno->id.'/criar-pedido-aluno', 'autocomplete' => 'off']) }}
        <div class="row">
            <div class="form-group col-md-7">
                {{Form::label('Produto') }} <small>*</small>
                {{Form::select('produto',
                    $objAssinaturas, [],
                    [
                        'id' => 'produto', 'name' => 'produto',
                        'class' => 'form-control', 'allowClear' => true,
                        'placeholder' => 'Selecione um tipo de produto'
                     ]
                )}}
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary" style="margin-top: 23px;" >Criar pedido</button>
            </div>
        </div>
    {{ Form::close() }}

    <br>
    <hr>
    <div class="row">
        <div class="form-group col-md-10">
        </div>
        <div class="form-group col-md-2">
{{--                <button class="btn btn-danger" onclick="deletarTag({{ $tag->id }}, this)">Deletar</button>--}}
        </div>
        <table class="table table-bordered table-striped dataTable">
            <thead>
                <tr>
                    <th>Data Venda</th>
                    <th>Id Pedido</th>
                    <th>IES</th>
                    <th>Formato</th>
                    <th>Curso</th>
                    <th>Status</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>

                @foreach($objPedidos as $pedido)
                    <tr style="background: {{ $pedido->status_cor }}">
                        <td>{{ implode('/', array_reverse(explode('-', substr($pedido->criacao, 0, 10)))) }} {{ substr($pedido->criacao, 11, 8) }}</td>
                        <td>{{ $pedido->pid }}</td>
                        <td><?= $pedido->faculdade['fantasia']; ?></td>
                        <td>
                            @foreach ($pedido->items as $item)
                                @if (!empty($item->evento))
                                    <?= 'Evento' ?>
                                @elseif (!empty($item->curso))
                                    <?= 'Curso' ?>
                                @elseif (!empty($item->trilha))
                                    <?= 'Trilha' ?>
                                @elseif (!empty($item->assinatura))
                                    <?= 'Assinatura' ?>
                                @endif
                            @endforeach
                        </td>
                        <td>
                            @foreach ($pedido->items as $item)
                                @if (!empty($item->evento))
                                    <?= isset($item->evento->titulo) ? $item->evento->titulo : ''; ?>
                                @elseif (!empty($item->curso))
                                    <?= isset($item->curso->titulo) ? $item->curso->titulo : ''; ?>
                                @elseif (!empty($item->trilha))
                                    <?= isset($item->trilha->titulo) ? $item->trilha->titulo : ''; ?>
                                @elseif (!empty($item->assinatura))
                                    <?= isset($item->assinatura->titulo) ? $item->assinatura->titulo : ''; ?>
                                @endif
                            @endforeach
                        </td>
                        <td>{{$pedido->status_titulo}}</td>
                        <?php $vlr_liquido = floatval($pedido->valor_bruto) - (floatval($pedido->valor_desconto) + floatval($pedido->valor_imposto)) ?>
                        <td>R$ {{ number_format( $vlr_liquido , 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

