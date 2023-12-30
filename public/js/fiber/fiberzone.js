/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Agosto 2022,
 */
/**
 * [verifyZone Consulta el status del endpoint o elimina el item]
 * @param  {[type]} id  [id de la zona]
 * @param  {[type]} url [url al cual se envia la peticion]
 * @return {[type]}     [description]
 */
function verifyZone(id, url = false) {
  $(".preloader").fadeIn();
  $.ajax({
    type: "POST",
    url: url,
    data: {
      _token: $('meta[name="csrf-token"]').attr('content'),
      id: id
    },
    dataType: "json",
    success: function(response) {
      $(".preloader").fadeOut();
      const search = 'deleteZone';
      if (url.indexOf(search) >= 0) {
        //Es una eliminacion
        search_listZone();
      }
      swal(response.title, response.msg, response.icon);
    },
    error: function(err) {
      console.log("error al consultar el endpoint de fibra ", err);
      $(".preloader").fadeOut();
    }
  });
}

function DownloadReport() {
  $(".preloader").fadeIn();
  $.ajax({
    type: "POST",
    url: 'view/fiber/getDownloadZones',
    data: {
      _token: $('meta[name="csrf-token"]').attr('content'),
      owner: $('#owner').val()
    },
    dataType: "json",
    success: function(response) {
      $(".preloader").fadeOut();
      swal('Generando reporte de zonas de fibra', 'El reporte estara disponible en unos minutos.', 'success');
      // var a = document.createElement("a");
      // a.target = "_blank";
      // a.href = "{route('downloadFile',['delete' => 1])}}?p=" + response.url;
      // a.click();
    },
    error: function(err) {
      console.log("error al crear el reporte de zonas de fibra: ", err);
      $(".preloader").fadeOut();
    }
  });
}

function resetFromZone() {
  $('#nameZone').val('');
  $('#endpoint').val('');
  $('#user').val('');
  $('#password').val('');
  $('#type').val('815');
  $('#nodo').val('');
  $('#modo').val('dhcp');
  $('#relay').val('False');
  $('#msg').val('');
  $('#owner_CU').val('');
  $('#collector').val('');
}

function CreateZone() {
  $('.preloader').fadeIn()
  resetFromZone();
  $('h4#modal-title').text('Crear zona de fibra');
  $('button#submit').attr('data-action', 'create');
  $('div#myModal').modal();
  $('.preloader').fadeOut();
}

function textMaximo(campo, limite = 140) {
  if (campo.value.length > limite) {
    campo.value = campo.value.substring(0, limite);
  } else {
    $('#cantLimit').html(limite - campo.value.length);
  }
}

function showZone(id) {
  $('.preloader').fadeIn()
  resetFromZone();
  $('h4#modal-title').text('Editar zona de fibra');
  $('button#submit').attr('data-action', 'update');
  $.ajax({
    type: "POST",
    url: 'view/fiber/getDetailZona',
    data: {
      _token: $('meta[name="csrf-token"]').attr('content'),
      id: id
    },
    dataType: "json",
    success: function(response) {
      if (response.success) {
        //console.log(response.msg);
        $('#id').val(response.msg.id);
        $('#nameZone').val(response.msg.name);
        $('#endpoint').val(response.msg.url_api);
        $('#user').val(response.msg.param.user);
        $('#password').val(response.msg.param.password);
        $('#password2').val(response.msg.param.password);
        $('#type').val(response.msg.type_soft);
        $('#nodo').val(response.msg.param.nodo_de_red);
        $('#modo').val(response.msg.param.mode_default);
        $('#relay').val(response.msg.param.dhcp_relay);
        $('#msg').val(response.msg.configuration.sms);
        $('#cantLimit').html(140 - response.msg.configuration.sms.length);
        $('#owner_CU').val(response.msg.configuration.owner);
        $('#collector').val(response.msg.configuration.collector);
      } else {
        swal(response.title, response.msg, response.icon);
      }
      $('div#myModal').modal();
      $(".preloader").fadeOut();
    },
    error: function(err) {
      console.log("error al consultar el endpoint de fibra ", err);
      $(".preloader").fadeOut();
    }
  });
}
jQuery.validator.addMethod("urlValidate", function(value, element) {
  return this.optional(element) || /^(http(s)?:\/\/)?(www.)?([a-zA-Z0-9])+([\-\.]{1}[a-zA-Z0-9]+)*\.[a-zA-Z0-9]{2,5}(:[0-9]{1,5})?(\/[^\s]*)?(\/gateway\/integracion\/)$/.test(value);
}, "URL inválida. Recuerda la URL de 815 debe terminar en '/gateway/integracion/' ");
$('form#form-zone').validate({
  rules: {
    nameZone: {
      required: true
    },
    endpoint: {
      required: true,
      urlValidate: true
    },
    user: {
      required: true
    },
    password: {
      required: true
    },
    type: {
      required: true
    },
    nodo: {
      required: true,
      number: true
    },
    modo: {
      required: true
    },
    relay: {
      required: true
    },
    msg: {
      required: true
    },
    owner_CU: {
      required: true
    },
    collector: {
      required: true
    }
  },
  messages: {
    nameZone: {
      required: "Por favor ingrese un nombre para la zona",
    },
    endpoint: {
      required: "Por favor ingrese el endpoint o url",
    },
    user: {
      required: "Por favor ingrese el usuario",
    },
    password: {
      required: "Por favor ingrese la contrasena",
    },
    type: {
      required: "Por favor seleccione el proveedor de software",
    },
    nodo: {
      required: "Por favor ingrese el nodo de conexión",
      number: "Por favor ingrese solo numero"
    },
    modo: {
      required: "Por favor seleccione el modo de conexión del endpoint",
    },
    relay: {
      required: "Por favor seleccione si la conexión sera por relay o no",
    },
    msg: {
      required: "Por favor ingrese el mensaje a ser enviado al momento de agendar la cita",
    },
    owner_CU: {
      required: "Por favor seleccione quien controlara la zona",
    },
    collector: {
      required: "Por favor seleccione quien sera el responsable de cobrar",
    }
  }
});
$('button#submit').on('click', () => {
  const actionSubmit = $('button#submit').attr('data-action');
  if (!$('form#form-zone').valid()) {
    return 0;
  }
  const formData = new FormData($('form#form-zone')[0]);
  $('.preloader').fadeIn();
  if (actionSubmit === 'create') {
    saveZone(formData, "view/fiber/createZone");
    return 1;
  } else {
    if (actionSubmit === 'update') {
      saveZone(formData, "view/fiber/updateZone");
      return 1;
    }
  }
});
saveZone = (data, url = null, type = 'POST') => {
  if (!url && data.length < 1) {
    return 0;
  }
  $.ajax({
    url,
    type,
    data,
    processData: false,
    contentType: false,
    success: (res) => {
      $('div#myModal').modal('hide');
      $('.preloader').fadeOut();
      swal(res.msg, {
        icon: res.icon
      });
      search_listZone();
      //$dataTableZone.draw();
    },
    error: (err) => {
      let message = 'Ocurrió un error al intentar guardar el recurso.'
      if (err.responseJSON.message) {
        message = err.responseJSON.message
      }
      $('.preloader').fadeOut();
      swal(message, {
        icon: 'error'
      });
    }
  });
}

function DeleteZone(id, name) {
  swal({
    title: "Confirma eliminar la Zona de fibra?",
    text: "Se eliminara " + name + ". El proceso no se prodra revertir",
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
    if (option == 'ok') {
      verifyZone(id, 'view/fiber/deleteZone');
    }
  });
}