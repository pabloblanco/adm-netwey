<link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">
<style>
  .level-2 {
    padding-left: 1.4em;
  }
  .select2 {
    width: 100% !important;
  }
  .has-attach-file > a {
    flex: 1;
    font-size: 1.3rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
</style>
@php
  $actions = [];
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'WMT-CRT' && $policy->value > 0) {
      array_push($actions, 'create');
    } else if ($policy->code == 'WMT-URT' && $policy->value > 0) {
      array_push($actions, 'update');
    } else if ($policy->code == 'WMT-DRT' && $policy->value > 0) {
      array_push($actions, 'delete');
    }
  }
@endphp

<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Tarifario</h4>
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
  @if (in_array('create', $actions))
  <div class="row">
    <button type="button" id="add-fee" class="btn btn-info btn-lg">
      Agregar
    </button>
  </div>
  @endif

  <div class="row white-box mt-5 mb-5">
    <div class="table-responsive">
      <table id="myTable" class="table table-striped">
        <thead>
          <tr>
            @if (in_array('update', $actions) || in_array('delete', $actions))<th>Acciones</th>@endif
            <th>Descripción</th>
            <th>Pertenece a</th>
            <th>Fecha</th>
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
        <h5 class="modal-title" id="exampleModalLabel"></h5>
      </div>
      <div class="modal-body">
        <form id="form-rate">
          {{ csrf_field() }}
          <input type="hidden" name="id">
          <input type="hidden" name="url_file">
          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label for="description">Descripción</label>
                <textarea name="description" class="form-control" rows="3" style="line-height: 2.4rem;"></textarea>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-4">
              <div class="form-group">
                <label for="position">Posición</label>
                <input type="number" name="position" class="form-control text-center" value="99">
              </div>
            </div>
            <div class="col-8">
              <div class="h-100 d-flex align-items-center justify-content-center">
                <label class="d-flex align-items-center">
                  <input type="checkbox" name="label" class="mr-3"> ¿Mostrar etiqueta de nuevo?
                </label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label class="control-label" for="child">Pertenece a</label>
                <select id="select-parent" name="parent_id" class="form-control"></select>
              </div>
            </div>
          </div>
          <div id="is-not-new" class="row d-none">
            <div class="col-12">
              <div class="form-group">
                <label for="attach_file">Documento Adjunto</label>
                <input type="file" name="attach_file" class="form-control" accept="application/pdf">
                <div class="has-attach-file d-none align-content-center px-4 py-3" style="border: 1px solid #e4e7ea;">
                  <a href="#" target="_blank" class="mr-3" style="margin-top: .2rem;">
                    <span></span>
                  </a>
                  <button type="button" id="remove-attach-file" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
          <input type="reset" class="hide">
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" id="submit" class="btn btn-primary" data-action="create">Guardar</button>
      </div>
    </div>
  </div>
</div>

<script src="js/select2.min.js"></script>

