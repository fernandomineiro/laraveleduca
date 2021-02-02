<table class="table table-bordered table-hover table-condensed">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Dt. nascimento</th>
            <th>CPF</th>
            <th>RG</th>
            <th>E-mail</th>
            <th>Telefones</th>
            <th>Curso Superior</th>
            <th>Universidade</th>
            <th>Pós-graduação</th>
            <th>Instituição</th>
            <th>Origem</th>
            <th>CEP</th>
            <th>Logradouro</th>
            <th>Número</th>
            <th>Complemento</th>
            <th>Bairro</th>
            <th>Cidade</th>
            <th>UF</th>
            <th>Dt. Cadastro</th>
        </tr>
    </thead>
    <tbody>
        @foreach($alunos as $aluno)
            <tr>
                <td nowrap>{{ $aluno->id }}</td>
                <td nowrap>{{ $aluno->nome }}</td>
                <td nowrap>{{ $aluno->data_nascimento }}</td>
                <td nowrap>{{ $aluno->cpf }}</td>
                <td nowrap>{{ $aluno->identidade }}</td>
                <td nowrap>{{ $aluno->email }}</td>
                <td nowrap>
                    @if (!empty(trim($aluno->telefone_1)))
                        {{ $aluno->telefone_1 }}
                    @endif

                    @if (!empty(trim($aluno->telefone_2)))
                        , {{ $aluno->telefone_2 }}
                    @endif

                    @if (!empty(trim($aluno->telefone_3)))
                        , {{ $aluno->telefone_3 }}
                    @endif
                </td>
                <td nowrap>{{ strtoupper($aluno->curso_superior) }}</td>
                <td nowrap>{{ strtoupper($aluno->universidade) }}</td>
                <td nowrap>{{ strtoupper($aluno->curso_especializacao) }}</td>
                <td nowrap>{{ strtoupper($aluno->especializacao_universidade) }}</td>
                <td nowrap>{{ $aluno->origem }}</td>
                <td nowrap>{{ $aluno->cep }}</td>
                <td nowrap>{{ $aluno->logradouro }}</td>
                <td nowrap>{{ $aluno->numero }}</td>
                <td nowrap>{{ $aluno->complemento }}</td>
                <td nowrap>{{ $aluno->bairro }}</td>
                <td nowrap>{{ $aluno->descricao_cidade }}</td>
                <td nowrap>{{ $aluno->uf_estado }}</td>
                <td nowrap>{{ $aluno->criacao }}</td>
            </tr>
        @endforeach

    <hr>

    </tbody>
</table>
