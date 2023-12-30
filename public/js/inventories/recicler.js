/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Marzo 2022,
 */
function search_reportRecicler() {
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
      url: 'api/get_inv_recicler',
      data: function(d) {
        d._token = $('meta[name="csrf-token"]').attr('content');
        d.dateStar = $('#dateStar').val();
        d.dateEnd = $('#dateEnd').val();
        d.status = $('#status').val();
      },
      type: "POST"
    },
    initComplete: function(settings, json) {
      $(".preloader").fadeOut();
      $('#rep-sc').attr('hidden', null);
    },
    deferRender: true,
    "order": [
      [4, "desc"]
    ],
    ordering: true,
    columns: [{
      data: 'id',
      searchable: false,
      orderable: false,
      render: function(data, type, row, meta) {
        var html = '';
        if (row.checkOffert == 'Y' && row.status == "Solicicitado") {
          //Boton forzar el procesado del DN
          html += '<button title="Procesar reciclaje" name="btn-ok-' + data + '\"  type="button" class="btn btn-info btn-md button d-block" onclick="confirmProcessItem(\'' + row.msisdn + '\',\'' + row.id + '\',\'' + row.date_reg + '\',\'' + row.codeOffert + '\',\'' + row.detail_error + '\',\'' + row.obs + '\')" > Procesar </button>';
          html += '<button title="Rechazar reciclaje" name="btn-ok-' + data + '\"  type="button" class="btn btn-danger btn-md button d-block" onclick="confirmProcessItem(\'' + row.msisdn + '\',\'' + row.id + '\',\'' + row.date_reg + '\',\'' + row.codeOffert + '\',\'' + row.detail_error + '\',\'' + row.obs + '\',\'R\')" > Rechazar </button>';
        } else {
          if (row.status == "Procesado") {
            if (row.ReciclerType == 'C') {
              if (row.loadInventary == 'Y') {
                html += '<button title="DN reciclado" name="btn-ok-' + data + '\"  type="button" class="btn btn-link btn-md button"  > OK </button>';
              } else {
                html += '<button title="DN en espera" name="btn-ok-' + data + '\"  type="button" class="btn btn-light btn-md button"  > En espera cron inventario </button>';
              }
            } else {
              html += '<button title="DN reciclado" name="btn-ok-' + data + '\"  type="button" class="btn btn-link btn-md button"  > OK </button>';
            }
          } else {
            if (row.status == "Error") {
              //boton para ver el motivo de error
              html += '<button title="DN con problemas" name="btn-ok-' + data + '\"  type="button" class="btn btn-warning btn-md button" onclick="errorItem(\'' + row.msisdn + '\',\'' + row.detail_error + '\')" > Error </button>';
            } else {
              if (row.checkAltan == 'Y') {
                html += '<button title="Problemas con Altan" name="btn-ok-' + data + '\"  type="button" class="btn btn-danger btn-md button"  > Inconveniente con Altan </button>';
              } else {
                if (row.checkOffert == 'N' && row.status == "Solicicitado" && row.checkAltan == 'N') {
                  html += '<button title="En espera" name="btn-ok-' + data + '\"  type="button" class="btn btn-light btn-md button"  > En espera cron reciclaje </button>';
                } else {
                  if (row.status == "Rechazado") {
                    html += '<button title="Rechazado" name="btn-ok-' + data + '\"  type="button" class="btn btn-link btn-md button"  > Rechazado </button>';
                  } else {
                    html += '<button title="Desconocido" name="btn-ok-' + data + '\"  type="button" class="btn btn-light btn-md button"  > Desconocido </button>';
                  }
                }
              }
            }
          }
        }
        return html;
      }
    }, {
      data: 'msisdn',
      searchable: true,
      orderable: false
    }, {
      data: 'origin_netwey',
      searchable: false,
      orderable: true
    }, {
      data: 'user_netwey',
      searchable: true,
      orderable: true
    }, {
      data: 'date_reg',
      searchable: false,
      orderable: true
    }, {
      data: 'codeOffert',
      searchable: false,
      orderable: true
    }, {
      data: 'obs',
      searchable: false,
      orderable: false
    }, {
      data: 'statusClient',
      searchable: false,
      orderable: true
    }, {
      data: 'dias_recharge',
      searchable: false,
      orderable: true
    }],
    "language": {
      "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
    }
  });
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

