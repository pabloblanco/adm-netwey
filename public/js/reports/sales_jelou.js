/*
Autor: Ing. LuisJ 
Contact: luis@gdalab.com
Septiembre 2021, Mejora voyweyExterno e Interno Marzo 2022
Octubre 2022, se agrega filtro para tipo de fechas y entregas que fallo en activar
 */
$(document).ready(function() {
  //cuando es voywey existe pago conciliado
  $('#operador').change(function(e) {
    e.preventDefault();
    var option = $(this).val();
    if (option == 'voy' || option == '') {
      $('#optionConciliado').removeClass('d-none');
      if (option == 'voy') {
        $('#blockCurrier').removeClass('d-none');
      } else {
        $('#blockCurrier').addClass('d-none');
        $('#optionPago').val('').attr('disabled', false);
        $('#optionPago').val('').attr('title', "");
      }
      $('#optionCurrier').val('');
    } else {
      $('#blockCurrier').addClass('d-none');
      $('#optionConciliado').addClass('d-none');
      $('#optionPago').val('').attr('disabled', false);
      $('#optionPago').val('').attr('title', "");
    }
  });
  $('#optionCurrier').change(function(e) {
    e.preventDefault();
    var option = $(this).val();
    if (option == 'EX') {
      //$('#optionConciliado').addClass('d-none');
      $('#optionPago').val('SI').attr('disabled', true);
      $('#optionPago').val('SI').attr('title', "Todos los envios de Voywey externos al crearse son conciliados");
    } else {
      //$('#optionConciliado').removeClass('d-none');
      $('#optionPago').val('').attr('disabled', false);
      $('#optionPago').val('').attr('title', "");
    }
  });
  $('#optionDeliveryComplete').change(function(e) {
    e.preventDefault();
    var option = $(this).val();
    if (option == 'SI') {
      $('#BlockViewFail').attr('hidden', false);
      $("#typeDate option[value='init']").prop("disabled", false);
      $("#typeDate option[value='send']").prop("disabled", false);
      $("#typeDate option[value='high']").prop("disabled", false);
    } else {
      $("#optionFailActive").prop("checked", false);
      $('#BlockViewFail').attr('hidden', true);
      //$('#BlockViewFail input[type=checkbox]').prop('checked', false);
      $("#typeDate option[value='send']").prop("disabled", true);
      $("#typeDate option[value='high']").prop("disabled", true);
      $("#typeDate option[value='init']").prop("disabled", false);
      $('#typeDate').val('init');
    }
  });
  $("#optionFailActive").on("click", function() {
    if ($(this).prop('checked')) {
      $("#typeDate option[value='high']").prop("disabled", true);
      $('#typeDate').val('init');
    } else {
      $("#typeDate option[value='high']").prop("disabled", false);
    }
  });
  var format = {
    autoclose: true,
    format: 'dd-mm-yyyy'
  };
  $('#dateStar').datepicker(format);
  $('#dateEnd').datepicker(format);
  var format = {
    autoclose: true,
    format: 'yyyy-mm-dd'
  };
  var rangoTime = 90; //3meses
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
      $('#dateStar').datepicker('update', sumDays($('#dateEnd').datepicker('getDate'), -rangoTime));
    } else {
      var diff = getDateDiff($('#dateStar').datepicker('getDate'), selected.date);
      if (diff > rangoTime) $('#dateStar').datepicker('update', sumDays($('#dateEnd').datepicker('getDate'), -rangoTime));
    }
  });
  /**
   * crear reporte
   */
  $('#search').on('click', function(e) {
    $('.preloader').show();
    if ($.fn.DataTable.isDataTable('#list-com')) {
      $('#list-com').DataTable().destroy();
    }
    $('#list-com').DataTable({
      searching: true,
      processing: true,
      serverSide: true,
      ajax: {
        url: "reports/get_dt_sales_jelou",
        data: function(d) {
          d._token = $('meta[name="csrf-token"]').attr('content');
          d.typeDate = $('#typeDate').val();
          d.dateStar = $('#dateStar').val();
          d.dateEnd = $('#dateEnd').val();
          d.operador = $('#operador').val();
          d.conciliado = $('#optionPago').val();
          d.deliveryFull = $('#optionDeliveryComplete').val();
          d.currier = $('#optionCurrier').val();
          d.listFail = $('#optionFailActive').prop('checked');
        },
        type: "POST"
      },
      initComplete: function(settings, json) {
        $(".preloader").fadeOut();
        $('#rep-sc').attr('hidden', null);
      },
      "order": [
        [6, "desc"] /*Fecha de sales*/
      ],
      deferRender: true,
      ordering: true,
      columns: [{
        data: 'folio',
        searchable: true,
        orderable: true
      }, {
        data: 'courier',
        searchable: false,
        orderable: true
      }, {
        data: 'nameClient',
        searchable: true,
        orderable: false
      }, {
        data: 'telfClient',
        searchable: true,
        orderable: false
      }, {
        data: 'dniClient',
        searchable: false,
        orderable: false
      }, {
        data: 'status_ord',
        searchable: false,
        orderable: false
      }, {
        data: 'days_Lastsales',
        render: function(data, type, row, meta) {
          //console.log(row);
          var html = "";
          if (row.days_Lastsales >= 6) {
            html = "<p style='color:red; font-weight:700;' >" + row.days_Lastsales + " días</p>";
          } else {
            html = "<p>" + row.days_Lastsales + " días</p>";
          }
          return html;
        },
        searchable: false,
        orderable: true
      }, {
        data: 'msisdn',
        searchable: true,
        orderable: true
      }, {
        data: 'statusDN',
        searchable: false,
        orderable: false
      }, {
        data: 'typeDN',
        searchable: false,
        orderable: false
      }, {
        data: 'SKU',
        searchable: false,
        orderable: false
      }, {
        data: 'operadorLogistico',
        searchable: false,
        orderable: false
      }, {
        data: 'date_sales',
        searchable: false,
        orderable: true
      }, {
        data: 'state_delivery',
        searchable: false,
        orderable: true
      }, {
        data: 'address_delivery',
        searchable: false,
        orderable: false
      }, {
        data: 'date_delivery',
        searchable: false,
        orderable: true
      }, {
        data: 'mount',
        searchable: false,
        orderable: true
      }, {
        data: 'type_payment',
        searchable: false,
        orderable: true
      }, {
        data: 'conciliado',
        searchable: false,
        orderable: true
      }, {
        data: 'date_conciliado',
        searchable: false,
        orderable: true
      }, {
        data: 'release_date',
        searchable: false,
        orderable: true
      }],
      "language": {
        "url": window.location.href + "js/datatable_spanish.json"
        // "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
      }
    });
  });
  $('#download').on('click', function() {
    $(".preloader").fadeIn();
    var data = $("#report_tb_form").serialize();
    $.ajax({
      type: "POST",
      url: 'reports/download_dt_sales_jelou',
      data: {
        data,
        _token: $('meta[name="csrf-token"]').attr('content'),
        typeDate: $('#typeDate').val(),
        dateStar: $('#dateStar').val(),
        dateEnd: $('#dateEnd').val(),
        operador: $('#operador').val(),
        conciliado: $('#optionPago').val(),
        deliveryFull: $('#optionDeliveryComplete').val(),
        currier: $('#optionCurrier').val(),
        listFail: $('#optionFailActive').prop('checked'),
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