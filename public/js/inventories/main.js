var fv;
jQuery.validator.addMethod("MACValidation", function(value, element) {
  //console.log(element);
  return this.optional(element) || (/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/.test(value));
}, 'Mac Address inválida.');

function save() {
  let type = $('#inv_article_id option:selected').data('type');
  let rule = {
    msisdn: {
      required: true,
      minlength: 10,
      maxlength: 10,
      digits: true
    },
    iccid: {
      required: true,
      minlength: 20,
      maxlength: 20
    },
    price_pay: {
      required: true
    },
    inv_article_id: {
      required: true
    },
    warehouses_id: {
      required: true
    }
  };
  let messages = {
    msisdn: "Por Favor especifique el MSISDN (10 Digitos)",
    iccid: "Por Favor especifique el ICCID (20 Caracteres)",
    price_pay: "Por favor especifique el precio pagado",
    inv_article_id: "Por favor especifique un producto",
    warehouses_id: "Por favor especifique una bodega"
  }
  if (type) {
    if (type == 'H') {
      rule.imei = {
        required: true,
        minlength: 14,
        maxlength: 15,
        digits: true
      };
      messages.imei = "Por favor especifique el IMEI (14 o 15 Digitos)";
    }
    if (type == 'F') {
      rule.imei = {
        required: true,
        MACValidation: true
      };
      messages.imei = "Por favor especifique una MAC Address Válida";

      rule.serial = {
        required: true
      };
      messages.serial = "Por favor especifique el serial";
    }
  }
  if (fv) {
    fv.destroy();
  }
  fv = $('#inventory_form').validate({
    rules: rule,
    messages: messages
  });
  if ($('#inventory_form').valid()) {
    sav('#inventory_form', function(res) {
      //alert(res);
      swal(res);
      getview('inventories');
    }, function(res) {
      //alert('Ocurrio un error al realizar su operación');
      swal('Ocurrio un error al realizar su operación', {
        icon: "warning",
      });
      console.log('error');
      console.log(res);
    });
  } else {
    $('#inventory_form').submit(function(e) {
      e.preventDefault();
    })
  }
}

function savefile() {
  var params = new FormData();
  file = document.getElementById('csv').files[0];
  params.append('csv', file);
  params.append('_token', $('meta[name="csrf-token"]').attr('content'));
  $('.preloader').show();
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    contentType: false,
    processData: false,
    cache: false,
    async: true,
    url: 'api/inventories/store-csv',
    method: 'POST',
    data: params,
    success: function(res) {
      $(".preloader").fadeOut();
      if (res.success) {
        var success = res.DNsuccess;
        var fail = res.fail;
        var error_product = res.error_product;
        var error_wh = res.error_wh;
        var error_dnag = res.error_dnag;
        var error_imeimacl = res.error_imeimacl;
        var error_imeimacd = res.error_imeimacd;
        var assigment = res.assigment;
        var no_assigment = res.no_assigment;
        var invalid = res.invalid;
        var response = '';
        if (success.length > 0) {
          response = response + 'Los siguientes articulos ' + success + ' fueron agregados ' + '\n\n';
        }
        if (fail.length > 0) {
          response = response + 'Los siguientes articulos ' + fail + ' no fueron agregados, ya existen  ' + '\n\n';
        }
        if (invalid.length > 0) {
          response = response + 'Los siguientes articulos ' + invalid + ' no fueron agregados, msisdn, imei, mac o iccid invalidos ' + '\n\n';
        }
        if (error_product.length > 0) {
          response = response + 'El id de los siguientes productos: ' + error_product + ', no se encuentran ' + '\n\n';
        }
        if (error_wh.length > 0) {
          response = response + 'El id de las siguientes bodegas: ' + error_wh + ', no se encuentran ' + '\n\n';
        }
        if (assigment.length > 0) {
          response = response + 'Los siguientes articulos: ' + assigment + ', fueron asignados al vendedor ' + '\n\n';
        }
        if (no_assigment.length > 0) {
          response = response + 'Los siguientes articulos: ' + no_assigment + ', no fueron asignados al vendedor ' + '\n\n';
        }
        if (error_dnag.length > 0) {
          response = response + 'Para los articulos de las lineas: ' + error_dnag + ', no se pudo generar el DN ' + '\n\n';
        }
        if (error_imeimacl.length > 0) {
          response = response + 'Los articulos de las lineas: ' + error_imeimacl + ', no se pueden agregar porque el imei o la mac ya se encuentran en uso ' + '\n\n';
        }
        if (error_imeimacd.length > 0) {
          response = response + 'Los siguientes articulos: ' + error_imeimacd + ', no se pueden agregar porque el imei o la mac ya se encuentran en uso ' + '\n\n';
        }
        console.log(response);
        //alert(response);
        swal(response);
      } else {
        swal(res.msg, {
          icon: "warning",
        });
      }
      getview('inventories');
    },
    error: function(res) {
      $(".preloader").fadeOut();
      console.log(res);
    }
  });
}
$("#fileup").click(function() {
  $('#file_form').submit(function(e) {
    e.preventDefault();
  })
  if ($('#file_form').valid()) {
    savefile();
  }
});