function confirmProcessItem($msisdn, $id, $date_reg, $offert, $error, $obs, $type = 'C') {
  if ($type == 'C') {
    $title = "Procesar msisdn!";
    $botonLabel = "Procesar";
    let wrapper = document.createElement('div');
    let tex = "<p class='text-left'>";
    tex += "> Antes de proceder, verifica la informacion. </br> ";
    tex += "<strong> DN a reciclar: </strong>" + $msisdn + "</br>";
    tex += "<strong> Oferta de Altan: </strong>" + $offert + "</br>";
    tex += "<strong> Fecha de solicitud: </strong>" + $date_reg + "</br>";
    if ($obs != 'N/A') {
      tex += "<strong> Observaciones: </strong>" + $obs + "</br>";
    }
    if ($error != 'N/A') {
      tex += "<strong> Detalles del error: </strong>" + $error + "</br>";
    }
    tex += "</p>";
    wrapper.innerHTML = tex;
    let el = wrapper.firstChild;
    //let divIS = document.getElementsByClassName("swal-content");
    // divIS.innerHTML = "<div><button id='b1'>Nuevo Botón</button></div>";
    swal({
      title: $title,
      content: el,
      //  text: "Antes de proceder, verifica la informacion \n\n" + "DN a reciclar: .............. " + $msisdn + "\n Oferta de Altan: ......... " + $offert + "\n Fecha de solicitud: ... " + $date_reg + $error + $obs,
      icon: "warning",
      buttons: {
        cancel: {
          text: "Cancelar",
          value: 'cancel',
          visible: true,
          className: "",
          closeModal: true,
        },
        confirm: {
          text: $botonLabel,
          value: 'ok',
          visible: true,
          className: "",
          closeModal: true
        },
      },
      dangerMode: true,
    }).then((option) => {
      /*switch (option) {
          case "ok":*/
      if (option == 'ok') {
        requestProcessDN($msisdn, $id, $type);
      }
      /*     break;
          case "cancel":
              swal("Haz cancelado la verificacion");
              break;
          default:
              break;
      }*/
    });
  } else {
    if ($obs != 'N/A') {
      $obs = "\n Observaciones: " + $obs;
    } else {
      $obs = "";
    }
    if ($error != 'N/A') {
      $error = "\n Detalles del error: " + $error;
    } else {
      $error = "";
    }
    $title = "Rechazar procesado de reciclaje!";
    $botonLabel = "Rechazar";
    areaText = NewelementTextarea('msj', '4', '25', '', "Escribe el motivo del rechazo");
    swal({
      title: $title,
      text: "DN a reciclar: .............. " + $msisdn + "\n Oferta de Altan: ......... " + $offert + "\n Fecha de solicitud: ... " + $date_reg + $error + $obs,
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
          text: $botonLabel,
          value: 'save',
          visible: true,
          className: "",
          closeModal: true
        },
      },
    }).then((value) => {
      if (value == 'save') {
        if ($('.swal-content textarea').val() != null && $('.swal-content textarea').val() != '') {
          requestProcessDN($msisdn, $id, $type, $('.swal-content textarea').val());
        } else {
          swal("Debes registrar un motivo de rechazo ", {
            icon: "warning",
          });
        }
      }
    });
  }
}

function requestProcessDN($msisdn, $id, $status, $obs = false) {
  $(".preloader").fadeIn();
  if ($status == 'C') {
    $msg = 'El Msisdn ' + $msisdn + ' estara disponible en unas horas.';
  } else {
    $msg = 'El Msisdn ' + $msisdn + ' se ha rechazo para reciclarse.';
  }
  $.ajax({
    type: "POST",
    url: 'api/setReprocessRecicler',
    data: {
      _token: $('meta[name="csrf-token"]').attr('content'),
      msisdn: $msisdn,
      id: $id,
      status: $status,
      obs: $obs
    },
    dataType: "json",
    success: function(response) {
      $(".preloader").fadeOut();
      swal('MSISDN ha sido procesado', $msg, 'success');
      search_reportRecicler();
    },
    error: function(err) {
      console.log("error al solicitar procesar el DN para reciclaje: ", err);
      $(".preloader").fadeOut();
    }
  });
}

function errorItem($msisdn, $error) {
  swal('Informacion del error', 'MSISDN: ' + $msisdn + ' - ' + $error, 'warning');
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
  $('#search').on('click', function() {
    search_reportRecicler();
  });
  $('#download').on('click', function() {
    $(".preloader").fadeIn();
    var data = $("#report_tb_form").serialize();
    $.ajax({
      type: "POST",
      url: 'api/invReciclerDownload',
      data: {
        data,
        _token: $('meta[name="csrf-token"]').attr('content'),
        dateStar: $('#dateStar').val(),
        dateEnd: $('#dateEnd').val(),
        status: $('#status').val()
      },
      dataType: "json",
      success: function(response) {
        $(".preloader").fadeOut();
        swal('Generando reporte de reciclaje de DN', 'El reporte estara disponible en unos minutos.', 'success');
        // var a = document.createElement("a");
        // a.target = "_blank";
        // a.href = "{route('downloadFile',['delete' => 1])}}?p=" + response.url;
        // a.click();
      },
      error: function(err) {
        console.log("error al crear el reporte de reciclaje de DN: ", err);
        $(".preloader").fadeOut();
      }
    });
  });
});