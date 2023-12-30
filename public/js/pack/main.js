var fv;

function save() {
  let rules = {
    title: {
      required: true
    },
    description: {
      required: true
    }
  };
  let messages = {
    title: 'Por favor ingrese el titulo',
    description: "Por Favor ingrese una descripción",
  }
  if ($('#view_web').val() == 'Y') {
    rules.desc_web = {
      required: true
    };
    messages.desc_web = 'Por favor ingrese la descripción para la web';
  }
  if (fv) {
    fv.destroy();
  }
  fv = $('#pack_form').validate({
    rules: rules,
    messages: messages
  });

  if($('#pack_type').val()!= 'T'){
    $('#is_band_twenty_eight').val('');
  }

  if ($('#pack_form').valid()) {
    sav('#pack_form', function(res) {
      alert(res.msg);
      getview('pack');
    }, function(res) {
      alert('Ocurrio un error creando el pack.');
    });
  } else {
    $('#pack_form').submit(function(e) {
      e.preventDefault();
    })
  }
}

function update(object) {
  setModal(JSON.parse(object));
  $('#open_modal_btn').click();
}

function deleteData(id, name) {
  del('api/pack/'.concat(id), name, function(res) {
    console.log('success: '.concat(res));
    if (res) {
      alert('fue eliminado satisfactoriamente el registro: '.concat(name));
      getview('pack');
    } else {
      alert('error al eliminar el registro: '.concat(name));
    }
  }, function(res) {
    alert('error al eliminar el registro: '.concat(name));
  });
}

