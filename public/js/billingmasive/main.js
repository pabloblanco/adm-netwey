$(document).ready(function() {
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


  drawTable = function(id_file){
    if ($.fn.DataTable.isDataTable('#myTable')){
      $('#myTable').DataTable().destroy();
    }

    ordercol=1;
    columnss = [];

    columnss.push(
      { data: null, render: function(data,type,row,meta){
          html = '';
          if(row.action){
            if(row.action == 'U'){
              html = html + '<div class="bg-info text-white text-center p-2" style="font-weight:500">ACTUALIZAR</div>';
            }
            if(row.action == 'C'){
              html = html + '<div class="bg-success text-white text-center p-2" style="font-weight:500">CREAR</div>';
            }
          }
          return html;
      }, searchable: false, orderable: false}
    );

    columnss.push({data: 'action', "visible": false});
    columnss.push({data: 'place'});
    columnss.push({data: 'date_expired'});
    columnss.push({data: 'term'});
    columnss.push({data: 'oxxo_folio_date'});
    columnss.push({data: 'oxxo_folio_id'});
    columnss.push({data: 'oxxo_folio_nro'});
    columnss.push({data: 'date_pay'});
    columnss.push({data: 'doc_pay'});
    columnss.push({data: 'status_pay'});
    columnss.push({data: 'sub_total'});
    columnss.push({data: 'tax'});
    columnss.push({data: 'total'});
    columnss.push({data: 'pay_type'});
    columnss.push({data: 'mk_serie'});
    columnss.push({data: 'mk_folio'});

    $('.preloader').show();

    $('#myTable').DataTable({
      searching: false,
      processing: true,
      serverSide: true,
      ajax: {
        url: 'api/billingmasive/file-details/list-dt',
        data: function (d) {
          d._token = $('meta[name="csrf-token"]').attr('content');
          d.id = id_file;
        },
        type: "POST"
      },
      initComplete: function(settings, json){
        //cantrows()
        if($('#myTable').DataTable().rows().count() == 0){
          if ($.fn.DataTable.isDataTable('#myTable')){
            $('#myTable').DataTable().destroy();
          }
          $('.data-table-container').addClass('d-none');
          $('#process-file').data('id','');

          swal("El archivo fue analizado con exito, pero no se encontró en él datos para crear registros nuevos o actualizar lo ya existentes", {
            icon: "warning",
          });
        }
        else{
          $(".preloader").fadeOut();
        }
      },
      order: [[ ordercol, "desc" ]],
      deferRender: true,
      columns: columnss
    });
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
      url: 'api/billingmasive/file-details/store-csv',
      method: 'POST',
      data: params,
      success: function(res) {
        $(".preloader").fadeOut();
        if (res.success) {
          $('.data-table-container').removeClass('d-none');
          $('#process-file').data('id',res.id_file);
          drawTable(res.id_file);
        } else {
          swal(res.msg, {
            icon: "warning",
          });
        }
        $('input#csv').val("");
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

  $('#process-file').on('click',function(){

    swal({
      title: "Atención!",
      text: "¿Confirma que la data suministrada es correcta y que deseas procesar el archivo?",
      icon: "warning",
      buttons: ["Cancelar", "Continuar"],
    })
    .then((yes) => {
      if (yes) {
        id = $('#process-file').data('id');
        $('.preloader').show();

        $.ajax({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          async: true,
          url: "api/billingmasive/file-details/process",
          method: "POST",
          data: {
            'id': id
          },
          dataType: "json",
          success: function(res) {
            $(".preloader").fadeOut();
            if (res.success) {

              if ($.fn.DataTable.isDataTable('#myTable')){
                $('#myTable').DataTable().destroy();
              }
              $('.data-table-container').addClass('d-none');
              $('#process-file').data('id','');

              swal("El archivo fue procesado, en breve se realizará la facturación de aquellos registros que cumplan con las condiciones para ser facturados, la relacion de facturas será enviada por correo", {
                icon: "success",
              });
            } else {
              swal(res.msg, {
                icon: "warning",
              });
            }
          },
          error: function(res) {
            $(".preloader").fadeOut();
            console.log(res);
          }
        });
      } else {
        return;
      }
    });
  });
});