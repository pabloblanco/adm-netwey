{{--/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Agosto 2022
 */--}}
@php
  $actions = [];
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'FIB-UPD' && $policy->value > 0) {
      array_push($actions, 'update');
    }elseif ($policy->code == 'FIB-NEW' && $policy->value > 0) {
      array_push($actions, 'create');
    }elseif ($policy->code == 'FIB-DEL' && $policy->value > 0) {
      array_push($actions, 'delete');
    } 
  }
@endphp
<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">
        Listado de Zonas de Fibra
      </h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12 d-flex justify-content-end">
      <ol class="breadcrumb">
        <li>
          <a href="#">
            Gestión de Fibra
          </a>
        </li>
        <li class="active">
          Zonas de Fibra
        </li>
      </ol>
    </div>
  </div>
</div>
<div class="container">
  @if (in_array('create', $actions))
  <div class="row justify-content-center">
    <div class="col-12">
      <button class="btn btn-info btn-lg" id="add_zone" type="button">
        Agregar zona
      </button>
    </div>
  </div>
  @endif
</div>
<div class="container">
  <div class="row justify-content-center">
    <div class="col-12 pb-5">
      <h3>
        Configuración del listado
      </h3>
      <form action="" class="form-horizontal" id="report_tb_form" method="POST" name="report_tb_form">
        {{ csrf_field() }}
        <div class="container">
          <div class="row justify-content-center">
            <div class="pr-md-4 col-md-4 col-12">
              <div class="form-group">
                <label class="control-label">
                  Control de la zona
                </label>
                <select class="form-control" id="owner" name="owner">
                  <option value="">
                    Seleccione una opción
                  </option>
                  <option value="N">
                    Netwey
                  </option>
                  <option value="V">
                    Compartida con Velocom
                  </option>
                </select>
              </div>
            </div>
            <div class="col-12 text-center">
              <button class="btn btn-success" id="search" name="search" type="button">
                <i class="fa fa-check">
                </i>
                Mostrar zonas
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="col-12" hidden="" id="rep-sc">
      <div class="row white-box">
        <div class="col-12">
          <h3 class="text-center">
            Listado de Zonas de Fibra
          </h3>
        </div>
        {{--
        <div class="col-12">
          <button class="btn btn-success m-b-20" id="download" type="button">
            Exportar listado
          </button>
        </div>
        --}}
        <div class="col-12">
          <div class="table-responsive">
            <table class="table table-striped display nowrap" id="list-com">
              <thead>
                <tr>
                  <th class="text-center align-middle">
                    Acciones
                  </th>
                  <th class="text-center align-middle">
                    Nombre de la zona
                  </th>
                  <th class="text-center align-middle">
                    EndPoint
                  </th>
                  @if(in_array('update', $actions) || in_array('delete', $actions))
                  <th class="text-center align-middle">
                    Usuario
                  </th>
                  @endif
                  <th class="text-center align-middle">
                    Nodo de red
                  </th>
                  <th class="text-center align-middle">
                    Modo de conexión
                  </th>
                  <th class="text-center align-middle">
                    Relay
                  </th>
                  <th class="text-center align-middle">
                    Control de la zona
                  </th>
                  <th class="text-center align-middle">
                    Colector
                  </th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal modalAnimate" id="myModal" role="dialog">
  <div class="modal-dialog" id="modal01">
    <div class="modal-content">
      <div class="modal-header">
        <button class="modal_close_btn close" data-modal="#myModal" id="modal_close_x" type="button">
          ×
        </button>
        <h4 class="modal-title" id="modal-title">
        </h4>
      </div>
      <div class="modal-body">
        <form id="form-zone">
          {{ csrf_field() }}
          <input id="id" name="id" type="hidden"/>
          <div class="container">
            <div class="row">
              <h4 class="col-12 box-title">
                Datos de conexión la zona de fibra
              </h4>
              <hr width="80%"/>
              <div class="col-lg-4 col-md-5 col-12">
                <div class="form-group">
                  <label for="nameZone">
                    Nombre de la zona
                  </label>
                  <input autocomplete="false" class="form-control text-left" id="nameZone" name="nameZone" placeholder="Ingrese el nombre de la zona" required="" type="text">
                  </input>
                </div>
              </div>
              <div class="col-lg-8 col-md-7 col-12">
                <div class="form-group">
                  <label for="endpoint">
                    EndPoint de la zona
                  </label>
                  <input autocomplete="false" class="form-control text-left" id="endpoint" name="endpoint" placeholder="https://g1islimtelco.815d.net:815/gateway/integracion/" required="" title="Recuerda: El EndPoint de 815 debe terminar en '/gateway/integracion/' para ser considerada valida" type="text">
                  </input>
                </div>
              </div>
              <div class="col-md-4 col-sm-6 col-12">
                <div class="form-group">
                  <label for="user">
                    Usuario
                  </label>
                  <input autocomplete="false" class="form-control text-left" id="user" name="user" placeholder="Ingrese el usuario" required="" type="text">
                  </input>
                </div>
              </div>
              <div class="col-md-4 col-sm-6 col-12">
                <div class="form-group">
                  <label for="password">
                    Contrasena
                  </label>
                  <input autocomplete="false" class="form-control text-left" id="password" name="password" placeholder="Ingrese la contrasena" required="" type="password">
                  </input>
                </div>
                <input id="password2" name="password2" type="hidden">
                </input>
              </div>
              <div class="col-md-4 col-sm-6 col-12">
                <div class="form-group">
                  <label for="type">
                    Proveedor de Software
                  </label>
                  <select class="form-control" id="type" name="type">
                    <option value="815">
                      815
                    </option>
                  </select>
                </div>
              </div>
              <div class="col-md-4 col-sm-6 col-12">
                <div class="form-group">
                  <label for="nodo">
                    Nodo de red
                  </label>
                  <input autocomplete="false" class="form-control text-left" id="nodo" name="nodo" placeholder="Ingrese nodo de red. Ejemplo: 187" required="true" title="Corresponde al ID con el cual seran conectados los clientes para la asignación de direcciones IP" type="number">
                  </input>
                </div>
              </div>
              <div class="col-md-4 col-sm-6 col-12">
                <div class="form-group">
                  <label for="modo">
                    Modo de red
                  </label>
                  <select class="form-control" id="modo" name="modo" required="true">
                    <option value="dhcp">
                      DHCP
                    </option>
                  </select>
                </div>
              </div>
              <div class="col-md-4 col-sm-6 col-12">
                <div class="form-group">
                  <label for="relay">
                    Relay dhcp
                  </label>
                  <select class="form-control" id="relay" name="relay" required="true">
                    <option value="False">
                      Inactivo
                    </option>
                  </select>
                </div>
              </div>
              <h4 class="col-12 box-title">
                Datos de configuración de la zona de fibra ante Netwey
              </h4>
              <hr width="80%"/>
              <div class="col-lg-6 col-12">
                <div class="form-group">
                  <label for="msg">
                    Mensaje(sms) de agendamiento de cita.
                  </label>
                  <span id="cantLimit">
                    140
                  </span>
                  caracteres
                  <textarea class="form-control" id="msg" name="msg" onkeydown="textMaximo(this);" onkeyup="textMaximo(this);" placeholder="Escriba el mensaje de texto al momento de agendar la cita" required="true" rows="3" style="line-height: 2.4rem;">
                  </textarea>
                </div>
              </div>
              <div class="col-md-6 col-12">
                <div class="form-group">
                  <label for="owner_CU">
                    Control de la zona
                  </label>
                  <select class="form-control" id="owner_CU" name="owner_CU" required="true" title="Corresponde a la forma que sera llevado a cabo la auto-asignación de inventario o dependera que se asigne previamente">
                    <option value="">
                      Seleccione una opción
                    </option>
                    <option value="N">
                      Netwey
                    </option>
                    <option value="V">
                      Compartida con Velocom
                    </option>
                  </select>
                </div>
              </div>
              <div class="col-md-6 col-12">
                <div class="form-group">
                  <label for="collector">
                    Responsable del cobro
                  </label>
                  <select class="form-control" id="collector" name="collector" required="true" title="Representa quien cobrara la instalación y por tanto a quien se le asigne la deuda">
                    <option value="">
                      Seleccione una opción
                    </option>
                    <option value="V">
                      Vendedor Netwey
                    </option>
                    <option value="I">
                      Instalador
                    </option>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-12 text-center">
              <h3 class="text-white label-red px-4 py-3">
                <strong>
                  Recordatorio: ⚠
                </strong>
                Se debe solicitar a 815 que habiliten el request "
                <u>
                  bloquear_conexiones
                </u>
                " para el uso del proceso automatico de suspención de servicio.
              </h3>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal" type="button">
          Cancelar
        </button>
        <button class="btn btn-primary" data-action="create" id="submit" type="button">
          Guardar
        </button>
      </div>
    </div>
  </div>
