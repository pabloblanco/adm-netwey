<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Reporte SIM SWAP</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reportes</a></li>
                <li class="active">SIM SWAP</li>
            </ol>
        </div>
    </div>
</div>

<div class="white-box">
    <div class="row ">
        <div class="col-md-12">
            <h3>
                SIM SWAP Realizados
            </h3>
            <button class="btn btn-success" id="download" type="button">
                Exportar Excel
            </button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 p-t-20">
            <div class="table-responsive">
                <table id="tablebt" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>DN Origen</th>
                            <th>Imei Origen</th>
                            <th>Iccid Origen</th>
                            <th>DN Destino</th>
                            <th>Imei Destino</th>
                            <th>Iccid Destino</th>
                            <th>Número de orden</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        drawTable = function(){
            //Si ya existe la tabla se elimina
            if ($.fn.DataTable.isDataTable('#tablebt')){
                $('#tablebt').DataTable().destroy();
            }

            $('.preloader').show();

            $('#tablebt').DataTable({
                searching: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('getSwap')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                    },
                    type: "POST"
                },
                initComplete: function(settings, json){
                    $(".preloader").fadeOut();
                },
                deferRender: true,
                order: [[ 9, "desc" ]],
                columns: [
                    {data: 'name'},
                    {data: 'msisdn_origin'},
                    {data: 'imei_origin'},
                    {data: 'iccid_origin'},
                    {data: 'msisdn_dest'},
                    {data: 'imei_dest'},
                    {data: 'iccid_dest'},
                    {data: 'id_order'},
                    {data: 'tipo'},
                    {data: 'date_reg'}
                ]
            });
        }

        drawTable();

        $('#download').on('click', function(){
            $(".preloader").fadeIn();

            //var data = $("#report_tb_form").serialize();

            $.ajax({
                headers:{
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                url: "{{route('downloadSwapReport')}}",
                //data: data,
                dataType: "json",
                success: function(response){
                    $(".preloader").fadeOut();

                    var a = document.createElement("a");
                        a.target = "_blank";
                        a.href = "{{route('downloadFile',['delete' => 1])}}?p=" + response.url;
                        a.click();
                },
                error: function(err){
                    $(".preloader").fadeOut();
                }
            });
        });
    });
</script>