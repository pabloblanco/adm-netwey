@php
  $view = false;
  $modify = false;

  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'DMF-RDM' && $policy->value > 0) {
      $view = true;
    }
    if ($policy->code == 'DMF-UDM' && $policy->value > 0) {
      $modify = true;
    }
  }
@endphp

<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Descuentos Metodos de Pago</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li><a href="/islim/">Dashboard</a></li>
        <li class="active">Gestion Web</li>
      </ol>
    </div>
  </div>
</div>

<div class="container">
  <div class="row white-box mt-5 mb-5">
    <div class="table-responsive">
      <table id="myTable" class="table table-striped">
        <thead>
          <tr>
            @if ($modify)<th>Acciones</th>@endif
            <th>Metodo</th>
            <th>Monto</th>
            <th>Fecha Ultima Modificación</th>
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
        <h5 class="modal-title" id="exampleModalLabel">Actualizar Descuento</h5>
      </div>
      <div class="modal-body">
        <form id="form-discount">
          {{ csrf_field() }}
          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label for="new_amount">Metodo: <strong id="modal_method"></strong></label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-6">
              <div class="form-group">
                <label for="new_amount">Monto Anterior</label>
                <input type="number" id="last_amount" class="form-control text-center" value="" readonly>
              </div>
            </div>
            <div class="col-6">
              <div class="form-group">
                <label for="new_amount">Monto Nuevo</label>
                <input type="number" name="new_amount" id="new_amount" class="form-control text-center">
              </div>
            </div>
          </div>
          <div class="form-group">
              <input type="hidden" name="method_id" id="method_id">
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
  let url = `${baseUrl}/api/web-management/financing-methods`;

  $(document).ready(function () {

    $.ajaxSetup({
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    })

    // Se definen las columnas por defecto de la tabla
    let static_amount;
    let columnsDataTable = [
      { 
        name: 'Metodo', data: 'method', searchable: true, orderable: false 
      },{
        name: 'Monto', data: 'discount', searchable: false, orderable: false, render: function(data, type, row){
          return row.discount+" &#36;"
        }
      },{ 
        name: 'Fecha Ultima Modificación', data: 'date_modif', searchable: false, orderable: false 
      }
    ]

    if ({{json_encode($modify)}}) {
      columnsDataTable.unshift({
        data: '', render: function (data, type, row) {
            return `<div class="d-flex flex-column"><button type="button" class="btn btn-warning btn-md" onclick="update(${row.id}, '${row.method}', ${row.discount})">Editar</button></div>`
          
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
        url: url,
        data: function (d) {
          d._token = $('meta[name="csrf-token"]').attr('content');
        },
        type: "GET"
      },
      columns: columnsDataTable
    })
    // Se inicializa las validaciones para el formulario
    $('form#form-discount').validate({
      rules: {
        new_amount: {
          required: true
        }
      },
      messages: {
        new_amount: 'Por favor ingrese un monto valido de Descuento.',
      }
    })
    
    //Listo-------------------------------------------------------------

    // Se muestra la ventana modal para el registro de una nueva tarifa
    // y se llena el elemento select para anidar nuevos registros
    update = async (id = null, name = null, amount = null) => {

      if(id != null){

        $('#new_amount').val('')

        $('#modal_method').text(name)
        $('#method_id').val(id)
        $('#last_amount').val(amount)
        $('div#myModal').modal()
      }

    }

    // Guardar información de la tarifa
    $('button#submit').on('click', () => {

      if (!$('form#form-discount').valid()) {
        return
      }
      
      $('.preloader').fadeIn()
      let type = 'post';
      $.ajax({
        url: url,
        type: 'post',
        data: new FormData($('form#form-discount')[0]),
        processData: false,
        contentType: false,
        success: (res) => {

          if(res.success){
            $('div#myModal').modal('hide')
            swal('El Descuento se ha Actualizado Correctamente.', { icon: 'success' })
            $dataTableFees.draw()
          }else{
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
