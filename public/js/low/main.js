/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Febrero 2022,
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
      url: 'view/low/viewListRequest',
      data: function(d) {
        d._token = $('meta[name="csrf-token"]').attr('content');
        d.dateStar = $('#dateStar').val();
        d.dateEnd = $('#dateEnd').val();
        d.emailLow = $('#seller').val();
      },
      type: "POST"
    },
    initComplete: function(settings, json) {
      $(".preloader").fadeOut();
      $('#rep-sc').attr('hidden', null);
    },
    deferRender: true,
    "order": [
      [6, "desc"]
    ],
    ordering: true,
    columns: [{
      data: 'id',
      searchable: false,
      orderable: false,
      render: function(data, type, row, meta) {
        var html = '';
        //Boton de acciones
        html += '<button title="Aceptar solicitud" name="btn-ok-' + data + '\"  type="button" class="btn btn-primary btn-md button d-block" onclick="ProcessItem(\'' + row.id + '\',\'' + row.userDetail_req + '\',\'' + row.userDetail_low + '\',\'' + row.date_reg + '\',\'' + row.cash_request + '\',\'' + row.days_cash_request + '\',\'' + row.article_request + '\',\'' + row.reason + '\')" > Procesar </button>';
        html += '<button title="Rechazar solicitud" name="btn-rechaz-' + data + '\"  type="button" class="btn btn-danger btn-md button d-block" onclick="Rechazar(\'' + row.id + '\')"> Rechazar </button>';
        return html;
      }
    }, {
      data: 'user_req',
      searchable: true,
      orderable: true
    }, {
      data: 'userDetail_req',
      searchable: true,
      orderable: false
    }, {
      data: 'user_dismissal',
      searchable: true,
      orderable: true
    }, {
      data: 'userDetail_low',
      searchable: true,
      orderable: false
    }, {
      data: 'reason',
      searchable: false,
      orderable: true
    }, {
      data: 'date_reg',
      searchable: false,
      orderable: true
    }, {
      data: 'cash_request',
      searchable: false,
      orderable: true
    }, {
      data: 'days_cash_request',
      searchable: false,
      orderable: true
    }, {
      data: 'article_request',
      searchable: false,
      orderable: true
    }, {
      data: 'cash_abonos',
      searchable: false,
      orderable: true
    }, {
      data: 'cant_abonos',
      searchable: false,
      orderable: true
    }, {
      data: 'cash_total',
      searchable: false,
      orderable: true
    }, {
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

function DownloadEvidencia($urlFile, $nameFile) {
  axios({
    url: $urlFile,
    method: 'GET',
    responseType: 'blob'
  }).then((response) => {
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', $nameFile);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  })
}

function NewelementTextarea($name, $row, $col, $value, $placeholder) {
  var temp = document.createElement("textarea");
  temp.placeholder = $placeholder;
  temp.name = $name;
  temp.rows = $row;
  temp.cols = $col;
  temp.value = $value;
  temp.style.width = '95%';
  return temp;
}

function Rechazar($idLow) {
  areaText = NewelementTextarea('msj', '5', '25', '', "Escribe el motivo del rechazo");
  swal({
    title: "Motivo del rechazo de la baja",
    dangerMode: true,
    closeOnClickOutside: false,
    content: {
      element: areaText,
    },
    icon: "info",
    dangerMode: true,
    buttons: {
      cancel: {
        text: "Cancelar",
        value: 'cancel',
        visible: true,
        className: "",
        closeModal: true,
      },
      confirm: {
        text: "Guardar",
        value: 'save',
        visible: true,
        className: "",
        closeModal: true
      },
    },
  }).then((value) => {
    if (value == 'save') {
      if ($('.swal-content textarea').val() != null && $('.swal-content textarea').val() != '') {
        $.ajax({
          url: 'view/low/setRejectionLow',
          method: 'POST',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            id: $idLow,
            msj: $('.swal-content textarea').val(),
          },
          dataType: "json",
          success: function(data) {
            swal(data.msg, {
              icon: "success",
            });
            search_listLow();
          },
          error: function(data) {
            swal("Ocurrio un error al guardar el rechazo: " + data, {
              icon: "warning",
            });
          }
        });
      } else {
        swal("Debes escribir un motivo si deseas rechazar la solicitud de baja ", {
          icon: "warning",
        });
      }
    }
  });
}

function ProcessItem($idLow, $detail_Reg, $detail_low, $dateReg, $cash, $timeCash, $inventary, $motive) {
  /*var body = document.getElementsByTagName("body")[0];
  var tabla = document.createElement("table");
  var tblBody = document.createElement("tbody");
  //
  var hilera = document.createElement("tr");
  var celda = document.createElement("td");
  var textoCelda = document.createTextNode("Fecha de solicitud:");
  celda.appendChild(textoCelda);
  hilera.appendChild(celda);
  var celda = document.createElement("td");
  var textoCelda = document.createTextNode($dateReg);
  celda.appendChild(textoCelda);
  hilera.appendChild(celda);
  //
  tblBody.appendChild(hilera);
  //
  var hilera = document.createElement("tr");
  var celda = document.createElement("td");
  var textoCelda = document.createTextNode("Usuario solicitante:");
  celda.appendChild(textoCelda);
  hilera.appendChild(celda);
  var celda = document.createElement("td");
  var textoCelda = document.createTextNode($mailReg);
  celda.appendChild(textoCelda);
  hilera.appendChild(celda);
  //
  tblBody.appendChild(hilera);
  //
  var hilera = document.createElement("tr");
  var celda = document.createElement("td");
  var textoCelda = document.createTextNode("Usuario a dar de baja:");
  celda.appendChild(textoCelda);
  hilera.appendChild(celda);
  var celda = document.createElement("td");
  var textoCelda = document.createTextNode($mailLow);
  celda.appendChild(textoCelda);
  hilera.appendChild(celda);
  //
  tblBody.appendChild(hilera);
  //
  var hilera = document.createElement("tr");
  var celda = document.createElement("td");
  var textoCelda = document.createTextNode("Motivo a dar de baja:");
  celda.appendChild(textoCelda);
  hilera.appendChild(celda);
  var celda = document.createElement("td");
  var textoCelda = document.createTextNode($motive);
  celda.appendChild(textoCelda);
  hilera.appendChild(celda);
  //
  tblBody.appendChild(hilera);
  //
  var hilera = document.createElement("tr");
  var celda = document.createElement("td");
  var textoCelda = document.createTextNode("Dinero pendiente por entregar:");
  celda.appendChild(textoCelda);
  hilera.appendChild(celda);
  var celda = document.createElement("td");
  var textoCelda = document.createTextNode($cash);
  celda.appendChild(textoCelda);
  hilera.appendChild(celda);
  //
  tblBody.appendChild(hilera);
  //
  var hilera = document.createElement("tr");
  var celda = document.createElement("td");
  var textoCelda = document.createTextNode("Dias con la deuda:");
  celda.appendChild(textoCelda);
  hilera.appendChild(celda);
  var celda = document.createElement("td");
  var textoCelda = document.createTextNode($timeCash);
  celda.appendChild(textoCelda);
  hilera.appendChild(celda);
  //
  tblBody.appendChild(hilera);
  //
  var hilera = document.createElement("tr");
  var celda = document.createElement("td");
  var textoCelda = document.createTextNode("Cantidad de equipo aun asignados:");
  celda.appendChild(textoCelda);
  hilera.appendChild(celda);
  var celda = document.createElement("td");
  var textoCelda = document.createTextNode($inventary);
  celda.appendChild(textoCelda);
  hilera.appendChild(celda);
  //
  tblBody.appendChild(hilera);
  //
  tabla.appendChild(tblBody);
  body.appendChild(tabla);
  tabla.setAttribute("border", "1");
  */
  let wrapper = document.createElement('div');
  let tex = "<p class='text-left'>";
  tex += "<strong> > RESUMEN:</strong></br> ";
  tex += "<strong> Fecha de solicitud:: </strong>" + $dateReg + "</br>";
  tex += "<strong> Usuario solicitante: </strong>" + $detail_Reg + "</br>";
  tex += "<strong> Usuario a dar de baja: </strong>" + $detail_low + "</br>";
  tex += "<strong> Motivo a dar de baja: </strong>" + $motive + "</br>";
  tex += "<strong> Dias con la deuda: </strong>" + $timeCash + "</br>";
  tex += "<strong> Efectivo por entregar:  </strong>" + $cash + "</br>";
  tex += "<strong> Deuda en equipos: </strong>" + $inventary + "</br>";
  tex += "</p>";
  wrapper.innerHTML = tex;
  let el = wrapper.firstChild;
  swal({
    title: "Desear aceptar la solicitud de baja?",
    content: el,
    dangerMode: true,
    closeOnClickOutside: false,
    icon: "info",
    buttons: {
      cancel: {
        text: "Cancelar",
        value: 'cancel',
        visible: true,
        className: "",
        closeModal: true,
      },
      confirm: {
        text: "Procesar",
        value: 'save',
        visible: true,
        className: "",
        closeModal: true
      },
    },
  }).then((value) => {
    if (value == 'save') {
      $.ajax({
        url: 'view/low/setAceptLow',
        method: 'POST',
        data: {
          _token: $('meta[name="csrf-token"]').attr('content'),
          id: $idLow,
        },
        dataType: "json",
        success: function(data) {
          if (data.success) {
            swal(data.msg, {
              icon: "success",
            });
          } else {
            swal(data.msg, {
              icon: "warning",
            });
          }
          search_listLow();
        },
        error: function(data) {
          swal("Ocurrio un error al guardar el procesado de la baja: " + data, {
            icon: "warning",
          });
        }
      });
    }
  });
}

function DownloadReport() {
  $(".preloader").fadeIn();
  var data = $("#report_tb_form").serialize();
  $.ajax({
    type: "POST",
    url: 'view/low/getRequestDownload',
    data: {
      data,
      _token: $('meta[name="csrf-token"]').attr('content'),
      dateStar: $('#dateStar').val(),
      dateEnd: $('#dateEnd').val(),
      emailLow: $('#seller').val()
    },
    dataType: "json",
    success: function(response) {
      $(".preloader").fadeOut();
      swal('Generando reporte de solicitud de bajas', 'El reporte estara disponible en unos minutos.', 'success');
      // var a = document.createElement("a");
      // a.target = "_blank";
      // a.href = "{route('downloadFile',['delete' => 1])}}?p=" + response.url;
      // a.click();
    },
    error: function(err) {
      console.log("error al crear el reporte de solicitud de bajas: ", err);
      $(".preloader").fadeOut();
    }
  });
}
$(document).ready(function() {
  userSearch = function(query, callback) {
    if (!query.length) return callback();
    $.ajax({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      url: 'view/low/get_filter_users_lows',
      type: 'POST',
      dataType: 'json',
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
  var configSelect = {
    valueField: 'user_dismissal',
    labelField: 'username',
    searchField: 'username',
    options: [],
    create: false,
    persist: false,
    render: {
      option: function(item, escape) {
        return '<p>' + escape(item.name.toLocaleUpperCase()) + ' ' + escape(item.last_name.toLocaleUpperCase()) + '</p>';
      }
    }
  };
  configSelect.load = userSearch;
  $('#seller').selectize(configSelect);
  $('#search').on('click', function(e) {
    search_listLow();
  });
  $('#download').on('click', function(e) {
    DownloadReport();
  });
});