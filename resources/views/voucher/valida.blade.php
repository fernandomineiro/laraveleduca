<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Validar Voucher</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <!-- bootstrap 3.0.2 -->
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/valida-voucher.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <div class="container page-valida-voucher">
            <center><h1>VALIDAÇÃO DE VOUCHER</h1></center>
            {{ Form::open(array('url' => '/autentica-voucher', 'method' => 'post')) }}
            {{ Form::token() }}
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <div class="form-group">
                        {{ Form::label('code', 'Código para validação:') }}
                    </div>
                    <div class="form-group">
                        {{ Form::text('code', $code, array('class' => 'form-control', 'autocomplete' => 'off')) }}
                    </div>
                    <div class="form-group">
                        {{ Form::submit('Validar', array('class' => 'btn btn-primary form-control')) }}
                    </div>
                </div>
            </div>
            {{ Form::close() }}
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    @if ($voucher === 1)
                        <center><p class="text-success"><b>VOUCHER VÁLIDO!</b></p></center>
                    @elseif ($voucher === 0)
                        <center><p class="text-danger"><b>ATENÇÃO: VOUCHER INVÁLIDO!</b></p></center>
                    @endif
                    @if (!empty($url))
                        <iframe src="{{ $url }}" height="350" width="100%"></iframe>
                    @endif
                    </div>
                </div>
        </div>
    </body>
</html>