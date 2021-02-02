<div style="overflow-x:auto;">
    <table class="table table-bordered table-striped ">
        <thead>
            <tr>
                <th>Data de Venda</th>
                <th>ID do Pedido</th>
                <th>Produto Adquirido</th>
                <th>NFE</th>
                <th>Nome do Projeto</th>
                <th>ID do Aluno</th>
                <th>Nome do aluno</th>
                <th>CPF</th>
                <th>Status do Pagamento</th>
                <th>Método de Pagamento</th>
                <th>Código do Cupom</th>
                <th>Valor do Cupom</th>
                <th>Valor Pago Bruto</th>
                <th>Crédito ou Débito [3,99%]</th>
                <th>ISS [5%]</th>
                <th>PIS/Cofins [3,65%]</th>
                <th>IRPJ/CSLL [7,5%]</th>
                <th>Tarifa Boleto</th>
                <th>Tarifa Processamento</th>
                <th>Valor Líquido</th>
                <th>Qtd Parcelas</th>
                <th>Valor Parcela</th>
                <th>Total Líquido Parcelado</th>
                <th>Valor Pago</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedidos as $pedido)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($pedido->data_venda)->format('d/m/Y') }}</td>
                    <td>{{ $pedido->pedido_pid }}</td>
                    <td>{{ $pedido->produto_nome ? $pedido->produto_nome : '---' }}</td>
                    <td><?php $recebido = json_decode($pedido->recebido); echo isset($recebido->number) ? $recebido->number : '---' ?></td>
                    <td>{{ $pedido->faculdade_nome }}</td>
                    <td>{{ $pedido->aluno_id }}</td>
                    <td>{{ $pedido->aluno_nome }} {{ $pedido->aluno_sobrenome }}</td>
                    <td>{{ $pedido->aluno_cpf ? $pedido->aluno_cpf : '---' }}</td>
                    <td>{{ $pedido->pedido_status }}</td>
                    <td>{{ $pedido->metodo_pagamento ? $pedido->metodo_pagamento : '---' }}</td>
                    <td>{{ $pedido->cupom_codigo ? $pedido->cupom_codigo : '---' }}</td>
                    <td>{{ $pedido->cupom_valor ? $pedido->cupom_valor : '---' }}</td>
                    <td>{{ $pedido->valor_bruto }}</td>
                    <td>{{ $pedido->valor_bruto == 'Grátis' ? 'Grátis' : ($pedido->metodo_pagamento == 'Cartão de Crédito' ? $pedido->tarifa_cartao : "---") }}</td>
                    <td>{{ $pedido->valor_bruto == 'Grátis' ? 'Grátis' : $pedido->valor_iss }}</td>
                    <td>{{ $pedido->valor_bruto == 'Grátis' ? 'Grátis' : $pedido->valor_pis }}</td>
                    <td>{{ $pedido->valor_bruto == 'Grátis' ? 'Grátis' : $pedido->valor_irpj }}</td>
                    <td>{{ $pedido->valor_bruto == 'Grátis' ? 'Grátis' : ($pedido->metodo_pagamento == 'Boleto Bancário' ? $pedido->tarifa_boleto : "---") }}</td>
                    <td>{{ $pedido->valor_bruto == 'Grátis' ? 'Grátis' : $pedido->tarifa_processamento }}</td>
                    <td>{{ $pedido->valor_bruto == 'Grátis' ? 'Grátis' : $pedido->valor_liquido }}</td>
                    <td>{{ $pedido->metodo_pagamento == 'Cartão de Crédito' ? $pedido->parcelas : '---' }}</td>
                    <td>{{ $pedido->metodo_pagamento == 'Cartão de Crédito' ? $pedido->valor_parcela : '---' }}</td>
                    <td>{{ $pedido->metodo_pagamento == 'Cartão de Crédito' ? $pedido->valor_liquido_parcela : '---' }}</td>
                    <td>{{ $pedido->valor_pago ? $pedido->valor_pago : '---' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{ $pedidos->appends(request()->input())->links() }}
