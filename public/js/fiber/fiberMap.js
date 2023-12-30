function search_MapFiber() {
  $('.preloader').show();
  if ($.fn.DataTable.isDataTable('#list-com')) {
    $('#list-com').DataTable().destroy();
  }
  //Columnas del reporte
  let columnsDataTable = [];
  botones = {
    data: 'id',
    searchable: false,
    orderable: false,
    render: function(data, type, row) {
      let html = '<div class="d-flex flex-column">';
      if (row.status === "Activo") {
        if (row.poligono.poligono !== undefined) {
          if (row.poligono.poligono.length > 0) {
            $etiqueta = 'Ver mapa';
            $colorBTN = "btn-primary";
          }
        } else {
          $etiqueta = 'Cargar mapa';
          $colorBTN = "btn-warning";
        }
        html += `<button id="btnmap` + row.id + `" type="button" class="btn ` + $colorBTN + ` btn-md" onclick="viewMap(${row.id})" > ` + $etiqueta + ` </button>`;
        html += `<button id="btnStatu` + row.id + `" type="button" class="btn btn-danger btn-md" onclick="OffMap(${row.id},false)" > Desactivar </button>`;
      } else {
        html += `<button id="btnStatu` + row.id + `" type="button" class="btn btn-danger btn-md" onclick="OffMap(${row.id},true)" > Activar </button>`;
      }
      html += '</div>';
      return html;
    }
  };
  columnsDataTable.push(botones);
  columnsDataTable.push({
    name: 'olt',
    data: 'olt',
    searchable: true,
    orderable: true
  });
  columnsDataTable.push({
    name: 'city',
    data: 'city',
    searchable: false,
    orderable: false
  });
  columnsDataTable.push({
    name: 'zoom',
    data: "poligono.zoom",
    searchable: false,
    orderable: false
  });
  columnsDataTable.push({
    name: 'status',
    data: "status",
    searchable: false,
    orderable: true
  });
  $('#list-com').DataTable({
    deferRender: true,
    paging: false,
    procesing: true,
    search: true,
    serverSide: true,
    ajax: {
      url: "view/fiber/listViewMap",
      data: function(d) {},
      type: "POST"
    },
    initComplete: function(settings, json) {
      $(".preloader").fadeOut();
      $('#rep-sc').attr('hidden', null);
    },
    columns: columnsDataTable,
    "language": {
      "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
    }
  });
}

function viewMap(id) {
  $(".preloader").fadeIn();
  $.ajax({
    type: "POST",
    url: "view/fiber/viewMap",
    data: {
      _token: $('meta[name="csrf-token"]').attr('content'),
      id: id
    },
    dataType: "json",
    success: function(response) {
      if (response.success) {
        $('#infoMap').html(response.html);
        if (response.pointCenter.length < 1) {
          $('#map-content').css('filter', 'blur(6px)');
          $('#NoMap').removeClass('d-none');
          $('#map').addClass('text-center');
        } else {
          $('#map-content').css('filter', 'none');
          $('#NoMap').addClass('d-none');
          $('#map').removeClass('text-center');
        }
        initPlaces(response.poligono, response.pointCenter);
      }
      $('div#myModal').modal();
      $('.preloader').fadeOut();
    },
    error: function(err) {
      console.log("error en obtener el mapa de cobertura de fibra ", err);
      $(".preloader").fadeOut();
    }
  });
}

function OffMap(id, type) {
  if (type) {
    type = 'A';
    //console.log("Activo item");
  } else {
    type = 'I';
    //console.log("Apago item");
  }
  $(".preloader").fadeIn();
  $.ajax({
    type: "POST",
    url: "view/fiber/updateItemMap",
    data: {
      _token: $('meta[name="csrf-token"]').attr('content'),
      id: id,
      status: type
    },
    dataType: "json",
    success: function(response) {
      if (response.success) {
        search_MapFiber();
      } else {
        swal(response.msg, {
          icon: "warning",
        });
      }
      $('.preloader').fadeOut();
    },
    error: function(err) {
      console.log("error en actualizar el item de cobertura ", err);
      $(".preloader").fadeOut();
    }
  });
}
$(document).ready(function() {
  // Set default headers
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
  $('#update_zones').on('click', function(e) {
    //815
    $(".preloader").fadeIn();
    $.ajax({
      type: "POST",
      url: "view/fiber/updateListMap",
      data: {
        _token: $('meta[name="csrf-token"]').attr('content')
      },
      dataType: "json",
      success: function(response) {
        if (response.success) {
          search_MapFiber();
        }
        swal(response.msg, {
          icon: response.icon,
        });
        $('.preloader').fadeOut();
      },
      error: function(err) {
        console.log("error en actualizar la lista de cobertura ", err);
        $(".preloader").fadeOut();
      }
    });
  });
  //Al cargar que busque x defecto todo
  search_MapFiber();
});