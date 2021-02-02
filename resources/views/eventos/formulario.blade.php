@extends('layouts.app')
@section('content')
    <div class="box padding20">
        <h2 class="table"><span>Eventos</span></h2>
        <hr class="hr"/>

        @if(Request::is('*/editar'))
            {{ Form::model( $eventos, ['method' => 'PATCH',  'files' => true, 'route' => ['admin.eventos.atualizar', $eventos->id]] ) }}
        @else
            {{ Form::open(['url' => '/admin/eventos/salvar',  'files' => true]) }}
        @endif

        <div class="row">
            <div class="form-group col-md-5">
                {{ Form::label('Projeto') }}<br>
                <small>(Projeto a que o evento pertence)</small>
                {{ Form::select('fk_faculdade', $lista_faculdades, (isset($eventos->fk_faculdade) ? $eventos->fk_faculdade : 0), ['class' => 'form-control']) }}
            </div>
            @if(Request::is('*/editar'))
            <div class="form-group col-md-5">
                {{ Form::label('Status (workflow)') }}<br>
                <small>(Status do evento)</small>
                {{ Form::select('status', $lista_status, (isset($eventos->status) ? $eventos->status : 1), ['class' => 'form-control']) }}
            </div>
            @endif
        </div>

        <div class="row">
            <div class="form-group col-md-5">
                {{ Form::label('Título') }} <span id="tituloCount"></span><br>
                <small>(Título do evento)</small>
                {{ Form::input('text', 'titulo', null, ['class' => 'form-control', 'maxlength' => 60, 'onkeyup' => 'countChar(this, "tituloCount", 60)', 'placeholder' => 'Título']) }}
            </div>
            <div class="form-group col-md-5">
                {{ Form::label('Categoria do Evento') }}<br>
                <small>(Categoria que o evento pertence)</small>
                {{ Form::select('fk_categoria', $lista_categorias, (isset($eventos->fk_categoria) ? $eventos->fk_categoria : null), ['class' => 'form-control']) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-10">
                {{ Form::label('Descrição') }} <span id="descricaoCount"></span><br>
                <small>(Descrição do evento)</small>
                {{ Form::textarea('descricao', null, ['class' => 'form-control', 'maxlength' => 500, 'onkeyup' => 'countChar(this, "descricaoCount", 500)', 'id' => 'editor1', 'placeholder' => 'Descrição']) }}
            </div>
        </div>
        <br />
        <div class="form-group">
            {{ Form::label('Endereço (da realização do evento)') }}
            {{ Form::input('text', 'endereco', (isset($eventos->endereco) ? $eventos->endereco : ''), ['class' => 'form-control map-input', 'id' => 'address-input', 'style' => 'width: 50%;']) }}
            <input type="hidden" name="address_latitude" id="address-latitude" value="0" />
            <input type="hidden" name="address_longitude" id="address-longitude" value="0" />
        </div>
        <div id="address-map-container" style="width:100%;height:400px; ">
            <div style="width: 100%; height: 100%" id="address-map"></div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-3">
                <div class="well">
                    <div id="box_upload" class="row form-group">
                        {{ Form::label('Imagem do Evento') }}<br>
                        <small>(Dimensões: 350x130px)</small>
                        {{ Form::file('imagem', ['id' => 'evento_image', 'onChange' => 'previewImage(event)']) }}
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                @if(Request::is('*/editar') && !empty($eventos->imagem))
                    <img src="{{URL::asset('files/eventos/imagem/' . $eventos->imagem)}}" id="previewImg" height="130px" width="350px" />
                @else
                    <img height="130px" width="350px" id="previewImg"/>
                @endif
            </div>
        </div>

        <div class="form-group">
            <a href="{{ route('admin.eventos') }}" class="btn btn-default">Voltar</a>
            <a href="{{ url()->current() }}" class="btn btn-default">Cancel</a>
            {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>
@endsection

@push('js')
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCyqijVqZ1GY17qPoPrERN0FiFJs8jRXGQ&libraries=places&callback=initialize" async defer></script>
    <script>
        function initialize() {
            const geocoder = new google.maps.Geocoder;

            $('form').on('keyup keypress', function(e) {
                var keyCode = e.keyCode || e.which;
                if (keyCode === 13) {
                    e.preventDefault();
                    return false;
                }
            });
            const locationInputs = document.getElementsByClassName("map-input");

            const autocompletes = [];
            for (let i = 0; i < locationInputs.length; i++) {

                const input = locationInputs[i];
                const fieldKey = input.id.replace("-input", "");
                const isEdit = document.getElementById(fieldKey + "-latitude").value != '' && document.getElementById(fieldKey + "-longitude").value != '';

                let latitude = parseFloat(document.getElementById(fieldKey + "-latitude").value) || -33.8688;
                let longitude = parseFloat(document.getElementById(fieldKey + "-longitude").value) || 151.2195;

                if ($('#address-input').val()) {
                    geocoder.geocode({
                        address: $('#address-input').val()
                    }, (results, status) => {
                        if (status === google.maps.GeocoderStatus.OK) {
                            latitude = results[0].geometry.location.lat();
                            longitude = results[0].geometry.location.lng();

                            const map = new google.maps.Map(document.getElementById(fieldKey + '-map'), {
                                center: {lat: latitude, lng: longitude},
                                zoom: 13
                            });
                            const marker = new google.maps.Marker({
                                map: map,
                                position: {lat: latitude, lng: longitude},
                            });

                            marker.setVisible(isEdit);

                            const autocomplete = new google.maps.places.Autocomplete(input);
                            autocomplete.key = fieldKey;
                            autocompletes.push({input: input, map: map, marker: marker, autocomplete: autocomplete});
                            autoCompleteInit(autocompletes);
                        }
                    });
                } else {
                    const map = new google.maps.Map(document.getElementById(fieldKey + '-map'), {
                        center: {lat: latitude, lng: longitude},
                        zoom: 13
                    });
                    const marker = new google.maps.Marker({
                        map: map,
                        position: {lat: latitude, lng: longitude},
                    });

                    marker.setVisible(isEdit);

                    const autocomplete = new google.maps.places.Autocomplete(input);
                    autocomplete.key = fieldKey;
                    autocompletes.push({input: input, map: map, marker: marker, autocomplete: autocomplete});
                    autoCompleteInit(autocompletes);
                }
            }
        }

        function autoCompleteInit(autocompletes) {
            const geocoder = new google.maps.Geocoder;

            for (let i = 0; i < autocompletes.length; i++) {
                const input = autocompletes[i].input;
                const autocomplete = autocompletes[i].autocomplete;
                const map = autocompletes[i].map;
                const marker = autocompletes[i].marker;

                google.maps.event.addListener(autocomplete, 'place_changed', function () {
                    marker.setVisible(false);
                    const place = autocomplete.getPlace();

                    geocoder.geocode({'placeId': place.place_id}, function (results, status) {
                        if (status === google.maps.GeocoderStatus.OK) {
                            const lat = results[0].geometry.location.lat();
                            const lng = results[0].geometry.location.lng();
                            setLocationCoordinates(autocomplete.key, lat, lng);
                        }
                    });

                    if (!place.geometry) {
                        window.alert("No details available for input: '" + place.name + "'");
                        input.value = "";
                        return;
                    }

                    if (place.geometry.viewport) {
                        map.fitBounds(place.geometry.viewport);
                    } else {
                        map.setCenter(place.geometry.location);
                        map.setZoom(17);
                    }
                    marker.setPosition(place.geometry.location);
                    marker.setVisible(true);

                });
            }
        }

        function setLocationCoordinates(key, lat, lng) {
            const latitudeField = document.getElementById(key + "-" + "latitude");
            const longitudeField = document.getElementById(key + "-" + "longitude");
            latitudeField.value = lat;
            longitudeField.value = lng;
        }
        countChar = function(event, tipo, max, len) {
            if (len == null) len = event.value.length;
            $('#'+tipo).empty();
            $('#'+tipo).append( len + '/' + max);
        };

        previewImage = function(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('previewImg');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        };

        $(function () {
            //CKEDITOR.replace('editor1');
            //$('.textarea').wysihtml5();
            /*CKEDITOR.instances['editor1'].on('change',function(event){
                var textLimit = 900;
                var str = CKEDITOR.instances['editor1'].editable().getText();
                countChar(event, "descricaoCount", textLimit, str.replace(/[\x00-\x1F\x7F-\x9F]/g, "").length);
                if (str.length >= textLimit) {
                    countChar(event, "descricaoCount", textLimit, str.slice(0, textLimit).length)
                    CKEDITOR.instances['editor1'].setData(str.slice(0, textLimit));
                    return false;
                }
            });*/

			$('#editor1').on('change',function(event){
                var textLimit = 900;
                var str = $('#editor1').editable().getText();
                countChar(event, "descricaoCount", textLimit, str.replace(/[\x00-\x1F\x7F-\x9F]/g, "").length);
                if (str.length >= textLimit) {
                    countChar(event, "descricaoCount", textLimit, str.slice(0, textLimit).length)
                    $('#editor1').setData(str.slice(0, textLimit));
                    return false;
                }
            });

            $('input').trigger('keyup');
            $('#editor1').trigger('keyup');
        })
    </script>
@endpush
