<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Servicialidad</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Clientes</a></li>
                <li class="active">Art&iacute;culos no activos</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
    <div class="white-box m-t-20">
        <div class="row">
            <div class="col-md-12">
                <h3 class="text-center">
                    Servicialidad por dirección
                </h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <input type="text" class="form-control form-control-sm" id="address" name="address" placeholder="Escribe la dirección donde estara el Netwey*">

                <div id="map" class="col-md-12 col-sm-12 map-container" style="height: 50vh;min-height: 300px; padding-top: 20px;">

                    <div id="map-content" style="width: 100%;height: 100%;"></div>

                </div>
            </div>

            <div class="col-md-12 p-t-20">
                <div class="col-lg-4 col-xs-12 col-md-12 col-sm-12">
                    <label>Latitud:</label>
                    <input class="form-control lat-map" name="lat" type="text">
                    <div class="help-block with-errors"></div>
                </div>
                <div class="col-lg-4 col-xs-12 col-md-12 col-sm-12">
                    <label>longitud:</label>
                    <input class="form-control lon-map" name="lon" type="text">
                    <div class="help-block with-errors"></div>
                </div>
                <div class="col-lg-4 col-xs-12 col-md-4 col-sm-12">
                    <button type="button" name="btnGeo" id="btnGeo" class="btn btn-success waves-effect waves-light m-r-10" style="margin-top: 26px;">Consultar</button>
                </div>
            </div>

            <div class="col-md-12 p-t-20 hidden" id="serv-c">
                <div class="card card-outline-secondary text-center text-dark">
                    <div class="card-block">
                        <p class="m-0 font-18 serviciability"></p>
                    </div>
                </div>
            </div>
       </div>
    </div>
</div>

{{--<script src='https://maps.googleapis.com/maps/api/js?key={{env('GOOGLE_KEY')}}&libraries=places&callback=initPlaces'></script>--}}

<script type="text/javascript">
    function initPlaces(){
        var input = document.getElementById('address'),
            autocomplete = new google.maps.places.Autocomplete(input),
            geocoder = new google.maps.Geocoder,
            lat = 19.39068, 
            lng = -99.2836963;

        center = new google.maps.LatLng(lat, lng),
        map = new google.maps.Map(document.getElementById('map-content'), {
                        center,
                        zoom: 5
                    });

        marker = new google.maps.Marker({
                    map: map,
                    draggable: true,
                    animation: google.maps.Animation.DROP,
                    position: {lat: lat, lng: lng}
                });

        map.addListener('click', function(e) {
            marker.setPosition(e.latLng);
            map.panTo(e.latLng);
            
            lat = marker.getPosition().lat();
            lng = marker.getPosition().lng();

            geocodeLatLng(lat, lng, geocoder, map, marker, true);
        });

        marker.addListener('dragend', function (event){
            lat = marker.getPosition().lat();
            lng = marker.getPosition().lng();

            geocodeLatLng(lat, lng, geocoder, map, marker, true);
        });

        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            var place = autocomplete.getPlace();

            if(place.geometry){
                lat = place.geometry.location.lat();
                lng = place.geometry.location.lng();

                geocodeLatLng(lat, lng, geocoder, map, marker, true);
            }
        });

        $('#address').on('keypress', function(e){
            if(e.which == 10 || e.which == 13){
                var firstA = $('.pac-container').first().find('.pac-item-query').first().text();
                firstA += ' ' + $('.pac-container').first().find('.pac-item-query').first().next().text();

                $('#address').val(firstA);

                request = {
                    query: firstA,
                    fields: ['geometry']
                }

                placeService = new google.maps.places.PlacesService(map);

                placeService.findPlaceFromQuery(request, function(results, status){
                    if (status == google.maps.places.PlacesServiceStatus.OK){
                        if(results && results[0]){
                            lat = results[0].geometry.location.lat();
                            lng = results[0].geometry.location.lng();

                            geocodeLatLng(lat, lng, geocoder, map, marker, true);
                        }
                    }
                });
                e.preventDefault();
            }
        });

        $('.lon-map').on('blur', function(e){
            if($('.lon-map').val().trim() != '' && $('.lat-map').val().trim() != '' && !isNaN(parseFloat($('.lat-map').val())) && !isNaN(parseFloat($('.lon-map').val()))){
                geocodeLatLng(parseFloat($('.lat-map').val()), parseFloat($('.lon-map').val()), geocoder, map, marker, true);
            }
        });

        $('.lat-map').on('blur', function(e){
            if($('.lon-map').val().trim() != '' && $('.lat-map').val().trim() != '' && !isNaN(parseFloat($('.lat-map').val())) && !isNaN(parseFloat($('.lon-map').val()))){
                geocodeLatLng(parseFloat($('.lat-map').val()), parseFloat($('.lon-map').val()), geocoder, map, marker, true);
            }
        });

        $('#btnGeo').on('click', function(event){
            var lon = $('.lon-map').val().trim(),
                lat = $('.lat-map').val().trim();

            if(lon != '' && lon != '')
                callServ(lat, lon);
            else
                alert('No se puede consultar servicialidad sin las coordenadas');
        });
    }

    function centerMap(map, marker, lat, lng){
        if(lat != '' && lng != '' && !isNaN(parseFloat(lat)) && !isNaN(parseFloat(lng))){
            var latlng = {lat: parseFloat(lat), lng: parseFloat(lng)};

            map.setCenter(latlng);
            marker.setPosition(latlng);
            map.setZoom(16);

            return true;
        }
        return false;
    }

    function geocodeLatLng(lat,lng, geocoder, map, marker, ban) {
        var latlng = {lat:lat, lng: lng};

        geocoder.geocode({'location': latlng}, function(results, status) {
            $('#preloader').hide();
            if (status === 'OK') {
                if (results[0]){
                    if(ban){
                        $('#address').val(results[0].formatted_address);
                        $('.lon-map').val(lng),
                        $('.lat-map').val(lat);
                    }
                    centerMap(map, marker, lat, lng);
                }else{
                    showMessageAjax('alert-danger','No se encontro la dirección del punto marcado.');
                }
            }else{
                showMessageAjax('alert-danger','Ocurrio un error cargando la dirección, por favor intente mas tarde.');
                console.log(status);
            }
        });
    }

    function callServ(lat, lon){
        $('.preloader').show();

        //centerMap(map, marker, lat, lon);    

        $.ajax({
            type: 'POST',
            url: "{{route('getServiciability')}}",
            data: { _token: "{{ csrf_token() }}", lat:lat, lon:lon},
            dataType: "json",
            success: function(serv){
                $('.preloader').hide();
                if(serv.error){
                    alert(serv.message);
                }else{
                    $('.serviciability').text(serv.message);
                    $('#serv-c').removeClass('hidden');
                }
            },
            error: function(e){
                $('.preloader').hide();
                alert('Ocurrio un error, no se pudo consultar servicialidad.');
            }
        });
    }

    initPlaces();
</script>