@extends('layouts.app')
@section('content')
	<div class="box padding20" style="overflow-x: auto;">
	    <h2 class="table"><span>Importar Cupons em Massa</span></h2>
	    <a href="{{ url('admin/download/modelo_importar_cupom.xlsx') }}" class="label label-primary">Download do modelo de Planilha</a>
	    <a href="{{ route('admin.cupom') }}" class="label label-default">Voltar</a>
		<hr class="hr" />

		{{ Form::open(['method' => 'POST','url' => '/admin/cupom_importar/salvar','files' => true]) }}
			<div class="form-group">
				{{ Form::label('Arquivo') }}
				{{ Form::file('arquivo_excel',['accept'=>'.xlsx , .xls']) }}
			</div>
			<div class="form-group">
				{{ Form::submit('Enviar', ['class' => 'btn btn-primary']) }}
			</div>
		{{ Form::close() }}

        <hr class="hr" />

        <h1>Prévia do Arquivo a ser enviado</h1>

        <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered">
            <thead>
                <th>status</th>
                <th>titulo</th>
                <th>codigo</th>
                <th>descricao</th>
                <th>validade_inicial</th>
                <th>validade_final</th>
                <th>tipo</th>
                <th>valor</th>
                <th>numero_maximo_usos</th>
                <th>numero_maximo_produtos</th>
                <th>faculdade</th>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Cupom Exemplo</td>
                    <td>cupomexemplo</td>
                    <td>Cupom Exemplo</td>
                    <td>22/12/2020</td>
                    <td>22/12/2021</td>
                    <td>1</td>
                    <td>100</td>
                    <td>1</td>
                    <td>1</td>
                    <td>7</td>
                </tr>
            </tbody>
        </table>
        <hr class="hr" />
            <h1>Legenda</h1>
            <div>tipo 1 = Percentual</div>
            <div>tipo 2 = Espécie</div>
            <br>
            <div>status 0 = Inativo</div>
            <div>status 1 = Ativo</div>
            <br>
            <div>Faculdade 7 = Educaz (id de cadastro da faculdade)</div>
        <hr class="hr" />

	</div>

@endsection
