@if(request('ies') == "6")

<div style="overflow-x:auto;">
    <table class="table table-bordered table-striped" width="100%">
        <thead>
        <tr>
            <th>Compra</th>
            <th>ID Aluno</th>
            <th>Aluno</th>
            <th>Email</th>
            <th>ID Curso</th>
            <th>Curso</th>
            <th>IES</th>
            <th>Emite Certificado</th>
            <th>Status Conclusão</th>
            <th>Conclusão %</th>
            <th>Último acesso</th>
        </tr>
        </thead>
        
        <tbody>
        @if(isset($data) && count($data) > 0)
            @foreach($data as $d)
                <tr>
                    <td>{{($d->data_criacao) ? date('d/m/Y', strtotime($d->data_criacao)) : '-'}}</td>
                    <td>{{ $d->id }}</td>
                    <td>{{ $d->nome }} {{ $d->sobre_nome }}</td>
                    <td>{{ $d->email }}</td>
                    <td>@isset($d->curso_id) {{ $d->curso_id }} @else --- @endif</td>
                    <td>@isset($d->curso_titulo) {{ $d->curso_titulo }} @else --- @endif</td>
                    <td>ITV</td>
                    <td>
                        @if(!isset($d->curso_id))
                        --- 
                        @else
                        @if(isset($d->fk_certificado)) SIM @else NÃO @endif
                        @endif
                    </td>
                    <td>
                        @if($d->curso_concluido != null)
                            Concluído
                        @else
                            Em andamento
                        @endif
                    </td>
                    <td>@isset($d->percentual_conclusao) {{ round($d->percentual_conclusao, 2) }} @else 0 @endif</td>
                    <td>@if(isset($d->ultimo_acesso)) {{$d->ultimo_acesso}} @else Informação não disponível @endif</td>
                </tr>
            @endforeach
        @endif
        </tbody>
    </table>
</div>

@else

<div style="overflow-x:auto;">
    <table class="table table-bordered table-striped" width="100%">
        <thead>
        <tr>
            <th>ID Pedido</th>
            <th>Compra</th>
            <th>Matricula</th>
            <th>ID Aluno</th>
            <th>Aluno</th>
            <th>Email</th>
            <th>ID Curso</th>
            <th>Curso</th>
            <th>Tipo Curso</th>
            <th>IES</th>
            <th>Emite Certificado</th>
            <th>Status Pagamento</th>
            <th>Status Conclusão</th>
            <th>Conclusão %</th>
            <th>Último acesso</th>
        </tr>
        </thead>
    
        <tbody>
        @if(isset($data) && count($data) > 0)
            @foreach($data as $d)
                <tr>
                    <td>{{ $d->id }}</td>
                    <td>{{($d->criacao) ? date('d/m/Y', strtotime($d->criacao)) : '-'}}</td>
                    <td>{{($d->status) == 2 ? date('d/m/Y', strtotime($d->atualizacao)) : '-'}}</td>
                    <td>{{ $d->aluno_id }}</td>
                    <td>{{ $d->aluno_nome }} {{ $d->aluno_sobre_nome }}</td>
                    <td>{{ $d->aluno_email }}</td>
                    <td>{{ $d->curso_id }}</td>
                    <td>{{ $d->curso_titulo }}</td>
                    <td>{{ $d->curso_tipo }}</td>
                    <td>{{ $d->ies_fantasia }}</td>
                    <td> @if($d->fk_certificado) SIM @else NÃO @endif</td>
                    <td>{{ $d->pedido_status_titulo }}</td>
                    <td>
                        @if($d->curso_concluido != null)
                            Concluído
                        @else
                            Em andamento
                        @endif
                    </td>
                <td>{{ round($d->percentual_conclusao, 2) }}</td>
                    <td>@if($d->ultimo_acesso) {{ $d->ultimo_acesso }} @else Informação não disponível @endif</td>
                </tr>
            @endforeach
        @endif
        </tbody>
    </table>
</div>
@endif
