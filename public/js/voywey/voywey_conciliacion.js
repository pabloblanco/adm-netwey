/*
Autor: Ing. LuisJ 
Marzo 2021
 */
function searchConciliacionVoywey() {
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
      url: 'voywey/dt_voywey_conciliacion',
      data: function(d) {
        d._token = $('meta[name="csrf-token"]').attr('content');
        d.dateStar = $('#dateStar').val();
        d.dateEnd = $('#dateEnd').val();
      },
      type: "POST"
    },
    initComplete: function(settings, json) {
      $(".preloader").fadeOut();
      $('#rep-sc').attr('hidden', null);
    },
    deferRender: true,
    ordering: true,
    "order": [
      [12, "asc"],
      [10, "asc"]
    ],
    columns: [{
      data: 'folio',
      searchable: true,
      orderable: true
    }, {
      data: 'nameUser',
      searchable: true,
      orderable: false
    }, {
      data: 'lastNameUser',
      searchable: true,
      orderable: false
    }, {
      data: 'seller',
      searchable: true,
      orderable: false
    }, {
      data: 'name',
      searchable: true,
      orderable: false
    }, {
      data: 'last_name',
      searchable: true,
      orderable: false
    }, {
      data: 'email',
      searchable: true,
      orderable: false
    }, {
      data: 'phone',
      searchable: false,
      orderable: false
    }, {
      data: 'dni',
      searchable: true,
      orderable: false
    }, {
      data: 'address_dest',
      searchable: false,
      orderable: false
    }, {
      data: 'address_active',
      searchable: false,
      orderable: false
    }, {
      data: 'total',
      searchable: false,
      orderable: true
    }, {
      data: 'payment_method',
      searchable: false,
      orderable: true
    }, {
      data: 'hrs_desde_entrega',
      searchable: false,
      orderable: true
    }, {
      data: 'client_name',
      searchable: true,
      orderable: false
    }, {
      data: 'client_last_name',
      searchable: true,
      orderable: false
    }, {
      data: 'client_email',
      searchable: true,
      orderable: false
    }, {
      data: 'client_phone',
      searchable: false,
      orderable: false
    }, {
      data: 'date_reg',
      searchable: false,
      orderable: true
    }, {
      data: 'date_del',
      searchable: false,
      orderable: true
    }],
    "language": {
      "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
    }
  });
}
$(document).ready(function() {
  /**
   * filtrar reporte
   */
  $('#search').on('click', function(e) {
    searchConciliacionVoywey();
  });
  $('#download').on('click', function() {
    $(".preloader").fadeIn();
    var data = $("#report_tb_form").serialize();
    $.ajax({
      type: "POST",
      url: 'voywey/download_dt_voywey_conciliacion',
      data: {
        data,
        _token: $('meta[name="csrf-token"]').attr('content'),
        dateStar: $('#dateStar').val(),
        dateEnd: $('#dateEnd').val()
      },
      dataType: "json",
      success: function(response) {
        $(".preloader").fadeOut();
        swal('Generando reporte', 'El reporte estara disponible en unos minutos.', 'success');
      },
      error: function(err) {
        console.log("error al crear el reporte: ", err);
        $(".preloader").fadeOut();
      }
    });
  });
});