/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Febrero 2021, actualizado en Agosto, Octubre 2021
 */
function searchportability() {
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
      url: 'view/port/PortImportPeriod',
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
      [7, "asc"],
      [5, "asc"]
    ],
    ordering: true,
    columns: [{
      data: 'id',
      searchable: false,
      orderable: false,
      className: 'd-block',
      render: function(data, type, row, meta) {
        var html = '';
        if (row.status != '') {
          switch (row.status) {
            case "Activo":
              html += '<button title="Peticion de nueva portabilidad" name="btn-newPort-' + data + '\"  type="button" class="btn btn-info btn-md button d-block" onclick="NewItem(\'' + row.id + '\',\'' + row.msisdn_user + '\',\'' + row.msisdn_netwey + '\',\'' + row.nip + '\',\'' + row.sale_id + '\',\'' + row.date_reg + '\')" > Ver Detalles </button>';
              // html += '<button title="Peticion de nueva portabilidad" name="btn-confirm-' + data + '\"  type="button" class="btn btn-info btn-md button" onclick="confirmItem(\'' + row.id + '\',\'' + row.msisdn_user + '\',\'' + row.msisdn_netwey + '\',\'' + row.nip + '\',\'' + row.sale_id + '\')" title="Procesar un portabilidad" >Confirmar</button>';
              html += '<button name="btn-cancel-' + data + '\"  type="button" class="btn btn-danger btn-md button d-block" onclick="cancel(\'' + row.id + '\')" title="Ingresa un motivo por el cual se rechaza la portabilidad" >Rechazar</button>';
              break;
            case "Cancelado":
              //console.log(row);
              html += '<button title="La portabilidad se cancelo por inconvenientes ante ADB o por peticion de personal de Netwey" name="btn-reprocess-' + data + '\"  type="button" class="btn btn-success btn-md button" onclick="reprocess(\'' + row.id + '\',\'' + row.msisdn_user + '\',\'' + row.msisdn_netwey + '\',\'' + row.nip + '\',\'' + row.sale_id + '\',\'' + row.Observation + '\',\'' + row.latest_soap + '\',\'' + row.portID + '\',\'' + row.date_reg + '\')">Re-procesar</button>';
              break;
            case "Procesado":
              html += '<button title="El proceso de portabilidad se completo satisfactoriamente" name="btn-ok-' + data + '\"  type="button" class="btn btn-light btn-md button" title="Portabilidad realizada" onclick="viewADB(\'' + row.id + '\',\'' + row.portID + '\',\'' + row.date_process + '\',\'' + row.status + '\',\'' + row.msisdn_user + '\' )"> ¡' + row.status + '! </button>';
              break;
            case "Solicitud Netwey":
              html += '<button title="La portabilidad cumplio la etapa de mensajeria ADB y notificacion Altan, ahora sera actualizado dentro de Netwey" name="btn-solictud-' + data + '\"  type="button" class="btn btn-danger btn-md button" title="Portabilidad en espera para ser procesada y actualizada en Netwey" onclick="viewADB(\'' + row.id + '\',\'' + row.portID + '\',\'' + row.date_process + '\',\'' + row.status + '\',\'' + row.msisdn_user + '\')"> ' + row.status + ' </button>';
              break;
            case "En proceso Netwey":
              html += '<button title="La portabilidad se esta actualizado dentro de Netwey" name="btn-inprocess-' + data + '\"  type="button" class="btn btn-warning btn-md button" title="Ve por un cafe y un pan, este proceso suele tardar un poco" onclick="viewADB(\'' + row.id + '\',\'' + row.portID + '\',\'' + row.date_process + '\',\'' + row.status + '\',\'' + row.msisdn_user + '\')" > ' + row.status + ' </button>';
              break;
            case "Error":
              html += '<button title="La portabilidad presento un problema, debes resolver el inconvenientes antes de continuar" name="btn-error-' + data + '\"  type="button" class="btn btn-secondary btn-md button" title="Ver detalles del error" onclick="viewerror(\'' + row.id + '\',\'' + row.details_error + '\')"> ¡' + row.status + '! </button>';
              break;
            case "En proceso ADB":
              if (row.boton_disable == 'Y') {
                html += '<button title="Haz solicitado cancelar la portabildad ante ADB, debes esperar que sea procesado" disabled="" name="btn-ADB-' + data + '\"  type="button" class="btn btn-warning btn-md button" title="Esta en proceso de cancelar portabilidad, espere por favor" onclick="viewADB(\'' + row.id + '\',\'' + row.portID + '\',\'' + row.date_process + '\',\'' + row.status + '\',\'' + row.msisdn_user + '\')"> ' + row.status + ' </button>';
              } else {
                html += '<button title="La portabilidad inicio proceso de intercambio de mensajes con ADB, este proceso suele tardar aproximadamente 2 dias" name="btn-ADB-' + data + '\"  type="button" class="btn btn-warning btn-md button" title="Ver detalles de la portacion" onclick="viewADB(\'' + row.id + '\',\'' + row.portID + '\',\'' + row.date_process + '\',\'' + row.status + '\',\'' + row.msisdn_user + '\')"> ' + row.status + ' </button>';
              }
              break;
            case "Incidencia ADB":
              html += '<button title="La portabilidad tiene un problema ante ADB, debes verificar y corregir la incidencia" name="btn-ADB2-' + data + '\"  type="button" class="btn btn-dark btn-md button" title="Ver detalles de la incidencia" onclick="viewADB(\'' + row.id + '\',\'' + row.portID + '\',\'' + row.date_process + '\',\'' + row.status + '\',\'' + row.msisdn_user + '\',\'' + row.Observation + '\')"> ' + row.status + ' </button>';
              break;
            case "En proceso Altan":
              html += '<button title="La portabilidad fue procesa correctamente por ADB, esta en espera de ser notificado y procesado por Altan" name="btn-altan-' + data + '\"  type="button" class="btn btn-warning btn-md button" title="Ver detalles de la portacion" onclick="viewADB(\'' + row.id + '\',\'' + row.portID + '\',\'' + row.date_process + '\',\'' + row.status + '\',\'' + row.msisdn_user + '\')"> ' + row.status + ' </button>';
              break;
            default:
              break;
          }
        }
        return html;
      }
    }, {
      data: 'sale_id',
      searchable: true,
      orderable: false
    }, {
      data: 'msisdn_user',
      searchable: true,
      orderable: false
    }, {
      data: 'msisdn_netwey',
      searchable: true,
      orderable: false
    }, {
      data: 'nip',
      searchable: true,
      orderable: false
    }, {
      data: 'date_reg',
      searchable: false,
      orderable: true
    }, {
      data: 'date_process',
      searchable: false,
      orderable: true
    }, {
      data: 'status',
      searchable: false,
      orderable: true
    }],
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
    searchportability();
  });
  $('#download').on('click', function() {
    $(".preloader").fadeIn();
    var data = $("#report_tb_form").serialize();
    $.ajax({
      type: "POST",
      url: 'view/port/PortImportPeriodFile',
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
        swal('Generando reporte de importación', 'El reporte estara disponible en unos minutos.', 'success');
        // var a = document.createElement("a");
        // a.target = "_blank";
        // a.href = "{route('downloadFile',['delete' => 1])}}?p=" + response.url;
        // a.click();
      },
      error: function(err) {
        console.log("error al crear el listado: ", err);
        $(".preloader").fadeOut();
      }
    });
  });
});

