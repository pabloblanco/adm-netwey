<style type="text/css">
    .selectize-input:after{
        content: none !important;
    }
</style>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">
                Reporte de actualización de datos de clientes
            </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li>
                    <a href="#">
                        Reporte
                    </a>
                </li>
                <li class="active">
                    Reporte de actualización de datos de clientes
                </li>
            </ol>
        </div>
    </div>
</div>
<div class="container report-retention">
    <div class="row justify-content-center">
        <div class="col-12 pb-5">
            <h3>
                Configuración del reporte
            </h3>
            <form action="" class="form-horizontal" id="report_tb_form" method="POST" name="report_tb_form">
                {{ csrf_field() }}
                <div class="col-md-5 col-12">
                    <div class="form-group">
                        <label class="control-label">
                            Fecha Inicio
                        </label>
                        <div class="input-group">
                            <input class="form-control" data-date-format="dd-mm-yyyy" id="dateStar" name="dateStar" placeholder="dd-mm-yyyy" type="text" value="{{ date('d-m-Y', strtotime('- 30 days', time())) }}">
                                <span class="input-group-addon">
                                    <i class="icon-calender">
                                    </i>
                                </span>
                            </input>
                        </div>
                    </div>
                </div>
                <div class="offset-md-1 col-md-5 col-12">
                    <div class="form-group">
                        <label class="control-label">
                            Fecha Fin
                        </label>
                        <div class="input-group">
                            <input class="form-control" data-date-format="dd-mm-yyyy" id="dateEnd" name="dateEnd" placeholder="dd-mm-yyyy" type="text" value="{{ date('d-m-Y') }}">
                                <span class="input-group-addon">
                                    <i class="icon-calender">
                                    </i>
                                </span>
                            </input>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 text-center">
                    <button class="btn btn-success" id="search" name="search" type="button">
                        <i class="fa fa-check">
                        </i>
                        Generar reporte
                    </button>
                </div>
            </form>
        </div>
        <div class="col-12" hidden="" id="rep-sc">
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">
                        Reporte de actualización de datos de clientes
                    </h3>
                </div>
                <div class="col-md-12">
                    <button class="btn btn-success m-b-20" id="download" type="button">
                        Exportar Excel
                    </button>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped" id="list-com">
                            <thead>
                                <tr>
                                    <th>
                                        #
                                    </th>
                                    <th>
                                        Dn del cliente
                                    </th>
                                    <th>
                                        Usuario responsable
                                    </th>
                                    <th>
                                        Fecha de la actualización
                                    </th>
                                    <th>
                                        Campo modificado
                                    </th>
                                    <th>
                                        Info original
                                    </th>
                                    <th>
                                        Info actualizada
                                    </th>
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
            searching: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('getDTClientsUpdateCall')}}",
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
            //order: [[ 6, "desc" ]],
            ordering: true,
            columns: [{
                data: 'id',
                searchable: false,
                orderable: false
            }, {
                data: 'msisdn',
                searchable: false,
                orderable: false
            }, {
                data: 'users_mail',
                searchable: true,
                orderable: false
            },  {
                data: 'date_reg',
                searchable: false,
                orderable: true
            }, {
                data: 'campo',
                searchable: false,
                orderable: false
            }, {
                data: 'data_last',
                searchable: false,
                orderable: false
            }, {
                data: 'data_new',
                searchable: false,
                orderable: false
            }]
        });
    });

    $('#download').on('click', function() {
        $(".preloader").fadeIn();
        var data = $("#report_tb_form").serialize();
        $.ajax({
            type: "POST",
            url: "{{route('downloadDTClientsUpdateCall')}}",
            data: data,
            dataType: "json",
            success: function(response) {

                $(".preloader").fadeOut();
                swal('Generando reporte','El reporte estara disponible en unos minutos.','success');
                var a = document.createElement("a");
                a.target = "_blank";
                a.href = "{{route('downloadFile',['delete' => 1])}}?p=" + response.url;
                a.click();
            },
            error: function(err) {
                console.log("error error")
                $(".preloader").fadeOut();
            }
        });
    });

});
</script>