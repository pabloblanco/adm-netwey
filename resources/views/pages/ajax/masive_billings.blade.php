@php
  $accessPermission = 0;
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'BIL-MSO')
      $accessPermission = $policy->value;
  }
@endphp
@if ($accessPermission > 0)
<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">
        Facturación Masiva Oxxo
      </h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li>
          <a href="/islim/">
            Dashboard
          </a>
        </li>
        <li class="active">
          Facturación Masiva Oxxo
        </li>
      </ol>
    </div>
  </div>
</div>
<div class="container">
  <form id="file_form">
    <div class="row">
      <label class="control-label">
        Importacion por archivos CSV
      </label>
      <br/>
      <div class="col-md-4">
        <input id="csv" name="csv" type="file" accept="text/csv">
      </div>
      <div class="col-md-2">
        <button class="btn btn-success" id="fileup">
          {{-- <i class="fa fa-check">
          </i> --}}
          Continuar
        </button>
      </div>
    </div>
  </form>
  <hr>

  <div class="data-table-container d-none">
    <p style="font-weight: 500;">El archivo fue analizado y se ejecutaran las siguientes acciones luego de hacer click en el boton "PROCESAR", lo invitamos a revisar este listado y que este completamente seguro que la data es correcta antes de ejecutar esta acción</p>
    <div class="row white-box">
      <div class="table-responsive">
        <table id="myTable" class="table table-striped">
            <thead>
                <tr>
                    <th id="actionCol">Acción</th>
                    <th>AcciónH</th>
                    <th>Plaza/Cedis</th>
                    <th>Fecha Vencimiento</th>
                    <th>Termino</th>
                    <th>Fecha Factura</th>
                    <th>IdFactura</th>
                    <th>Numero Factura</th>
                    <th>Fecha de pago</th>
                    <th>Doc. de Pago</th>
                    <th>Estado</th>
                    <th>Subtotal</th>
                    <th>Impuestos</th>
                    <th>Total</th>
                    <th>Método de Pago</th>
                    <th>Serie</th>
                    <th>Folio</th>
                </tr>
            </thead>
        </table>
      </div>

      <div class="row w-100 text-right mt-4">
        <div class="col-md-12">
            <button class="btn btn-success" id="process-file">
              <i class="fa fa-check">
              </i>
              Procesar
            </button>
        </div>
      </div>

    </div>
  </div>

  {{-- <div class="row">
    <div class="col-md-12">
      @if($addPermission > 0)
      <label class="control-label">
        Agregar inventario de forma manual
      </label>
      <br/>
      <button class="btn btn-info btn-lg" id="open_modal_btn" type="button">
        Agregar
      </button>
      <hr/>
      @endif
      <div class="table-responsive mb-5">
        <table class="table table-striped" id="myTable">
          <thead>
            <tr>
              <th>
                Acciones
              </th>
              <th>
                Id
              </th>
              <th>
                Producto
              </th>
              <th>
                MSISDN
              </th>
              <th>
                IMEI / MAC
              </th>
              <th>
                Precio
              </th>
              <th>
                Estado
              </th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div> --}}
</div>
<script src="js/billingmasive/main.js?v=2.1">
</script>
<script src="js/common-modals.js">
</script>
@else
<h3>
  Lo sentimos, usted no posee permisos suficientes para acceder a este módulo
</h3>
@endif