function processPortability($id, $user, $netwey, $nip, $obser = false, $sale_id) {
  $('.preloader').show();
  $.ajax({
    url: 'view/port/PortImportItem',
    method: 'POST',
    data: {
      _token: $('meta[name="csrf-token"]').attr('content'),
      id: $id,
      obser: $obser,
      dnTransitorio: $netwey,
      dnaPortar: $user,
      sale_id: $sale_id,
    },
    dataType: "json",
    success: function(data) {
      if (data.success) {
        swal({
          title: " Haz realizado la peticion de portabilidad en la plataforma Netwey satisfactoriamente.",
          text: "RESUMEN:\n\n" + "DN transitorio............... " + $netwey + "\n" + "DN a portar................... " + $user + "\n" + "NIP................................ " + $nip + "\n\n Espere un momento para completar la portabilidad, este proceso suele tardar un poco.",
          icon: "success",
        });
        $(".preloader").fadeOut();
        $('#rep-sc').attr('hidden', null);
        searchportability();
      } else {
        swal({
          title: " Atencion!",
          text: "Ocurrio un error al procesar la portabilidad. " + data.error,
          icon: "warning",
        });
        $(".preloader").fadeOut();
        $('#rep-sc').attr('hidden', null);
        searchportability();
      }
    },
    error: function(data) {
      swal({
        title: " Atencion!",
        text: "Ocurrio un error al actualizar la portabilidad: " + data.error,
        icon: "warning",
      });
      $(".preloader").fadeOut();
      $('#rep-sc').attr('hidden', null);
      searchportability();
    }
  });
}
//Muestra el detalle de la nueva portabilidad
function NewItem($id, $user, $netwey, $nip, $sale_id, $date_reg) {
  swal({
    title: "Nueva portabilidad!",
    text: "Detalles de la nueva solicitud: \n\n" + "DN transitorio............... " + $netwey + "\n" + "DN a portar................... " + $user + "\n" + "NIP..................................... " + $nip + "\n" + "Id de Venta.................. " + $sale_id + "\n" + "Creado:.............. " + $date_reg + "\n" + "Duracion estimada: 2 dias habiles.",
    icon: "warning",
    dangerMode: true,
  });
}

