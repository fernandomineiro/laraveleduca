var ValidarCNPJCPF=function(){};ValidarCNPJCPF.prototype.onlyNumbers=function(r){var t=window.event?event.keyCode:r.which;return 47<t&&t<58||(8==t||0==t)},ValidarCNPJCPF.prototype.ValidarCPF=function(r){if(""==(r=r.replace(/[^\d]+/g,"")))return!1;if(11!=r.length||"00000000000"==r||"11111111111"==r||"22222222222"==r||"33333333333"==r||"44444444444"==r||"55555555555"==r||"66666666666"==r||"77777777777"==r||"88888888888"==r||"99999999999"==r)return!1;for(add=0,i=0;i<9;i++)add+=parseInt(r.charAt(i))*(10-i);if(rev=11-add%11,10!=rev&&11!=rev||(rev=0),rev!=parseInt(r.charAt(9)))return!1;for(add=0,i=0;i<10;i++)add+=parseInt(r.charAt(i))*(11-i);return rev=11-add%11,10!=rev&&11!=rev||(rev=0),rev==parseInt(r.charAt(10))},ValidarCNPJCPF.prototype.ValidarCNPJ=function(r){if(""==(r=r.replace(/[^\d]+/g,"")))return!1;if(14!=r.length)return!1;if("00000000000000"==r||"11111111111111"==r||"22222222222222"==r||"33333333333333"==r||"44444444444444"==r||"55555555555555"==r||"66666666666666"==r||"77777777777777"==r||"88888888888888"==r||"99999999999999"==r)return!1;for(tamanho=r.length-2,numeros=r.substring(0,tamanho),digitos=r.substring(tamanho),soma=0,pos=tamanho-7,i=tamanho;1<=i;i--)soma+=numeros.charAt(tamanho-i)*pos--,pos<2&&(pos=9);if(resultado=soma%11<2?0:11-soma%11,resultado!=digitos.charAt(0))return!1;for(tamanho+=1,numeros=r.substring(0,tamanho),soma=0,pos=tamanho-7,i=tamanho;1<=i;i--)soma+=numeros.charAt(tamanho-i)*pos--,pos<2&&(pos=9);return resultado=soma%11<2?0:11-soma%11,resultado==digitos.charAt(1)},ValidarCNPJCPF.prototype.maskCPF=function(r){return r.substring(0,3)+"."+r.substring(3,6)+"."+r.substring(6,9)+"-"+r.substring(9,11)},ValidarCNPJCPF.prototype.maskCNPJ=function(r){return r.substring(0,2)+"."+r.substring(2,5)+"."+r.substring(5,8)+"/"+r.substring(8,12)+"-"+r.substring(12,14)};