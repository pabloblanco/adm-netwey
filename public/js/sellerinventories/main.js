function save() {
  if (isValidateForm()) {
    var files = document.getElementById('inventory_file');
    var params = new FormData();
    params.append('seller', $('#seller').val());
    params.append('_token', $('meta[name="csrf-token"]').attr('content'));
    if ($('#inventory_manual_assign_check').is(':checked')) {
      params.append('inventory_select', getSelectObject('inventory_select').getValue());
    } else {
      params.append('inventory_file', files.files[0]);
    }
    $('#notification_area').html('');
    $(".preloader").fadeIn();
    $.ajax({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      url: 'api/seller_inventories/associate/show',
      type: 'post',
      data: params,
      contentType: false,
      processData: false,
      cache: false,
      async: true,
      success: function(res) {
        getSellerInventory($('#seller').val());
        res.itemError.forEach(function(item, key) {
          $('#notification_area').html($('#notification_area').html().concat('<div class="row"><div class="col-md-12 bg-warning"><b>').concat(item).concat('</b></div></div><hr>'));
        });
        res.itemSuccess.forEach(function(item, key) {
          $('#notification_area').html($('#notification_area').html().concat('<div class="row"><div class="col-md-12 text-white bg-success"><b>').concat(item).concat('</b></div></div><hr>'));
        });
        alert(res.msg);
        $(".preloader").fadeOut();
      },
      error: function(res) {
        $(".preloader").fadeOut();
      }
    });
  } else {
    console.log('isValidateForm()', isValidateForm());
  }
}

function deleteItem(req, item_name, seller) {
  del(req, item_name, function(res) {
    //getSelectObject('inventory_select').setValue(null);
    getSellerInventory(seller);
    alert(res.msg);
  }, function(res) {});
}

function deleteItems() {
  var arrIds = [],
    email = $('#seller').val();
  $('#myTable input[type=checkbox]').each(function() {
    if ($(this).is(':checked')) {
      arrIds.push($(this).val());
    }
  });
  if (arrIds.length > 0) {
    $.ajax({
      url: 'api/seller_inventories/delete_batch',
      type: 'POST',
      data: {
        email: email,
        ids: arrIds,
        _token: $('meta[name="csrf-token"]').attr('content')
      },
      dataType: 'json',
      success: function(result) {
        //getSelectObject('inventory_select').setValue(null);
        getSellerInventory(email);
        alert(result.msg);
      },
      error: function() {
        alert('Ocurrio un error en la eliminación en lote');
      }
    });
  } else {
    alert('Debes seleccionar al menos un producto para eliminar en lotes.');
  }
}

function getSellerInventory(seller) {
  $(".preloader").fadeIn();
  let inv_user = $('#inventory_select')[0].selectize;
  inv_user.clearOptions();
  requestView('seller_inventories/'.concat(seller), 'GET', function(res) {
    $('#inventory_detail_container').html(res.msg);
    
    if (res.success) {
      if(res.user_status == 'A'){
        $('.asign-inv-content').attr('hidden', null);
        $('#alert-dis').attr('hidden', true);
      }else{
        $('.asign-inv-content').attr('hidden', true);
        $('#alert-dis').attr('hidden', null);
      }

      if (res.inventory.length > 0) {
        res.inventory.forEach(function(ele) {
          inv_user.addOption({
            id: ele.id,
            product: ele.title + ': ' + ele.msisdn,
            msisdn: ele.msisdn
          });
          inv_user.addItem(ele.id);
        });
        $('#inventory_manual_assign_check').prop('checked', true);
        $('#inventory_file_container').hide();
        $('#inventory_select_container').show();
      }
    } else {
      alert(res.errorMsg);
    }
    $(".preloader").fadeOut();
  }, function(res) {
    $(".preloader").fadeOut();
    //var select = getSelectObject('inventory_select');
    //select.setValue(null);
    alert(res.msg);
  });
}