</div>
<script src="js/common-modals.js">
</script>
<script src="js/fiber/fiberzone.js">
</script>
<script defer="" type="text/javascript">
  const actions = @php echo json_encode($actions) @endphp;
  //const baseUrl = "@php echo url('/') @endphp";

  function search_listZone() {
    $('.preloader').show();
    if ($.fn.DataTable.isDataTable('#list-com')) {
      $('#list-com').DataTable().destroy();
    }
    //Columnas del reporte
    let columnsDataTable = [];

    /*if (actions.includes('update') || 
      actions.includes('delete')) {*/
      botones = {
        data: 'id', 
        searchable: false, 
        orderable: false,
        render: function (data, type, row) {
          let html = '<div class="d-flex flex-column">';
          html += `<button type="button" class="btn btn-primary btn-md" onclick="verifyZone(${row.id},'view/fiber/chekingZone' )" >Verificar</button>`;
          if (actions.includes('update')) {
            html += `<button type="button" class="btn btn-warning btn-md" onclick="showZone(${row.id})">Editar</button>`;
          }
          if (actions.includes('delete')) {
            html += `<button type="button" class="btn btn-danger btn-md" onclick="DeleteZone(${row.id},'${row.name}')">Eliminar</button>`;
          }
          if (actions.length < 1) {
            //html += '-';
          }
          html += '</div>';
          return html;
        }
      };
      columnsDataTable.push(botones);
    /*}*/
    columnsDataTable.push({ 
      name: 'name', 
      data: 'name', 
      searchable: true, 
      orderable: true 
    });

    columnsDataTable.push({ 
      name: 'endpoint', 
      data: 'url_api', 
      searchable: false, 
      orderable: false 
    });

    if (actions.includes('update') || 
      actions.includes('delete')) {
      columnsDataTable.push({ 
        name: 'Usuario', 
        data: 'param.user', 
        searchable: false, 
        orderable: false 
      });
    }

    columnsDataTable.push({ 
      name: 'nodo', 
      data: "param.nodo_de_red", 
      searchable: false, 
      orderable: false 
    });

    columnsDataTable.push({ 
      name: 'modo', 
      data: "param.mode_default", 
      searchable: false, 
      orderable: false 
    });

    columnsDataTable.push({ 
      name: 'relay', 
      data: "param.dhcp_relay", 
      searchable: false, 
      orderable: false 
    });

    columnsDataTable.push({ 
      name: 'owner', 
      data: "configuration.owner", 
      searchable: false, 
      orderable: false 
    });

    columnsDataTable.push({ 
      name: 'collector', 
      data: "configuration.collector", 
      searchable: false, 
      orderable: false 
    });

    $('#list-com').DataTable({
      deferRender: true,
      paging: false,
      procesing: true,
      search: true,
      serverSide: true,
      ajax: {
        url: "{{ route('ListZone') }}",
        data: function (d) {
          d.owner = $('#owner').val();
        },
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

  $(document).ready(function () {
    // Set default headers
    $.ajaxSetup({
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $('#search').on('click', function(e) {
      search_listZone();
    });
    $('#download').on('click', function(e) {
      DownloadReport();
    });
    $('#add_zone').on('click', function(e) {
      CreateZone();
    }); 

    //Al cargar que busque x defecto todo
    search_listZone();
  });
</script>