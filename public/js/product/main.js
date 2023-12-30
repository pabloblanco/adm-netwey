function save() {

  ban = 1;
  if($("#artic_type").val() === 'F'){
    if($('#prod-fiber-zone').val().length>0){
      $("#addArtBtn").data('val',1);
    }
    else{
      valzone=$("#fiber_zone").valid();
      valarti=$("#fiber_article").valid();

      if(valzone && valarti){
        alert("Por Favor agrege al menos una relacion de producto de zona de fibra, para ello seleccione una zona de fibra y su respectivo producto asociado y haga click en el boton agregar");
        ban = 0;
      }
    }
  }

  if ($('#product_form').valid() && ban==1) {
    sav('#product_form', function(res) {
      alert(res);
      getview('products');
      $("#addArtBtn").data('val',0);
    }, function(res) {
      alert('Ocurrio un error al realizar su operación');
      console.log('error');
      console.log(res);
      $("#addArtBtn").data('val',0);
    });
  } else {
    $('#product_form').submit(function(e) {
      e.preventDefault();
      $("#addArtBtn").data('val',0);
    })
  }

}

function update(object) {
  setModal(JSON.parse(object));
  // $('#open_modal_btn').click();
  $('#myModal').modal('show');
}

function deleteData(id, name) {
  if (confirm('¿desea eliminar el producto: ' + name + '?')) {
    request('api/products/'.concat(id), 'DELETE', null, function(res) {
      if (res) {
        alert('fue eliminado satisfactoriamente el producto: ' + name);
        getview('products');
      } else {
        alert('error al eliminar el producto: ' + name);
      }
    }, function(res) {
      console.log('error: '.concat(res));
    });
  }
}

function setModal(object) {
  if (object != null) {
    $('#artic_id').val(object.id);
    $('h4.modal-title').text('Editar datos: '.concat(object.id));
    $('#provider_dni').val(object.provider_dni);
    $('#category_id').val(object.category_id);
    $('#title').val(object.title);
    $('#description').val(object.description);
    $('#brand').val(object.brand);
    $('#model').val(object.model);
    //$('#type_barcode').val(object.type_barcode);
    $('#sku').val(object.sku);
    $('#artic_type').val(object.artic_type);
    if ($('#artic_type').val() == 'F') {
      $("#list_fiber_zones").removeClass('d-none');
      $('.preloader').show();
      params={
          product_id:object.id
      };
      request ('api/products/get-article-fiber-product', 'POST', params,
              function (res) {
                  if ( res.success ) {
                    $.each(res.data, function (i, item) {
                        $('#fiber_zone option[value="'+item.fiber_zone_id+'"]').prop('disabled',true);
                        addProductCard(item.fiber_zone_id,item.fiber_zone_name,item.product_fz_pk,item.product_fz_name);
                    });

                  }
                  else{
                    alert(res.msg)
                    console.log(res.msg);
                  }
                  $('.preloader').hide();
              },
              function (res) {
                  alert('Hubo un error consultando articulos de fibra en zona');
                  console.log("error");
                  $('.preloader').hide();
            });
    }
    $('#status').val(object.status);
    $('#price_ref').val(object.price_ref);
    $('#product_form').attr('action', 'api/products/'.concat(object.id));
    $('#product_form').attr('method', 'PUT');
  } else {
    $('#artic_id').val('0');
    $('h4.modal-title').text('Crear Producto');
    $('#provider_dni').val('');
    $('#category_id').val('');
    $('#title').val('');
    $('#description').val('');
    $('#brand').val('');
    $('#model').val('');
    //$('#type_barcode').val('');
    $('#sku').val('');
    $('#artic_type').val('H');
    $("#list_fiber_zones").addClass('d-none');
    $("#fiber_zone").val('');
    $("#fiber_article").val('');
    $('#prod-fiber-zone').val('');
    $('#prod-fiber-zone-container').html('');
    $('#fiber_zone option').prop('disabled',false);
    $('#status').val('A');
    $('#price_ref').val('');
    $('#product_form').attr('action', 'api/products/store');
    $('#product_form').attr('method', 'POST');
  }
}
$('#myModal').on('hide.bs.modal', function() {
  setModal(null);
});
/*
$('button[type="submit"]').attr('disabled','disabled');
$('input').blur(function() {
    if(
        ($('input[name=title]').val().length != 0) &&
        ($('input[name=description]').val().length != 0) &&
        ($('input[name=type_barcode]').val().length != 0)
        ){

        $('button[type="submit"]').removeAttr('disabled');
    }
});
*/

function closeProductCard(idza){

    if($("#alert-"+idza).length > 0){
      $("#alert-"+idza).alert('close');
      let fiberzoneelems = $('#prod-fiber-zone').val().split(',').filter((item) => item !== idza);
      fiberzoneelems = fiberzoneelems.join();
      $('#prod-fiber-zone').val(fiberzoneelems);
      idzal = atob(idza).split('-');
      zoneid = idzal[0];
      $('#fiber_zone option[value="'+zoneid+'"]').prop('disabled',false);
    }
}

