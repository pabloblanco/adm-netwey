{{--/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Agosto 2022
 */--}}
<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">
        Cobertura de Zonas de Fibra
      </h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12 d-flex justify-content-end">
      <ol class="breadcrumb">
        <li>
          <a href="#">
            Gestión de Fibra
          </a>
        </li>
        <li class="active">
          Cobertura de Fibra
        </li>
      </ol>
    </div>
  </div>
</div>
<div class="container-fluid">
  <div class="row justify-content-center">
    <div class="col-12">
      <button class="btn btn-info btn-lg" id="update_zones" type="button">
        Actualizar lista de zonas
      </button>
    </div>
  </div>
</div>
<div class="container">
  <div class="row justify-content-center">
    <div class="col-12" hidden="" id="rep-sc">
      <div class="row white-box">
        <div class="col-12">
          <h3 class="text-center">
            Listado de Cobertura de Zonas de Fibra
          </h3>
        </div>
        <div class="col-12">
          <div class="table-responsive">
            <table class="table table-striped display nowrap" id="list-com">
              <thead>
                <tr>
                  <th class="text-center align-middle">
                    Acciones
                  </th>
                  <th class="text-center align-middle">
                    Zona o OLT de conexión
                  </th>
                  <th class="text-center align-middle">
                    Ciudad
                  </th>
                  <th class="text-center align-middle">
                    Zoom
                  </th>
                  <th class="text-center align-middle">
                    Status
                  </th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal modalAnimate" id="myModal" role="dialog">
  <div class="modal-dialog" id="modal01">
    <div class="modal-content" id="infoMap">
    </div>
  </div>
</div>
<script src="js/common-modals.js">
</script>
<script defer="" type="text/javascript">
  //Funciones para manejo del mapa
    //    
    var temp_lat  = 19.39068;
    var temp_lng  = -99.2836963;
    var temp_zoom = 5;
    var FiberZonePolygon = null;
    
    let setPoligono = function(dataPoligono, point_center){

      if(dataPoligono !== null && point_center !== null){
        poligono = dataPoligono['poligono'];
        temp_lat = point_center['lat'];
        temp_lng = point_center['lng'];
        temp_zoom = dataPoligono['zoom'];

        if(FiberZonePolygon !== null ){
          //reseteo area
          FiberZonePolygon.setMap(null);
        }
        // Construye el polígono.
         FiberZonePolygon = new google.maps.Polygon({
            paths: poligono,
            strokeColor: '#82954B',
            strokeOpacity: 0.8,
            strokeWeight: 3,
            fillColor: '#BABD42',
            fillOpacity: 0.4,
            clickable: false
        });
      }
    }    

    var dataPoligono, point_center;
    let map, marker, center, geocoder;

    let eventoMapa = function(latLng, geocoder){
      //console.log(latLng);
     // marker.setPosition(latLng);
      map.panTo(latLng);
        
    //  lat = marker.getPosition().lat();
    //  lng = marker.getPosition().lng();

      geocodeLatLng(lat, lng, geocoder, map, marker, true);
    }

     function initPlaces(dataPolygon, point_init){
      dataPoligono = dataPolygon;
      point_center = point_init;
      setPoligono(dataPoligono, point_center);

      geocoder = new google.maps.Geocoder;
      lat = temp_lat; 
      lng = temp_lng;

      center = new google.maps.LatLng(lat, lng);

      map = new google.maps.Map(document.getElementById('map-content'), {
                    center,
                    zoom: temp_zoom
      });

      if(FiberZonePolygon !== null ){
        FiberZonePolygon.setMap(map);
      } 
     /* marker = new google.maps.Marker({
        map: map,
        draggable: true,
       // icon: image,
        animation: google.maps.Animation.DROP,
        position: {lat: lat, lng: lng}
      });
*/
      map.addListener('click', function(e) {
        eventoMapa(e.latLng, geocoder);
      });
/*
      marker.addListener('dragend', function(event) {
        eventoMapa(event.latLng, geocoder);
      });

      marker.addListener('dragend', function (event){
          lat = marker.getPosition().lat();
          lng = marker.getPosition().lng();

          geocodeLatLng(lat, lng, geocoder, map, marker, true);
      });
*/
    }

    function centerMap(map, marker, lat, lng){
      if(lat != '' && lng != '' && !isNaN(parseFloat(lat)) && !isNaN(parseFloat(lng))){

        setPointMap(lat, lng); 
        setPoligono(dataPoligono, point_center);
        if(FiberZonePolygon !== null ){
          FiberZonePolygon.setMap(map);
        }

        if(!$('.loading-ajax').is(':visible')){
          $('.loading-ajax').show();
        }
        return true;
      }
      return false;
    }

    let geocodeLatLng = function(lat,lng, geocoder, map, marker, ban) {
      centerMap(map, marker, lat, lng);
    }
</script>
<script src="js/fiber/fiberMap.js">
</script>
