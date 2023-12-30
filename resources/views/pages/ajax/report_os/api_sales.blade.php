<style type="text/css">
  .selectize-input:after {
    content: none !important;
  }

</style>

<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Reporte de Ventas por API</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li><a href="#">Reporte</a></li>
        <li class="active">Reporte Ventas por API</li>
      </ol>
    </div>
  </div>
</div>
<div class="container report-retention">
  <div class="row">
    <div class="col-12 pb-5">
      <h3>Configuración del reporte</h3>
      <form action="" id="report_sa_form" method="POST" name="report_sa_form">
        {{ csrf_field() }}
        <div class="row">
          <div class="col-md-3 col-sm-6">
            <div class="form-group">
              <label class="control-label">Organizaci&oacute;n</label>
              <select id="org_sale" name="org_sale" class="form-control">
                <option value="">Todas</option>
                @foreach($orgs as $org)
                <option value="{{$org->id}}">{{$org->business_name}}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="col-md-3 col-sm-6">
            <div class="form-group">
              <label class="control-label">Tipo linea</label>
              <select id="type_line" name="type_line" class="form-control">
                <option value="">Todos</option>
                <option value="H">Internet Hogar</option>
                <option value="T">Telefon&iacute;a</option>
                <option value="M">MIFI</option>
              </select>
            </div>
          </div>

          <div class="col-md-3 col-sm-6">
            <div class="form-group">
              <label class="control-label">Fecha desde</label>
              <div class="input-group">
                <input type="text" name="date_ini" id="date_ini" class="form-control" placeholder="dd-mm-yyyy" value="{{ date('Y-m-d', strtotime('- 90 days', time())) }}" readonly="true">
                <span class="input-group-addon"><i class="icon-calender"></i></span>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6">
            <div class="form-group">
              <label class="control-label">Fecha hasta</label>
              <div class="input-group">
                <input type="text" name="date_end" id="date_end" class="form-control" placeholder="dd-mm-yyyy" value="{{ date('Y-m-d') }}" readonly="true">
                <span class="input-group-addon"><i class="icon-calender"></i></span>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3 col-sm-6">
          <div class="form-group">
            <label class="control-label">Estatus de la venta</label>
            <select id="status_sale" name="status_sale" class="form-control">
              <option value="">Todos</option>
              <option value="G">Generada</option>
              <option value="E">Entregada</option>
              <option value="F">Finalizada</option>
            </select>
          </div>
        </div>

        <div class="col-md-12 text-center">
          <button class="btn btn-success" id="search" name="search" type="button">
            <i class="fa fa-check"></i>Generar reporte
          </button>
        </div>
    </div>
    </form>
  </div>

  <div class="col-12" hidden="" id="rep-sa">
    <div class="row white-box">
      <div class="col-md-12">
        <h3 class="text-center">Reporte de Ventas por API</h3>
      </div>
      <div class="col-md-12">
        <button class="btn btn-success m-b-20" id="download" type="button">Exportar Excel</button>
      </div>
      <div class="col-md-12">
        <div class="table-responsive">
          <table class="table table-striped" id="list-sal">
            <thead>
              <tr>
                <th>Transacción</th>
                <th>Organización</th>
                <th>Vendedor</th>
                <th>Producto</th>
                <th>Tipo de linea</th>
                <th>Plan</th>
                <th>Servicio</th>
                <th>MSISDN</th>
                <th>Cliente</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Monto producto</th>
                <th>Proveedor</th>
                <th>Último estatus de la entrega</th>
                <th>Fecha de último estatus de la entrega</th>
                <th>Monto delivery</th>
                <th>Orden delivery</th>
                <th>Código postal</th>
                <th>Estado</th>
                <th>Ciudad</th>
                <th>Cupón</th>
                <th>Descuento</th>
                <th>Fecha de venta</th>
                <th>Fecha de entrega</th>
                <th>Días en activar</th>
                <th>Estatus de la compra</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
