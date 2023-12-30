@php
  $flimit='2020-01-01 00:00:00';
  $fini = date('d-m-Y', strtotime('- 30 days', time()));
  if(strtotime($fini) < strtotime($flimit))
      $fini = date('d-m-Y',strtotime($flimit));

  $fend = date('d-m-Y', strtotime('- 0 days', time()));
  if(strtotime($fend) < strtotime($flimit))
      $fend = date('d-m-Y',strtotime($flimit));

  $accessPermission = 0;
  $validatePermission = 0;
  $moveTomerma = 0;
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'EIV-REI')
      $accessPermission = $policy->value;
    if ($policy->code == 'EIV-VEI')
      $validatePermission = $policy->value;
    if($policy->code == 'EIV-MMO')
      $moveTomerma = $policy->value;
  }
@endphp
  @if ($accessPermission > 0 || $validatePermission > 0)
<link crossorigin="anonymous" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" referrerpolicy="no-referrer" rel="stylesheet"/>
<link as="style" rel="preload" async="async" href="{{ asset('css/select2.min.css')}}" onload="this.onload=null;this.rel='stylesheet'" />

<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      @if(!$is_val)
      <h4 class="page-title">
        Estatus de Inventario
      </h4>
      @else
      <h4 class="page-title">
        Validar Estatus de Inventario
      </h4>
      @endif
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li>
          <a href="/islim/">
            Dashboard
          </a>
        </li>
        @if(!$is_val)
        <li class="active">
          Estatus de Inventario
        </li>
        @else
        <li class="active">
          Validar Estatus de Inventario
        </li>
        @endif
      </ol>
    </div>
  </div>
</div>
<div class="container">
  <div class="row">
    <div class="col-12">
      @if($is_val)
      @if($moveTomerma)
      <div class="col-12" id="status_file_container">
        <div class=" form-group">
          <div class="row">
            <div class="col-12">
              <label class="control-label">
                Mover inventario a bodega merma equipos viejos
              </label>

              <div class="alert alert-warning m-t-10" style="padding: 5px;font-weight: 600;">
                Esta acción ejecuta el proceso para pasar el inventario que no se ha validado a la bodega merma equipos viejos, por favor tomar en ceunta que los equipos que se moverán son los del mes pasado, <b>esta acción no tiene reverso</b>.
              </div>

              <button class="btn btn-success" id="move-inv">
                Mover inventario
              </button>
            </div>
          </div>
        </div>
        <hr/>
      </div>
      @endif

      <div class="col-12" id="status_file_container">
        <div class=" form-group">
          <div class="row justify-content-center align-items-center">
            <label class="col-12 control-label">
              Cargar archivo csv con status de forma masiva
            </label>
            <div class="col-md-8 col-12">
              <input class="form-control-file" id="file_csv" name="file_csv" type="file">
                <label class="control-label" id="error_status_file">
                </label>
              </input>
            </div>
            <div class="col-md-4 col-12 text-center">
              <button class="btn btn-success" onclick="pushFile()">
                <i class="fa fa-check">
                </i>
                Cargar archivo
              </button>
            </div>
          </div>
        </div>
        <hr/>
      </div>
      @endif
      <form class=" text-left" id="filterConc" method="POST" name="filterConc">
        <div class="row">
          <div class="col-md-6 col-sm-6">
            <div class="form-group">
              <label class="control-label">
                Fecha desde
              </label>
              <div class="input-group">
                <input autocomplete="off" class="form-control" id="dateb" name="dateb" placeholder="dd-mm-yyyy" type="text" value="{{ $fini }}">
                  <span class="input-group-addon">
                    <i class="icon-calender">
                    </i>
                  </span>
                </input>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-sm-6">
            <div class="form-group">
              <label class="control-label">
                Fecha hasta
              </label>
              <div class="input-group">
                <input autocomplete="off" class="form-control" id="datee" name="datee" placeholder="dd-mm-yyyy" type="text" value="{{ $fend }}">
                  <span class="input-group-addon">
                    <i class="icon-calender">
                    </i>
                  </span>
                </input>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-sm-6">
            <div class="form-group">
              <label class="control-label">
                Color Estatus
              </label>
              <select class="form-control" id="color_status" name="color_status">
                <option value="">
                  Seleccione
                </option>
                <option value="orange">
                  Naranja
                </option>
                <option value="red">
                  Rojo
                </option>
              </select>
            </div>
          </div>
          <div class="col-md-6 col-sm-6">
            <div class="form-group">
              <label class="control-label">
                MSISDNS
              </label>
              <div class="input-group">
                <input autocomplete="off" class="form-control" id="msisdns" name="msisdns" placeholder="Ingresa MSISDNS separado por (,)" type="text">
                </input>
              </div>
            </div>
          </div>
          @if($is_val)
            <div class="col-md-6 col-sm-6">
              <div class="form-group">
                <label class="control-label">
                  Con evidencia
                </label>
                <select class="form-control" id="evidence" >
                  <option value="">
                    Seleccione
                  </option>
                  <option value="Y">
                    Si
                  </option>
                  <option value="N">
                    No
                  </option>
                </select>
              </div>
            </div>
      
          @endif
        </div>
        @if(!$is_val)
        <input id="is_val" type="hidden" value="false">
          @else
          <input id="is_val" type="hidden" value="true">
            @endif
            <div class="col-md-12 p-t-20 text-center">
              <div class="form-group">
                <button class="btn btn-success" id="search" type="button">
                  Buscar
                </button>
                <button class="btn btn-success" id="downloadReport" type="button">
                  Generar Reporte
                </button>
              </div>
            </div>
          </input>
        </input>
      </form>
    </div>
    <hr/>
    <div class="col-12 white-box">
      <div class="table-responsive">
        <table class="table table-striped display nowrap" id="myTable">
          <thead>
            <tr>
              <th>
                Asignado a
              </th>
              <th>
                Nombre y Apellido
              </th>
              <th>
                Coordinación
              </th>
              <th>
                MSISDN
              </th>
              <th>
                Producto
              </th>
              <th>
                Tipo de Producto
              </th>
              <th>
                Estatus de Color
              </th>
              @if ($is_val && $validatePermission > 0)
              <th>
                Evidencia
              </th>
              <th>
                Motivo
              </th>
              @endif
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>


