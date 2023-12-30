/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Abril 2022,
 */
function search_listScheme() {
  $('.preloader').show();
  if ($.fn.DataTable.isDataTable('#list-com')) {
    $('#list-com').DataTable().destroy();
  }
  $('#list-com').DataTable({
    searching: true,
    processing: true,
    serverSide: true,
    ajax: {
      url: 'api/user/schemeDt',
      data: function(d) {
        d._token = $('meta[name="csrf-token"]').attr('content');
        d.type = $('#type').val();
        d.nameScheme = $('#nameScheme').val();
      },
      type: "POST"
    },
    initComplete: function(settings, json) {
      $(".preloader").fadeOut();
      $('#rep-sc').attr('hidden', null);
    },
    deferRender: true,
    "order": [
      [2, "asc"]
    ],
    ordering: true,
    columns: [{
      data: 'id',
      searchable: false,
      orderable: false,
      render: function(data, type, row, meta) {
        var html = '';
        //Boton de acciones
        html += '<button title="Editar" name="btn-edit-' + data + '\"  type="button" class="btn btn-primary btn-md button d-block" onclick="editItem(\'' + row.id + '\', \'' + row.nameScheme + '\')" > Editar </button>';
        html += '<button title="Eliminar" name="btn-delete-' + data + '\"  type="button" class="btn btn-danger btn-md button d-block" onclick="deleteItem(\'' + row.id + '\', \'' + row.nameScheme + '\')"> Eliminar </button>';
        return html;
      }
    }, {
      data: 'type',
      searchable: false,
      orderable: true
    }, {
      data: 'nameScheme',
      searchable: true,
      orderable: true
    }, {
      data: 'responsable',
      searchable: true,
      orderable: true
    }],
    "language": {
      "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
    }
  });
}

function editItem(id, name) {
  swal({
    title: "Por favor ingresa el nuevo nombre",
    text: "Nombre actual: " + name,
    icon: "info",
    content: {
      element: "input",
      attributes: {
        name: "keySecure",
        id: "keySecure",
        placeholder: "Escribe el nuevo nombre ",
        type: "text",
      },
    },
    buttons: {
      accept: {
        text: "Actualizar",
        value: 'ok',
        visible: true,
        className: "btn btn-primary",
        closeModal: true,
        botonesStyling: false
      },
      ignorar: {
        text: "Cancelar",
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
      if ($('.swal-content #keySecure').val() != '') {
        sendEditItem(id, $('.swal-content #keySecure').val());
      } else {
        swal("Debes escribir un nombre por el cual se deba actualizar " + name, {
          icon: "warning",
        });
      }
    }
  });
}

function sendEditItem(id, newName) {
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    url: 'api/user/scheme/edit',
    type: 'POST',
    dataType: 'json',
    data: {
      id: id,
      Newname: newName,
    },
    error: function() {
      swal({
        title: "Hubo un problema en editar el registro " + name,
        text: null,
        icon: "warning",
      });
    },
    success: function(res) {
      if (res.success) {
        swal({
          title: "Actualizacion exitosa",
          text: null,
          icon: "success",
        });
        getview('user/scheme');
        search_listScheme();
      } else {
        swal({
          title: "Hubo un problema",
          text: res.msg,
          icon: "warning",
        });
      }
    }
  });
}

function deleteItem(id, name) {
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    url: 'api/user/scheme/delete',
    type: 'POST',
    dataType: 'json',
    data: {
      id: id,
      removeName: name,
    },
    error: function() {
      swal({
        title: "Hubo un problema en eliminar el registro " + name,
        text: null,
        icon: "warning",
      });
    },
    success: function(res) {
      if (res.success) {
        swal({
          title: "Eliminacion exitosa de " + name,
          text: null,
          icon: "success",
        });
        search_listScheme();
        getview('user/scheme');
      } else {
        swal({
          title: "Hubo un problema",
          text: res.msg,
          icon: "warning",
        });
      }
    }
  });
}

function NewItem() {
  $('.preloader').show();
  $(".preloader").fadeOut();
  $('#myModal').modal({
    backdrop: 'static',
    keyboard: false
  });
}
$(document).ready(function() {
  SearchScheme = function(query, callback) {
    if (!query.length) return callback();
    $.ajax({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      url: 'api/user/get_filter_scheme',
      type: 'POST',
      dataType: 'json',
      data: {
        name: query
      },
      error: function() {
        callback();
      },
      success: function(res) {
        if (res.success) callback(res.schemes);
        else callback();
      }
    });
  }
  var configSelect = {
    valueField: 'id',
    labelField: 'NameLabelScheme',
    searchField: 'nameScheme',
    options: [],
    create: false,
    persist: false,
    render: {
      option: function(item, escape) {
        return '<p>' + escape(item.nameScheme.toLocaleUpperCase()) + ' ( ' + escape(item.type.toLocaleUpperCase() + ' )') + '</p>';
      }
    }
  };
  configSelect.load = SearchScheme;
  $('#nameScheme').selectize(configSelect);
  //creacion new item
  $("#typeCreate").change(function(e) {
    e.preventDefault();
    $('.preloader').show();
    $.ajax({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      url: 'api/user/scheme/formCreate',
      type: 'POST',
      dataType: 'json',
      data: {
        typeCreate: $(this).val()
      },
      error: function() {
        $(".preloader").fadeOut();
      },
      success: function(res) {
        $(".preloader").fadeOut();
        if (res.success) {
          $('#blockNew').html(res.msg);
        }
      }
    });
  });
  //Botones
  $('#search').on('click', function(e) {
    search_listScheme();
  });
  $('#open_modal_btn').on('click', function(e) {
    NewItem();
  });
});