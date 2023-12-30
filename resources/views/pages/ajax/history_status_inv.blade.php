@php
  $flimit='2022-04-01 00:00:00';
  $fini = date('d-m-Y', strtotime('- 30 days', time()));
  if(strtotime($fini) < strtotime($flimit))
      $fini = date('d-m-Y',strtotime($flimit));

  $fend = date('d-m-Y', strtotime('- 0 days', time()));
  if(strtotime($fend) < strtotime($flimit))
      $fend = date('d-m-Y',strtotime($flimit));

  $accessPermission = 0;
  $validatePermission = 0;
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'EIV-HSI')
      $accessPermission = $policy->value;
  }
@endphp
  @if ($accessPermission > 0)
<link crossorigin="anonymous" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" referrerpolicy="no-referrer" rel="stylesheet"/>
<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">
        Histórico de Estatus de Inventario
      </h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li>
          <a href="/islim/">
            Dashboard
          </a>
        </li>
        <li class="active">
          Histórico de Estatus de Inventario
        </li>
      </ol>
    </div>
  </div>
</div>
<div class="container">
  <div class="row">
    <div class="col-12">
      <form class=" text-left" id="filterConc" method="POST" name="filterConc">
        <div class="row">
          <div class="col-md-6 col-sm-6">
            <div class="form-group">
              <label class="control-label">
                Cambio de Estatus (Desde)
              </label>
              <div class="input-group">
                <input autocomplete="off" class="form-control" id="dateb" name="dateb" placeholder="dd-mm-yyyy" type="text" value="{{ $fini }}">
                  <span class="input-group-addon">
                    <i class="icon-calender">
                    </i>
                  </span>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-sm-6">
            <div class="form-group">
              <label class="control-label">
                Cambio de Estatus (Hasta)
              </label>
              <div class="input-group">
                <input autocomplete="off" class="form-control" id="datee" name="datee" placeholder="dd-mm-yyyy" type="text" value="{{ $fend }}">
                  <span class="input-group-addon">
                    <i class="icon-calender">
                    </i>
                  </span>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-sm-6">
            <div class="form-group">
              <label class="control-label">
                Color de Estatus Actual
              </label>
              <select class="form-control" id="color_status" name="color_status">
                <option value="">Seleccione</option>
                <option value="orange">Naranja</option>
                <option value="red">Rojo</option>
              </select>
            </div>
          </div>
          <div class="col-md-6 col-sm-6">
            <div class="form-group">
              <div class="form-group">
                  <label class="control-label">MSISDN</label>
                  <select id="msisdn_select" name="msisdn_select" class="form-control" multiple>
                      <option value="">Seleccione el(los) msisdn(s)</option>
                  </select>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-12 p-t-20 text-center">
          <div class="form-group">
            <button class="btn btn-success" id="search" type="button">
              Buscar
            </button>
          </div>
        </div>
      </form>
    </div>
    <hr/>
    <div class="col-12 white-box">
      <div class="col-md-12">
          <h3 class="text-center">
              Reporte Histórico de Estatus de Inventario
          </h3>
          <button class="btn btn-success" id="download" type="button">
              Exportar Excel
          </button>
      </div>
      <div class="col-md-12 p-t-20">
        <div class="table-responsive">
          <table class="table table-striped {{-- display nowrap --}}" id="myTable">
            <thead>
              <tr>
                <th>Asignado a</th>
                <th>Coordinación</th>
                <th>Coordinador</th>
                <th>Region</th>
                <th>Regional</th>
                <th>MSISDN</th>
                <th>Producto</th>
                <th>Tipo de Producto</th>
                <th>Estatus de Color Actual</th>
                <th>Ultima Fecha que cambio a Naranja</th>
                <th>Veces que cambio a Naranja</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">

  function searchStatus() {
    $('.preloader').show();
    if ($.fn.DataTable.isDataTable('#myTable')) {
      $('#myTable').DataTable().destroy();
    }
    $('#myTable').DataTable({

      ajax: {
        url: "api/inventories/status/get-dt-status-history-inv",
        data: function(d) {
          d._token = $('meta[name="csrf-token"]').attr('content');
          d.dateb = $('#dateb').val();
          d.datee = $('#datee').val();
          d.color = $('#color_status').val();
          d.msisdns = getSelectObject('msisdn_select').getValue();
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
      columns: [
        { data: 'assigned',searchable: true,orderable: false},
        { data: 'coordination',searchable: true,orderable: true},
        { data: 'nameCoordinator',searchable: true,orderable: true},
        { data: 'region',searchable: true,orderable: true},
        { data: 'nameRegional',searchable: true,orderable: true},
        { data: 'msisdn',searchable: true,orderable: false},
        { data: 'title',searchable: true,orderable: false},
        { data: 'artic_type',searchable: true,orderable: false},
        { data: null,
          render: function(data, type, row, meta) {
            var html ="";
            if(row.color != ""){
              html = '<div style="background: ' + row.color + ';color: white;padding: 3px 10px;font-weight: bold;">' + row.date_color + '</div>';
            }
            return html;
          },
        searchable: false, orderable: false, width: "160px" },
      { data: 'last_date_orange',searchable: false,orderable: false},
      { data: 'cant_orange',searchable: false,orderable: false}
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


    var configSelect = {
      valueField: 'msisdn',
      labelField: 'msisdn',
      searchField: 'msisdn',
      options: [],
      create: false,
      render: {
          option: function(item, escape) {
              return '<p>'+item.msisdn+'</p>';
          }
      },
      load: function(query, callback) {
          if (!query.length) return callback();
          $.ajax({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              url: 'api/inventories/get-dns',
              type: 'POST',
              dataType: 'json',
              data: {
                  q: query
              },
              error: function() {
                  callback();
              },
              success: function(res){
                  if(res.success)
                      callback(res.clients);
                  else
                      callback();
              }
          });
      }
  };

  $('#msisdn_select').selectize(configSelect);


    $('#color_status').selectize();

    $('#search').on('click', function(e) {
      searchStatus();
    });

    $('#search').trigger('click');
  });

  $('#download').on('click', function(e){
          //var data = $("#kpi_dismissal_form").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');

          $(".preloader").fadeIn();

          $.ajax({
              type: "POST",
              url: "api/inventories/status/download-dt-status-history-inv",
              data: {
                    _token : $('meta[name="csrf-token"]').attr('content'),
                    dateb : $('#dateb').val(),
                    datee : $('#datee').val(),
                    msisdn : getSelectObject('msisdn_select').getValue()
                },
              dataType: "text",
              success: function(response){
                  $(".preloader").fadeOut();
                  swal('Generando reporte','El reporte estara disponible en unos minutos.','success');
              },
              error: function(err){
                  $(".preloader").fadeOut();
                  swal('Error','No se pudo generar el reporte.','error');
              }
          });
      });

  // $('#downloadReport').on('click', function(){

  //   var params = new FormData();
  //   params.append('_token', $('meta[name="csrf-token"]').attr('content'));
  //   params.append('dateb', $('#dateb').val());
  //   params.append('datee', $('#datee').val());
  //   params.append('color', $('#color_status').val());
  //   params.append('msisdns', $('#msisdns').val());
  //   params.append('is_val', $('#is_val').val());

  //   $(".preloader").fadeIn();
  //   $.ajax({
  //       headers: {
  //           'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  //       },
  //       url: 'view/reports/download-report-status-inv',
  //       type: 'post',
  //       data: params,
  //       contentType: false,
  //       processData: false,
  //       cache: false,
  //       async: true,
  //       success: function (res) {
  //           swal('Generando reporte','El reporte estara disponible en unos minutos.','success');
  //           $(".preloader").fadeOut();
  //       },
  //       error: function (res) {
  //           swal('Generando reporte','Ha ocurrido un error al intentar generar el reporte.','error');
  //           $(".preloader").fadeOut();
  //       }
  //   });
  // })
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
