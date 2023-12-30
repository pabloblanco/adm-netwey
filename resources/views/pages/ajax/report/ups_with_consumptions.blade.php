<style type="text/css">
    .selectize-input:after{
        content: none !important;
    }
</style>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">
                Reporte de Altas con consumos
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
                    Reporte de Altas con consumos
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
                            <input class="form-control" data-date-format="dd-mm-yyyy" id="dateStar" name="dateStar" placeholder="dd-mm-yyyy" type="text" value="{{ date('d-m-Y', strtotime('- 1 months', time())) }}">
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
                            <input class="form-control" data-date-format="dd-mm-yyyy" id="dateEnd" name="dateEnd" placeholder="dd-mm-yyyy" type="text" value="{{ date('d-m-Y', strtotime('- 1 days', time())) }}">
                                <span class="input-group-addon">
                                    <i class="icon-calender">
                                    </i>
                                </span>
                            </input>
                        </div>
                    </div>
                </div>
                {{--
                <div class="col-md-5">
                    <div class="form-group">
                        <label class="control-label">
                            Usuario creador
                        </label>
                        <select class="form-control" id="seller_name" name="seller_name">
                            <option value="">
                                Seleccione el nombre de usuario
                            </option>
                        </select>
                    </div>
                </div>
                --}}
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
                        Reporte de Altas con consumos
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
                                        msisdn
                                    </th>
                                    <th>
                                        Fecha Alta
                                    </th>
                                    <th>
                                        Fecha Consumo
                                    </th>
                                    <th>
                                        Consumo (MB)
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
{{-- <script src="js/reports/rangePicker.js">
</script> --}}
<script type="text/javascript">
    $(document).ready(function () {

        var getDate = function (input) {
            return new Date(input.date.valueOf());
        }

        $('#dateStar, #dateEnd').datepicker({
            format: "dd-mm-yyyy",
            autoclose: true,
            language: 'es'
        });

        $('#dateEnd').datepicker('setEndDate',(moment().add(-1,'days')).format('DD-MM-YYYY'));

        $('#dateStar').on('changeDate',function (selected) {
            $('#dateEnd').datepicker('setStartDate', getDate(selected));
            start = moment($('#dateStar').val(),'DD-MM-YYYY');
            end = moment($('#dateEnd').val(),'DD-MM-YYYY');
            duration = moment.duration(end.diff(start));
            days = duration.asDays();
            if(days > 31){
                dend=(start.add(31,'days')).format('DD-MM-YYYY');
                $('#dateEnd').datepicker('setDate',dend);
            }
        });

        $('#dateEnd').on('changeDate',function (selected) {
            $('#dateStar').datepicker('setEndDate', getDate(selected));
            start = moment($('#dateStar').val(),'DD-MM-YYYY');
            end = moment($('#dateEnd').val(),'DD-MM-YYYY');
            duration = moment.duration(end.diff(start));
            days = duration.asDays();
            if(days > 31){
                dini=(end.add(-31,'days')).format('DD-MM-YYYY');
                $('#dateStar').datepicker('setDate',dini)
            }
        });


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
                url: "{{route('getDTUpsWithConsumptions')}}",
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
            ordering: false,
            columns: [{
                data: 'msisdn',
                searchable: false,
                orderable: false
            }, {
                data: 'Fecha_Alta',
                searchable: false,
                orderable: false
            }, {
                data: 'Fecha_Consumo',
                searchable: false,
                orderable: false
            }, {
                data: 'Consumo',
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
            url: "{{route('downloadDTUpsWithConsumptions')}}",
            data: data,
            dataType: "json",
            success: function(response) {

                $(".preloader").fadeOut();
                swal('Generando reporte','El reporte estara disponible en unos minutos.','success');
            },
            error: function(err) {
                console.log("error error")
                $(".preloader").fadeOut();
            }
        });
    });

});
</script>