function confirmItem($id, $user, $netwey, $nip, $sale_id) {
  swal({
    title: "Procesar portabilidad!",
    text: "Antes de proceder, verifica la informacion \n\n" + "DN transitorio............... " + $netwey + "\n" + "DN a portar................... " + $user + "\n" + "NIP..................................... " + $nip,
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
        text: "Continuar",
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
      processPortability($id, $user, $netwey, $nip, false, $sale_id);
    }
    /*     break;
        case "cancel":
            swal("Haz cancelado la verificacion");
            break;
        default:
            break;
    }*/
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

function cancel($id) {
  areaText = NewelementTextarea('msj', '4', '25', '', "Escribe el motivo del rechazo");
  swal({
    title: "Motivo del rechazo de la portabilidad",
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
          url: 'view/port/PortImportObservation',
          method: 'POST',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            id: $id,
            msj: $('.swal-content textarea').val(),
          },
          dataType: "json",
          success: function(data) {
            swal("Haz guardado el motivo del rechazo de la portabilidad", {
              icon: "success",
            });
            searchportability();
          },
          error: function(data) {
            swal("Ocurrio un error al guardar el motivo: " + data, {
              icon: "warning",
            });
          }
        });
      } else {
        swal("Debes escribir un motivo si deseas rechazar la portabilidad ", {
          icon: "warning",
        });
      }
    }
  });
}
/**
 * [reprocess description]
 * @param  {[type]} $id          [id de la tabla portability]
 * @param  {[type]} $user        [campo msisdn_user o DN a portar]
 * @param  {[type]} $netwey      [campo msisdn_netwey o DN de netwey]
 * @param  {[type]} $nip         [nip de la solicitud de portabilidad]
 * @param  {[type]} $sale_id     [id de venta del DN]
 * @param  {[type]} $obser       [campo Observation de portability]
 * @param  {[type]} $latest_soap [ultimo status del soap]
 * @param  {[type]} $portID      [codigo de portacion]
 * @param  {[type]} $date_reg    [fecha de registro de solicitud de portacion]
 * @return {[type]}              [description]
 */
