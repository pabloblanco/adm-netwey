/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Marzo 2022,
 */
function search_listLowProcess() {
  $('.preloader').show();
  if ($.fn.DataTable.isDataTable('#list-com')) {
    $('#list-com').DataTable().destroy();
  }
  $('#list-com').DataTable({
    searching: true,
    processing: true,
    serverSide: true,
    ajax: {
      url: 'view/low/viewListLowFiniquite',
      data: function(d) {
        d._token = $('meta[name="csrf-token"]').attr('content');
        d.dateStar = $('#dateStar').val();
        d.dateEnd = $('#dateEnd').val();
        d.statusCash = $('#statusCash').val();
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
    },{
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
      data: 'cash_total',
      searchable: false,
      orderable: true
    }, {
      data: 'residue_amount',
      searchable: false,
      orderable: true
    }, {
      data: 'cash_discount_total',
      searchable: false,
      orderable: true
    }, {
      data: 'date_step1',
      searchable: false,
      orderable: true
    },{
      data: 'id',
      searchable: false,
      orderable: false,
      render: function(data, type, row, meta) {
        var html = '';
        //Boton de evidencia
        if (row.cant_evidenci > 0) {
          html += '<button title="Ver ' + row.cant_evidenci + ' evidencia" name="btn-rechaz-' + data + '\"  type="button" class="btn btn-danger btn-md button d-block" onclick="VerEvidencia(\'' + row.id + '\', \'' + row.user_req + '\', \'' + row.date_reg + '\', \'' + row.user_dismissal + '\', \'' + row.reason + '\')"> Ver evidencia </button>';
        } else {
          html += '<button title="No hay evidencia" name="btn-rechaz-' + data + '\"  type="button" class="btn btn-light btn-md button d-block" > Sin evidencia </button>';
        }
        return html;
      }
    }],
    "language": {
      "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
    }
  });
}

function DownloadReportProcess() {
  $(".preloader").fadeIn();
  var data = $("#report_tb_form").serialize();
  $.ajax({
    type: "POST",
    url: 'view/low/getLowFiniquiteDownload',
    data: {
      data,
      _token: $('meta[name="csrf-token"]').attr('content'),
      dateStar: $('#dateStar').val(),
      dateEnd: $('#dateEnd').val(),
      statusCash: $('#statusCash').val()
    },
    dataType: "json",
    success: function(response) {
      $(".preloader").fadeOut();
      swal('Generando reporte de bajas por finiquitar', 'El reporte estara disponible en unos minutos.', 'success');
      // var a = document.createElement("a");
      // a.target = "_blank";
      // a.href = "{route('downloadFile',['delete' => 1])}}?p=" + response.url;
      // a.click();
    },
    error: function(err) {
      console.log("error al crear el reporte de bajas por finiquitar: ", err);
      $(".preloader").fadeOut();
    }
  });
}
$(document).ready(function() {
  $('#search').on('click', function(e) {
    search_listLowProcess();
  });
  $('#download').on('click', function(e) {
    DownloadReportProcess();
  });
});

function VerEvidencia($idRequest, $userReg, $date_reg, $userLow, $motivo) {
  $('.preloader').show();
  if ($.fn.DataTable.isDataTable('#myTableDetailEvidence')) {
    $('#myTableDetailEvidence').DataTable().destroy();
  }
  $('#myTableDetailEvidence').DataTable({
    searching: true,
    processing: true,
    serverSide: true,
    ajax: {
      url: 'view/low/viewEvidenceRequest',
      data: function(d) {
        d._token = $('meta[name="csrf-token"]').attr('content');
        d.idLow = $idRequest;
      },
      type: "POST"
    },
    initComplete: function(settings, json) {
      $(".preloader").fadeOut();
      $('#rep-sc').attr('hidden', null);
    },
    "order": [
      [1, "desc"] //Fecha de registro
    ],
    deferRender: true,
    ordering: true,
    columns: [{
      data: 'url',
      searchable: false,
      orderable: false,
      render: function(data, type, row, meta) {
        var html = '';
        //Link del archivo
        namefile = row.url.split('evidence-photo/');
        html += '<a class="" href="' + row.url + '" rel="noreferrer" target="_blank">' + namefile[1] + '</a>';
        return html;
      }
    }, {
      data: 'date_reg',
      searchable: false,
      orderable: false
    }, {
      data: 'url',
      searchable: false,
      orderable: false,
      render: function(data, type, row, meta) {
        var html = '';
        //Boton de acciones
        namefile = row.url.split('evidence-photo/');
        // extension = extension[1].split('.');
        //namefile = "Evidencia_" + row.id + "_" + (Math.floor(Math.random() * (5 - 1)) + 1) + "." + extension[1];
        //
        html += '<a title="Ver archivo" class="btn btn-success btn-md" style="width: 40px; padding: 0.4rem 1rem;" href="' + row.url + '" rel="noreferrer" target="_blank"><i class="fa fa-eye"></i></a>';
        html += '<button title="Descargar" type="button" class="btn btn-danger btn-md" style="width: 40px; padding: 0.4rem 1rem;" href="' + row.url + '" onclick="DownloadEvidencia(\'' + row.url + '\',\'' + namefile[1] + '\')"><i class="fa fa-cloud-download"></i></button>';
        // html += '<a title="Descargar" class="btn btn-success btn-md" style="width: 40px; padding: 0.4rem 1rem;" href="' + row.url + '" rel="noreferrer" target="_blank" download="' + namefile[1] + '"><i class="fa fa-eye"></i></a>';
        return html;
      }
    }],
    "language": {
      "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
    }
  });
  $('#myModal').modal({
    backdrop: 'static',
    keyboard: false
  });
  $('#date_Low').html($date_reg);
  $('#email_req').html($userReg);
  $('#email_Low').html($userLow);
  $('#reason_low').html($motivo);
}

$(document).ready(function() {
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