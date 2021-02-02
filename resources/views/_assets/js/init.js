$(document).ready(function () {
    $('input'). attr("autocomplete", "Off");

    $(".datepicker").datepicker({
        format: "dd/mm/yyyy"
    });

    $('select[name="fk_estado_id"]').blur(function() {
        carregaCidades($(this).val());
    });

    $("#cep").blur(function () {
        var cep = $(this).val().replace(/\D/g, '');
        if (cep != "") {
            var validacep = /^[0-9]{8}$/;
            if (validacep.test(cep)) {
                $("#logradouro").val("...");
                $("#bairro").val("...");
                $("#cidades").html("...");
                $("#estados").html("...");

                $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function (dados) {
                    if (!("erro" in dados)) {
                        $("#logradouro").val(dados.logradouro);
                        $("#bairro").val(dados.bairro);

                        carregaEstado(dados.uf);
                        carregaCidade(dados.uf, dados.localidade);
                    } else {
                        alert("CEP não encontrado.");
                    }
                });
            } else {
                alert("Formato de CEP inválido.");
            }
        }
    });

    $('.cep').inputmask('99.999-999');
    $('.timepicker').wickedpicker({
        twentyFour: true,
        upArrow: 'fa fa-chevron-up fa-lg',  //The up arrow class selector to use, for custom CSS
        downArrow: 'fa fa-chevron-down fa-lg', //The down arrow class selector to use, for custom CSS
    });
    $('.percentual').mask('999');
    $('.data_nascimento').mask('00/00/0000');
    $('.moeda').mask('#.##0,00', {reverse: true});
    $('.money').mask("#.##0,00", {reverse: true});
    $('.hora').mask('#99:99', {reverse: true});

    $('.telefone').inputmask('(99) 9999-9999');
    $('.celular').inputmask('(99) 99999-9999');

    $('ul.sidebar-menu > li > a').click(function () {
        if($(this).parent('li').hasClass('active')){
            $('ul.sidebar-menu li.active').removeClass('active');
            $('.sidebar-menu > li > .treeview-menu').hide();

            $('ul.sidebar-menu > li > a').find('i:eq( 0 )').removeClass('fa fa-angle-double-down');
            $('ul.sidebar-menu > li > a').find('i:eq( 0 )').addClass('fa fa-angle-double-left');
            $('ul.sidebar-menu > li > a').find('i:eq( 1 )').removeClass('fa pull-right fa-angle-down');
            $('ul.sidebar-menu > li > a').find('i:eq( 1 )').addClass('fa pull-right fa-angle-left');
            $('ul.sidebar-menu > li > a').find('i:eq( 2 )').removeClass('fa pull-right fa-angle-down');
            $('ul.sidebar-menu > li > a').find('i:eq( 2 )').addClass('fa pull-right fa-angle-left');

        }else{
            $('ul.sidebar-menu li.active').removeClass('active');
            $('.sidebar-menu > li > .treeview-menu').hide();

            $('ul.sidebar-menu > li > a').find('i:eq( 0 )').removeClass('fa fa-angle-double-down');
            $('ul.sidebar-menu > li > a').find('i:eq( 0 )').addClass('fa fa-angle-double-left');
            $('ul.sidebar-menu > li > a').find('i:eq( 1 )').removeClass('fa pull-right fa-angle-down');
            $('ul.sidebar-menu > li > a').find('i:eq( 1 )').addClass('fa pull-right fa-angle-left');
            $('ul.sidebar-menu > li > a').find('i:eq( 2 )').removeClass('fa pull-right fa-angle-down');
            $('ul.sidebar-menu > li > a').find('i:eq( 2 )').addClass('fa pull-right fa-angle-left');

            $(this).find('i:eq( 0 )').removeClass('fa pull-right fa-angle-left');
            $(this).find('i:eq( 0 )').addClass('fa fa-angle-double-down');

            $(this).find('i:eq( 1 )').removeClass('fa pull-right fa-angle-left');
            $(this).find('i:eq( 1 )').addClass('fa pull-right fa-angle-down');

            $(this).find('i:eq( 2 )').removeClass('fa pull-right fa-angle-left');
            $(this).find('i:eq( 2 )').addClass('fa pull-right fa-angle-down');

            $(this).parent('li').addClass('active');
            $(this).parent('li').find('.treeview-menu').show();
        }
    });

    var objValidar = new ValidarCNPJCPF();

    $(document).on('blur', '.cpf', function (e) {
        if ($(this).prop('readonly')) return;

        var field = $(this).val();
        if ($.trim(field) == '') return;

        var numeros = $(this).val().replace(/\D/g, '')
        var numeros = numeros.replace('.', '').replace('-', '');

        if (numeros.length >= 14) {
            if (objValidar.ValidarCNPJ(numeros)) {
                $(this).val(objValidar.maskCNPJ(numeros));
            }else {
                $(this).val('');
                $(this).focus();
                alert('CNPJ INVÁLIDO');
            }
        } else {
            if (objValidar.ValidarCPF(numeros)) {
                $(this).val(objValidar.maskCPF(numeros));
            } else {
                $(this).val('');
                $(this).focus();
                alert('CPF INVÁLIDO');
            }
        }
    });


    $('.dataTable thead tr').clone(true).appendTo( '.dataTable thead' );
    $('.dataTable thead tr:eq(1) th').each( function () {
        var title = $(this).text();
        $(this).html( '<input type="text" class="form-control" placeholder="'+title+'" />' );
    });

    var table = $('.dataTable').DataTable({
        // "scrollX": true,
        "order": [[ 0, "desc" ]], 
        "language": {
            "sEmptyTable": "Nenhum registro encontrado",
            "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
            "sInfoFiltered": "(Filtrados de _MAX_ registros)",
            "sInfoPostFix": "",
            "sInfoThousands": ".",
            "sLengthMenu": "_MENU_ resultados por página",
            "sLoadingRecords": "Carregando...",
            "sProcessing": "Processando...",
            "sZeroRecords": "Nenhum registro encontrado",
            "sSearch": "Pesquisar por:",
            "oPaginate": {
                "sNext": "Próximo",
                "sPrevious": "Anterior",
                "sFirst": "Primeiro",
                "sLast": "Último"
            },
            "oAria": {
                "sSortAscending": ": Ordenar colunas de forma ascendente",
                "sSortDescending": ": Ordenar colunas de forma descendente"
            },
            "select": {
                "rows": {
                    "_": "Selecionado %d linhas",
                    "0": "Nenhuma linha selecionada",
                    "1": "Selecionado 1 linha"
                }
            }
        },
        "orderCellsTop": true,
        "responsive": true,
        "fixedHeader": true,
    });
    if (table.columns() && table.columns().length > 0) {
        table.columns().eq(0).each(function (colIdx) {
            $('input', $('.dataTable thead tr:eq(1) th')[colIdx]).on('keyup change', function () {
                table
                    .column(colIdx)
                    .search(this.value)
                    .draw();
            });
        });
    }
    $(document).ready(function() {
        $(".dropdown-toggle").dropdown();
    });
});