function reprocess($id, $user, $netwey, $nip, $sale_id, $obser, $latest_soap, $portID, $date_reg) {
  areaText = NewelementTextarea('msj', '4', '25', '', "Escribe el motivo si lo tienes para reprocesar la portabilidad");
  swal({
    title: "Re-procesar portabilidad!",
    text: "Antes de proceder, verifica la informacion \n\n" + "DN transitorio............... " + $netwey + "\n" + "DN a portar................... " + $user + "\n" + "NIP..................................... " + $nip + "\n\n" + "Detalles: " + $obser,
    icon: "warning",
    content: {
      element: areaText,
    },
    buttons: {
      nip: {
        text: "Actualizar NIP",
        value: 'nip',
        visible: true,
        className: "",
        closeModal: true,
      },
      cancel: {
        text: "Cancelar",
        value: 'cancel',
        visible: true,
        className: "",
        closeModal: true,
      },
      confirm: {
        text: "Reprocesar",
        value: 'confirm',
        visible: true,
        className: "",
        closeModal: true,
      },
    },
    dangerMode: true,
  }).then((value) => {
    // console.log(" value: " + value + " msj: " + $('.swal-content textarea').val());
    /*switch (value) {
        case "confirm":*/
    if (value == 'confirm' && !CadenaVacia($latest_soap) && $nip.length == 4 && $user.length == 10 && $netwey.length == 10) {
      var promesa = last_status($latest_soap, $portID);
      promesa.then(
        // Registrar el valor de la promesa cumplida
        function(last) {
          //console.log('LAST: ' + last);
          if (last === '1007') {
            if ($('.swal-content textarea').val() != null && $('.swal-content textarea').val() != '') {
              processPortability($id, $user, $netwey, $nip, $('.swal-content textarea').val(), $sale_id);
            } else {
              processPortability($id, $user, $netwey, $nip, false, $sale_id);
            }
          } else {
            if (last === '9999') {
              //Significa que hay un error 9999 o un 1092 donde la portabilidad termino
              if (Diff_today_dateReg($date_reg) > 5) {
                swal('El codigo NIP tiene mas de 5 dias, se recomienda que solicite un nuevo codigo', {
                  icon: "warning",
                });
              } else {
                ReprocessNewPortabilityADB($id, $user, $nip);
              }
            } else {
              var $index8 = $latest_soap.indexOf('3002');
              var text0 = "";
              if ($index8 >= 0) {
                text0 = "Debes volver a solicitar un codigo NIP para continuar";
              }
              swal(last + '. ' + text0, {
                icon: "warning",
              });
            }
          }
        }).catch(
        // Registrar la razón del rechazo
        function(reason) {
          console.log('Rechazada (' + reason + ').');
        });
    } else {
      if (value == 'nip' && $nip.length == 4 && $user.length == 10 && $netwey.length == 10) {
        //Verifico que el nip se vencio y si no tiene el tamano correcto
        var $index8 = $latest_soap.indexOf('3002');
        if (Diff_today_dateReg($date_reg) > 5 || $nip.length !== 4 || $index8 >= 0) {
          //se verifica que este en error 9999 por nip o 1092 que termino la portacion en slim_portability o esta vacio el campo de ultimo status del soap, o halla sido cancelada la portabilidad
          var $index3 = $latest_soap.indexOf('9999');
          var $index4 = $obser.indexOf('NIP');
          var $index5 = $latest_soap.indexOf('1092');
          if (CadenaVacia($latest_soap) || ($index3 >= 0 && $index4 >= 0) || $index5 >= 0 || $nip.length !== 4 || $index8 >= 0) {
            swal({
              title: "Actualizacion de codigo NIP",
              text: "Recuerda el codigo NIP tiene una validez de 5 dias, puede solicitarse desde el equipo del cliente via SMS con la palabra NIP al 051 o llamando al 051",
              icon: "info",
              content: {
                element: "input",
                attributes: {
                  name: "keyNip",
                  id: "keyNip",
                  placeholder: "Codigo NIP XXXX ",
                  autofocus: "true",
                  type: "number",
                },
              },
              buttons: {
                cancelNIP: {
                  text: "Cancelar",
                  value: 'cancelNIP',
                  visible: true,
                  className: "btn btn-primary",
                  closeModal: true,
                  botonesStyling: false
                },
                okNIP: {
                  text: "Aceptar",
                  value: 'okNIP',
                  visible: true,
                  className: "btn btn-danger",
                  closeModal: true,
                  botonesStyling: false
                },
              },
              dangerMode: true,
            }).then((result) => {
              //alert(result);
              if (result == 'okNIP') {
                if ($('.swal-content #keyNip').val().length == 4) {
                  ReprocessNewNIP($id, $('.swal-content #keyNip').val());
                  searchportability();
                } else {
                  swal("El codigo NIP no es valido para continuar", {
                    icon: "warning",
                  });
                }
              }
            });
          } else {
            var $index6 = $latest_soap.indexOf('1007');
            var text0 = '';
            if ($index6 >= 0) {
              text0 = "La portabilidad ya fue procesada por ADB";
            } else {
              if ($index4 < 0 && $index3 >= 0) {
                text0 = "El problema ante ADB no tiene que ver con codigo NIP. Por favor revisa el caso en mas detalle";
              }
            }
            swal("El cambio del codigo NIP no se puede realizar. No cumple condiciones. " + text0, {
              icon: "warning",
            });
          }
        } else {
          swal("El cambio del codigo NIP no se puede realizar. Aun es vigente y valido", {
            icon: "warning",
          });
        }
      } else {
        if (value == 'confirm') {
          $text0 = '';
          if ($nip.length !== 4) {
            $text0 = "NIP erroneo";
          } else {
            if ($user.length !== 10) {
              $text0 = "DN a portar tiene inconsistencia";
            } else {
              if ($netwey.length !== 10) {
                $text0 = "DN transitorio tiene inconsistencia";
              }
            }
          }
          swal("Información insuficiente o erronea para reprocesar la portabilidad. " + $text0, {
            icon: "warning",
          });
        }
      }
    }
  });
}

