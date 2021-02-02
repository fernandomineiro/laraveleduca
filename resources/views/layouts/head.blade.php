<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Educaz 2.0') }}</title>

    {{--<link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">--}}
    <link rel="stylesheet" href="{{ url(mix('assets/css/vendors.css')) }}">

    @yield('styles')

<script src="{{ mix('assets/js/app.js')  }}"></script>

</head>
