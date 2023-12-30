<style type="text/css">
    .selectize-input:after{
        content: none !important;
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Reporte de financiamientos payjoy</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reporte</a></li>
                <li class="active">Reporte de financiamientos payjoy</li>
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
                        <label class="control-label">Coordinador</label>
                        <select id="coord" name="coord" class="form-control">
                            <option value="">Seleccione un Coordinador</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Vendedor</label>
                        <select id="seller" name="seller" class="form-control">
                            <option value="">Seleccione un vendedor</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Estatus</label>
                        <select id="status" name="status" class="form-control">
                            <option value="">Seleccione un status</option>
                            <option value="A">Notificado</option>
                            <option value="P">Asociado</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Fecha desde</label>
                        <input type="text" name="dateb" id="dateb" class="form-control" placeholder="dd-mm-yyyy" value="{{ date('Y-m-d', strtotime('- 90 days', time())) }}">
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Fecha hasta</label>
                        <input type="text" name="datee" id="datee" class="form-control" placeholder="dd-mm-yyyy" value="{{ date('Y-m-d') }}">
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

        <div class="col-md-12 col-sm-12" id="rep-si" hidden>
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">
                        Reporte de Financiamientos con Payjoy
                    </h3>
                </div>

                <div class="col-md-12">
                    <button class="btn btn-success m-b-20" id="download" type="button">
                        Exportar Excel
                    </button>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table id="list-payjoy" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>DN Netwey</th>
                                    <th>Coordinador</th>
                                    <th>Vendedor</th>
                                    <th>Cliente</th>
                                    <th>Monto inicial</th>
                                    <th>Monto financiado</th>
                                    <th>Monto total</th>
                                    <th>Fecha financiamiento</th>
                                    <th>Fecha asociaci&oacute;n</th>
                                    <th>Estatus</th>
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
            format: 'yyyy-mm-dd',
            todayHighlight: true
        }

        $('#dateb').datepicker(config)
                .on('changeDate', function(selected){
                    var dt = $('#datee').val();
                    if(dt == ''){
                        $('#datee').datepicker('setDate', sumDays($('#dateb').datepicker('getDate'), 90));
                    }else{
                        var diff = getDateDiff($('#dateb').datepicker('getDate'), $('#datee').datepicker('getDate'));
                        if(diff > 90)
                            $('#datee').datepicker('setDate', sumDays($('#dateb').datepicker('getDate'), 90));
                    }
                });

        $('#datee').datepicker(config)
                .on('changeDate', function(selected){
                    var dt = $('#dateb').val();
                    if(dt == ''){
                        $('#dateb').datepicker('update', sumDays($('#datee').datepicker('getDate'), -90));
                    }else{
                        var diff = getDateDiff($('#dateb').datepicker('getDate'), selected.date);
                        if(diff > 90)
                            $('#dateb').datepicker('update', sumDays($('#datee').datepicker('getDate'), -90));
                    }
                });

        ajax1 = function(query, callback) {
                    if (!query.length) return callback();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '{{route("getFilterUsersSellers")}}',
                        type: 'POST',
                        dataType: 'json',
                        cache: false,
                        data: {
                            name: query,
                            //org: '',
                            type: 'coordinador'
                        },
                        error: function() {
                            callback();
                        },
                        success: function(res){
                            if(res.success)
                                callback(res.users);
                            else
                                callback();
                        }
                    });
                }

        ajax2 = function(query, callback) {
                    if (!query.length) return callback();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '{{route("getFilterUsersSellers")}}',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            name: query,
                            //org: '',
                            coord: $('#coord').val(),
                            type: 'vendor'
                        },
                        error: function() {
                            callback();
                        },
                        success: function(res){
                            if(res.success)
                                callback(res.users);
                            else
                                callback();
                        }
                    });
                }

        var configSelect = {
            valueField: 'email',
            labelField: 'username',
            searchField: 'username',
            options: [],
            create: false,
            persist: false,
            render: {
                option: function(item, escape) {
                    return '<p>'+item.name+' '+item.last_name+'</p>';
                }
            }
        };

        configSelect.load = ajax1;
        $('#coord').selectize(configSelect);

        configSelect.load = ajax2;
        $('#seller').selectize(configSelect);

        $('#coord').on('change', function(e){
            var sel = $('#seller')[0].selectize;
            
            if(sel)
                sel.clearOptions();
        });

        $('#status').selectize();

        $('#search').on('click', function(e){
            $('.preloader').show();

            if ($.fn.DataTable.isDataTable('#list-payjoy')){
                $('#list-payjoy').DataTable().destroy();
            }

            $('#list-payjoy').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('getPayjoyDt')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.dateb = $('#dateb').val();
                        d.datee = $('#datee').val();
                        d.coord = $('#coord').val();
                        d.seller = $('#seller').val();
                        d.status = $('#status').val();
                    },

                    type: "POST"
                },
                initComplete: function(settings, json){
                    $(".preloader").fadeOut();
                    $('#rep-si').attr('hidden', null);
                },
                deferRender: true,
                order: [[ 7, "desc" ]],
                columns: [
                    {data: 'msisdn', orderable: false},
                    {data: 'coordinador', searchable: false, orderable: false},
                    {data: 'seller', searchable: false, orderable: false},
                    {data: 'client', searchable: false, orderable: false},
                    {data: 'init_amount', searchable: false, orderable: false},
                    {data: 'amount', searchable: false, orderable: false},
                    {data: 'total_amount', orderable: false},
                    {data: 'date_reg', orderable: false},
                    {data: 'date_process', searchable: false, orderable: false},
                    {data: 'status', searchable: false, orderable: false}
                ]
            });
        });

        $('#download').on('click', function(e){
            var data = $("#filterConc").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');

            $(".preloader").fadeIn();

            $.ajax({
                type: "POST",
                url: "{{route('downloadPayjoyReport')}}",
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