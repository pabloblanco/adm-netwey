{{-- <button class="btn btn-success mb-4" onclick="downloadcsv();">Exportar en CSV</button> --}}
<div class="table-responsive">
  <table id="compensationsTable" class="table table-striped">
    <thead>
        <tr>
            <th>Fecha de Compensación</th>
            <th>Descripción</th>
            <th>Ajuste en MB</th>
            <th>Fecha de Expiración</th>
            <th>Incidente</th>
            <th>Fecha del Incidente</th>
            <th>Duración del Incidente (Hrs)</th>
            <th>Resultado de la Compensación</th>
        </tr>
    </thead>
  </table>
</div>
<table id="compensationsTablecsv" class="table table-striped" hidden>
    <caption>Compensaciones</caption>
    <thead>
        <tr>
            <th>Fecha de Compensación</th>
            <th>Descripción</th>
            <th>Ajuste en MB</th>
            <th>Fecha de Expiración</th>
            <th>Incidente</th>
            <th>Fecha del Incidente</th>
            <th>Duración del Incidente (Hrs)</th>
            <th>Resultado de la Compensación</th>    
        </tr>
    </thead>
</table>
<script>
    var msisdn = {{$msisdn}};
</script>
<script src="js/client/datatable_compensations.js"></script>