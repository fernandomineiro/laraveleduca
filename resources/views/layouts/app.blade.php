<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

@include('layouts.head')

    <body @if(Auth::guard('admin')->check()) class="skin-blue" @else style="background: #BBB;" @endif>

    @if(Auth::guard('admin')->check())
        <header class="header">
            <a class="logo" href="{{ url('/admin') }}">
                <img src="{{ URL::asset('img/logomarca-pequena.png') }}"/>
            </a>
            <nav class="navbar navbar-static-top" role="navigation">
                <div class="navbar-right">
                </div>
            </nav>
        </header>
    @endif

    <div class="wrapper row-offcanvas row-offcanvas-left">
        @if(Auth::guard('admin')->check())
            @include('sidebar')
        @endif

        <aside @if(Auth::guard('admin')->check()) class="right-side" @endif style="padding: 25px;">
            @include('mensagens')

            <div id="conteudo" class="overlay">
                @yield('content')
                <hr class="clear"/>
            </div>

            <span id="modelo_carregando" style="display:none;">
                <img src="{{ url('/img/ajax-loader.gif') }}" style="width: 38px; height: 38px;"><br/>
                <span style="margin: 20px 50px; font-size: 14px; color: #EFEFEF;">Carregando...</span>
            </span>
        </aside>
    </div>

    @include('layouts.scripts')
    @stack('js')

    </body>
</html>