function addProductCard(zoneid,zonename,articleid,articlename){

  let idza=btoa(zoneid+'-'+articleid).replaceAll('=','').trim();

  let html = '<div class="col-md-3 mb-2 p-0 alert alert-dismissible fade show" role="alert" id="alert-'+idza+'">';
  html += '<div class="mx-md-2 my-0 px-4 py-3 alert-personal" >';
  html += '<strong>Zona: </strong>'+zonename;
  html += '<br>';
  html += '<strong>Articulo: </strong>'+articlename;
  html += '<button type="button" class="close" onclick="closeProductCard(\''+idza+'\')" aria-label="Close">';
  html += '<span aria-hidden="true">×</span>';
  html += '</button>';
  html += '</div>';
  html += '</div>';

  $('#prod-fiber-zone-container').append(html);

  fiberzoneelems=[];
  if($('#prod-fiber-zone').val().length > 0)
    fiberzoneelems = $('#prod-fiber-zone').val().split(',');

  fiberzoneelems.push(idza);

  fiberzoneelems = fiberzoneelems.join();

  $('#prod-fiber-zone').val(fiberzoneelems);

}


$(document).ready(function() {
  $(".preloader").fadeOut();
  //$('#provider_dni').selectize();
  //$('#category_id').selectize();
  $("#artic_type").on("change", function() {
    var field = $(this).val();
    if (field == 'F') {
      $("#list_fiber_zones").removeClass('d-none');
    } else {
      $("#list_fiber_zones").addClass('d-none');
      $("#fiber_zone").val('');
    }
  });

  $("#fiber_zone").on("change", function() {

    if($('#fiber_zone').val() != "" && $('#fiber_zone').val() != "0"){
      $('.preloader').show();
      params={
          fiber_zone_id:$('#fiber_zone').val()
      };

      request ('api/products/get-fiber-products-list', 'POST', params,
              function (res) {
                  $("#fiber_article option[value!='']").remove();
                  if ( res.success ) {
                    $.each(res.data, function (i, item) {
                        $('#fiber_article').append($('<option>', {
                            value: item.id,
                            text : item.title,
                            disabled : item.art_asociate != null ? ( item.art_asociate != $('#artic_id').val() ? true : null) : null
                        }));
                    });

                  }
                  else{
                    alert(res.msg)
                    console.log(res.msg);
                  }
                  $('.preloader').hide();
              },
              function (res) {
                  alert('Hubo un error consultando articulos de fibra en zona');
                  console.log("error");
                  $('.preloader').hide();
            });
    }
    else{
      $("#fiber_article option[value!='']").remove();
    }

  });

  $("#addArtBtn").on("click",() => {

    $("#addArtBtn").data('val',0);

    valzone=$("#fiber_zone").valid();
    valarti=$("#fiber_article").valid();

    if(valzone && valarti){
      let zoneid = $('#fiber_zone option:selected').val();
      let articleid = $('#fiber_article option:selected').val();
      let zonename = $('#fiber_zone option:selected').text().replace(/\n/g,'').trim();
      let articlename = $('#fiber_article option:selected').text().replace(/\n/g,'').trim();

      addProductCard(zoneid,zonename,articleid,articlename);

      $("#fiber_article option[value!='']").remove();
      $('#fiber_zone option:selected').prop('disabled',true);
      $('#fiber_zone').val('');

    }

    $('select#fiber_zone').focus();
    $('select#fiber_zone').select();

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
    order: false,
  });

  jQuery.validator.addMethod("select_zonFibra", function(value, element) {
    if($('#addArtBtn').data('val') === 0){
      if ($("#artic_type").val() === 'F') {
        if ($("#fiber_zone").val() === "") {
          return false;
        }
      }
    }
    return true;
  }, "Por Favor agrege una relacion de producto de zona de fibra");

  jQuery.validator.addMethod("select_artFibra", function(value, element) {
    if($('#addArtBtn').data('val') === 0){
      if ($("#artic_type").val() === 'F') {
        if ($("#fiber_article").val() === "") {
          return false;
        }
      }
    }
    return true;
  }, "Por Favor agrege una relacion de producto de zona de fibra");

  $('#product_form').validate({
    rules: {
      title: {
        required: true
      },
      description: {
        required: true
      },
      price_ref: {
        required: true,
        number: true
      },
      fiber_zone: {
        select_zonFibra: true
      },
      fiber_article: {
        select_artFibra: true
      },
      sku: {
        remote: {
          headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "api/products/is-unique-sku",
            type: "post",
            data: {
              sku: function() {
                return $("#sku").val();
              },
              id: function() {
                return $('#artic_id').val();
              },
          }
        }
      }
      /*type_barcode: {
          required: true
      }*/
    },
    messages: {
      title: "Por favor especifique el titulo del producto",
      description: "Por Favor especifique la descripción del producto",
      price_ref: "Por Favor especifique el precio referencial del producto",
      select_zonFibra: "Por Favor agrege una relacion de producto de zona de fibra",
      sku: "SKU indicado ya se encuentra en uso"
      //type_barcode: "Por Favor indique el tipo de codigo de barra"
    }
  });
  $("#open_modal_btn").on('click', () => {
    $("#myModal").modal();
  });
});