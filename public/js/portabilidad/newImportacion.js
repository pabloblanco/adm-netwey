/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Abril 2022,
 */
$(function() {
  jQuery.extend(jQuery.validator.messages, {
    required: "Este campo es obligatorio.",
    remote: "Por favor, rellena este campo.",
    email: "Por favor, escribe una dirección de correo válida",
    url: "Por favor, escribe una URL válida.",
    date: "Por favor, escribe una fecha válida.",
    dateISO: "Por favor, escribe una fecha (ISO) válida.",
    number: "Por favor, escribe un número entero válido.",
    digits: "Por favor, escribe sólo dígitos.",
    creditcard: "Por favor, escribe un número de tarjeta válido.",
    equalTo: "Por favor, verifica el valor este no coincide.",
    accept: "Por favor, escribe un valor con una extensión aceptada.",
    maxlength: jQuery.validator.format("Por favor, no escribas más de {0} caracteres."),
    minlength: jQuery.validator.format("Por favor, no escribas menos de {0} caracteres."),
    rangelength: jQuery.validator.format("Por favor, escribe un valor entre {0} y {1} caracteres."),
    range: jQuery.validator.format("Por favor, escribe un valor entre {0} y {1}."),
    max: jQuery.validator.format("Por favor, escribe un valor menor o igual a {0}."),
    min: jQuery.validator.format("Por favor, escribe un valor mayor o igual a {0}.")
  });
  jQuery.validator.addMethod("notEqual", function(value, element, param) {
    return this.optional(element) || value != $(param).val();
  }, "El valor debe ser diferente");
  $("#Preform-portability").validate({
    rules: {
      dnTransitorio: {
        required: true,
        digits: true,
        minlength: 10,
        maxlength: 10
      },
      dnPort: {
        required: true,
        digits: true,
        minlength: 10,
        maxlength: 10,
        notEqual: "#dnTransitorio"
      }
    }
  });
  $("#form-portability").validate({
    rules: {
      dnTransitorio: {
        required: true,
        digits: true,
        minlength: 10,
        maxlength: 10
      },
      dnPort: {
        required: true,
        digits: true,
        minlength: 10,
        maxlength: 10,
        notEqual: "#dnTransitorio"
      },
      operator: {
        required: true,
      },
      PortNIP: {
        required: true,
        digits: true,
        minlength: 4,
        maxlength: 4
      },
      PortNIP2: {
        equalTo: "#PortNIP"
      }
    }
  });
});

function infoAlert(msg) {
  swal({
    title: "Hay un inconveniente!",
    text: msg,
    icon: "warning",
    dangerMode: true,
  });
}

function VerificarPass(password) {
  /*Se chequea la clave*/
  $('.preloader').show();
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    async: true,
    url: "view/port/chekingSupervisor",
    method: 'POST',
    data: {
      pass: password
    },
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        page2();
      } else {
        infoAlert(res.msg);
      }
      $(".preloader").fadeOut();
    },
    error: function(res) {
      infoAlert('No se pudo verificar el supervisor.');
    }
  });
}

function authorizePort(nameprnt = null) {
  valid = $('#Preform-portability').valid();
  if (valid) {
    if (nameprnt) {
      namecoord = "(" + nameprnt.UserFullName + ")";
    }
    swal({
      title: "Requiere Autorización",
      text: "Para continuar es necesario que ingreses la clave de autorización de tu supervisor: " + namecoord,
      content: {
        element: "input",
        required: "required",
        attributes: {
          placeholder: "clave de autorización",
          type: "password",
        },
      },
      buttons: true,
      closeOnEsc: false,
      closeOnClickOutside: false,
    }).then((password) => {
      if (password.trim() == "") {
        swal("Debe ingresar una clave de autorización", {
          className: "text-success",
        }).then(() => {
          authorizePort(nameprnt);
        });
      } else {
        VerificarPass(password);
      }
    });
  }
}

function initPreChekin() {
  $('#BtnNewDN').hide();
  $('#BtnnextPort').hide();
}

