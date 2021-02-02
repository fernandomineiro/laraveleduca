<div id="pdf-historico-escolar">
    <div class="row">
            @if (!empty($aluno))
                <div class="col-md-12">
                    <div id="printable">
                        <h1>HISTÓRICO ESCOLAR</h1>
                        <b>Nome:</b> {{ $aluno->nome }} {{ $aluno->sobre_nome }}<br/>
                        <b>Data de Nascimento:</b> {{ $aluno->data_nascimento }}<br/>
                        <b>CPF:</b> {{ $aluno->cpf }}<br/>
                        <b>RG:</b> {{ $aluno->identidade }}<br/>
                        <b>Cidade/UF:</b> {{ $aluno->descricao_cidade }}/{{ $aluno->uf_estado }}<br/>
                        <b>Telefone fixo:</b> {{ $aluno->telefone_1 }}<br/>
                        <b>Telefone celular:</b> {{ $aluno->telefone_2 }}<br/>
                        <b>Instituição:</b> {{ $aluno->faculdade_instituicao }}<br/>

                        @if ($aluno->curso_superior == 'sim')
                            @if ($aluno->universidade == 'outro')
                                <b>Universidade:</b> {{ $aluno->universidade_outro }}
                            @else
                                <b>Universidade:</b> {{ $aluno->universidade }}
                            @endif
                        @endif
                        <hr/>

                        @if (!empty($semestres))
                            @foreach ($semestres as $semestre => $cursos)
                            @if (!empty($cursos['online']) || !empty($cursos['remoto']) || !empty($cursos['presencial']) || !empty($cursos['trilha_do_conhecimento']))
                                <center><h2>Extracurricular</h2></center>
                                <center><h4><b>SEMESTRE {{ $semestre }}</b></h4></center>
                                @if (!empty($cursos['online']))
                                    <b><h4>CURSOS ONLINE</h4></b>
                                    <table width="100%">
                                        <thead>
                                            <tr>
                                                <th>DISCIPLINA</th>
                                                <th>PROFESSOR</th>
                                                <th>NOTA - QUESTIONÁRIO</th>
                                                <th>NOTA - TRABALHO</th>
                                                <th>MÉDIA</th>
                                                <th>C/H</th>
                                                <th>SITUAÇÃO</th>
                                                <th>DT. INÍCIO</th>
                                                <th>DT. CONCLUSÃO</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($cursos['online'] as $curso)
                                            <tr>
                                                <th>{{ $curso['nome'] }}</th>
                                                <th>{{ $curso['professor_nome'] }}</th>
                                                <th>{{ $curso['nota_quiz'] }}</th>
                                                <th>{{ $curso['nota_trabalho'] }}</th>
                                                <th>{{ $curso['media'] }}</th>

                                                @if ($curso['carga_horaria'] > 0)
                                                    <th>{{ $curso['carga_horaria'] }} hora(s)</th>
                                                @else
                                                    <th>--</th>
                                                @endif

                                                @if ($curso['data_conclusao'] == '--')
                                                    <th>Em andamento</th>
                                                @else
                                                    <th>Concluído</th>
                                                @endif
                                                <th>{{ $curso['data_inicio'] }}</th>
                                                <th>{{ $curso['data_conclusao'] }}</th>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    @if ($cursos_online_carga_horaria_total > 0)
                                        <table width="100%" style="margin-top: 10px">
                                            <thead>
                                                <tr>
                                                    <th><span class="pull-right">CARGA HORÁRIA TOTAL</span></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <th><span class="pull-right">{{ $cursos_online_carga_horaria_total }} hora(s)</span></th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    @endif
                                    
                                @endif
                                @if (!empty($cursos['remoto']))
                                    <b><h4>CURSOS REMOTOS</h4></b>
                                    <table width="100%">
                                        <thead>
                                            <tr>
                                                <th>DISCIPLINA</th>
                                                <th>PROFESSOR</th>
                                                <th>NOTA - QUESTIONÁRIO</th>
                                                <th>NOTA - TRABALHO</th>
                                                <th>MÉDIA</th>
                                                <th>C/H</th>
                                                <th>FREQUÊNCIA</th>
                                                <th>SITUAÇÃO</th>
                                                <th>DT. INÍCIO</th>
                                                <th>DT. CONCLUSÃO</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($cursos['remoto'] as $curso)
                                            <tr>
                                                <th>{{ $curso['nome'] }}</th>
                                                <th>{{ $curso['professor_nome'] }}</th>
                                                <th>{{ $curso['nota_quiz'] }}</th>
                                                <th>{{ $curso['nota_trabalho'] }}</th>
                                                <th>{{ $curso['media'] }}</th>

                                                @if ($curso['carga_horaria'] > 0)
                                                    <th>{{ $curso['carga_horaria'] }} hora(s)</th>
                                                @else
                                                    <th>--</th>
                                                @endif

                                                <th>{{ $curso['frequencia'] }}</th>

                                                @if ($curso['data_conclusao'] == '--')
                                                    <th>Em andamento</th>
                                                @else
                                                    <th>Concluído</th>
                                                @endif
                                                <th>{{ $curso['data_inicio'] }}</th>
                                                <th>{{ $curso['data_conclusao'] }}</th>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    @if ($cursos_remotos_carga_horaria_total > 0)
                                        <table width="100%" style="margin-top: 10px">
                                            <thead>
                                                <tr>
                                                    <th><span class="pull-right">CARGA HORÁRIA TOTAL</span></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <th><span class="pull-right">{{ $cursos_remotos_carga_horaria_total }} hora(s)</span></th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    @endif
                                @endif

                                @if (!empty($cursos['presencial']))
                                    <b><h4>CURSOS PRESENCIAIS</h4></b>
                                    <table width="100%">
                                        <thead>
                                            <tr>
                                                <th>DISCIPLINA</th>
                                                <th>PROFESSOR</th>
                                                <th>NOTA - QUESTIONÁRIO</th>
                                                <th>NOTA - TRABALHO</th>
                                                <th>MÉDIA</th>
                                                <th>C/H</th>
                                                <th>FREQUÊNCIA</th>
                                                <th>SITUAÇÃO</th>
                                                <th>DT. INÍCIO</th>
                                                <th>DT. CONCLUSÃO</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($cursos['presencial'] as $curso)
                                            <tr>
                                                <th>{{ $curso['nome'] }}</th>
                                                <th>{{ $curso['professor_nome'] }}</th>
                                                <th>{{ $curso['nota_quiz'] }}</th>
                                                <th>{{ $curso['nota_trabalho'] }}</th>
                                                <th>{{ $curso['media'] }}</th>

                                                @if ($curso['carga_horaria'] > 0)
                                                    <th>{{ $curso['carga_horaria'] }} hora(s)</th>
                                                @else
                                                    <th>--</th>
                                                @endif

                                                <th>{{ $curso['frequencia'] }}</th>

                                                @if ($curso['data_conclusao'] == '--')
                                                    <th>Em andamento</th>
                                                @else
                                                    <th>Concluído</th>
                                                @endif
                                                <th>{{ $curso['data_inicio'] }}</th>
                                                <th>{{ $curso['data_conclusao'] }}</th>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    @if ($cursos_presenciais_carga_horaria_total > 0)
                                        <table width="100%" style="margin-top: 10px">
                                            <thead>
                                                <tr>
                                                    <th><span class="pull-right">CARGA HORÁRIA TOTAL</span></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <th><span class="pull-right">{{ $cursos_presenciais_carga_horaria_total }} hora(s)</span></th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    @endif
                                @endif

                                @if (!empty($cursos['trilha_do_conhecimento']))
                                    <b><h4>CURSOS - TRILHA DO CONHECIMENTO</h4></b>
                                    <table width="100%">
                                        <thead>
                                            <tr>
                                                <th>DISCIPLINA</th>
                                                <th>PROFESSOR</th>
                                                <th>NOTA - QUESTIONÁRIO</th>
                                                <th>NOTA - TRABALHO</th>
                                                <th>MÉDIA</th>
                                                <th>C/H</th>
                                                <th>FREQUÊNCIA</th>
                                                <th>SITUAÇÃO</th>
                                                <th>DT. INÍCIO</th>
                                                <th>DT. CONCLUSÃO</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($cursos['trilha_do_conhecimento'] as $curso)
                                            <tr>
                                                <th>{{ $curso['nome'] }}</th>
                                                <th>{{ $curso['professor_nome'] }}</th>
                                                <th>{{ $curso['nota_quiz'] }}</th>
                                                <th>{{ $curso['nota_trabalho'] }}</th>
                                                <th>{{ $curso['media'] }}</th>

                                                @if ($curso['carga_horaria'] > 0)
                                                    <th>{{ $curso['carga_horaria'] }} hora(s)</th>
                                                @else
                                                    <th>--</th>
                                                @endif

                                                <th>{{ $curso['frequencia'] }}</th>

                                                @if ($curso['data_conclusao'] == '--')
                                                    <th>Em andamento</th>
                                                @else
                                                    <th>Concluído</th>
                                                @endif
                                                <th>{{ $curso['data_inicio'] }}</th>
                                                <th>{{ $curso['data_conclusao'] }}</th>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    @if ($cursos_trilha_do_conhecimento_carga_horaria_total > 0)
                                        <table width="100%" style="margin-top: 10px">
                                            <thead>
                                                <tr>
                                                    <th><span class="pull-right">CARGA HORÁRIA TOTAL</span></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <th><span class="pull-right">{{ $cursos_trilha_do_conhecimento_carga_horaria_total }} hora(s)</span></th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    @endif
                                @endif
                            @endif
                            @endforeach
                       
                            @if($cursos_nao_iniciados && count($cursos_nao_iniciados) > 0)
                                    <b><h4>CURSOS NÃO INICIADOS</h4></b>
                                    <table width="100%">
                                        <thead>
                                        <tr>
                                            <th>DISCIPLINA</th>
                                            <th>PROFESSOR</th>
                                            <th>NOTA - QUESTIONÁRIO</th>
                                            <th>NOTA - TRABALHO</th>
                                            <th>MÉDIA</th>
                                            <th>C/H</th>
                                            <th>FREQUÊNCIA</th>
                                            <th>SITUAÇÃO</th>
                                            <th>DT. INÍCIO</th>
                                            <th>DT. CONCLUSÃO</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($cursos_nao_iniciados as $curso)
                                            <tr>
                                                <th>{{ $curso->titulo }}</th>
                                                <th>---</th>
                                                <th>---</th>
                                                <th>---</th>
                                                <th>---</th>
                                                <th>---</th>
                                                <th>---</th>
                                                <th>---</th>
                                                <th>---</th>
                                                <th>---</th>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                            @endif
                        @else 
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-info">Nenhum curso iniciado.</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
    </div>
</div>
<style>
    @media print {
        body * {
            visibility: hidden;
        }

        #printable, #printable * {
            visibility: visible;
        }
        
        #printable {
            position: fixed;
            left: 0;
            top: 0;
        } 
    }

    body {
        font-family: 'Arial';
    }
    
    body table tbody {
        font-size: 14px;
        font-weight: 500;
    }

    table {
        border-collapse: collapse;
    }

    #pdf-historico-escolar table th,    
    #pdf-historico-escolar table tr {
        padding: 5px;
        border: 1px solid black;
    }
</style>
