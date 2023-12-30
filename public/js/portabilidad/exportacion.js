/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Febrero 2022,
 */
function search_exportacionPortability() {
  $('.preloader').show();
  // $.fn.dataTable.ext.errMode = 'throw';
  if ($.fn.DataTable.isDataTable('#list-com')) {
    $('#list-com').DataTable().destroy();
  }
  $('#list-com').DataTable({
    searching: true,
    processing: true,
    serverSide: true,
    ajax: {
      url: 'view/port/PortExportPeriod',
      data: function(d) {
        d._token = $('meta[name="csrf-token"]').attr('content');
        d.dateStar = $('#dateStar').val();
        d.dateEnd = $('#dateEnd').val();
        d.typePort = $('#typePort').val();
      },
      type: "POST"
    },
    initComplete: function(settings, json) {
      $(".preloader").fadeOut();
      $('#rep-sc').attr('hidden', null);
    },
    deferRender: true,
    "order": [
      [3, "desc"]
    ],
    ordering: true,
    columns: [
      /*{
            data: 'id',
            searchable: false,
            orderable: false,
            render: function(data, type, row, meta) {
              var html = '';
              if (!row.id) {
                //Boton con portacion de mas de 15 dias y que no se puede reversar
                html += '<button title="Exportacion realizada" name="btn-ok-' + data + '\"  type="button" class="btn btn-info btn-md button" onclick="viewItem(\'' + row.msisdn + '\',\'' + row.sales_id + '\',\'' + row.port_date + '\')" > OK </button>';
              } else {
                //Boton de reversas
                html += '<button title="Exportacion realizada" name="btn-ok-' + data + '\"  type="button" class="btn btn-info btn-md button" onclick="viewItem(\'' + row.msisdn + '\',\'' + row.sales_id + '\',\'' + row.port_date + '\')" > OK </button>';
                //html += '<button title="Solicitar reverso ante ADB de esta exportacion" name="btn-reverse-' + data + '\"  type="button" class="btn btn-success btn-md button" onclick="reversar(\'' + row.portID + '\',\'' + row.msisdn + '\')">Reversar</button>';
              }
              return html;
            }
          },*/
      {
        data: 'msisdn',
        searchable: true,
        orderable: false
      }, {
        data: 'sales_id',
        searchable: true,
        orderable: false
      }, {
        data: 'sales_date',
        searchable: false,
        orderable: true
      }, {
        data: 'port_date',
        searchable: false,
        orderable: true
      }, {
        data: 'portID',
        searchable: true,
        orderable: false
      }, {
        data: 'dni_client',
        searchable: true,
        orderable: false
      }, {
        data: 'NameClient',
        searchable: true,
        orderable: false
      }, {
        data: 'status',
        searchable: false,
        orderable: true
      }, {
        data: 'result',
        searchable: false,
        orderable: false
      }
    ],
    "language": {
      "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
    }
  });
}
$(document).ready(function() {
  var format = {
    autoclose: true,
    format: 'yyyy-mm-dd'
  };
  var rangoTime = 180; //6meses
  $('#dateStar').datepicker(format).on('changeDate', function(selected) {
    var dt = $('#dateEnd').val();
    if (dt == '') {
      $('#dateEnd').datepicker('setDate', sumDays($('#dateStar').datepicker('getDate'), rangoTime));
    } else {
      var diff = getDateDiff($('#dateStar').datepicker('getDate'), $('#dateEnd').datepicker('getDate'));
      if (diff > rangoTime) $('#dateEnd').datepicker('setDate', sumDays($('#dateStar').datepicker('getDate'), rangoTime));
    }
  });
  $('#dateEnd').datepicker(format).on('changeDate', function(selected) {
    var dt = $('#dateStar').val();
    if (dt == '') {
      $('#dateStar').datepicker('setDate', sumDays($('#dateEnd').datepicker('getDate'), -rangoTime));
    } else {
      var diff = getDateDiff($('#dateStar').datepicker('getDate'), selected.date);
      if (diff > rangoTime) $('#dateStar').datepicker('setDate', sumDays($('#dateEnd').datepicker('getDate'), -rangoTime));
    }
  });
  /**
   * filtrar reporte
   */
  $('#search').on('click', function(e) {
    search_exportacionPortability();
  });
  $('#download').on('click', function() {
    $(".preloader").fadeIn();
    var data = $("#report_tb_form").serialize();
    $.ajax({
      type: "POST",
      url: 'view/port/PortExportPeriodFile',
      data: {
        data,
        _token: $('meta[name="csrf-token"]').attr('content'),
        dateStar: $('#dateStar').val(),
        dateEnd: $('#dateEnd').val(),
        typePort: $('#typePort').val()
      },
      dataType: "json",
      success: function(response) {
        $(".preloader").fadeOut();
        swal('Generando reporte de Exportación', 'El reporte estara disponible en unos minutos.', 'success');
        // var a = document.createElement("a");
        // a.target = "_blank";
        // a.href = "{route('downloadFile',['delete' => 1])}}?p=" + response.url;
        // a.click();
      },
      error: function(err) {
        console.log("error al crear el reporte de exportacion: ", err);
        $(".preloader").fadeOut();
      }
    });
  });
});