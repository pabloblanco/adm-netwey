<div class="modal-header">
  <button class="modal_close_btn close" data-modal="#myModal" id="modal_close_x" type="button">
    ×
  </button>
  <h4 class="modal-title" id="modal-title">
    Detalle del mapa de cobertura
    <p class="mb-0">
      <strong>
        Zona o OLT de conexión:
      </strong>
      <span id="olt">
        {{$infoMap['olt']}}
      </span>
    </p>
    <p class="mb-0">
      <strong>
        Ciudad de conexión:
      </strong>
      <span id="city">
        {{$infoMap['city']}}
      </span>
    </p>
  </h4>
</div>
<div class="modal-body">
  <div class="container">
    <div class="row justify-content-center align-items-center">
      <div class="col-md-5 col-12">
        <form id="form-map">
          <div class="col-12">
            <div class="form-group">
              <label for="filekml">
                Cargar archivo .kml con los datos de cobertura
              </label>
              <input class="form-control-file" id="filekml" name="filekml" type="file"/>
              <label class="control-label" id="error_status_file">
              </label>
            </div>
          </div>
          <div class="col-12">
            <div class="form-group" id="block_zoom">
              <label for="zoom">
                Zoom de carga inicial
              </label>
              <input autocomplete="false" class="form-control text-left" id="zoom" name="zoom" placeholder="Ingrese el zoom del mapa" required="" title="zoom con el que se cargara por defecto" type="number" value="{{!empty($infoMap['poligono'])? $infoMap['poligono']['zoom']:''}}">
              </input>
            </div>
          </div>
        </form>
        <button class="btn btn-primary" data-action="create" id="submit" type="button">
          Cargar nuevo mapa
        </button>
      </div>
      <div class="col-md-7 col-12 map-container pt-md-0 pt-3" id="map" style="height: 50vh;min-height: 300px; ">
        <div id="map-content" style="width: 100%;height: 100%;">
        </div>
        <div class="position-absolute w-100" id="NoMap" style="position: absolute;">
          <h2 style="margin-top: -26vh;">
            <b>
              No se poseen datos de cobertura
            </b>
          </h2>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal-footer">
  <button class="btn btn-info" data-dismiss="modal" type="button">
    Cerrar
  </button>
</div>
<script defer="" type="text/javascript">
  $(document).ready(function() {

  $('form#form-map').validate({
      rules: {
        filekml: {
          required: true,
          extension: "kml"
        },
        zoom: {
          required: true,
          number: true
        }
      },
      messages: {
      filekml: {
        required: "Ingrese un archivo con formato .kml",
        extension: "El archivo no cumple con el formato .kml"
      },
      zoom:{
        number: "Por favor ingrese solo numero"
      }
    }
  });

  function upMap(){
    var params = new FormData();
    file = document.getElementById('filekml').files[0];
    params.append('filekml', file);
    params.append('zoom', $('#zoom').val());
    params.append('_token', $('meta[name="csrf-token"]').attr('content'));
    $('.preloader').show();
    $.ajax({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      contentType: false,
      processData: false,
      cache: false,
      async: true,
      url: 'view/fiber/loadMap',
      method: 'POST',
      data: params,
      success: function(res) {
        $(".preloader").fadeOut();
        if (res.success) {
          $('#map-content').css('filter', 'none');
          $('#NoMap').addClass('d-none');
          $('#btnmap'+res.id).removeClass('btn-warning');
          $('#btnmap'+res.id).addClass('btn-primary');
          $('#btnmap'+res.id).html('Ver mapa');

          initPlaces(res.poligono, res.pointCenter);
        } else {
          swal(res.msg, {
            icon: "warning",
          });
        }
        $('input#filekml').val("");
      },
      error: function(res) {
        $(".preloader").fadeOut();
        console.log(res);
      }
    });
  }

  $('#submit').on('click', function(e) {
    //se sube el archivo
      if (!$('form#form-map').valid()) {
        return 0;
      }
      upMap();
    });
});
</script>