function Diff_today_dateReg($date_reg) {
  hoy = new Date();
  cadenaText = $date_reg.split(' ');
  P_year = cadenaText[0].split('-');
  var year = P_year[2];
  var mes = P_year[1];
  var dia = P_year[0];
  P_time = cadenaText[1].split(':');
  var hours = P_time[0];
  var minutos = P_time[1];
  var seconds = P_time[2];
  //  var today = new Date(year, mes, dia, hours, minutos, seconds); //).toLocaleFormat("%Y-%m-%d");
  var today = new Date();
  today.setFullYear(year, mes - 1, dia);
  // today.setMonth(mes);
  // today.setDate(dia);
  today.setHours(hours, minutos, seconds);
  // today.setHours();
  // today.setMinutes();
  // today.setSeconds();
  var diff = Math.abs(hoy - today);
  var day = 1000 * 60 * 60 * 24;
  var days = diff / day;
  //var months = days / 31;
  //var years = months / 12;
  // console.log('days ', days);
  return days;
}
//indica ultimo status 9999 o 1007
function last_status($latest_soap, $portID, $obser = false) {
  return new Promise((resolve, reject) => {
    var $index = -1;
    var $index2 = -1;
    var $port = -1;
    var $index7 = -1;
    $index = $latest_soap.indexOf('9999');
    $index7 = $latest_soap.indexOf('1092');
    //si es un error de mensaje o que se termino la portabilidad
    if ($index >= 0 || $index7 >= 0) {
      //reprocess en ADB
      return resolve('9999');
    } else {
      //alert($portID);
      // console.log($portID);
      $port = $portID.indexOf('N/A');
      if ($port >= 0) {
        return resolve("Por favor, verifica que el codigo de portabilidad este disponible");
      }
      $index2 = $latest_soap.indexOf('1007');
      if ($index2 >= 0) {
        //verificamos que ya se proceso por altan en portability_result
        $.ajax({
          url: 'view/port/getStatusResult',
          method: 'POST',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            portID: $portID,
          },
          dataType: "json",
          success: function(data) {
            if (data['success']) {
              return resolve('1007');
            } else {
              return resolve("No se puede reprocesar la portabilidad. +Detalles: " + data['error']);
            }
          },
          error: function(data) {
            return resolve("Ocurrio un error obtener el ultimo status del resultado de portabilidad: " + data['error']);
          }
        });
      } else {
        return resolve("No se puede reprocesar en el status: '" + $latest_soap + "'");
      }
    }
  });
}
//verifica si es nula o vacia el campo a consultar
function CadenaVacia($cadena) {
  //alert($cadena);
  if (!$cadena || 0 === $cadena.length || !$cadena.trim().length || $cadena == 'null') {
    return true;
  }
  return false;
}
//Solo permite introducir números.
function soloNumeros(e) {
  /* var key = window.event ? e.which : e.keyCode;
   if (key < 48 || key > 57) {
     //Usando la definición del DOM level 2, "return" NO funciona.
     e.preventDefault();
   }*/
  const regex = /^[0-9]*$/;
  return regex.test(e);
}