<script src="js/reports/rangePicker.js">
</script>
<script type="text/javascript">
  $(document).ready(function() {
    $('#org_sale').selectize();
    $('#type_line').selectize();
    $('#status_sale').selectize();

    let format = {
      autoclose: true
      , format: 'yyyy-mm-dd'
    };

    $('#date_ini').datepicker(format)
      .on('changeDate', function(selected) {
        var dt = $('#date_end').val();
        if (dt == '') {
          $('#date_end').datepicker('setDate', sumDays($('#date_ini').datepicker('getDate'), 90));
        } else {
          var diff = getDateDiff($('#date_ini').datepicker('getDate'), $('#date_end').datepicker('getDate'));
          if (diff > 90)
            $('#date_end').datepicker('setDate', sumDays($('#date_ini').datepicker('getDate'), 90));
        }
      });

    $('#date_end').datepicker(format)
      .on('changeDate', function(selected) {
        var dt = $('#date_ini').val();
        if (dt == '') {
          $('#date_ini').datepicker('update', sumDays($('#date_end').datepicker('getDate'), -90));
        } else {
          var diff = getDateDiff($('#date_ini').datepicker('getDate'), selected.date);
          if (diff > 90)
            $('#date_ini').datepicker('update', sumDays($('#date_end').datepicker('getDate'), -90));
        }
      });

    $('#search').on('click', function(e) {
      $('.preloader').show();

      if ($.fn.DataTable.isDataTable('#list-sal')) {
        $('#list-sal').DataTable().destroy();
      }

      $('#list-sal').DataTable({
        language: {
          sProcessing: "Procesando..."
          , sLengthMenu: "Mostrar _MENU_ registros"
          , sZeroRecords: "No se encontraron resultados"
          , sEmptyTable: "Ningún dato disponible en esta tabla"
          , sInfo: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros"
          , sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros"
          , sInfoFiltered: "(filtrado de un total de _MAX_ registros)"
          , sInfoPostFix: ""
          , sSearch: "Buscar:"
          , sUrl: ""
          , sInfoThousands: ","
          , sLoadingRecords: "Cargando..."
          , oPaginate: {
            sFirst: "Primero"
            , sLast: "Último"
            , sNext: "Siguiente"
            , sPrevious: "Anterior"
          }
          , oAria: {
            sSortAscending: ": Activar para ordenar la columna de manera ascendente"
            , sSortDescending: ": Activar para ordenar la columna de manera descendente"
          }
        }
        , processing: true
        , serverSide: true
        , deferRender: true
        , order: [
          [20, "desc"]
        ]
        , ajax: {
          url: 'report_os/get_sales_api'
          , data: function(d) {
            d._token = $('meta[name="csrf-token"]').attr('content');

            var filters = $("#report_sa_form").serializeArray();

            filters.forEach(function(e) {
              d[e.name] = e.value;
            });
          }
          , type: "POST"
        }
        , initComplete: function(settings, json) {
          $(".preloader").fadeOut();
          $('#rep-sa').attr('hidden', null);
        }
        , columns: [{
            data: 'transaction'
            , orderable: false
          }
          , {
            data: 'business_name'
            , searchable: false
          }
          , {
            data: 'seller'
            , searchable: false
            , orderable: false
          }
          , {
            data: 'product'
            , searchable: false
            , orderable: false
            , name: 'islim_inv_articles.title'
          }
          , {
            data: 'pack_type'
            , searchable: false
          }
          , {
            data: 'title'
            , searchable: false
          }
          , {
            data: 'service'
            , searchable: false
            , orderable: false
            , name: 'islim_services.title'
          }
          , {
            data: 'msisdn'
            , searchable: false
          }
          , {
            data: 'client'
            , searchable: false
            , orderable: false
          }
          , {
            data: 'phone_home'
            , searchable: false
            , orderable: false
          }
          , {
            data: 'email_client'
            , searchable: false
          }
          , {
            data: 'sub_monto'
            , searchable: false
          }
          , {
            data: 'logic'
            , searchable: false
            , orderable: false
          },
          {
            data: 'last_status', 
            searchable: false, 
            orderable: false
          },
          {
            data: 'date_status', 
            searchable: false, 
            orderable: false
          },
          {
            data: 'monto_envio', 
            searchable: false, 
            orderable: false
          , }, 
          {
            data: 'delivery_orden'
            , orderable: false
          },
          {
            data: 'postal_code'
            , orderable: false
          },
          {
            data: 'state'
            , orderable: false
          },
          {
            data: 'city'
            , orderable: false
          },
          {
            data: 'cod_prom'
            , orderable: false
          }
          , {
            data: 'discount'
            , searchable: false
            , orderable: false
          }
          , {
            data: 'sale_date'
            , searchable: false
            , orderable: false
          }
          , {
            data: 'del_date'
            , searchable: false
            , orderable: false
          }
          , {
            data: 'active_days'
            , searchable: false
            , orderable: false
          }
          , {
            data: 'status_sale'
            , searchable: false
            , orderable: false
          }
        ]
      });
    });

    $('#download').on('click', function(e){
      var data = $("#report_sa_form").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');

      $(".preloader").fadeIn();

      $.ajax({
          type: "POST",
          url: "report_os/download_sales_api",
          data: data,
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
  });

</script>
