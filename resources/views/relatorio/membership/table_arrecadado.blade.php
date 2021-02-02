<table class="table table-bordered table-hover table-condensed">
    <thead>
        <tr>
            <th>IES</th>
            <th>PLANO</th>
            <th>TOTAL ARRECADADO</th>
            <th>TOTAL DE ASSINANTES</th>
            <th>NÚMERO DE PARCEIROS </th>
            <th>TOTAL DE VISUALIZAÇÕES</th>
            <th>VALOR DA VISUALIZAÇÃO</th>
        </tr>
    </thead>
    <tbody>
        @foreach($repasses as $repasse)
            <tr>
                <td nowrap>{{ $repasse->fantasia }}</td>
                <td nowrap>{{ $repasse->plano }}</td>
                <td nowrap>R$ {{ number_format( $repasse->total_arrecadado, 2, ',', '.') }}</td>
                <td nowrap>{{ $repasse->total_assinantes }}</td>
                <td nowrap>{{ $repasse->total_parceiros }}</td>
                <td nowrap>{{ $repasse->total_views }}</td>
                <td nowrap>R$ {{ number_format( $repasse->valor_view, 2, ',', '.') }}</td>
            </tr>
        @endforeach

    </tbody>
</table>