@if ($is_val && $validatePermission > 0)
<!--modal de editar-->
<div class="modal" id="evidenceModal" role="dialog">
    <div class="modal-dialog modal-md">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button class="close" data-dismiss="modal" type="button">
                    ×
                </button>
                <h4 class="modal-title">
                    Evidencia de estatus Rojo del Dn: <span id="evidence-dn"></span>
                    <span id="modal_edit_dn">
                    </span>
                </h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                      <img id='evidence-img' class="w-100" src="" alt="">
                    </div>
                    <div class="col-12 text-center">
                        <button class="btn btn-info btn-md button my-3" style="max-width: 120px;" data-dismiss="modal">
                            Volver
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
<script type="text/javascript">
  function pushFile() {
    filevalid = validatFiles('file_csv', 'error_status_file', 'Debe seleccionar el archivo CSV con la columna msisdn y motivo(V,I,R)', 'El archivo debe ser de extensión CSV');
    if (filevalid) {
      savefile();
    }
  }

  function savefile() {

    var params = new FormData();
    file = document.getElementById('file_csv').files[0];
    params.append('file_csv', file);
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
      url: 'api/inventories/status/load-file',
      method: 'POST',
      data: params,
      success: function(res) {
        $(".preloader").fadeOut();
        $('#file_csv').html('');
        if(res.success){
          var msisdnSuccess  = res.msisdnSuccess;
          var msisdnFail     = res.msisdnFail;
          var msisdnOrange     = res.msisdnOrange;
          var msisdnNotFound = res.msisdnNotFound;
          var response       = "";
          if (msisdnSuccess.length > 0) {
            response = response + 'Los siguientes msisdn: ' + msisdnSuccess + ' fueron procesados ' + '\n\n';
          }
          if (msisdnFail.length > 0) {
            response = response + 'Los siguientes msisdn: ' + msisdnFail + ' son registros a actualizar pero presentaron inconvenientes en procesar ' + '\n\n';
          }
          if (msisdnOrange.length > 0) {
            response = response + 'Los siguientes msisdn: ' + msisdnOrange + ' no fueron procesados ya que aun se encuentra en ( Naranja ) ' + '\n\n';
          }
          if (msisdnNotFound.length > 0) {
            response = response + 'Los siguientes msisdn: ' + msisdnNotFound + ' no cumplen con el criterio de validar status de inventario' + '\n\n';
          }

          swal({
            text: response,
            dangerMode: true,
            closeOnClickOutside: false});
          searchStatus();
        }else{
          swal({
            text: res.msg,
            dangerMode: true,
            closeOnClickOutside: false});
        }
      },
      error: function(res) {
        $(".preloader").fadeOut();
        console.log(res);
      }
    });
  }

  function searchStatus() {
    $('.preloader').show();
    if ($.fn.DataTable.isDataTable('#myTable')) {
      $('#myTable').DataTable().destroy();
    }
    $('#myTable').DataTable({

      ajax: {
        url: "api/inventories/status/get-dt-status-inv",
        data: function(d) {
          d._token = $('meta[name="csrf-token"]').attr('content');
          d.dateb = $('#dateb').val();
          d.datee = $('#datee').val();
          d.color = $('#color_status').val();
          d.msisdns = $('#msisdns').val();
          d.is_val = $('#is_val').val();
          d.evidence = $('#evidence').val();
        },
        type: "POST"
      },
      initComplete: function(settings, json) {
        $(".preloader").fadeOut();
        $('#rep-sc').attr('hidden', null);
      },
      searching: true,
      processing: true,
      serverSide: true,
      iDisplayLength: 20,
      deferRender: true,
      ordering: true,
      columns: [{
        data: 'assigned',
        searchable: true,
        orderable: false
      }, {
        data: 'nameAssigned',
        searchable: true,
        orderable: false
      },{
        data: 'esquema',
        searchable: true,
        orderable: true
      },{
        data: 'msisdn',
        searchable: true,
        orderable: false
      }, {
        data: 'title',
        searchable: true,
        orderable: false
      }, {
        data: 'artic_type',
        searchable: true,
        orderable: false
      },
      {
        data: null,
        render: function(data, type, row, meta) {
          var html = '<div style="background: ' + row.color + ';color: white;padding: 3px 10px;font-weight: bold;">' + row.date_color + '</div>';
          return html;
        },
        searchable: false,
        orderable: false,
        width: "160px"
      },
      @if ($is_val && $validatePermission > 0)
      {
        data: null,
        render: function(data, type, row, meta) {

          var html = '<div class="container-evidence-btns" data-id="' + row.assigned + '-' + row.inv_arti_details_id + '">';

          if(row.evidence && row.color == 'red'){
            html += '<button title="ver evidencia" type="button" class="btn btn-link btn-md text-info" style="width: 100%; padding: 0.4rem 1rem;" onclick="evidence_btn(`' + row.evidence + '`,`' + row.msisdn + '`)"><i class="fas fa-eye"></i></button>';
          }

          html += '</div>';

          return html;
        },
        searchable: false,
        orderable: false
      },
      {
        data: null,
        render: function(data, type, row, meta) {
          if (row.color == 'red') {
            var html = '<div class="container-motive-btns" data-id="' + row.assigned + '-' + row.inv_arti_details_id + '">';
            html += '<button title="Válido" type="button" id="btn-valid" class="btn btn-success btn-md" style="width: 40px; padding: 0.4rem 1rem;" onclick="motive_btn(`valido`,`' + row.assigned + '`,`' + row.inv_arti_details_id + '`,`' + row.msisdn + '`)"><i class="fas fa-check-circle"></i></button>';
            html += '<button title="No válido" type="button" id="btn-invalid" class="btn btn-warning btn-md"  style="width: 40px; padding: 0.4rem 1rem;" data-evidence="'+ (row.evidence != null ? 'Y':'N') +'" onclick="motive_btn(`invalido`,`' + row.assigned + '`,`' + row.inv_arti_details_id + '`,`' + row.msisdn + '`)"><i class="fas fa-times-circle"></i></button>';
            //if(row.evidence == null){
              html += '<button title="Robo" type="button" id="btn-theft" class="btn btn-danger btn-md '+(row.evidence == null?"":"d-none")+'" style="width: 40px; padding: 0.4rem 1rem;" onclick="motive_btn(`robo`,`' + row.assigned + '`,`' + row.inv_arti_details_id + '`,`' + row.msisdn + '`)"><i class="fas fa-mask"></i></button>';
            //}
            html += '</div>';
            return html;
          }
          return "";
        },
        searchable: false,
        orderable: false,
        width: "130px"
      }
      @endif
      ]
    });
  }

  $(document).ready(function() {
    maxdays = 2 * 365;
    flimit = new Date(Date.parse("{{$flimit}}"));
    var config = {
      autoclose: true,
      format: 'dd-mm-yyyy',
      todayHighlight: true,
      language: 'es',
      startDate: flimit,
      endDate: new Date()
    }

    $('#dateb').datepicker(config).on('changeDate', function(selected) {
      var dt = $('#datee').val();
      if (dt == '') {
        $('#datee').datepicker('update', sumDays($('#dateb').datepicker('getDate'), maxdays));
      } else {
        var diff = getDateDiff($('#dateb').datepicker('getDate'), $('#datee').datepicker('getDate'));
        if (diff > maxdays) {
          $('#datee').datepicker('update', sumDays($('#dateb').datepicker('getDate'), maxdays));
        }
      }
      var diff2 = getDateDiff($('#datee').datepicker('getDate'), flimit);
      if (diff2 > 0) {
        $('#datee').datepicker('update', flimit);
      }
      var maxDate = new Date(selected.date.valueOf());
      $('#datee').datepicker('setStartDate', maxDate);
    });

    config.endDate = new Date(new Date().setTime(new Date().getTime()));

    $('#datee').datepicker(config).on('changeDate', function(selected) {
      var dt = $('#dateb').val();
      if (dt == '') {
        $('#dateb').datepicker('update', sumDays($('#datee').datepicker('getDate'), -maxdays));
      } else {
        var diff = getDateDiff($('#dateb').datepicker('getDate'), selected.date);
        if (diff > maxdays) {
          $('#dateb').datepicker('update', sumDays($('#datee').datepicker('getDate'), -maxdays));
        }
      }
      var diff2 = getDateDiff($('#dateb').datepicker('getDate'), flimit);
      if (diff2 > 0) {
        $('#dateb').datepicker('update', flimit);
      }
      var maxDate = new Date(selected.date.valueOf());
      $('#dateb').datepicker('setEndDate', maxDate);
    });

    $('#color_status').selectize();
    $("#evidence").selectize();
      

    $('#search').on('click', function(e) {
      searchStatus();
    });

    $('#search').trigger('click');

    evidence_btn = (url,dn) => {
      if(url.length > 0){
        $('#evidence-img').prop('src',url);
        $('#evidence-dn').text(dn);
        $('#evidenceModal').modal();
      }
    }

    @if($moveTomerma)
    $('#move-inv').on('click', function(e){
      swal({
        title: "¿Deseas mover el inventario a la bodega de merma equipos viejos?",
        text: "Esta acción no tiene reverso",
        icon: "warning",
        buttons: {
          cancel: "Cancelar",
          accept: "Aceptar"
        },
        closeOnClickOutside: false
      }).then((accept) => {
        if(accept === 'accept'){
          $.ajax({
            url: "{{route('moveToMerma')}}",
            type: 'POST',
            data: {
              _token: $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(result) {
              if (result.success) {
                swal({
                  title: 'Inventario movido exitosamente',
                  text: '',
                  icon: "success"
                });
                
                $('.preloader').hide();
              } else {
                swal({
                  title: 'Ocurrio un error',
                  text: 'por favor intente nuevamente',
                  icon: "error"
                });
                $('.preloader').hide();
              }
            },
            error: function(e) {
              swal({
                title: 'Ocurrio un error',
                text: 'por favor intente nuevamente',
                icon: "error"
              });
              
              $('.preloader').hide();
            }
          });
        }
      });
    });
    @endif

    motive_action = (motive, user_email, arti_detail, msisdn, reason = null) => {

      switch (motive) {
        case "valido":
          txt = "El DN #" + msisdn + " no se ha vendido por un motivo válido";
          url = "api/inventories/status/set-valid-motive";
          reason = null;
        break;
        case "invalido":
            if(reason != null){
              txt = 'La solicitud para volver a estatus naranja del DN #' + msisdn + ' se rechazó por el motivo: "'+reason+'"';
            }
            else{
              txt = "El DN #" + msisdn + " no se ha vendido por un motivo no válido";
            }
            url = "api/inventories/status/set-invalid-motive";
        break;
        case "robo":
          txt = "El DN #" + msisdn + " no se ha vendido por robo";
          url = "api/inventories/status/set-theft-motive";
          reason = null;
        break;

      }
      swal({
        title: "¿Desea realizar esta acción?",
        text: "Se registrará que "+txt + ", esta acción no se podrá revertir",
        icon: "warning",
        buttons: {
          cancel: "Cancelar",
          accept: "Aceptar"
        },
        closeOnClickOutside: false
      }).then((accept) => {
        if (accept) {
          $('.preloader').show();
          $.ajax({
            url: url,
            type: 'POST',
            data: {
              users_email: user_email,
              inv_arti_details_id: arti_detail,
              reason: reason,
              _token: $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(result) {
              if (result.success) {
                swal({
                  title: 'Motivo registrado con éxito',
                  text: txt,
                  icon: "success"
                });
                if(reason == null){
                  $('.container-motive-btns[data-id="' + user_email + '-' + arti_detail + '"]').addClass('d-none');
                  $('.container-evidence-btns[data-id="' + user_email + '-' + arti_detail + '"]').addClass('d-none');
                }
                else{
                  $('.container-motive-btns[data-id="' + user_email + '-' + arti_detail + '"] > #btn-invalid').data('evidence','N');
                  $('.container-motive-btns[data-id="' + user_email + '-' + arti_detail + '"] > #btn-theft').removeClass('d-none');
                  $('.container-evidence-btns[data-id="' + user_email + '-' + arti_detail + '"]').addClass('d-none');
                }
                $('.preloader').hide();
              } else {
                swal({
                  title: 'Ocurrio un error',
                  text: 'por favor verifique e intente nuevamente',
                  icon: "error"
                });
                $('.preloader').hide();
              }
            },
            error: function(e) {
              swal({
                title: 'Ocurrio un error',
                text: 'por favor intente nuevamente ',
                icon: "error"
              });
              console.log(e);
              $('.preloader').hide();
            }
          });
        }
      });

    }
    motive_btn = (motive, user_email, arti_detail, msisdn) => {
      switch (motive) {
        case "valido":
          motive_action(motive, user_email, arti_detail, msisdn);
        break;
        case "invalido":
          has_evidence = $('.container-motive-btns[data-id="' + user_email + '-' + arti_detail + '"] > #btn-invalid').data('evidence');

          console.log(has_evidence);

          if(has_evidence == 'Y'){
            swal({
              icon:'info',
              text: 'Por favor ingrese el motivo',
              content: {
                  element: "input",
                  attributes: {
                    type: "text",
                    id : "input-motive"
                  },
                },
              buttons: {
                cancel: "Cancelar",
                accept: "Aceptar"
              },
              closeOnClickOutside: false
            })
            .then(accept => {
              if (accept){
                reason = $('#input-motive').val();
                if(reason.trim().length > 3){
                  motive_action(motive, user_email, arti_detail, msisdn, reason.trim());
                }
                else{
                  swal({
                    icon: "error",
                    text: "Debes escribir  un motivo valido de al menos 3 caracteres",
                    closeOnClickOutside: false
                  }).then(()=>{
                    motive_btn(motive, user_email, arti_detail, msisdn)
                  })
                }
              }
            })
          }
          else{
            motive_action(motive, user_email, arti_detail, msisdn);
          }
        break;
        case "robo":
          motive_action(motive, user_email, arti_detail, msisdn);
        break;
      }
    }
  });

 $('#downloadReport').on('click', function(){

    var params = new FormData();
    params.append('_token', $('meta[name="csrf-token"]').attr('content'));
    params.append('dateb', $('#dateb').val());
    params.append('datee', $('#datee').val());
    params.append('color', $('#color_status').val());
    params.append('msisdns', $('#msisdns').val());
    params.append('is_val', $('#is_val').val());
    params.append('evidence', $('#evidence').val());

    $(".preloader").fadeIn();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'view/reports/download-report-status-inv',
        type: 'post',
        data: params,
        contentType: false,
        processData: false,
        cache: false,
        async: true,
        success: function (res) {
          console.log(res);
            swal('Generando reporte','El reporte estara disponible en unos minutos.','success');
            $(".preloader").fadeOut();
        },
        error: function (res) {
            swal('Generando reporte','Ha ocurrido un error al intentar generar el reporte.','error');
            $(".preloader").fadeOut();
        }
    });
})
</script>
{{--
<script src="js/statusinv/main.js?v=2.0" type="text/javascript">
</script>
--}}
@else
<h3>
  Lo sentimos, usted no posee permisos suficientes para acceder a este módulo
</h3>
@endif
