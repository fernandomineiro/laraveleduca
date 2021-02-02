<!DOCTYPE html>
<html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    * {
      font-family: 'Arial';
      line-height: 25px;
    }

    .container {
      position: relative;
      text-align: center;
      color: white;
    }

    .centered {
      color: black;
    }

    .img {
      margin: auto;
      width: 800px;
      height: 600px;
    }

    .bottom-right {
      position: absolute;
      bottom: 120px;
      color: black;
      left: 80%;
    }
  </style>
</head>

<body>
  @if ($certificado_path)
  <?php $pdf_path = secure_url('/files/certificado/emitidos') ?>
  <div class="container">
    <div class="centered">
      <h3>
        Esse certificado é válido. Você pode visualizá-lo ou realizar o download abaixo.
      </h3>
      <object style="width: 100%; height: 800px;" data="{{ $pdf_path . '/' . $certificado_path }}" type="application/pdf">
        <embed src="{{ $pdf_path . '/' . $certificado_path }}" type="application/pdf" />
      </object>
    </div>
  </div>
  @else
  <div class="container">
    <div class="centered">
      Esse certificado não existe.
    </div>

  </div>
  @endif

</body>

</html>
