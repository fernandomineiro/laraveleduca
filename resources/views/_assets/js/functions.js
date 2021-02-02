function onlyNumbers(e) {
    var tecla = (window.event) ? event.keyCode : e.which;
    if ((tecla > 47 && tecla < 58)) return true;
    else {
        if (tecla == 8 || tecla == 0) return true;
        else return false;
    }
}

function carregaEstado(uf) {
    jQuery("#estados").html('Carregando <img src="/img/ajax-loader2.gif" />');
    jQuery('#estados').load('/admin/estado/' + uf);
}

function carregaCidade(uf, descricao) {
    jQuery.ajax({
        type: 'POST',
        url: "/admin/cidade",
        data: "uf=" + uf + '&cidade=' + descricao + '&_token=' + $('#token').val(),
        dataType: "html",
        beforeSend: function() {
            $("#cidades").html('Carregando <img src="/img/ajax-loader2.gif" />')
            jQuery('#botao_salvar').hide();
        },
        success: function(html) {
            $("#cidades").html(html);
        },
        complete: function() {
            jQuery('#botao_salvar').show();
        }
    });
}

function carregaCidades(idEstado) {
    jQuery("#cidades").html('Carregando <img src="/img/ajax-loader2.gif" />')
    jQuery("#cidades").load("/admin/carrega_cidades/" + idEstado);
}