<script type="text/javascript" defer>
  const baseUrl = "@php echo url('/') @endphp";
  const actions = @php echo json_encode($actions) @endphp;
  $(document).ready(function () {
    // Set default headers
    $.ajaxSetup({
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    })

    // Se definen las columnas por defecto de la tabla
    let columnsDataTable = [
      { name: 'Descripción', data: 'descripcion_web', searchable: false, orderable: false },
      {
        name: 'Pertenece a', data: 'belongs_to', searchable: false, render: (data, type, row) => {
          if (!row.belongs_to) {
            return '-'
          }

          return data
        }
      },
      { name: 'Fecha', data: 'date_reg', searchable: false, orderable: false }
    ]

    // Se valida si se imprimira en pantalla la columna de las acciones
    if (actions.includes('update') || actions.includes('delete')) {
      columnsDataTable.unshift({
        data: 'actions', render: function (data, type, row) {
          let html = '<div class="d-flex flex-column">';
          if (actions.includes('update')) {
            html += `<button type="button" class="btn btn-warning btn-md" onclick="showFee(${row.id})">Editar</button>`
          }
          if (actions.includes('delete')) {
            html += `<button type="button" class="btn btn-danger btn-md" onclick="deleteFee(${row.id}, '${row.descripcion_web}')">Eliminar</button>`
          }
          if (actions.length < 1) {
            html += '-'
          }
          html += '</div>'
          return html;
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
        url: `${baseUrl}/api/web-management/fees`,
        data: function (d) {
          d._token = $('meta[name="csrf-token"]').attr('content');
        },
        type: "GET"
      },
      columns: columnsDataTable
    })

    // Se inicializa las validaciones para el formulario
    $('form#form-rate').validate({
      rules: {
        description: {
          required: true
        },
        attach_file: {
          required: function() {
            const selected = $select2Parent.val()
            if ($(`option[value=${selected}]`).hasClass('level-2') > 0) {
              return true
            }

            return false
          }
        },
        position: {
          required: true,
          min: 1,
          max: 99
        }
      },
      messages: {
        description: 'Por favor ingrese la descripción de la tarifa.',
        position: 'Por favor ingrese un valor mayor que 1.',
        attach_file: 'Por favor adjunte el documento relacionado a la tarifa.'
      }
    })
    
    // Se inicializa el plugin select2
    const $select2Parent = $('select#select-parent')
    $select2Parent.select2()

    // Se muestra la ventana modal para el registro de una nueva tarifa
    // y se llena el elemento select para anidar nuevos registros
    $('button#add-fee').on('click', async () => {
      $('.preloader').fadeIn()

      // Se muestra input file
      $('input[name=attach_file]').removeClass('d-none')
      $('.has-attach-file').removeClass('d-flex').addClass('d-none')

      $('input[name=label]').removeAttr('checked', false)
      $('input[type=reset]').click()
      $('button#submit').attr('data-action', 'create')

      // Se llena el elemento select
      await populateSelect()

      $('h5#exampleModalLabel').text('Crear Tarifa')
      $('div#myModal').modal()
      $('.preloader').fadeOut()
    })

    // Mostrar unicamente el documento adjunto cuando no es un registro nuevo
    $select2Parent.on('change', (e) => {
      if ($select2Parent.val() > 0) {
        $('div#is-not-new').removeClass('d-none')
        return
      }
      $('div#is-not-new').addClass('d-none')
    })

    // Guardar información de la tarifa
    $('button#submit').on('click', () => {
      const actionSubmit = $('button#submit').attr('data-action')
      if (!$('form#form-rate').valid()) {
        return
      }
      
      $('.preloader').fadeIn()
      const formData = new FormData($('form#form-rate')[0])

      // Se evalua si se creara un nuevo recurso o se actualizara uno existente
      if (actionSubmit === 'create') {
        saveFee(formData, `${baseUrl}/api/web-management/fees`)
        return
      }

      // Se actualiza el recurso
      const id = $('input[name=id]').val()
      saveFee(formData, `${baseUrl}/api/web-management/fees/${id}`)
    })

    // Actualiza o crea un nuevo recurso
    saveFee = (data, url = null, type = 'post') => {
      if (!url && data.length < 1) {
        return
      }

      $.ajax({
        url,
        type,
        data,
        processData: false,
        contentType: false,
        success: (res) => {
          $('div#myModal').modal('hide')
          $('input[name=label]').removeAttr('checked', false)
          $('input[type=reset]').click()
          $('.preloader').fadeOut()
          swal('La tarifa se ha actualizado correctamente.', { icon: 'success' })
          $dataTableFees.draw()
        },
        error: (err) => {
          let message = 'Ocurrió un error al intentar guardar el recurso.'
          if (err.responseJSON.message) {
            message = err.responseJSON.message
          }

          $('.preloader').fadeOut()
          swal(message, { icon: 'error' })
        }
      })
    }

    // Se llena el select con las opciones retornadas por ajax
    populateSelect = () => {
      return new Promise(async (resolve) => {
        $select2Parent.select2('destroy')
        $select2Parent.empty()

        // Se obtienes las tarifas mediante ajax
        const fees = await $.ajax({
          url: `${baseUrl}/api/web-management/fees/get-fees-select`,
          contentType: 'application/json',
          type: 'get'
        })

        $.each(fees, (index, obj) => {
          $select2Parent.append($('<option class="level-1" />').val(obj.value).text(obj.name))
          if (Object.keys(obj.l2).length > 0) {
            $.each(obj.l2, ($index, $obj) => {
              $select2Parent.append($('<option class="level-2" />').val($obj.value).text($obj.name))
            })
          }
        })

        // Se aplica 
        $select2Parent.select2({
          templateResult: function (data) {    
            // We only really care if there is an element to pull classes from
            if (!data.element) {
              return data.text
            }

            const $element = $(data.element)
            const $wrapper = $('<span></span>')
            $wrapper.addClass($element[0].className)
            $wrapper.text(data.text)

            return $wrapper
          }
        })

        resolve(true)
      })
    }

    // Se obtiene la información de una tarifa registrada para su edición.
    showFee = async (id = null) => {
      $('.preloader').fadeIn();
      $('input[name=label]').removeAttr('checked', false)
      $('input[type=reset]').click()
      $('button#submit').attr('data-action', 'update')

      // Se llena el elemento select
      await populateSelect()

      // Se obtiene la información del recurso
      await $.ajax({
        url: `${baseUrl}/api/web-management/fees/${id}`,
        success: (res) => {
          const { id, parent_id, descripcion_web, url_file, position, label } = res
          $('input[name=id]').val(id)
          $('textarea[name=description]').val(descripcion_web)
          $('input[name=url_file]').val(url_file)
          $('input[name=position]').val(position)
          if (label) {
            $('input[name=label]').attr('checked', true)
          }

          // Se evalua si existe el documento relacionado
          if (url_file) {
            $('input[name=attach_file]').addClass('d-none')
            $('div.has-attach-file').removeClass('d-none').addClass('d-flex')
            $('div.has-attach-file > a').attr('href', url_file)
            $('div.has-attach-file > a > span').text(url_file)
          } else {
            $('input[name=attach_file]').removeClass('d-none')
            $('.has-attach-file').removeClass('d-flex').addClass('d-none')
          }
          $select2Parent.val(parent_id).trigger('change')
        },
        error: (err) => {
          console.error(err)
          swal('Ocurrió un error al intentar recuperar el listado de tarifas.', { icon: 'error' })
        }
      })

      $('h5#exampleModalLabel').text('Modificar Tarifa')
      $('#myModal').modal()
      $('.preloader').fadeOut()
    }

    // Eliminar el documento adjunto relacionado a la tarifa
    $('button#remove-attach-file').on('click', () => {
      $('div.has-attach-file').removeClass('d-flex').addClass('d-none')
      $('input[name=attach_file]').removeClass('d-none')
    })

    // Eliminar tarifa
    deleteFee = (id = null, string = null) => {
      swal({
        title: 'Eliminar Tarifa',
        text: `Está a pundo de eliminar la tarifa: ${string}`,
        icon: 'warning',
        buttons: true,
        dangerMode: true,
      })
      .then((res) => {
        if (!res) {
          return
        }

        $('.preloader').fadeIn()
        $.ajax({
          url: `${baseUrl}/api/web-management/fees/${id}`,
          type: 'delete',
          success: (res) => {
            $dataTableFees.draw()
            $('#myModal').modal('hide')
            swal('La tarifa se ha eliminado correctamente.', { icon: 'success' })
            $('.preloader').fadeOut()
          },
          error: (err) => {
            console.error(err)
            let message = 'Ocurrió un error al intentar guardar el recurso.'
            if (err.responseJSON.message) {
              message = err.responseJSON.message
            }

            $('.preloader').fadeOut()
            swal(message, { icon: 'error' })
          }
        })
      })
    }
  })
</script>