function isValidateForm() {
  $('#error_seller').html('');
  $('#error_inventory_file').html('');
  $('#error_inventory_select').html('');
  var b = validateFields('seller', 'error_seller', 'Debe seleccionar un vendedor');
  if ($('#inventory_manual_assign_check').is(':checked')) {
    b = validateFieldsSelect('inventory_select', 'error_inventory_select', 'Debe seleccionar los artículos a asociar');
  } else {
    b = validatFiles('inventory_file', 'error_inventory_file', 'Debe seleccionar el archivo CSV con la información de los productos', 'El archivo debe ser de extensión CSV');
  }
  return b;
}

function validateFieldsSelect(id, errorId, error) {
  $('#'.concat(errorId)).html('');
  if ((getSelectObject(id).getValue() == null) || (getSelectObject(id).getValue() == undefined) || (getSelectObject(id).getValue() == '')) {
    $('#'.concat(errorId)).html(error);
    return false;
  }
  return true;
}

function validateFields(id, errorId, error) {
  $('#'.concat(errorId)).html('');
  if (($('#'.concat(id)).val() == null) || ($('#'.concat(id)).val() == undefined) || ($('#'.concat(id)).val() == '')) {
    $('#'.concat(errorId)).html(error);
    return false;
  }
  return true;
}
$(document).ready(function() {
  $('#error_seller').html('');
  $('#error_inventory_file').html('');
  $('#error_inventory_select').html('');
  $('#inventory_select').selectize({
    valueField: 'id',
    labelField: 'product',
    searchField: 'msisdn',
    options: [],
    create: false,
    persist: false,
    render: {
      option: function(item, escape) {

        switch (item.type)
        {
            case "H": type="Internet Hogar"; break;
            case "M": type="MIFI"; break;
            case "T": type="Telefonia"; break;
            case "F": type="Fibra Optica"; break;
        }

        opt = "<div>";
        opt += '<span>' + escape(item.msisdn) + "</span>" + '<span class="aai_description">' + escape(item.category) +" - "+ escape(type) +" | "+ escape(item.title) +"</span>";
        if(item.zones.length > 0){
          opt += '<ul class="aai_meta">';
          opt += '<li style="opacity:0.5"><strong>' + escape('Zonas: ') + "</strong></li>";
          item.zones.forEach(function(it, key) {
            coma="";
            if(key < item.zones.length - 1)
              coma=",";
            opt += '<li>' + escape(it.name.trim())+coma+"</li>";
          });
          opt += "</ul>";
        }
        opt += "</div>";



        //console.log(opt);
        return opt;
        //return '<p>' + escape(item.title) + ':' + escape(item.msisdn) + '</p>';
      }
    },
    load: function(query, callback) {
      if (!query.length) return callback();
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'api/seller_inventories/get_dns_available',
        type: 'POST',
        dataType: 'json',
        cache: false,
        data: {
          msisdn: query
        },
        error: function() {
          callback();
        },
        success: function(res) {
          if (res.success) callback(res.inventory);
          else callback();
        }
      });
    }
  });
  $('#inventory_select_container').hide();
  $('#inventory_manual_assign_check').click(function() {
    $('#error_inventory_file').html('');
    $('#error_inventory_select').html('');
    if ($('#inventory_manual_assign_check').is(':checked')) {
      validateFieldsSelect('inventory_select', 'error_inventory_select', 'Debe seleccionar los artículos a asociar');
      $('#inventory_file_container').hide();
      $('#inventory_select_container').show();
    } else {
      validatFiles('inventory_file', 'error_inventory_file', 'Debe seleccionar el archivo CSV con la información de los productos', 'El archivo debe ser de extensión CSV');
      $('#inventory_file_container').show();
      $('#inventory_select_container').hide();
    }
  });
  $('#seller').selectize({
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
        url: 'api/seller_inventories/get_users_inv',
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
  $('#seller').on('change', function() {
    $('#error_seller').html('');
    $('#error_inventory_file').html('');
    $('#error_inventory_select').html('');
    $('#inventory_detail_container').html('<p></p>');
    $('#inventory_file_container').show();
    $('#inventory_select_container').hide();
    $('#inventory_manual_assign_check').prop('checked', false);
    if ($(this).val() != '') {
      //getSelectObject('inventory_select').setValue(null);
      getSellerInventory($(this).val());
    }
  });
});