function ReprocessNewPortabilityADB($id, $user, $nip) {
  $.ajax({
    url: 'view/port/PortImportSetReprocessInADB',
    method: 'POST',
    data: {
      _token: $('meta[name="csrf-token"]').attr('content'),
      id: $id,
    },
    dataType: "json",
    success: function(data) {
      if (data['success']) {
        swal({
          title: "Haz reiniciado el proceso de portabilidad ante ADB satisfactoriamente",
          text: "Detalles: MSISDN " + $user + " NIP " + $nip,
          icon: "success"
        });
        searchportability();
      } else {
        swal("Hubo un problema al reprocesar la portabilidad ante ADB +Detalles: " + data['error'], {
          icon: "warning",
        });
      }
    },
    error: function(data) {
      swal("Ocurrio un error al actualizar el codigo NIP: " + data, {
        icon: "warning",
      });
    }
  });
}

function ReprocessNewNIP($id, $newNIP) {
  if (soloNumeros($newNIP)) {
    //Actualizamos el registro del NIP
    $.ajax({
      url: 'view/port/PortImportSetNewNIP',
      method: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        id: $id,
        newNIP: $newNIP,
      },
      dataType: "json",
      success: function(data) {
        if (data['success']) {
          swal("Haz actualizado el codigo NIP he iniciado de nuevo el proceso de solicitud de portabilidad ante ADB", {
            icon: "success",
          });
          searchportability();
        } else {
          swal("Hubo un problema al actualizar el NIP. +Detalles: " + data['error'], {
            icon: "warning",
          });
        }
      },
      error: function(data) {
        swal("Ocurrio un error al actualizar el codigo NIP: " + data, {
          icon: "warning",
        });
      }
    });
  } else {
    swal("Debes escribir un codigo NIP valido para continuar", {
      icon: "warning",
    });
  }
}

function viewerror($id, $details_error) {
  swal({
    title: "Detalles del error!",
    text: $details_error,
    icon: "warning",
    buttons: {
      confirm: {
        text: "Continuar",
        value: 'ok',
        visible: true,
        className: "",
        closeModal: true
      },
    },
    dangerMode: true,
  }).then((option) => {
    /*
    if (option == 'ok') {
        processPortability($id, $user, $netwey, $nip, false, $sale_id);
    }
    */
  });
}

function viewADB($id, $portID, $date_process, $status, $msisdn, $observacion = false) {
  if ($status == 'Procesado') {
    $infoText = 'El Dn ' + $msisdn + ' se actualizo en Netwey el ' + $date_process + ' con el PortID ' + $portID;
  } else {
    if ($status == 'Solicitud Netwey') {
      $infoText = 'El Dn ' + $msisdn + ' esta en espera desde ' + $date_process + ' para ser actualizado en el sistema de Netwey';
    } else {
      if ($status == 'En proceso Netwey') {
        $infoText = 'El Dn ' + $msisdn + ' se esta actualizado en el sistema de Netwey desde ' + $date_process + ', este proceso suele tardar un poco.';
      } else {
        if ($status == 'En proceso ADB') {
          $infoText = 'El Dn ' + $msisdn + ' inicio proceso de intercambio de mensajes con ADB de portabilidad Mexico bajo el PortID ' + $portID + ' con fecha ' + $date_process + '. Este proceso suele tardar un poco.';
        } else {
          if ($status == 'Incidencia ADB') {
            $infoText = 'El Dn ' + $msisdn + ' tuvo una incidencia el ' + $date_process + ' bajo el PortID: ' + $portID + '\n +Detalles: \n' + $observacion;
          } else {
            $infoText = 'El Dn ' + $msisdn + ' se encuentra en tramite desde la fecha ' + $date_process + ' con el PortID ' + $portID;
          }
        }
      }
    }
  }
  if ($status == 'En proceso ADB') {
    swal({
      title: "Detalles de la portabilidad-ADB!",
      text: $infoText,
      icon: "info",
      buttons: {
        deny: {
          text: "Cancelar portabilidad",
          value: 'cancelar',
          visible: true,
          className: "btn btn-sencondary",
          closeModal: true,
          botonesStyling: false
        },
        confirm: {
          text: "Ver detalles",
          value: 'view',
          visible: true,
          className: "btn btn-primary",
          closeModal: true,
          botonesStyling: false
        },
        cancel: {
          text: "Continuar",
          value: 'ok',
          visible: true,
          className: "btn btn-danger",
          closeModal: true,
          botonesStyling: false
        },
      },
      dangerMode: true,
    }).then((option) => {
      if (option == 'view') {
        //alert("HOLA DETALLES: " + $id + " " + $portID + " " + $msisdn + " " + $date_process);
        viewDetailSOAP($portID, $msisdn, $date_process);
      } else {
        if (option == 'cancelar') {
          swal({
            title: "Estas seguro de cancelar la portabilidad-ADB?",
            text: "Estas a punto de cancelar el proceso de portabilidad del DN: " + $msisdn + " El proceso no se podra revertir.",
            icon: "warning",
            content: {
              element: "input",
              attributes: {
                name: "keySecure",
                id: "keySecure",
                placeholder: "Escribe la palabra: `ADB` ",
                type: "text",
              },
            },
            buttons: {
              cancelADB: {
                text: "Cancelar portabilidad",
                value: 'ok',
                visible: true,
                className: "btn btn-primary",
                closeModal: true,
                botonesStyling: false
              },
              ignorar: {
                text: "Ignorar",
                value: 'cancel',
                visible: true,
                className: "btn btn-danger",
                closeModal: true,
                botonesStyling: false
              },
            },
            dangerMode: true,
          }).then((result) => {
            //alert(result);
            if (result == 'ok') {
              if ($('.swal-content #keySecure').val().toUpperCase() == 'ADB') {
                cancelSOAP($msisdn, $portID);
              } else {
                swal("Debes escribir `ADB` para confirmar que deseas cancelar la portabilidad del DN: " + $msisdn, {
                  icon: "warning",
                });
              }
            }
          });
        }
      }
    });
  } else {
    swal({
      title: "Detalles de la portabilidad!",
      text: $infoText,
      icon: "info",
      buttons: {
        confirm: {
          text: "Continuar",
          value: 'ok',
          visible: true,
          className: "",
          closeModal: true
        },
      },
      dangerMode: true,
    });
  }
}

