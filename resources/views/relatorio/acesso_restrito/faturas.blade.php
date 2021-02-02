<table class="table table-bordered table-hover table-condensed">
    <thead>
        <tr>
            <th>IES</th>
            <th>CURSO</th>
            <th>DATA DE PUBLICAÇÃO DO CURSO</th>
            <th>QTD. VENDAS</th>
            <th>VALOR VENDA</th>
            <th>IMPOSTOS</th>
            <th>TOTAL A RECEBER</th>
        </tr>
    </thead>
    <tbody>
        @foreach($faturas as $fatura)
            <tr>
                <th>{{ $ies }}</th>
                <td nowrap>{{ $fatura['curso'] }}</td>
                <td nowrap>{{ $fatura['publicacao'] }}</td>
                <td nowrap>{{ $fatura['vendas'] }}</td>
                <td nowrap>R$ {{ number_format($fatura['valor'], 2, ',', '.') }}</td>
                <td nowrap>R$ {{ number_format($fatura['impostos'], 2, ',', '.') }}</td>
                <td nowrap>R$ {{ number_format($fatura['total_receber'], 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>