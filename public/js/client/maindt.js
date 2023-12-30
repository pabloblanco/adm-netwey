$(document).ready(function() {
  $(".preloader").fadeOut();
  $('#clienteTable').DataTable({
    "columnDefs": [{
      "targets": 0,
      "orderable": false
    }],
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
    processing: true,
    serverSide: true,
    ajax: {
      url: "api/clients/datatable/get-dn",
      data: function(d) {
        d._token = $('meta[name="csrf-token"]').attr('content');
        d.msisdns = msisdns;
        d.dnis = dnis;
      },
      type: "POST"
    },
    //ajax: 'api/clients/datatable/['+msisdns+']',
    columns: [{
      data: null,
      render: function(data, type, row, meta) {
        var html = '';
        // console.log('row: ', row);
        html = '<button type="button" class="btn btn-info btn-md button d-block" onclick="detail(\'' + JSON.stringify(row).replace(/\\r\\n+|\\r+|\\n+|\\t+/g, " ").replace(/"/g, '\\\'') + '\')">ver</button>';
        if (row.canEdit) html = html + '<button type="button" class="btn btn-warning btn-md button d-block" onclick="editClient(\'' + JSON.stringify(row).replace(/\\r\\n+|\\r+|\\n+|\\t+/g, " ").replace(/"/g, '\\\'') + '\')">Editar</button>';
        return html;
      }
    }, {
      data: 'name'
    }, {
      data: 'email'
    }, {
      data: 'phone_home'
    }, {
      data: 'phone_2'
    }, {
      data: 'msisdn'
    }, {
      data: 'dn_type_l'
    }, {
      data: 'serviceability'
    }, {
      data: 'plan'
    }, {
      data: 'lat'
    }, {
      data: 'lng'
    }, {
      data: 'status'
    }, ]
  });
});