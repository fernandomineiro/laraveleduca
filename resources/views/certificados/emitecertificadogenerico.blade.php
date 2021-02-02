<!DOCTYPE html>
<html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    * {
      font-family: 'Arial', sans-serif;
      line-height: 25px;
    }

    p,
    h2 {
      text-align: center;
    }

    .body {
      width: 1100px;
      height: 730px;
      background-color: {{ $cor }};
    }

    #white-box {
      background-color: white;
      width: 1000px;
      height: 630px;
      position: absolute;
      top:50px;
      left: 50px;    
    }
    #white-box-border{
      position: relative;
      border:1px solid {{ $cor }};
      width: 980px;
      height: 610px;
      top: 43.5%;
      left: 50%;
      transform: translate(-50%, -50%);
    }

   #qr{
      position: absolute;
      bottom: -100;
      z-index: 99999999;
      height: 100px;
      width: 100px;
      right: 60px;
   }

   #selo{
      position: absolute;
      height: 100px;
      width: 100px;
      bottom: -130;
      border-radius: 50px;
      left: 40px;
      background-color: {{ $cor }};
   }

   #line1{
      position: relative;
   }

   #logo1,
   #logo2{
      position: absolute;
   }

   #logo1 img {
      width: auto;
      height: 70px;
   }

   #logo1{
      top: 0;
      left: 50%;
      transform: translate(-50%, 10%);
    }
   #logo2{
      top: 20%;
      left: 50%;
      transform: translate(-50%, 10%);
    }

   #line2{
     position: absolute;
     top:150px;
   }

   #code{
     position: absolute;
     top:130px;
     left: 15px;
     z-index: 99999;
   }

  
   @page {
         margin: 10px;
      }
   
  </style>
</head>
<body>
  <div class="body">
    <div id="white-box">
      <div id="white-box-border">
        <div id="line1">
          <div id="logo1">
            <?php $image_path = public_path().'/files/logotipos/' . $urlLogo; ?>
            <img src="{{ $image_path}}">
            
          </div>
          <div id="logo2">
            <img src="{{ public_path().'/img/'. 'certificado-byeducaz.PNG'}}">
          </div>
        </div>
        <div id="line2">
            <p style="color:#999">A Educaz certifica que {{ $aluno['nome'].' '.$aluno['sobre_nome'] }} concluiu o curso,
                {{$cursoTipo}}, sobre {{ $curso['titulo'] }}, do(a) Professor(a) {{ $nome_professor }}, em {{ $data }}, com carga horária total de {{ $curso['duracao_total']}} horas.</p>
        </div>
       
        <div id="selo">
            <img src="{{ public_path().'/img/'. 'certificado-selotemp.PNG'}}">
        </div>
        <div id="qr"> 
            <img 
            src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(150)->generate($url_qrcode))!!} ">
            <span id="code">{{$code}}</span>
        </div>   
      </div>  
    </div>
  </div>
</body>
</html>