function page1() {
  $('.preloader').show();
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    async: true,
    url: "view/port/portFromNew",
    method: 'POST',
    data: {
      msisdn: $('#dnTransitorio').val()
    },
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        $('#blockPortability').html(res.htmlCode);
      } else {
        alert(res.msg);
        setTimeout(function() {
          $('#detalles-tab').trigger('click');
        }, 100);
      }
      $(".preloader").fadeOut();
    },
    error: function(res) {
      alert('No se pudo mostrar el formulario de portabilidad.');
      setTimeout(function() {
        $(".preloader").fadeOut();
        $('#detalles-tab').trigger('click');
      }, 100);
    }
  });
}

function page2() {
  $(".preloader").fadeIn();
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    url: 'view/port/portSuccessNew',
    type: 'POST',
    dataType: 'json',
    data: {
      _token: $('meta[name="csrf-token"]').attr('content')
    },
    error: function() {
      swal({
        title: "Hubo un error en crear el formulario de solicitud de portabilidad",
        text: null,
        icon: "warning",
      });
      $(".preloader").fadeOut();
      $('#nextPort').prop('disabled', false);
    },
    success: function(res) {
      if (res.success) {
        $('#blockPortability').html(res.htmlCode);
      }
      $(".preloader").fadeOut();
      $('#nextPort').prop('disabled', false);
    }
  });
}
$(document).ready(function() {
  initPreChekin();
  //
  $('#updatePort').on('click', function(e) {
    preasg = $(e.currentTarget).data('stepport');
    if (preasg == '1') {
      page1();
    } else {
      if (preasg == '2') {
        page2();
      }
    }
  });
  $('#BtnVerify').on('click', function(e) {
    $('#BtnVerify').prop('disabled', true);
    valid = $('#Preform-portability').valid();
    if (valid) {
      $(".preloader").fadeIn();
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'view/port/portChekingNew',
        type: 'POST',
        dataType: 'json',
        data: {
          _token: $('meta[name="csrf-token"]').attr('content'),
          dnPort: $('#dnPort').val(),
        },
        error: function() {
          swal({
            title: "Hubo un error en verificar el DN a portar",
            text: null,
            icon: "warning",
          });
          $(".preloader").fadeOut();
          $('#BtnVerify').prop('disabled', false);
        },
        success: function(res) {
          if (res.success) {
            swal({
              title: "Verificación OK",
              text: res.msg,
              icon: "warning",
            });
            $('#dnPort').prop('disabled', true);
            $('#BtnNewDN').show();
            $('#BtnnextPort').show();
            $('#BtnVerify').hide();
          } else {
            swal({
              title: "El Dn a portar requiere de atención",
              text: res.msg,
              icon: "warning",
            });
          }
          $(".preloader").fadeOut();
          $('#BtnVerify').prop('disabled', false);
        }
      });
    }
  });
  //
  $('#BtnNewDN').on('click', function(e) {
    $('#dnPort').val('');
    $('#BtnVerify').show();
    $('#dnPort').prop('disabled', false);
    initPreChekin();
  });
  //
  $('#nextPort').on('click', function(e) {
    $('#nextPort').prop('disabled', true);
    valid = $('#Preform-portability').valid();
    if (valid) {
      page2();
    }
  });
  //
  $('#RenewDN').on('click', function(e) {
    page1();
  });
  //
  $('#newImportacion').on('click', function(e) {
    $('#newImportacion').prop('disabled', true);
    valid = $('#form-portability').valid();
    if (valid) {
      $(".preloader").fadeIn();
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'view/port/portSendNew',
        type: 'POST',
        dataType: 'json',
        data: {
          _token: $('meta[name="csrf-token"]').attr('content'),
          operator: $('#operator').val(),
          PortNIP: $('#PortNIP').val()
        },
        error: function() {
          swal({
            title: "Hubo un error en crear la solicitud de portabilidad",
            text: null,
            icon: "warning",
          });
          $(".preloader").fadeOut();
          $('#newImportacion').prop('disabled', false);
        },
        success: function(res) {
          if (res.success) {
            swal({
              title: "Solicitud de portación exitosa.",
              text: "El proceso se completara aproximadamente en 3 días hábiles",
              icon: "success",
            });
            setTimeout(function() {
              $('#detalles-tab').trigger('click');
            }, 100);
          } else {
            swal({
              title: "Hubo un inconveniente en la solicitud de portación",
              text: res.msg,
              icon: "warning",
            });
          }
          $(".preloader").fadeOut();
          $('#newImportacion').prop('disabled', false);
        }
      });
    }
  });
});