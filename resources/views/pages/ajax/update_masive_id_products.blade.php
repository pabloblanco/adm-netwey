<style>
    #table-detail {
        display: none;
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Actualizacion de Ids de Productos</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Inventario</a></li>
                <li class="active">Actualizaciond e Ids</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
  <section class="m-t-40">
    <div class="white-box">
      <div class="row">
        <form id="files_form"  enctype="multipart/form-data">
            <div class="form-group col-md-12">
              <label for="ids_products">Cargar Archivo</label>
              <input type="file" class="form-control-file" id="ids_products" name="ids_products">
            </div>

            <div class="form-group col-md-12">
              <button type="button" class="btn btn-success" id="uploadFile">Enviar</button>
            </div>
          </form>
      </div>
    </div>
  </section>
</div>

<div class="container">
  <section class="m-t-40">
    <div class="white-box">
      <div class="row">
        <table class="table table-condensed" id="table_actions_update">
          <thead>
            <th>MSISDN</th>
            <th>ID</th>
            <th>Accion</th>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>

<script type="text/javascript">

    var data = {};

    function sendFiles(req_leave_id) {


    }

    function clearValues() {
        $('#ids_products').val("");
    }

    $(document).ready(function () {
        $('#uploadFile').on('click', function() {
          $(".preloader").fadeIn();

          let formData = new FormData();  
          var file = document.getElementById('ids_products').files[0];

          if ( file ) {
              formData.append('ids_products', document.getElementById('ids_products').files[0]);
          }

           $.ajax({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              type: "POST",
              url: "{{route('updateIdsProductsAction')}}",
              data: formData,
              dataType: "JSON",
              processData: false,
              contentType: false,
              success: function(response){

                if ( response.data.length > 0 ) {
                  response.data.forEach(function(item) {
                    let tr = '';

                    if ( item.error == 0 ) {
                      tr = `<tr class="success">
                        <td>${item.row.msisdn}</td>
                        <td>${item.row.id}</td>
                        <td>${item.msg}</td>
                      </tr>`;
                    }
                    else {
                      tr = `<tr class="danger">
                        <td>${item.row.msisdn}</td>
                        <td>${item.row.id}</td>
                        <td>${item.msg}</td>
                      </tr>`
                    }

                    $('#table_actions_update tbody').append(tr);
                  });
                }

                $(".preloader").fadeOut();
                clearValues();
                swal('Bien','Actualizacion de ids completado.!','success');
              },
              error: function(err){

                $(".preloader").fadeOut();
                clearValues();
                swal('Error','Problemas con la actualizacion de ids.','error');

              }
          });      
        })
    });
</script>