function setModal(object) {
  if (object != null) {
    $('h4.modal-title').text('Editar datos: '.concat(object.title));
    $('#id').val(object.id);
    $('#title').val(object.title);
    $('#date_ini').val(object.date_ini);
    $('#date_end').val(object.date_end);
    $('#price').val(object.price_arti);
    $('#description').val(object.description);
    $('#status').val(object.status);
    $('#is_migration').val(object.is_migration);
    $('#valid_identity').val(object.valid_identity);
    if (object.esquemas.length) {
      let esquema = $('#id_esquema')[0].selectize;
      object.esquemas.forEach(function(ele) {
        esquema.addOption({
          id: ele.id_esquema,
          name: ele.name
        });
        esquema.addItem(ele.id_esquema);
      });
    }
    //Campos de la tienda
    $('#view_web').val(object.view_web);
    if (object.view_web == 'Y') {
      $('#desc_web').val(object.desc_web);
      $('#desc-store-contet').show();
      $('#sale_type').val('N');
      $('#content-acept-coupon').show();
      $('#acept_coupon').val(object.acept_coupon);
      $('#type-pay-content').hide();
    } else {
      $('#desc_web').val('');
      $('#desc-store-contet').hide();
      $('#content-acept-coupon').hide();
      $('#sale_type').val(object.sale_type);
      $('#type-pay-content').show();
    }
    $('#pack_type').val(object.pack_type);
    if (object.pack_type == 'H' || object.pack_type == 'M' || object.pack_type == 'MH' || object.pack_type == 'F') {
      $('#port-content').hide();
      $('#is_portability').val('N');
      $('#nbte-content').hide();
      $('#is_band_twenty_eight').val('');
    } else {
      $('#port-content').show();
      $('#is_portability').val(object.is_portability);
      $('#nbte-content').show();
      $('#is_band_twenty_eight').val(object.is_band_twenty_eight);
    }
    if (object.pack_type == 'MH' || object.pack_type == 'F') {
      $('#content-is-migration').show();
      if (object.pack_type == 'MH') {
        $('#is-migration-label').text('Es para migración MIFI');
      } else {
        $('#is-migration-label').text('Es para migración');
      }
    } else {
      $('#content-is-migration').hide();
    }
    $('#service_prom_id').val(object.service_prom_id);
    if (object.is_visible_payjoy == 'Y' || object.is_visible_coppel == 'Y' || object.is_visible_paguitos == 'Y' || object.is_visible_telmovPay == 'Y') {
      $('#block_finaciado').show();
      $('#is_visible_financiacion').val('Y');
    } else {
      $('#is_visible_financiacion').val('N');
      $('#block_finaciado').hide();
    }
    $('#is_visible_payjoy').val(object.is_visible_payjoy);
    $('#is_visible_coppel').val(object.is_visible_coppel);
    $('#is_visible_paguitos').val(object.is_visible_paguitos);
    $('#is_visible_telmovPay').val(object.is_visible_telmovPay);
    $('#pack_form').attr('action', 'api/pack/'.concat(object.id));
    $('#pack_form').attr('method', 'PUT');
  } else {
    $('h4.modal-title').text('Crear Paquete');
    $('#id').val('');
    $('#title').val('');
    $('#date_ini').val('');
    $('#date_end').val('');
    $('#price').val('');
    $('#description').val('');
    $('#status').val('A');
    $('#view_web').val('N');
    $('#desc_web').val('');
    $('#desc-store-contet').hide();
    $('#content-acept-coupon').hide();
    $('#sale_type').val('N');
    $('#acept_coupon').val('N');
    $('#type-pay-content').show();
    $('#port-content').hide();
    $('#is_portability').val('N');
    $('#is_migration').val('N');
    $('#content-is-migration').hide();
    $('#pack_type').val('H');
    $('#nbte-content').hide();
    $('#is_band_twenty_eight').val('');
    $('#service_prom_id').val('');
    $('#is_visible_payjoy').val('N');
    $('#is_visible_coppel').val('N');
    $('#is_visible_paguitos').val('N');
    $('#is_visible_telmovPay').val('N');
    $('#valid_identity').val('N');
    let esquema = $('#id_esquema')[0].selectize;
    esquema.clearOptions();
    $('#pack_form').attr('action', 'api/pack/store');
    $('#pack_form').attr('method', 'POST');
  }
}
$('#myModal').on('hidden.bs.modal', function() {
  setModal(null);
});
$(document).ready(function() {
  $(".preloader").fadeOut();
  $('#date_ini').datepicker({
    autoclose: true,
    todayHighlight: true,
    format: 'yyyy-mm-dd',
  });
  $('#date_end').datepicker({
    autoclose: true,
    todayHighlight: true,
    format: 'yyyy-mm-dd',
  });
  $('#block_finaciado').hide();
  $('#desc-store-contet').hide();
  $('#content-acept-coupon').hide();
  $('#content-is-migration').hide();
  $('#port-content').hide();
  $('#nbte-content').hide();
  $('#is_band_twenty_eight').val('');
  $('#view_web').on('change', function(e) {
    $('#desc-store-contet').hide();
    $('#content-acept-coupon').hide();
    $('#sale_type').val('N');
    $('#acept_coupon').val('N');
    $('#type-pay-content').show();
    if ($(this).val() == 'Y') {
      $('#desc-store-contet').show();
      $('#content-acept-coupon').show();
      //$('#sale_type').val('N');
      $('#type-pay-content').hide();
    }
  });
  $('#is_visible_financiacion').on('change', function(e) {
    if ($(this).val() == 'Y') {
      $('#block_finaciado').show();
    } else {
      if ($('#is_visible_payjoy').val() == 'Y' || $('#is_visible_coppel').val() == 'Y' || $('#is_visible_paguitos').val() == 'Y' || $('#is_visible_telmovPay').val() == 'Y') {
        swal({
          text: "Se marcaron como no visibles las opciones de financiación que habias seleccionado.",
          icon: "warning",
        });
      }
      $('#block_finaciado').hide();
      $('#is_visible_payjoy').val('N');
      $('#is_visible_coppel').val('N');
      $('#is_visible_paguitos').val('N');
      $('#is_visible_telmovPay').val('N');      
    }
  });
  $('#pack_type').on('change', function(e) {
    if ($(this).val() == 'H' || $(this).val() == 'M' || $(this).val() == 'MH' || $(this).val() == 'F') {
      $('#port-content').hide();
      $('#is_portability').val('N');
      $('#nbte-content').hide();
      $('#is_band_twenty_eight').val('');
    } else {
      $('#port-content').show();
      $('#nbte-content').show();
      $('#is_band_twenty_eight').val('Y');
    }
    if ($(this).val() == 'MH' || $(this).val() == 'F') {
      $('#content-is-migration').show();
      if ($(this).val() == 'MH') {
        $('#is-migration-label').text('Es para migración MIFI');
      } else {
        $('#is-migration-label').text('Es para migración');
      }
    } else {
      $('#content-is-migration').hide();
    }
    if ($(this).val() == 'F') {
      $('#id_esquema_content').hide();
    } else {
      $('#id_esquema_content').show();
    }
  });
  //Inicializando selector de coordinaciones
  $('#id_esquema').selectize({
    valueField: 'id',
    labelField: 'name',
    searchField: 'name',
    /*options: [],
    create: false,
    persist: false,*/
    render: {
      option: function(item, escape) {
        return '<p>' + item.name + '</p>';
      }
    },
    load: function(query, callback) {
      if (!query.length) return callback();
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'api/pack/get_coordinations',
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
          if (res.success) callback(res.coordinations);
          else callback();
        }
      });
    }
  });
  $('#myTable').DataTable({
    language: {
      sProcessing: "Procesando...",
      sLengthMenu: "Mostrar _MENU_ registros",
      sZeroRecords: "No se encontraron resultados",
      sEmptyTable: "Ningún dato disponible en esta tabla",
      sInfo: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
      sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
      sInfoFiltered: "(filtrado de un total de _MAX_ registros)",
      sInfoPostFix: "",
      sSearch: "Buscar:",
      sUrl: "",
      sInfoThousands: ",",
      sLoadingRecords: "Cargando...",
      oPaginate: {
        sFirst: "Primero",
        sLast: "Último",
        sNext: "Siguiente",
        sPrevious: "Anterior"
      },
      oAria: {
        sSortAscending: ": Activar para ordenar la columna de manera ascendente",
        sSortDescending: ": Activar para ordenar la columna de manera descendente"
      }
    },
    order: false
  });
});