@extends('layouts.app')

@section('content')
    <div class="box padding20">
        <div class="col-md-9">
            <h2 class="table">MEMBERSHIP</h2>
        </div>
        <hr class="clear hr" />

        <div class="col-md-3"> </div>

        <div class="col-md-6">
            <a href="/admin/assinatura/1/lista">
                <div style="width: 300px; height: 60px; border: 2px solid #CCC; background: #EFEFEF; text-align: center; text-transform: uppercase">
                    <br />Membership full
                </div>
            </a>
            <br />
            <br />

            <a href="/admin/assinatura/3/lista">
                <div style="width: 300px; height: 60px; border: 2px solid #CCC; background: #EFEFEF; text-align: center;text-transform: uppercase">
                    <br />membership trilha de conhecimento
                </div>
            </a>
            <br />
            <br />

            <a href="/admin/assinatura/4/lista">
                <div style="width: 300px; height: 60px; border: 2px solid #CCC; background: #EFEFEF; text-align: center;text-transform: uppercase">
                    <br />membership mentoria
                </div>
            </a>
            <br />
            <br />

{{--            <a href="/admin/assinatura/3/lista">--}}
{{--                <div style="width: 300px; height: 60px; border: 2px solid #CCC; background: #EFEFEF; text-align: center;text-transform: uppercase;">--}}
{{--                    <br />administrar memberships--}}
{{--                </div>--}}
{{--            </a>--}}
            <br />
            <br />
        </div>
        <div class="col-md-3"></div>
        <hr class="clear hr" />
    </div>
@endsection
