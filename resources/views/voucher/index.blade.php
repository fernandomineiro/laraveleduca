<div style="width: 100%; border: 1px solid; padding: 0px 30px 0px 0; position: relative;  ">
    <table border="0" style="font-family: 'Helvetica';">
        <tbody>
            <tr>
                <td>
                    <div style="border: 0 2px 0 0; border-color: #000; padding: 20px;">
                        <center>
                            <img src="http://ec2-3-81-68-4.compute-1.amazonaws.com/sitenovo/img/educaz_logo.png" style="width: 15%; margin-top: 10px">
                            
                                <h3>Pedido<br/><span style="font-size: 10t;">{{ $pid }}</span></h3>
                                <p>{{ $nome }}</p>
                        </center>
                    </div>
                </td>
                <td style="border: solid; border-width: 0 0 0 1px;">
                    <div style="width: 100%;">
                        <div style="text-align: left; padding-left: 30px;">
                            <h3 style="margin-bottom: 0;">{{ $item['nome'] }}</h3> 
                            <p style="margin-top: 0;">{{ $item['professor_nome'] }}</p>
                            <p>
                                {{ $item['endereco_presencial'] }}
                            </p>  

                            @if ($item['valor'])
                                <h3>R$ {{ number_format($item['valor'], 2, ',', '.') }}</h3>
                            @endif

                            <p style="font-size: 8pt;">
                                Seu certificado estará disponível na sua área de perfil em até 1 semana após a conclusão do curso caso seja verificada sua presença ou outras exigências do palestrante/professor.
                            </p>
                        </div>
                    </div>
                </td>
                <td> 
                    @if ($img_url)
                        <center><img src="{{ $img_url }}" style="width: 70px; position: absolute; margin-left: 35px; margin-top: 10px;"/></center>
                    @endif
                    <center>
                        <img src="data:image/png;base64, {!! base64_encode($qrcode) !!}" style="width: 70px; margin-top: 110px;">
                    </center>
                    <div style="">
                        <center><span style="font-size: 12px;">{{ $code_qrcode }}</span></center>
                    </div>
                    <br/><img src="http://ec2-3-81-68-4.compute-1.amazonaws.com/sitenovo/img/Educaz_preto.png" style="width: 100px; margin-top: 10px; margin-left: 30px;"/>
                </td>
            </tr>
        </tbody>
    </table>
</div>