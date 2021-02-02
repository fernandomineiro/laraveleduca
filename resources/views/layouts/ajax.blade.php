<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
	    <meta charset="utf-8">
	    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	    <meta name="csrf-token" content="{{ csrf_token() }}">
	    <title>{{ config('app.name', 'Educaz 2.0') }}</title>
        <!-- Styles -->
        <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
	    <link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet">
	    <link href="{{ asset('css/bootstrap-datepicker.css') }}" rel="stylesheet">
	    <link href="{{ asset('css/jquery-ui.css') }}" rel="stylesheet">
	    <link href="{{ asset('css/sweetalert2.min.css') }}" rel="stylesheet">
	    <link href="{{ asset('css/AdminLTE.css') }}" rel="stylesheet">
	    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

        <script src="{{ asset('js/jquery.js') }}"></script>
        <script src="{{ asset('js/jquery-ui.js') }}"></script>
        <script src="{{ asset('js/bootstrap.min.js') }}"></script>

		<script src="//cdn.ckeditor.com/4.8.0/basic/ckeditor.js"></script>
	</head>
    <body>

    <div class="wrapper row-offcanvas row-offcanvas-left">
        <div id="conteudo" class="overlay">
            @yield('content')
            <hr class="clear" />
        </div>
    </div>

    <script src="{{ asset('js/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('js/jquery.mask.js') }}"></script>
    <script src="{{ asset('js/AdminLTE.js') }}"></script>
    <script type="text/javascript">
        jQuery(".datepicker").datepicker({
            format: "dd-mm-yyyy",
            dayNames: ["Domingo","Segunda","Terça","Quarta","Quinta","Sexta","Sábado"],
            dayNamesMin: ["D","S","T","Q","Q","S","S","D"],
            dayNamesShort: ["Dom","Seg","Ter","Qua","Qui","Sex","Sáb","Dom"],
            monthNames: ["Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"],
            monthNamesShort: ["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"],
            nextText: "Próximo",
            prevText: "Anterior"
        });
        
        jQuery(document).ready( function() {
            jQuery('.percentual').mask('999');
            jQuery('.money').mask("#.##0,00", {reverse: true});
            jQuery('.hora').mask('#99:99');
            jQuery('.telefone').mask('(99) 99999-9999');
            jQuery('.data_nascimento').mask('00/00/0000');
            jQuery('.telefone').blur(function(event) {
            if(jQuery(this).val().length >= 14){
                jQuery('.telefone').mask('(99) 99999-9999');
                } else {
                    jQuery('.telefone').mask('(99) 9999-9999');
                }
            });

            jQuery('ul.sidebar-menu > li > a').click(function() {
                jQuery('ul.sidebar-menu li.active').removeClass('active');
                jQuery('.sidebar-menu > li > .treeview-menu').hide();

                jQuery('ul.sidebar-menu > li > a').find('i:eq( 0 )').removeClass('fa fa-angle-double-down');
                jQuery('ul.sidebar-menu > li > a').find('i:eq( 0 )').addClass('fa fa-angle-double-left');
                jQuery('ul.sidebar-menu > li > a').find('i:eq( 1 )').removeClass('fa pull-right fa-angle-down');
                jQuery('ul.sidebar-menu > li > a').find('i:eq( 1 )').addClass('fa pull-right fa-angle-left');
                jQuery('ul.sidebar-menu > li > a').find('i:eq( 2 )').removeClass('fa pull-right fa-angle-down');
                jQuery('ul.sidebar-menu > li > a').find('i:eq( 2 )').addClass('fa pull-right fa-angle-left');					

                jQuery(this).find('i:eq( 0 )').removeClass('fa pull-right fa-angle-left');
                jQuery(this).find('i:eq( 0 )').addClass('fa fa-angle-double-down');

                jQuery(this).find('i:eq( 1 )').removeClass('fa pull-right fa-angle-left');
                jQuery(this).find('i:eq( 1 )').addClass('fa pull-right fa-angle-down');

                jQuery(this).find('i:eq( 2 )').removeClass('fa pull-right fa-angle-left');
                jQuery(this).find('i:eq( 2 )').addClass('fa pull-right fa-angle-down');

                jQuery(this).parent('li').addClass('active');
                jQuery(this).parent('li').find('.treeview-menu').show();
            });
        })
        </script>
    </body>
    @yield('post-script')
</html>