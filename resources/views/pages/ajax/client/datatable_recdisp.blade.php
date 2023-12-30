<div class="table-responsive">
  <table id="recdispTable" class="table table-striped">
    <thead>
        <tr>
            <th>Servicio</th>
            <th>Descripción</th>
            <th>Precio</th>
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
        if ($.fn.DataTable.isDataTable('#recdispTable')) {
            $('#recdispTable').DataTable().destroy();
        }
       // var msisdn = {{$msisdn}};
        $(".preloader").fadeIn();
        $('#recdispTable').DataTable({
            searching: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('getDTPlansMP')}}",
                data: function(d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.msisdn = {{$msisdn}};
                },
                type: "POST"
            },
            initComplete:function( settings, json){
                $(".preloader").fadeOut();
            },
            deferRender: true,
            ordering: false,
            columns: [{
                data: 'title',
                searchable: false,
                orderable: false
            }, {
                data: 'description',
                searchable: false,
                orderable: false
            }, {
                data: 'price_pay',
                searchable: false,
                orderable: false
            }]
        });


});
</script>