<div class="padding20">
    {{ Form::open(['url' => '/admin/'.$modulo['moduloDetalhes']->uri.'/'.$objAluno->id.'/adicionar-estrutura', 'autocomplete' => 'off']) }}
    <div class="row">
        <div class="form-group col-md-7">
            {{Form::label('Estrutura Curricular') }} <small>*</small>
            {{Form::select('fk_estrutura',
                $objEstrutura, [],
                [
                    'id' => 'fk_estrutura', 'name' => 'fk_estrutura',
                    'class' => 'form-control', 'allowClear' => true,
                    'placeholder' => 'Selecione uma estrutura curricular'
                 ]
            )}}
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary" style="margin-top: 23px;" >Adicionar Estrutura</button>
        </div>
    </div>
    {{ Form::close() }}

    <br>
    <hr>
    <div class="row">
        <div class="form-group col-md-10">
        </div>
        <div class="form-group col-md-2">
        </div>
        @if(count($objEstruturaUsuario) > 0)
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Titulo</th>
                    <th>Ações</th>
                </tr>
                </thead>
                <tbody>
                @foreach($objEstruturaUsuario as $estrutura)
                    <tr>
                        <td>{{ $estrutura->estrutura->titulo }}</td>
                        <td>
                            {{ Form::open(['url' => '/admin/'.$modulo['moduloDetalhes']->uri.'/'.$estrutura->id.'/deletar-estrutura', 'method' => 'DELETE', 'autocomplete' => 'off']) }}
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir esse registro?')">
                                    <i class="fa fa-fw fa-trash"></i>
                                </button>
                            {{ Form::close() }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="form-group col-md-12 alert alert-info">
                Nenhuma estrutura vinculada a este aluno
            </div>
        @endif
    </div>
</div>

