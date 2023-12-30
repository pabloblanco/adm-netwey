/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Marzo 2022,
 */
function search_listLow() {
  $('.preloader').show();
  if ($.fn.DataTable.isDataTable('#list-com')) {
    $('#list-com').DataTable().destroy();
  }
  $('#list-com').DataTable({
    searching: true,
    processing: true,
    serverSide: true,
    ajax: {
      url: 'view/low/viewListLowReport',
      data: function(d) {
        d._token = $('meta[name="csrf-token"]').attr('content');
        d.dateStar = $('#dateStar').val();
        d.dateEnd = $('#dateEnd').val();
        d.statusCash = $('#statusCash').val();
        d.statusLow = $('#statusLow').val();
        d.user_dismissal = $('#user_dismissal').val();
      },
      type: "POST"
    },
    initComplete: function(settings, json) {
      $(".preloader").fadeOut();
      $('#rep-sc').attr('hidden', null);
    },
    deferRender: true,
    "order": [
      [2, "desc"]
    ],
    ordering: true,
    columns: [{
      data: 'user_dismissal',
      searchable: true,
      orderable: true
    }, {
      data: 'userDetail_low',
      searchable: true,
      orderable: false
    }, {
      data: 'distributor',
      searchable: true,
      orderable: false,
      render: function(data, type, row, meta) {
        if(row.distributor == null){
          return 'N/A';
        }else{
          return row.distributor;
        }
      }
    }, {
      data: 'date_reg',
      searchable: false,
      orderable: true
    }, {
      data: 'article_request',
      searchable: false,
      orderable: true
    }, {
      data: 'cash_request',
      searchable: false,
      orderable: true
    },  {
      data: 'cash_abonos',
      searchable: false,
      orderable: true
    }, {
      data: 'cash_total',
      searchable: false,
      orderable: true
    }, {
      data: 'residue_amount',
      searchable: false,
      orderable: true
    },  {
      data: 'cash_discount_total',
      searchable: false,
      orderable: true
    }, {
      data: 'date_step1',
      searchable: false,
      orderable: true
    }, {
      data: 'date_step2',
      searchable: false,
      orderable: true
    }, {
      data: 'status',
      searchable: false,
      orderable: true
    }, {
      data: 'reason_deny',
      searchable: false,
      orderable: true
    }, {
      data: 'discounted_amount',
      searchable: false,
      orderable: true
    }, {
      data: 'mount_liquidacion',
      searchable: false,
      orderable: true
    },{
      data: 'date_liquidacion',
      searchable: false,
      orderable: true
    }],
    "language": {
      "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
    }
  });
}

function DownloadReportLow() {
  $(".preloader").fadeIn();
  var data = $("#report_tb_form").serialize();
  $.ajax({
    type: "POST",
    url: 'view/low/getReportLowDownload',
    data: {
      data,
      _token: $('meta[name="csrf-token"]').attr('content'),
      dateStar: $('#dateStar').val(),
      dateEnd: $('#dateEnd').val(),
      statusCash: $('#statusCash').val(),
      statusLow: $('#statusLow').val()
    },
    dataType: "json",
    success: function(response) {
      $(".preloader").fadeOut();
      swal('Generando reporte de bajas', 'El reporte estara disponible en unos minutos.', 'success');
      // var a = document.createElement("a");
      // a.target = "_blank";
      // a.href = "{route('downloadFile',['delete' => 1])}}?p=" + response.url;
      // a.click();
    },
    error: function(err) {
      console.log("error al crear el reporte de bajas: ", err);
      $(".preloader").fadeOut();
    }
  });
}
$(document).ready(function() {
  $('#search').on('click', function(e) {
    search_listLow();
  });
  $('#download').on('click', function(e) {
    DownloadReportLow();
  });

  $('#user_dismissal').selectize({
    valueField: 'email',
    labelField: 'username',
    searchField: ["username", "email"],

    options: [],
    create: false,
    persist: false,
    render: {
      option: function(item, escape) {
        let platf = 'Gerente';
        if (item.platform == 'coordinador') {
          platf = 'Coordinador';
        }
        if (item.platform == 'vendor') {
          platf = 'Vendedor';
        }

        opt = "<div>";
        opt += '<span>' + escape(item.username.toLocaleUpperCase()) + "</span>";
        opt += '<span class="aai_description mb-0" style="color:#666; opacity:0.75; font-weight:400;">' + escape(item.email) +"</span>";
        opt += '<ul class="aai_meta my-0">';
        opt += '<li style="opacity:0.5"><strong>' + escape(platf) + "</strong></li>";
        opt += "</ul>";

        return opt;
      }
    },
    load: function(query, callback) {
      if (!query.length) return callback();
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'api/seller_inventories/get_users',
        type: 'POST',
        dataType: 'json',
        cache: false,
        data: {
          name: query
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
  });
});