function viewDetailSOAP($portID, $msisdn, $date_process) {
  $('.preloader').show();
  // $.fn.dataTable.ext.errMode = 'throw';
  if ($.fn.DataTable.isDataTable('#myTableDetailSoap')) {
    $('#myTableDetailSoap').DataTable().destroy();
  }
  $('#myTableDetailSoap').DataTable({
    searching: true,
    processing: true,
    serverSide: true,
    ajax: {
      url: 'view/port/getDetailsSoap',
      data: function(d) {
        d._token = $('meta[name="csrf-token"]').attr('content');
        d.portID = $portID;
      },
      type: "POST"
    },
    initComplete: function(settings, json) {
      $(".preloader").fadeOut();
      $('#rep-sc').attr('hidden', null);
    },
    "order": [
      [2, "desc"] //Fecha de registro
    ],
    deferRender: true,
    ordering: true,
    columns: [{
      data: 'messageID',
      searchable: true,
      orderable: false
    }, {
      data: 'messageID_type',
      searchable: false,
      orderable: false
    }, {
      data: 'message_fecha',
      searchable: false,
      orderable: true
    }],
    "language": {
      "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
    }
  });
  $('#myModal').modal({
    backdrop: 'static',
    keyboard: false
  });
  $('#dn_portar').html($msisdn);
  $('#date_solicitud').html($date_process);
}

function cancelSOAP($msisdn, $portID) {
  $('.preloader').show();
  $.ajax({
    url: 'view/port/PortImportSetCancelSoapItem',
    method: 'POST',
    data: {
      _token: $('meta[name="csrf-token"]').attr('content'),
      msisdn: $msisdn,
      portID: $portID,
    },
    dataType: "json",
    success: function(data) {
      if (data.success) {
        swal({
          title: "Peticion registrada satisfactoriamente.",
          text: "Haz realizado la peticion de cancelar la portabilidad ante ADB, en unos minutos sera procesada.",
          icon: "success",
        });
        $(".preloader").fadeOut();
        $('#rep-sc').attr('hidden', null);
        searchportability();
      } else {
        swal({
          title: " Atencion!",
          text: "No se puede procesar la peticion de cancelacion de portabilidad, no cumple aun con la condiciones necesarias",
          icon: "warning",
        });
        $(".preloader").fadeOut();
        $('#rep-sc').attr('hidden', null);
      }
    },
    error: function(data) {
      swal({
        title: " Atencion!",
        text: "Ocurrio un error al cancelar la portabilidad: " + data.error,
        icon: "warning",
      });
      $(".preloader").fadeOut();
      $('#rep-sc').attr('hidden', null);
    }
  });
}