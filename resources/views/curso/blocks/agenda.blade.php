<?php if((isset($tipo) && ($tipo == 2)) OR (isset($curso->fk_cursos_tipo) && ($curso->fk_cursos_tipo == 2))) : ?>
<div class="form-group">
    {{ Form::label('Endereço (das aulas presenciais)') }}
    {{ Form::input('text', 'endereco_presencial', isset($curso->endereco_presencial) ? $curso->endereco_presencial : '', ['class' => 'form-control map-input', 'id' => 'address-input', 'style' => 'width: 50%;']) }}
    <input type="hidden" name="address_latitude" id="address-latitude" value="0" />
    <input type="hidden" name="address_longitude" id="address-longitude" value="0" />
</div>
<div id="address-map-container" style="width:100%;height:400px; ">
    <div style="width: 100%; height: 100%" id="address-map"></div>
</div>
<div class="form-group">
    <hr />
    <div class="row">
        <div class="col-md-6">
            <h3>Agenda de Cursos Presenciais</h3>
        </div>
        <div class="col-md-6">
            <a href="javascript:;" id="btn_incluir_agenda" class="btn btn-success right">+ Agenda</a>
        </div>
    </div>

    <div class="well" id ="bloco_agenda" data-contador="1">
        @if(isset($agendas_cadastradas) && count($agendas_cadastradas))
        @foreach($agendas_cadastradas as $key => $agenda)
        <div class="row agenda well" style="margin:10px; background: #E6E6E6;" id="agendaRow" data-id="<?php echo $key + 1; ?>">
            @if(Request::is('*/editar'))
                <input type="hidden" name="<?php echo "agenda[".($key + 1)."]"."[id_agenda]"; ?>" value="<?php echo $agenda->id; ?>" />
            @endif
            <div class="col-md-6">
                {{ Form::label('Descrição') }}
                {{ Form::input('text', 'agenda['.($key + 1).'][descricao]', $agenda->nome, ['class' => 'form-control', '', 'placeholder' => 'Descrição']) }}
            </div>
            <div class="col-md-6">
                {{ Form::label('Data') }}
                {{ Form::input('text', 'agenda['.($key + 1).'][data_inicio]', implode('/', array_reverse(explode('-', $agenda->data_inicio))), ['class' => 'form-control datepicker', '', 'placeholder' => 'Data Início']) }}
            </div>
            <div class="col-md-4">
                {{ Form::label('Hora Início') }}
                {{ Form::input('text', 'agenda['.($key + 1).'][hora_inicio]', $agenda->hora_inicio, ['class' => 'form-control timepicker2', '', 'placeholder' => 'Hora Início', 'onchange' => 'duracaoPresencial(event)']) }}
            </div>
            <div class="col-md-4">
                {{ Form::label('Hora Término') }}
                {{ Form::input('text', 'agenda['.($key + 1).'][hora_fim]', $agenda->hora_final, ['class' => 'form-control timepicker2', '', 'placeholder' => 'Hora Término', 'onchange' => 'duracaoPresencial(event)']) }}
            </div>
            <div class="col-md-4">
                {{ Form::label('Duração Aula ') }}<small>(HH:MM:SS)</small>
                {{ Form::input('text', 'agenda['.($key + 1).'][duracao_aula]', $agenda->duracao_aula, ['class' => 'form-control duracao_aula', '', 'placeholder' => 'Duracao Aula', 'id' => 'duracao_aula-'.($key+1)]) }}
            </div>
            <div class="col-md-12">
                <a href="javascript:;" style="margin-top:15px;" class="btn btn-danger btn_excluir_agendamento" title="Excluir Agendamento" onclick="return confirm('Deseja realmente excluir?')"><i class="fa fa-fw fa-trash"></i></a>
            </div>
        </div>
        @endforeach
        @else
        <div class="row agenda well" data-id="1" style="margin:10px; background: #E6E6E6;">
            <div class="col-md-6">
                {{ Form::label('Descrição') }}
                {{ Form::input('text', 'agenda[1][descricao]', null, ['class' => 'form-control', '', 'placeholder' => 'Descrição']) }}
            </div>
            <div class="col-md-6">
                {{ Form::label('Data') }}
                {{ Form::input('text', 'agenda[1][data_inicio]', null, ['class' => 'form-control datepicker', '', 'placeholder' => 'Data']) }}
            </div>
            <div class="col-md-4">
                {{ Form::label('Hora Início') }}
                {{ Form::input('text', 'agenda[1][hora_inicio]', null, ['class' => 'form-control timepicker', '', 'placeholder' => 'Hora Início', 'onchange' => 'duracaoPresencial(event)']) }}
            </div>
            <div class="col-md-4">
                {{ Form::label('Hora Término') }}
                {{ Form::input('text', 'agenda[1][hora_fim]', null, ['class' => 'form-control timepicker', '', 'placeholder' => 'Hora Término', 'onchange' => 'duracaoPresencial(event)']) }}
            </div>
            <div class="col-md-4">
                {{ Form::label('Duração Aula ') }}<small>(HH:MM:SS)</small>
                {{ Form::input('text', 'agenda[1][duracao_aula]', null, ['class' => 'form-control duracao_aula', '', 'placeholder' => 'Duracao Aula', 'id' => 'duracao_aula-1']) }}
            </div>
            <div class="col-md-12">
                <a href="javascript:;" style="margin-top:15px;" class="btn btn-danger btn_excluir_agendamento" title="Excluir Agendamento" onclick="return confirm('Deseja realmente excluir?')"><i class="fa fa-fw fa-trash"></i></a>
            </div>
        </div>
        @endif
    </div>

    <hr />
    <!-- modelo Agenda de curso (para clonar) -->
    <div id="default_agenda" style="display: none;" data-id="__X__">
        <div class="row agenda well" data-id="__X__" style="margin:10px; background: #E6E6E6;">
            <hr />
            <div class="col-md-6">
                {{ Form::label('Descrição') }}
                {{ Form::input('text', 'agenda[__X__][descricao]', null, ['class' => 'form-control', '', 'placeholder' => 'Descrição']) }}
            </div>
            <div class="col-md-6">
                {{ Form::label('Data') }}
                {{ Form::input('text', 'agenda[__X__][data_inicio]', date('d/m/Y'), ['class' => 'form-control datepicker', '', 'placeholder' => 'Data Início', 'id' => 'agenda__X__data_inicio']) }}
            </div>
            <div class="col-md-4">
                {{ Form::label('Hora Início') }}
                {{ Form::input('text', 'agenda[__X__][hora_inicio]', null, ['class' => 'form-control timepicker', '', 'placeholder' => 'Hora Início', 'onchange' => 'duracaoPresencial(event)']) }}
            </div>
            <div class="col-md-4">
                {{ Form::label('Hora Término') }}
                {{ Form::input('text', 'agenda[__X__][hora_fim]', null, ['class' => 'form-control timepicker', '', 'placeholder' => 'Hora Término', 'onchange' => 'duracaoPresencial(event)']) }}
            </div>
            <div class="col-md-4">
                {{ Form::label('Duração Aula ') }}<small>(HH:MM:SS)</small>
                {{ Form::input('text', 'agenda[__X__][duracao_aula]', null, ['class' => 'form-control duracao_aula', '', 'placeholder' => 'Duracao Aula', 'id' => 'duracao_aula-__X__']) }}
            </div>
            <div class="col-md-12">
                <a href="javascript:;" style="margin-top:15px;" class="btn btn-danger btn_excluir_agendamento" title="Excluir Agendamento" onclick="return confirm('Deseja realmente excluir?')"><i class="fa fa-fw fa-trash"></i></a>
            </div>
        </div>
    </div>
    <!-- FIM Agenda -->
</div>

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
    function duracaoPresencial() {} // definido para evitar erro de não carregar nenhum js do blade formulario do curso
</script>
@endpush
<?php endif; ?>
