<style type="text/css">
    .selectize-input:after{
        content: none !important;
    }
</style>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Reporte de Migración</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reporte</a></li>
                <li class="active">Reporte Migración</li>
            </ol>
        </div>
    </div>
</div>
<div class="container report-retention">
    <div class="row justify-content-center">
        <div class="col-12 pb-5">
            <h3>Configuración del reporte</h3>
            <form action="" class="form-horizontal" id="report_tb_form" method="POST" name="report_tb_form">
                {{ csrf_field() }}
                <div class="col-md-5 col-12">
                    <div class="form-group">
                        <label class="control-label">Fecha de migracion Inicio</label>
                        <div class="input-group">
                            <input class="form-control" data-date-format="dd-mm-yyyy" id="dateStar" name="dateStar" placeholder="dd-mm-yyyy" type="text" value="{{ date('d-m-Y', strtotime('- 30 days', time())) }}">
                                <span class="input-group-addon"><i class="icon-calender"></i></span>
                            </input>
                        </div>
                    </div>
                </div>
                <div class="offset-md-1 col-md-5 col-12">
                    <div class="form-group">
                        <label class="control-label">Fecha de migracion Fin</label>
                        <div class="input-group">
                            <input class="form-control" data-date-format="dd-mm-yyyy" id="dateEnd" name="dateEnd" placeholder="dd-mm-yyyy" type="text" value="{{ date('d-m-Y') }}">
                                <span class="input-group-addon"><i class="icon-calender"></i></span>
                            </input>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 text-center">
                    <button class="btn btn-success" id="search" name="search" type="button">
                        <i class="fa fa-check"></i>Generar reporte
                    </button>
                </div>
            </form>
        </div>
        <div class="col-12" hidden="" id="rep-sc">
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">Reporte de Migración</h3>
                </div>
                <div class="col-md-12">
                    <button class="btn btn-success m-b-20" id="download" type="button">Exportar Excel</button>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped" id="list-com">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>DN Origen</th>
                                    <th>Alta DN Origen</th>
                                    <th>Vendedor DN Origen</th>
                                    <th>Ultima Recarga DN Origen</th>
                                    <th>IMEI</th>
                                    <th>Tipo</th>
                                    <th>DN Nuevo</th>
                                    <th>Fecha de Migracion</th>
                                    <th>Vendedor Migracion</th>
                                    <th>Paquete Migracion</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="js/reports/rangePicker.js">
</script>
<script type="text/javascript">
    $(document).ready(function () {

    /**
     * crear reporte
     */
    $('#search').on('click', function(e) {

        $('.preloader').show();
        // $.fn.dataTable.ext.errMode = 'throw';

        if ($.fn.DataTable.isDataTable('#list-com')) {
            $('#list-com').DataTable().destroy();
        }

        $('#list-com').DataTable({
            searching: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('getDTMigration')}}",
                data: function(d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.dateStar = $('#dateStar').val();
                    d.dateEnd = $('#dateEnd').val();
                },
                type: "POST"
            },
            initComplete: function(settings, json) {
                $(".preloader").fadeOut();
                $('#rep-sc').attr('hidden', null);
            },
            deferRender: true,
            ordering: false,
            columns: [
                {
                    data: 'client',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'msisdn_old',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'alta_old',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'vendor_old',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'last_recharge',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'imei_code',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'artic_type',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'msisdn_new',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'date_migration',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'vendor_new',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'pack',
                    searchable: false,
                    orderable: false
                }
            ]
        });
    });

    $('#download').on('click', function() {
        $(".preloader").fadeIn();
        var data = $("#report_tb_form").serialize();
        $.ajax({
            type: "POST",
            url: "{{route('downloadDTMigration')}}",
            data: data,
            dataType: "json",
            success: function(response) {
                $(".preloader").fadeOut();
                swal('Generando reporte', 'El reporte estara disponible en unos minutos.', 'success');
            },
            error: function(err) {
                console.log("error al crear el reporte: ", err);
                $(".preloader").fadeOut();
            }
        });
    });

});
</script>