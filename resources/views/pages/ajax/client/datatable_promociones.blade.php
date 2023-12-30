
<div class="table-responsive">
  <table id="promotionsTable" class="table table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Telefono netwey</th>
            <th>Servicio</th>
            <th>Venta</th>
            <th>Fecha de activaci&oacute;n</th>         
            <th>Fecha inicio del servicio</th>
            <th>Fecha de registro</th>         
        </tr>
    </thead>
  </table>
</div>


<script type="text/javascript">
   // $(window).on('load', function () {
    $(document).ready(function () {

    /**
     * crear reporte
     */
        if ($.fn.DataTable.isDataTable('#promotionsTable')) {
            $('#promotionsTable').DataTable().destroy();
        }
       // var msisdn = {{$msisdn}};
        $('#promotionsTable').DataTable({
            searching: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('getDTPromociones')}}",
                data: function(d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.msisdn = {{$msisdn}};
                },
                type: "POST"
            },
            
            deferRender: true,
            //order: [[ 6, "desc" ]],
            ordering: false,
            columns: [{
                data: 'id',
                searchable: false,
                orderable: false
            }, {
                data: 'msisdn',
                searchable: false,
                orderable: false
            }, {
                data: 'service',
                searchable: false,
                orderable: false
            }, {
                data: 'id_sale',
                searchable: false,
                orderable: false
            }, {
                data: 'activation_date',
                searchable: false,
                orderable: false
            },  {
                data: 'activated_date',
                searchable: false,
                orderable: false
            }, {
                data: 'date_reg',
                searchable: false,
                orderable: false
            }]
        });    
});
</script>