<table class="table table-bordered table-hover table-condensed">
    <thead>
        <tr>
            <th>IES</th>
            <th>Nº Pedido</th>
            <th>Produto</th>
            <th>Tipo de repasse</th>
            @if (!request()->get('ies'))
                <th>Usuário</th>
            @endif
            <th>Repasse (%)</th>
            <th>Repasse (R$)</th>
            <th>Pagamento</th>
            <th>Data</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pedidos as $pedido)
            <tr>
                <td nowrap>{{ $pedido->faculdade_nome }}</td>
                <td nowrap>{{ $pedido->pedido_pid }}</td>
                <td nowrap>{{ $pedido->pedido_item_nome }}</td>

                @if (request()->get('nome_professor'))
                    @if ($pedido->split_professor_manual == 1)
                        <td nowrap>MANUAL</td>
                    @else
                        <td nowrap>AUTOMÁTICO</td>
                    @endif

                    <td nowrap>{{ $pedido->professor_nome }}</td>
                    <td nowrap>{{ $pedido->professor_share_valor ? 'R$ ' . $pedido->professor_share_valor : '---' }}</td>
                    <td nowrap>{{ $pedido->professor_share }}%</td>
                @endif

                @if (request()->get('nome_curador'))
                    @if ($pedido->split_curador_manual == 1)
                        <td nowrap>MANUAL</td>
                    @else
                        <td nowrap>AUTOMÁTICO</td>
                    @endif

                    <td nowrap>{{ $pedido->curador_nome }}</td>
                    <td nowrap>{{ $pedido->curador_share_valor ? 'R$ ' . $pedido->curador_share_valor : '---' }}</td>
                    <td nowrap>{{ $pedido->curador_share }}%</td>
                @endif

                @if (request()->get('nome_parceiro'))
                    @if ($pedido->split_parceiro_manual == 1)
                        <td nowrap>MANUAL</td>
                    @else
                        <td nowrap>AUTOMÁTICO</td>
                    @endif

                    <td nowrap>{{ $pedido->parceiro_nome }}</td>
                    <td nowrap>{{ $pedido->parceiro_share_valor ? 'R$ ' . $pedido->parceiro_share_valor : '---' }}</td>
                    <td nowrap>{{ $pedido->parceiro_share }}%</td>
                @endif

                @if (request()->get('nome_produtora'))
                    @if ($pedido->split_produtora_manual == 1)
                        <td nowrap>MANUAL</td>
                    @else
                        <td nowrap>AUTOMÁTICO</td>
                    @endif

                    <td nowrap>{{ $pedido->produtora_nome }}</td>
                    <td nowrap>{{ $pedido->produtora_share_valor ? 'R$ ' . $pedido->produtora_share_valor : '---' }}</td>
                    <td nowrap>{{ $pedido->produtora_share }}%</td>
                @endif

                @if (request()->get('ies'))
                    @if ($pedido->split_faculdade_manual == 1)
                        <td nowrap>MANUAL</td>
                    @else
                        <td nowrap>AUTOMÁTICO</td>
                    @endif

                    <td nowrap>{{ $pedido->faculdade_share_valor ? 'R$ ' . $pedido->faculdade_share_valor : '---' }}</td>
                    <td nowrap>{{ $pedido->faculdade_share }}%</td>
                @endif

                <td nowrap>{{ $pedido->pagamento_tipo }}</td>
                <td nowrap>{{ \Carbon\Carbon::parse($pedido->pedido_criacao)->format('d/m/Y') }}</td>
            </tr>
        @endforeach
        
    </tbody>
</table>