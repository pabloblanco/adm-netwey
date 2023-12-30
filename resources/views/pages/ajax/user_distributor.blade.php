@php
  $view = false;
  $create = false;
  $modify = false;
  $delete = false;

  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'DDU-RDU' && $policy->value > 0) {
      $view = true;
    }
    if ($policy->code == 'DDU-CDU' && $policy->value > 0) {
      $create = true;
    }
    if ($policy->code == 'DDU-UDU' && $policy->value > 0) {
      $modify = true;
    }
    if ($policy->code == 'DDU-DDU' && $policy->value > 0) {
      $delete = true;
    }
  }
@endphp

<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Distribuidores de Usuarios</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li><a href="/islim/">Dashboard</a></li>
        <li class="active">Usuarios</li>
      </ol>
    </div>
  </div>
</div>

<div class="container">
  @if ($create)
  <div class="row">
    <button type="button" id="add-distributor" onclick="create()" class="btn btn-info btn-lg">
      Agregar
    </button>
  </div>
  @endif
  <div class="row white-box mt-5 mb-5">
    <div class="table-responsive">
      <table id="myTable" class="table table-striped">
        <thead>
          <tr>
            @if ($modify || $delete)<th>Acciones</th>@endif
            <th>Descripción</th>
            <th>Fecha de Creación</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<div class="modal modalAnimate" id="myModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h5 class="modal-title" id="modal-title"></h5>
      </div>
      <div class="modal-body">
        <form id="form-distributor">
          {{ csrf_field() }}
          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label for="new_amount">Descripción: </label>
                <input type="text" id="distributor_description" name="distributor_description" class="form-control text-center">
              </div>
            </div>
          </div>
          <div class="form-group">
            <input type="hidden" name="distributor_id" id="distributor_id">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" id="submit" class="btn btn-success">Guardar</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript" defer>

  const baseUrl = "@php echo url('/') @endphp";
  let url = `${baseUrl}/api/user/distributor`;
  var action = '';

  $(document).ready(function () {

    $.ajaxSetup({
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    })

    // Se definen las columnas por defecto de la tabla
    let static_amount;
    let columnsDataTable = [
      {
        name: 'Descripcion', data: 'description', searchable: false, orderable: false
      },{ 
        name: 'Fecha de Creación', data: 'date_reg', searchable: false, orderable: false 
      }
    ]

    if ({{json_encode($modify)}} || {{json_encode($delete)}}) {
      columnsDataTable.unshift({
        data: '', render: function (data, type, row) {
            var html = '<div class="d-flex flex-column">';

             if({{json_encode($modify)}}){
              html += `<button type="button" class="btn btn-warning btn-md" onclick="update(${row.id}, '${row.description}')">Editar</button>`;
             }

             if({{json_encode($delete)}}){
              html += `<div class="d-flex flex-column"><button type="button" class="btn btn-danger btn-md" onclick="if (confirm('¿Seguro desea Eliminar este Registro?')) deleted(${row.id})">Eliminar</button>`;
             }
              
             return html + '</div>';
          
        }, searchable: false, orderable: false
      })
    }

    // Se inicializa la tabla con el plugin DataTable
    const $dataTableFees = $('#myTable').DataTable({
      deferRender: true,
      paging: false,
      procesing: true,
      search: false,
      serverSide: true,
      ajax: {
        url: url + '/datatable',
        data: function (d) {
          d._token = $('meta[name="csrf-token"]').attr('content');
        },
        type: "GET"
      },
      columns: columnsDataTable
    })
    // Se inicializa las validaciones para el formulario
    $('form#form-distributor').validate({
      rules: {
        distributor_description: {
          required: true
        }
      },
      messages: {
        distributor_description: 'Por favor ingrese la Descripción o Nombre del Distribuidor.',
      }
    })
    
    //Listo-------------------------------------------------------------

    // Se muestra la ventana modal para el registro de una nueva tarifa
    // y se llena el elemento select para anidar nuevos registros
    update = async (id = null, description = null) => {

      if(id != null){

        action = 'update';
        $('#modal-title').text('Actualizar Distribuidor');
        $('#distributor_id').val(id)
        $('#distributor_description').val(description)
        $('div#myModal').modal()
      }

    }

    create = async () => {


      action = 'create';
      $('#modal-title').text('Crear Distribuidor');
      $('#distributor_id').val('')
      $('#distributor_description').val('')
      $('div#myModal').modal()
    }

    deleted = async (id = null) => {

      if(id != null){

        $.ajax({
          url: url + '/delete/' + id,
          type: 'delete',
          processData: false,
          contentType: false,
          success: (res) => {

            if(res.success){

              swal('El Distribuidor se ha Eliminado Correctamente.', { icon: 'success' })
              $dataTableFees.draw()
            }else{
              
                swal('Ha ocurrido un Error al intentar Eliminar.', { icon: 'warning' })

            }
            $('.preloader').fadeOut()

          },
          error: (err) => {
            let message = 'Ocurrió un error al intentar guardar el Cambio.'
            if (err.responseJSON.message) {
              message = err.responseJSON.message
            }
            $('.preloader').fadeOut()
            swal(message, { icon: 'error' })
          }
        })
      }
    }

    // Guardar información de la tarifa
    $('button#submit').on('click', () => {

      if (!$('form#form-distributor').valid()) {
        return
      }
      
      $('.preloader').fadeIn()
      var new_url = url;
      if(action == 'update')
        new_url += '/update';

      if(action == 'create')
        new_url += '/store';

      $.ajax({
        url: new_url,
        type: 'post',
        data: new FormData($('form#form-distributor')[0]),
        processData: false,
        contentType: false,
        success: (res) => {

          if(res.success){
            $('div#myModal').modal('hide')
            if(action == 'create')
              swal('El Distribuidor se ha Creado Correctamente.', { icon: 'success' })

            if(action == 'update')
              swal('El Distribuidor se ha Actualizado Correctamente.', { icon: 'success' })
            $dataTableFees.draw()
          }else{
            if(action == 'create')
              swal('Ha ocurrido un Error al intentar Crear.', { icon: 'warning' })

            if(action == 'update')
              swal('Ha ocurrido un Error al intentar Actualizar.', { icon: 'warning' })

          }
          $('.preloader').fadeOut()

        },
        error: (err) => {
          let message = 'Ocurrió un error al intentar guardar el Cambio.'
          if (err.responseJSON.message) {
            message = err.responseJSON.message
          }
          $('.preloader').fadeOut()
          swal(message, { icon: 'error' })
        }
      })
    })
  })
</script>
