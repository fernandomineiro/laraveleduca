<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  html { margin: 0px}
.container {
  position: relative;
  text-align: center;
  color: white;
}
.centered {
  position: absolute;
  color: black;
  /* top: 50%; */
  top: 50%;
  padding: 0 100px;
}
.centered div {
  font-size: 24px;
  font-family: Arial, sans-serif;
  font-weight: 500;
  line-height: 32px;
    text-align: justify;
}
.img{
    position: fixed;
    margin: auto;
    width: 100%; /* 800px; */
    height: 100%; /* 600px; */
    /* border: 1px solid black; */
}
.bottom-right {
  position: absolute;
  bottom: 50px;
  right: 110px;
  color: black;
  font-family: Arial, sans-serif;
}
</style>
</head>
<body>
    <?php $image_path = '/files/certificado/' . $certificadoLayout->layout; ?>
<div class="container" style="padding:0;">
   <div class="img" >
      <img src="{{ public_path().$image_path }}" style="width:100%;">
   </div>
  <div class="centered">
      <div>{{$faculdadeFantasia}} certifica que <b style="font-size: 32px;line-height: 32px;">{{ $aluno['nome'].' '.$aluno['sobre_nome'] }}</b>
      concluiu o {{$cursoTipo}}, sobre {{ $curso['titulo'] }}, do(a) Professor(a) {{ $nome_professor }}, em {{ $data }},
      com carga horária total de {{ $duracao_em_horas }} {{ ($duracao_em_horas > 1) ? 'horas' : 'hora' }}.</div>
  </div>
  <div class="bottom-right">
    <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(150)->generate($url_qrcode))!!} "><br/>
    <span id="code">{{$code}}</span>
  </div>
</div>

</body>
</html> 
