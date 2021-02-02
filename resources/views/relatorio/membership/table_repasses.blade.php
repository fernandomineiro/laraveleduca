<table class="table table-bordered table-hover table-condensed">
    <thead>
        <tr>
            <th>IES</th>

            @if (!request()->get('group_by') || request()->get('group_by') != 'tipo')
                <th>PLANO</th>
            @endif

            @if (!request()->get('group_by') || request()->get('group_by') != 'ies' && request()->get('group_by') != 'plano')
                <th>TIPO</th>
            @endif

            @if (!request()->get('group_by') || request()->get('group_by') != 'tipo' && request()->get('group_by') != 'ies' && request()->get('group_by') != 'plano')
                <th>NOME DO PARCEIRO</th>
            @endif

            <th>TOTAL DE VISUALIZAÇÕES</th>
            @if (!request()->get('group_by'))
                <th>VALOR DA VISUALIZAÇÃO</th>
                <th>REPASSE (%)</th>
            @endif
            <th>REPASSE TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @foreach($repasses as $repasse)
            <tr>
                <td nowrap>{{ $repasse->fantasia }}</td>

                @if (!request()->get('group_by') || request()->get('group_by') != 'tipo')
                    <td nowrap>{{ $repasse->plano }}</td>
                @endif  

                @if (!request()->get('group_by') || request()->get('group_by') != 'ies' && request()->get('group_by') != 'plano')
                    <td nowrap>{{ strtoupper($repasse->tipo_usuario) }}</td>
                @endif

                @if (!request()->get('group_by') || request()->get('group_by') != 'tipo' && request()->get('group_by') != 'ies' && request()->get('group_by') != 'plano')
                    <td nowrap>{{ $repasse->nome }}</td>
                @endif
            
                <td nowrap>{{ $repasse->total_views }}</td>

                @if (!request()->get('group_by'))
                    <td nowrap>R$ {{ number_format( $repasse->valor_view, 2, ',', '.') }}</td>
                    <td nowrap>{{ $repasse->percentual_repasse }}%</td>
                @endif

                <td nowrap>R$ {{ number_format( $repasse->repasse_total, 2, ',', '.') }}</td>
            </tr>
        @endforeach

    </tbody>
</table>
