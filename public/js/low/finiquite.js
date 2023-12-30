/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Marzo 2022,
 */
function pushFile() {
  filevalid = validatFiles('file_csv', 'error_status_file', 'Debe seleccionar el archivo CSV que contenga: email, liquidacion, fecha y descuento', 'El archivo debe ser de extensión CSV');
  if (filevalid) {
    savefile();
  }
}

function savefile() {
  var params = new FormData();
  file = document.getElementById('file_csv').files[0];
  params.append('file_csv', file);
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
    url: 'view/low/setUploadFiniquite',
    method: 'POST',
    data: params,
    success: function(res) {
      $(".preloader").fadeOut();
      $('#file_csv').html('');
      if (res.success) {
        // console.log(response);
        //alert(response);
        swal({
          title: "Procesado el archivo!",
          text: res.msg + '\n' + res.EmailNoprocess,
          dangerMode: true,
          closeOnClickOutside: false
        });
        search_listLowProcess();
      } else {
        swal({
          text: res.msg,
          dangerMode: true,
          closeOnClickOutside: false
        });
      }
    },
    error: function(res) {
      $(".preloader").fadeOut();
      console.log(res);
    }
  });
}