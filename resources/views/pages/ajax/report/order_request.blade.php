@php
    $flimit='2021-10-01 00:00:00';
    $fini = date('d-m-Y', strtotime('- 30 days', time()));
    if(strtotime($fini) < strtotime($flimit))
        $fini = date('d-m-Y',strtotime($flimit));

    $fend = date('d-m-Y', strtotime('- 0 days', time()));
    if(strtotime($fend) < strtotime($flimit))
        $fend = date('d-m-Y',strtotime($flimit));
@endphp
<style type="text/css">
    .selectize-input:after{
        content: none !important;
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Reporte Pedido Solicitado</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reporte</a></li>
                <li class="active">Pedido Solicitado</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <form id="filterConc" name="filterConc" class=" text-left" method="POST">
            <div class="row">
                <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Fecha desde</label>
                        <div class="input-group">
                            <input autocomplete="off" type="text" name="dateb" id="dateb" class="form-control" placeholder="dd-mm-yyyy" value="{{ $fini }}">
                            <span class="input-group-addon">
                                <i class="icon-calender"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Fecha hasta</label>
                        <div class="input-group">
                            <input autocomplete="off" type="text" name="datee" id="datee" class="form-control" placeholder="dd-mm-yyyy" value="{{ $fend }}">
                            <span class="input-group-addon">
                                <i class="icon-calender"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">¿Estatus de Asignación?</label>
                        <select id="status" name="status" class="form-control">
                            <option value="" selected></option>
                            <option value="A">Pendiente</option>
                            <option value="E">Con error</option>
                            <option value="P">Procesada - Asignado a Coordinador</option>
                            <option value="AS">Procesada - Asignado a Regional</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-12 p-t-20 text-center">
                <div class="form-group">
                    <button class="btn btn-success" id="search" type="button">
                        Buscar
                    </button>
                </div>
            </div>
            </form>
        </div>

        <div class="col-md-12 col-sm-12" id="rep-sc" hidden>
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">
                        Reporte Pedido Solicitado
                    </h3>
                </div>

                <div class="col-md-12">
                    <button class="btn btn-success m-b-20" id="download" type="button">
                        Exportar Excel
                    </button>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table id="list-com" class="table table-striped display nowrap">
                            <thead>
                                <tr>
                                    <th>Archivo</th>
                                    <th>Caja</th>
                                    <th>msisdn</th>
                                    <th>SKU</th>
                                    <th>iccid</th>
                                    <th>imei</th>
                                    <th>Branch</th>
                                    <th>Folio</th>
                                    <th>Usuario</th>
                                    <th>Estatus</th>
                                    <th>Estatus de reciclaje</th>
                                    <th title="Ultimo usuario en interactuar con el registro">Ultima acción por</th>
                                    <th title="Fecha en la que el regional acepta o rechaza">Acción del Regional</th>
                                    <th title="Fecha en la que el coordinador acepta o rechaza">Acción del Coordinador</th>
                                    <th>Comentario</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {

        maxdays = 365;

        flimit = new Date(Date.parse("{{$flimit}}"));

        $('#status').selectize();
        // $('#is_error').selectize();

        var config = {
            autoclose: true,
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            language: 'es',
            startDate: flimit,
            endDate: new Date()

        }


        $('#dateb').datepicker(config)
                   .on('changeDate', function(selected){
                        var dt = $('#datee').val();
                        if(dt == ''){
                            $('#datee').datepicker('update', sumDays($('#dateb').datepicker('getDate'), maxdays));
                        }else{
                            var diff = getDateDiff($('#dateb').datepicker('getDate'), $('#datee').datepicker('getDate'));
                            if(diff > maxdays){
                                $('#datee').datepicker('update', sumDays($('#dateb').datepicker('getDate'), maxdays));
                            }
                        }

                        var diff2 = getDateDiff($('#datee').datepicker('getDate'), flimit);
                        if(diff2 > 0){
                            $('#datee').datepicker('update', flimit);
                        }
                        var maxDate = new Date(selected.date.valueOf());
                        $('#datee').datepicker('setStartDate', maxDate);

                   });

        //config.endDate = new Date(new Date().setTime(new Date().getTime()- (24*60*60*1000)));
        config.endDate = new Date(new Date().setTime(new Date().getTime()));
        $('#datee').datepicker(config)
                   .on('changeDate', function(selected){
                    var dt = $('#dateb').val();
                        if(dt == ''){
                            $('#dateb').datepicker('update', sumDays($('#datee').datepicker('getDate'), -maxdays));
                        }else{
                            var diff = getDateDiff($('#dateb').datepicker('getDate'), selected.date);
                            if(diff > maxdays){
                                $('#dateb').datepicker('update', sumDays($('#datee').datepicker('getDate'), - maxdays));
                            }
                        }
                        var diff2 = getDateDiff($('#dateb').datepicker('getDate'), flimit);
                        if(diff2 > 0){
                            $('#dateb').datepicker('update', flimit);
                        }
                        var maxDate = new Date(selected.date.valueOf());
                        $('#dateb').datepicker('setEndDate', maxDate);

                   });

        $('#search').on('click', function(e){
            $('.preloader').show();

            if ($.fn.DataTable.isDataTable('#list-com')){
                $('#list-com').DataTable().destroy();
            }

            $('#list-com').DataTable({
                searching: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('getDTOrderRequest')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.dateb = $('#dateb').val();
                        d.datee = $('#datee').val();
                        d.status = $('#status').val();
                        d.is_error = $('#is_error').val();
                    },

                    type: "POST"
                },
                initComplete: function(settings, json){
                    $(".preloader").fadeOut();
                    $('#rep-sc').attr('hidden', null);
                },
                deferRender: true,
                ordering: false,
                columns: [
                    {data: 'file', searchable: false, orderable: false},
                    {data: 'box', searchable: false, orderable: false},
                    {data: 'msisdn', searchable: false, orderable: false},
                    {data: 'sku', searchable: false, orderable: false},
                    {data: 'iccid', searchable: false, orderable: false},
                    {data: 'imei', searchable: false, orderable: false},
                    {data: 'branch', searchable: false, orderable: false},
                    {data: 'folio', searchable: false, orderable: false},
                    {data: 'user', searchable: false, orderable: false},
                    {data: 'status', searchable: false, orderable: false},
                    {data: 'recicler_status', searchable: false, orderable: false},
                    {data: 'last_user_action', searchable: false, orderable: false},
                    {data: 'reg_date_action', searchable: false, orderable: false},
                    {data: 'coo_date_action', searchable: false, orderable: false},
                    {data: 'comment', searchable: false, orderable: false},
                    {data: 'date_reg', searchable: false, orderable: false}
                ]
            });
        });

        $('#download').on('click', function(e){
            var data = $("#filterConc").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content') ;

            $(".preloader").fadeIn();

            $.ajax({
                type: "POST",
                url: "{{route('downloadDTOrderRequest')}}",
                data: data,
                dataType: "text",
                success: function(response){
                    $(".preloader").fadeOut();
                    swal('Generando reporte','El reporte estara disponible en unos minutos.','success');
                },
                error: function(err){
                    $(".preloader").fadeOut();
                    swal('Error','No se pudo generar el reporte.','error');
                }
            });
        });
    });
</script>