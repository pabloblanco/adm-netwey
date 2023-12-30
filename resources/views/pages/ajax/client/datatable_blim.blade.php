{{-- <button class="btn btn-success mb-4" onclick="downloadcsv();">Exportar en CSV</button> --}}
<div class="table-responsive">
  <table id="blimTable" class="table table-striped">
    <thead>
        <tr>
            <th>Pin</th>
            <th>Fecha de Compra</th>
            <th>Estatus</th>
        </tr>
    </thead>
  </table>
</div>
{{--<table id="blimTablecsv" class="table table-striped" hidden>
    <caption>Blim</caption>
    <thead>
        <tr>
            <th>Pin</th>
            <th>Fecha de Compra</th>
            <th>Estatus</th>
        </tr>
    </thead>
</table>--}}
<script>
    var msisdn = {{$msisdn}};
</script>
<script src="js/client/datatable_blim.js"></script>