function update(object) {
  obj = JSON.parse(object.replace(/\'/g, '"'));
  setModal(obj);
  //$('#open_modal_btn').click();
  $("#myModal").modal("show");
}

function deleteData(id, name) {
  del('api/inventories/'.concat(id), name, function(res) {
    console.log('del(success)', res);
    if (res) {
      if (res == 'No se puede procesar su solicitud. El detalle de articulo se encuentra asignado a un vendedor') {
        //alert(res);
        swal(res, {
          icon: "warning",
        });
      } else {
        //alert('fue eliminado satisfactoriamente el registro: '.concat(name));
        swal('fue eliminado satisfactoriamente el registro: '.concat(name), {
          icon: "success",
        });
        getview('inventories');
      }
    } else {
      //alert(res);
      swal(res, {
        icon: "info",
      });
    }
  }, function(res) {
    // alert('error al eliminar el registro: '.concat(name));
    swal('error al eliminar el registro: '.concat(name), {
      icon: "warning",
    });
  });
}

function setModal(object) {
  var frm = $('#inventory_form');
  if (object != null) {
    frm.data('id',object.id);
    frm.data('type',object.artic_type);
    $('h4.modal-title').text('Editar datos: inventario general N° '.concat(object.id));
    $('#cb_parent_id_container').attr('checked', object.parent_id != null ? 'checked' : false);
    //setSelect('parent_id', ((object.parent_id != null) && (object.parent_id != undefined)) ? object.parent_id : null);
    //setSelect('inv_article_id', ((object.inv_article_id != null) && (object.inv_article_id != undefined)) ? object.inv_article_id : null);
    //setSelect('warehouses_id', ((object.warehouses_id != null) && (object.warehouses_id != undefined)) ? object.warehouses_id : null);
    $('#parent_id').val(((object.parent_id != null) && (object.parent_id != undefined)) ? object.parent_id : null);
    $('#inv_article_id').val(((object.inv_article_id != null) && (object.inv_article_id != undefined)) ? object.inv_article_id : null);
    $('#warehouses_id').val(((object.warehouses_id != null) && (object.warehouses_id != undefined)) ? object.warehouses_id : null);
    $('#serial').val(object.serial);
    $('#msisdn').val(object.msisdn);
    $('#iccid').val(object.iccid);
    $('#imsi').val(object.imsi);
    $('#imei').val(object.imei);
    $('#date_reception').val(object.date_reception);
    $('#date_sending').val(object.date_sending);
    $('#price_pay').val(object.price_pay);
    $('#obs').val(object.obs);
    $('#usr_col').addClass('d-none');
    $('#obs_col').addClass('col-md-12').removeClass('col-md-6');
    //$('#status').val(object.status);


    if(object.artic_type == 'F'){
      $('#inv_article_id option[data-type="F"]').removeClass('d-none');
      $('#inv_article_id option[data-type!="F"]').addClass('d-none');
      $('#msisdn').prop('readonly',true);
    }
    else{
      $('#inv_article_id option[data-type!="F"]').removeClass('d-none');
      $('#inv_article_id option[data-type="F"]').addClass('d-none');
      $('#msisdn').prop('readonly',false);
    }

    frm.attr('action', 'api/inventories/'.concat(object.id));
    frm.attr('method', 'PUT');
  } else {
    frm.data('id',null);
    frm.data('type',null);
    $('h4.modal-title').text('Crear inventario general');
    $('#cb_parent_id_container').attr('checked', false);
    //setSelect('parent_id', null);
    //setSelect('inv_article_id', null);
    //setSelect('warehouses_id', null);
    $('#parent_id').val('');
    $('#inv_article_id').val('');
    $('#warehouses_id').val('');
    $('#serial').val('');
    $('#msisdn').val('');
    $('#iccid').val('');
    $('#imei').val('');
    $('#imsi').val('');
    $('#date_reception').val('');
    $('#date_sending').val('');
    $('#price_pay').val('');
    $('#user_email').val('');
    $('#obs').val('');
    $('#usr_col').removeClass('d-none');
    $('#obs_col').addClass('col-md-6').removeClass('col-md-12');
    //$('#status').val('A');

    $('#inv_article_id option').removeClass('d-none');
    $('#msisdn').prop('readonly',false);

    frm.attr('action', 'api/inventories/store');
    frm.attr('method', 'POST');
  }
  $('#inv_article_id').trigger('change');
}
$('#myModal').on('hidden.bs.modal', function() {
  setModal(null);
});

function CBOnClick(id) {
  if ($(id).is(':checked')) {
    $('#parent_id_container').show();
  } else {
    $('#parent_id_container').hide();
  }
}
/*
$('button[type="submit"]').attr('disabled','disabled');
$('input').blur(function() {
    if(
      ($('input[name=serial]').val().length != 0) &&
      ($('input[name=msisdn]').val().length != 0) &&
      ($('input[name=iccid]').val().length != 0) &&
      ($('input[name=imsi]').val().length != 0) &&
      ($('input[name=price_pay]').val().length != 0)
      ){

        $('button[type="submit"]').removeAttr('disabled');
  }
});
*/
$(document).ready(function() {
  $('.preloader').show();
  if ($.fn.DataTable.isDataTable('#myTable')) {
    $('#myTable').DataTable().destroy();
  }
  //console.log(editPermission);
  var table = $('#myTable').DataTable({
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
    searching: true,
    processing: true,
    serverSide: true,
    ajax: {
      url: "api/inventories/get_dt_inventory_details",
      data: function(d) {
        d._token = $('meta[name="csrf-token"]').attr('content');
      },
      type: "POST"
    },
    initComplete: function(settings, json) {
      $(".preloader").fadeOut();
      //$('#rep-sc').attr('hidden', null);
    },
    deferRender: true,
    ordering: false,
    columns: [
      //{data: null, orderable: false, className:'details-control', defaultContent: ''},
      {
        data: null,
        render: function(data, type, row, meta) {
          html = '';
          if (editPermission > 0) {
            jsoncad = JSON.stringify(row).replace(/"/g, '\\\'');
            html = html + '<button type="button" class="btn btn-warning w-100 btn-md edit-bc" onclick="update(\'' + jsoncad + '\')">Editar</button>';
          }
          if (delPermission > 0) {
            html = html + '<button type="button" class="btn btn-danger w-100 btn-md edit-bc" onclick="deleteData(\'' + row.id+ '\',\'' + row.title+ '\')">Eliminar</button>';
          }
          return html;
        },
        searchable: false,
        orderable: false
      }, {
        data: 'id',
        searchable: false,
        orderable: false
      }, {
        data: 'title',
        searchable: false,
        orderable: false
      }, {
        data: 'msisdn',
        searchable: true,
        orderable: false
      }, {
        data: 'imei',
        searchable: true,
        orderable: false
      }, {
        data: 'price_pay',
        searchable: false,
        orderable: false
      }, {
        data: 'status',
        searchable: false,
        orderable: false
      }
    ]
  });
  var format = {
    autoclose: true,
    todayHighlight: true,
    format: 'yyyy-mm-dd',
    language: 'es'
  };
  $('#date_reception').datepicker(format);
  $('#date_sending').datepicker(format);
  $(".preloader").fadeOut();
  CBOnClick('#cb_parent_id_container');
  $('#cb_parent_id_container').click(function() {
    CBOnClick('#cb_parent_id_container');
  });
  $('#inventory_form').validate({
    rules: {
      msisdn: {
        required: true
      },
      iccid: {
        required: true
      },
      price_pay: {
        required: true
      },
      imei: {
        required: true
      },
      inv_article_id: {
        required: true
      },
      warehouses_id: {
        required: true
      }
    },
    messages: {
      msisdn: "Por Favor especifique el MSISDN",
      iccid: "Por Favor especifique el ICCID",
      price_pay: "Por favor especifique el precio pagado",
      imei: "Por favor especifique el IMEI",
      inv_article_id: "Por favor especifique un producto",
      warehouses_id: "Por favor especifique una bodega"
    }
  });
  // 2. Initiate the validator
  $("#file_form").validate({
    rules: {
      csv: {
        required: true,
        extension: "csv"
      }
    },
    messages: {
      csv: {
        required: "Ingrese un archivo con formato .CSV",
        extension: "El archivo no cumple con el formato CSV"
      }
    }
  });
  $("#open_modal_btn").on('click', () => {
    $("#myModal").modal();
  });
  $('#inv_article_id').on('change', () => {
    if ($('#inv_article_id option:selected').data('type') == 'F') {
      $('#iccid-container').addClass('d-none');
      $('#imei-label').text('MAC Address');
      $('#imei').attr('placeholder', 'Ingresar MAC Address');
      $("#imei").rules("remove");
      $("#imei").rules("add", {
        required: true,
        MACValidation: true,
        messages: {
          required: "Por favor especifique una MAC Address Válida",
          MACValidation: "Por favor especifique una MAC Address Válida"
        }
      });

      if($('#inventory_form').data('id') == null){
        var params = new FormData();
        params.append('_token', $('meta[name="csrf-token"]').attr('content'));
        $('.preloader').show();
        $.ajax({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          contentType: false,
          processData: false,
          cache: false,
          async: true,
          url: 'api/inventories/get-available-dn-autogen',
          method: 'POST',
          data: params,
          success: function(res) {
            $(".preloader").fadeOut();
            if(res.success){
              $('#msisdn').prop('readonly',true);
              $('#msisdn').val(res.msisdn);
            }
            else{
              swal('Ocurrio un error generando DN', {
                icon: "warning",
              });
            }

          },
          error: function(res) {
              $(".preloader").fadeOut();
              swal('Ocurrio un error generando DN', {
                icon: "warning",
              });
              console.log(res);
            }
        });
      }

    } else {
      $('#iccid-container').removeClass('d-none');
      $('#imei-label').text('IMEI');
      $('#imei').attr('placeholder', 'Ingresar IMEI');
      $("#imei").rules("remove");
      $("#imei").rules("add", {
        required: true,
        minlength: 14,
        maxlength: 15,
        digits: true,
        messages: {
          required: "Por favor especifique el IMEI (14 o 15 Digitos)",
          minlength: "Por favor especifique el IMEI (14 o 15 Digitos)",
          maxlength: "Por favor especifique el IMEI (14 o 15 Digitos)",
          digits: "Por favor especifique el IMEI (14 o 15 Digitos)"
        }
      });
    }
    if ($("#imei").val().length > 0) {
      var validator = $("#inventory_form").validate();
      validator.element("#imei");
    }
  });
});