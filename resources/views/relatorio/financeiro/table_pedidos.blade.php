<div style="overflow-x:auto;">
    <table class="table table-bordered table-striped ">
        <thead>
            <tr>
                <th>Data de Venda</th>
                <th>ID do Pedido</th>
                <th>Produto Adquirido</th>
                <th>Nome da Faculdade</th>
                <th>Professor</th>
                <th>ID do Aluno</th>
                <th>Nome do aluno</th>
                <th>E-mail do aluno</th>
                <th>CPF</th>
                <th>Status do Pagamento</th>
                <th>Forma de Pagamento</th>
                <th>Código do Cupom</th>
                <th>Valor do Cupom (R$)</th>
                <th>Valor Bruto (R$)</th>
                <th>Valor Pago (R$)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedidos as $pedido)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($pedido->data_venda)->format('d/m/Y H:m:s') }}</td>
                    <td>{{ $pedido->pedido_pid }}</td>
                    <td>{{ $pedido->produto_nome ? $pedido->produto_nome : '---' }}</td>
                    <td>{{ $pedido->faculdade_nome }}</td>
                    <td>{{ $pedido->professor_nome }}</td>
                    <td>{{ $pedido->aluno_id }}</td>
                    <td>{{ $pedido->aluno_nome }} {{ $pedido->aluno_sobrenome }}</td>
                    <td>{{ $pedido->email }}</td>
                    <td>{{ $pedido->aluno_cpf ? $pedido->aluno_cpf : '---' }}</td>
                    <td>{{ $pedido->pedido_status }}</td>
                    <td>{{ $pedido->forma_pagamento ? $pedido->forma_pagamento : '---' }}</td>
                    <td>{{ $pedido->cupom_codigo ? $pedido->cupom_codigo : '---' }}</td>
                    <td>{{ $pedido->cupom_valor ? $pedido->cupom_valor : '---' }}</td>
                    <td>{{ $pedido->valor_bruto }}</td>
                    <td>{{ $pedido->valor_pago ? $pedido->valor_pago : '---' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{ $pedidos->appends(request()->input())->links() }}
