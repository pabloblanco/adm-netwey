<button class="btn btn-success mb-4" onclick="downloadcsv();">Exportar en CSV</button>
<div class="table-responsive">
  <table id="rechargerTable" class="table table-striped">
  </table>
</div>
<table id="rechargerTablecsv" class="table table-striped" hidden>
</table>
<script>
    var dtclient = null;
    @if(isset($dtclient))
        dtclient = '{{ $dtclient }}';
        dtclient = dtclient.replaceAll('&quot;', '"');
        dtclient = JSON.parse(dtclient);
    @endif
    var msisdn = {{$msisdn}};
    const thead = (dtclient != null && dtclient.dn_type == 'F') ? 
        ('<thead>' +
            '<tr>' +
            '<th>Transacción única</th>' +
            '<th>Fecha de la Transacción</th>' +
            '<th>Vendedor</th>' +
            '<th>Servicio</th>' +
            '<th>Descripcion</th>' +
            '<th>Cliente</th>' +
            '<th>Teléfono Netwey</th>' +
            '<th>Teléfono de contacto</th>' +
            '<th>Monto pagado</th>' +
            '<th>Concentrador</th>' +
            '<th>Conciliado</th>' +
            '<th>Fecha de inicio</th>' +
            '<th>Fecha de finalización</th>' +
            '</tr>' +
            '</thead>'
        ) :
        ('<thead>' +
            '<tr>' +
            '<th>Transacción única</th>' +
            '<th>Fecha de la Transacción</th>' +
            '<th>Vendedor</th>' +
            '<th>Servicio</th>' +
            '<th>Cliente</th>' +
            '<th>Teléfono Netwey</th>' +
            '<th>Teléfono de contacto</th>' +
            '<th>Monto pagado</th>' +
            '<th>Concentrador</th>' +
            '<th>Conciliado</th>' +
            '</tr>' +
            '</thead>'
        );
    $('#rechargerTable').html(thead);
    $('#rechargerTablecsv').html('<caption>Recargas</caption>' + thead);
</script>
<script src="js/client/datatable.js"></script>