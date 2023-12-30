{{-- <button class="btn btn-success mb-4" onclick="downloadcsv();">Exportar en CSV</button> --}}
<div class="table-responsive">
  <table id="retentionTable" class="table table-striped">
    <thead>
        <tr>
            <th>Servicio</th>
            <th>Activada por</th>
            <th>Autorizada por</th>
            <th>Motivo</th>
            <th>Sub-Motivo</th>
            <th>Fecha</th>
        </tr>
    </thead>
  </table>
</div>
{{-- <table id="retentionTablecsv" class="table table-striped" hidden>
    <caption>Retentiones Activadas</caption>
    <thead>
        <tr>
            <th>Servicio</th>
            <th>Activada por</th>
            <th>Autorizada por</th>
            <th>Motivo</th>
            <th>Fecha</th>
        </tr>
    </thead>
</table> --}}
<script>
    var msisdn = {{$msisdn}};
</script>
<script src="js/client/datatable_retention.js"></script>