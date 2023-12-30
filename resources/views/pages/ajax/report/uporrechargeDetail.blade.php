<div class="white-box">
  <div class="row">
    <div class="col-md-12">
      <h3>
        <header>Reporte de @if ($view == 'ups') Altas @else Recargas @endif</header>
      </h3>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <button class="btn btn-success" id="downloadR">Exportar en Excel</button>
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-md-12">
      <div class="table-responsive">
        <table id="dt-{{ $view }}" class="table table-striped">
          <thead>
            <tr>
              <th>Transacción única</th>

              @if ($view != 'ups')
                <th>Folio OXXO</th>
              @endif

              <th>Fecha<br><span style="font-size: 10px;display: block;width: 85px;">(YYYY-mm-dd)</span></th>

              @if ($view == 'ups')
                <th>Organizaci&oacute;n</th>
              @else
                <th>Concentrador</th>
              @endif

              <th>Vendedor</th>


              @if ($view == 'ups')
                <th>Coordinador</th>
                <th>Plan</th>
              @endif

              <th>Producto</th>
              <th>Teléfono Netwey</th>
              <th>DN Migrado</th>
              <th>Tipo linea</th>
              <th>IMEI / MAC</th>
              <th>ICCID</th>
              <th>Servicio</th>
              <th>Cliente</th>
              <th>Telf de contacto</th>
              <th>Telf de contacto 2</th>
              <th>Zona de Cobertura</th>
              <th>Monto pagado</th>
              <th>Tipo</th>
              <th>Conciliado</th>
              <th>Latitud</th>
              <th>Longitud</th>
              <th>Factura</th>
              @if ($view == 'ups')
                <th>Campaña</th>
                <th>Origen</th>
                <th>Email Vendedor</th>
                <th>Email Coordinador</th>
                <th>Coordinador Bloqueado</th>
              @endif

              <th>Instalador</th>
              <th>Email Instalador</th>

              @if ($view == 'ups')
                <th>Financiamiento</th>
              @endif
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="js/reports/uporrechargeDetail.js?v=2.9"></script>
