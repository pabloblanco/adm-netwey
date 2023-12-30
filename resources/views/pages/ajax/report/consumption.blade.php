<style type="text/css">
    .selectize-input:after{
        content: none !important;
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Reporte de consumo</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reporte</a></li>
                <li class="active">Reporte de consumo</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <form id="filterConc" name="filterConc" class=" text-left" method="POST">
            <div class="row">
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Fecha desde</label>
                        <input type="text" name="dateb" id="dateb" class="form-control" placeholder="dd-mm-yyyy" value="{{ date('d-m-Y', strtotime('- 30 days', time())) }}">
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Fecha hasta</label>
                        <input type="text" name="datee" id="datee" class="form-control" placeholder="dd-mm-yyyy" value="{{ date('d-m-Y', strtotime('- 1 days', time())) }}">
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">msisdn</label>
                        <select id="msisdn" name="msisdn" class="form-control">
                            <option value="">Seleccione el msisdn</option>
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
                        Reporte de consumo
                    </h3>
                </div>

                <div class="col-md-12">
                    <button class="btn btn-success m-b-20" id="download" type="button">
                        Exportar Excel
                    </button>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table id="list-com" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>msisdn</th>
                                    <th>Consumo (GB)</th>
                                    <th>Servicio</th>
                                    <th>ID oferta</th>
                                    <th>Nombre oferta</th>
                                    <th>Fecha inicio oferta</th>
                                    <th>Fecha fin oferta</th>
                                    <th>D&iacute;as de consumo</th>
                                    <th>Tipo servicio</th>
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
        var config = {
            autoclose: true,
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            language: 'es',
            endDate: new Date()
        }

        $('#dateb').datepicker(config)
                   .on('changeDate', function(selected){
                    var dt = $('#datee').val();
                    if(dt == ''){
                        $('#datee').datepicker('setDate', sumDays($('#dateb').datepicker('getDate'), 30));
                    }else{
                        var diff = getDateDiff($('#dateb').datepicker('getDate'), $('#datee').datepicker('getDate'));
                        if(diff > 30)
                            $('#datee').datepicker('setDate', sumDays($('#dateb').datepicker('getDate'), 30));
                    }
                   });

        config.endDate = new Date(new Date().setTime(new Date().getTime()- (24*60*60*1000)));
        $('#datee').datepicker(config)
                   .on('changeDate', function(selected){
                    var dt = $('#dateb').val();
                    if(dt == ''){
                        $('#dateb').datepicker('update', sumDays($('#datee').datepicker('getDate'), -30));
                    }else{
                        var diff = getDateDiff($('#dateb').datepicker('getDate'), selected.date);
                        if(diff > 30)
                            $('#dateb').datepicker('update', sumDays($('#datee').datepicker('getDate'), -30));
                    }
                   });

        $('#msisdn').selectize({
            valueField: 'msisdn',
            labelField: 'msisdn',
            searchField: 'msisdn',
            options: [],
            create: false,
            render: {
                option: function(item, escape) {
                    return '<p>'+item.msisdn+'</p>';
                }
            },
            load: function(query, callback) {
                if (!query.length) return callback();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: 'api/client/get-clients-input',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        q: query
                    },
                    error: function() {
                        callback();
                    },
                    success: function(res){
                        if(res.success)
                            callback(res.clients);
                        else
                            callback();
                    }
                });
            }
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
                    url: "{{route('getDTConsumption')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.dateb = $('#dateb').val();
                        d.datee = $('#datee').val();
                        d.msisdn = $('#msisdn').val();
                    },

                    type: "POST"
                },
                initComplete: function(settings, json){
                    $(".preloader").fadeOut();
                    $('#rep-sc').attr('hidden', null);
                },
                deferRender: true,
                //order: [[ 6, "desc" ]],
                ordering: false,
                columns: [
                    {data: 'msisdn', searchable: false, orderable: false},
                    {data: 'consuption', searchable: false, orderable: false},
                    {data: 'title', searchable: false, orderable: false},
                    {data: 'codeAltan', searchable: false, orderable: false},
                    {data: 'offer_name', searchable: false, searchable: false, orderable: false},
                    {data: 'date_reg', searchable: false, orderable: false},
                    {data: 'date_sup_en', searchable: false, orderable: false},
                    {data: 'days', searchable: false, orderable: false},
                    {data: 'type', searchable: false, orderable: false}
                ]
            });
        });

        $('#download').on('click', function(e){
            var data = $("#filterConc").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');

            $(".preloader").fadeIn();

            $.ajax({
                type: "POST",
                url: "{{route('downloadDTConsumption')}}",
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