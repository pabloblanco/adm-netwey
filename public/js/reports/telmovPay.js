$(document).ready(function() {
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
  ajaxCoordination = function(query, callback) {
    if (!query.length) return callback();
    $.ajax({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      url: 'view/reports/get_filter_users_sellers',
      type: 'POST',
      dataType: 'json',
      cache: false,
      data: {
        name: query,
        //org: '',
        type: 'coordinador'
      },
      error: function() {
        callback();
      },
      success: function(res) {
        if (res.success) callback(res.users);
        else callback();
      }
    });
  }
  ajaxVendedor = function(query, callback) {
    if (!query.length) return callback();
    $.ajax({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      url: 'view/reports/get_filter_users_sellers',
      type: 'POST',
      dataType: 'json',
      cache: false,
      data: {
        name: query,
        //org: '',
        coord: $('#coord').val(),
        type: 'vendor'
      },
      error: function() {
        callback();
      },
      success: function(res) {
        if (res.success) callback(res.users);
        else callback();
      }
    });
  }
  var configSelect = {
    valueField: 'email',
    labelField: 'username',
    searchField: 'username',
    options: [],
    create: false,
    persist: false,
    render: {
      option: function(item, escape) {
        return '<p>' + item.name + ' ' + item.last_name + '</p>';
      }
    }
  };
  configSelect.load = ajaxCoordination;
  $('#coord').selectize(configSelect);
  configSelect.load = ajaxVendedor;
  $('#seller').selectize(configSelect);
  //
  $('#__lpform_coord-selectized_icon').addClass('d-none');
  //console.log('DELETE');
  //
  $('#coord').on('change', function(e) {
    var sel = $('#seller')[0].selectize;
    if (sel) sel.clearOptions();
  });
  $('#status').selectize();
  //
  $('#search').on('click', function(e) {
    $('.preloader').show();
    if ($.fn.DataTable.isDataTable('#list-telmov-pay')) {
      $('#list-telmov-pay').DataTable().destroy();
    }
    $('#list-telmov-pay').DataTable({
      searching: true,
      processing: true,
      serverSide: true,
      ajax: {
        url: "reports/telmov-pay/get-telmov-pay-dt",
        data: function(d) {
          d._token = $('meta[name="csrf-token"]').attr('content');
          d.dateStar = $('#dateStar').val();
          d.dateEnd = $('#dateEnd').val();
          d.coord = $('#coord').val();
          d.seller = $('#seller').val();
          d.status = $('#status').val();
        },
        type: "POST"
      },
      initComplete: function(settings, json) {
        $(".preloader").fadeOut();
        $('#rep-si').attr('hidden', null);
      },
      deferRender: true,
      order: [
        [7, "desc"]
      ],
      columns: [{
        data: 'msisdn',
        orderable: false
      }, {
        data: 'nameCoordFull',
        searchable: false,
        orderable: false
      }, {
        data: 'nameSellerFull',
        searchable: false,
        orderable: false
      }, {
        data: 'nameClientFull',
        searchable: false,
        orderable: false
      }, {
        data: 'init_amount',
        searchable: false,
        orderable: false
      }, {
        data: 'initial_amount',
        searchable: false,
        orderable: false
      }, {
        data: 'total_amount',
        orderable: false
      }, {
        data: 'date_reg',
        orderable: false
      }, {
        data: 'date_process',
        searchable: false,
        orderable: false
      }, {
        data: 'status',
        searchable: false,
        orderable: false
      }]
    });
  });
  //
  $('#download').on('click', function(e) {
    var data = $("#filterConc").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');
    $(".preloader").fadeIn();
    $.ajax({
      type: "POST",
      url: "reports/telmov-pay/download-telmov-pay-report",
      data: data,
      dataType: "text",
      success: function(response) {
        $(".preloader").fadeOut();
        swal('Generando reporte', 'El reporte estara disponible en unos minutos.', 'success');
      },
      error: function(err) {
        $(".preloader").fadeOut();
        swal('Error', 'No se pudo generar el reporte.', 'error');
      }
    });
  });
});