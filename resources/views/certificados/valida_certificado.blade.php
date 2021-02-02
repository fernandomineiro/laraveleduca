<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
      .container {
        position: relative;
        text-align: center;
        width:800px;
        margin:auto;
      }
      .centered {
        position: absolute;
        color: black;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%); 
      }
      .bottom-right {
        position: absolute;
        bottom: 155px;
        right: 220px;
        color: black;
      }
      .img{
        margin:auto;
        width:800px;
        height:600px;
      }
    </style>
  </head>
  <body>
    <div class="container">
      @if( $certificado )
      <?php $image_path = '/files/certificado/' . $certificado->layout; ?>
      <div class="img" >       
          <img src="{{ public_path().$image_path }}"  style="width:100%;">          
      </div>
      <div class="centered"> 
        <br /> <br />  <br />  <br /> <br />  
        <span style="color:#9c9696;">Certificamos</span><br />
        <h2>{{ $nomeUsuario }}</h2>
        <p>Portador do RG 12345679, por ter concluído o curso </p>
        <h1>{{ $curso }}</h1> 
        <br />
        <p>Com duração de 1 hora</p>
        <p>24 de Janeiro de 2019</p>
        
      </div>
      @else
        <h2>Certificado Inválido</h2>
      @endif
    </div>
  </body>
</html> 