<style type="text/css">
    .selectize-input:after{
        content: none !important;
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Reporte de ventas en abono</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reporte</a></li>
                <li class="active">Reporte de ventas en abono</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <form id="filterConc" name="filterConc" class=" text-left" method="POST">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Organizaci&oacute;n</label>
                        <select id="org" name="org" class="form-control">
                            <option value="">Seleccione una organizaci&oacute;n</option>
                            @foreach($orgs as $org)
                                <option value="{{ $org->id }}" @if($orgs->count() == 1) selected @endif>{{ $org->business_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Coordinador</label>
                        <select id="coord" name="coord" class="form-control">
                            <option value="">Seleccione un Coordinador</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Vendedor</label>
                        <select id="seller" name="seller" class="form-control">
                            <option value="">Seleccione un vendedor</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Estatus</label>
                        <select id="status" name="status" class="form-control">
                            <option value="">Seleccione un status</option>
                            <option value="OK">Al d&iacute;a</option>
                            <option value="EXP">Vencidas</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Fecha desde</label>
                        <input type="text" name="dateb" id="dateb" class="form-control" placeholder="dd-mm-yyyy" value="{{ date('Y-m-d', strtotime('- 90 days', time())) }}">
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Fecha hasta</label>
                        <input type="text" name="datee" id="datee" class="form-control" placeholder="dd-mm-yyyy" value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Plan</label>
                        <select id="service" name="service" class="form-control">
                            <option value="">Seleccione un estatus</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">
                                    {{ $service->description }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Equipo</label>
                        <select id="product" name="product" class="form-control">
                            <option value="">Seleccione un equipo</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->title }}
                                </option>
                            @endforeach
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

        <div class="col-md-12 col-sm-12" id="rep-si" hidden>
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">
                        Reporte de Ventas a Cuotas
                    </h3>
                </div>

                <div class="col-md-12">
                    <button class="btn btn-success m-b-20" id="download" type="button">
                        Exportar Excel
                    </button>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table id="list-si" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Transacción &uacute;nica</th>
                                    <th>Organizaci&oacute;n</th>
                                    <th>Vendedor</th>
                                    <th>Coordinador</th>
                                    <th>Pack</th>
                                    <th>Producto</th>
                                    <th>DN Netwey</th>
                                    <th>Tipo linea</th>
                                    <th>Imei</th>
                                    <th>Plan</th>
                                    <th>Cliente</th>
                                    <th>Telf contacto</th>
                                    <th>Fecha de venta</th>
                                    <th>Fecha vencimiento prox. cuota</th>
                                    <th>Estatus</th>
                                    <th>Cutoa</th>
                                    <th>Monto</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal modalAnimate" id="showQuotes" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <input type="hidden" id="regIdModal" name="regIdModal">
            <div class="modal-header">
                <button type="button" class="modal_close_btn close" id="modal_close_x" data-modal="#showQuotes">&times;</button>
                <h4 class="modal-title">Detalle de las Cuotas</h4>
            </div>

            <div class="modal-body" style="overflow-y: auto; max-height: calc(100vh - 130px);">

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
            //endDate: new Date()
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

        //$('#dateb').datepicker(config);

        //$('#datee').datepicker(config);

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
                            org: $('#org').val(),
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
                            org: $('#org').val(),
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

        $('#org').on('change', function(e){
            var coor = $('#coord')[0].selectize;

            if(coor){
                coor.clearOptions();
            }

            var sel = $('#seller')[0].selectize;

            if(sel){
                sel.clearOptions();
            }
        });

        $('#org').selectize({
            valueField: 'id',
            labelField: 'business_name',
            searchField: 'business_name'
        });

        $('#coord').on('change', function(e){
            var sel = $('#seller')[0].selectize;

            if(sel)
                sel.clearOptions();
        });

        $('#service').selectize({
            valueField: 'id',
            labelField: 'description',
            searchField: 'description'
        });

        $('#product').selectize({
            valueField: 'id',
            labelField: 'title',
            searchField: 'title'
        });

        $('#status').selectize();

        $('#search').on('click', function(e){
            $('.preloader').show();

            if ($.fn.DataTable.isDataTable('#list-si')){
                $('#list-si').DataTable().destroy();
            }

            $('#list-si').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('getSalesInstDT')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.dateb = $('#dateb').val();
                        d.datee = $('#datee').val();
                        d.coord = $('#coord').val();
                        d.seller = $('#seller').val();
                        d.org = $('#org').val();
                        d.status = $('#status').val();
                        d.product = $('#product').val();
                        d.service = $('#service').val();
                    },

                    type: "POST"
                },
                initComplete: function(settings, json){
                    $(".preloader").fadeOut();
                    $('#rep-si').attr('hidden', null);
                },
                deferRender: true,
                order: [[ 11, "desc" ]],
                columns: [
                    {data: 'unique_transaction', orderable: false},
                    {data: 'org', searchable: false, orderable: false},
                    {data: 'seller', searchable: false, orderable: false},
                    {data: 'coordinador', searchable: false, orderable: false},
                    {data: 'pack', searchable: false, orderable: false},
                    {data: 'product', searchable: false, orderable: false},
                    {data: 'msisdn', orderable: false},
                    {data: 'artic_type', orderable: false},
                    {data: 'imei', searchable: false, orderable: false},
                    {data: 'service', searchable: false, orderable: false},
                    {data: 'client', searchable: false, orderable: false},
                    {data: 'phone_home', searchable: false, orderable: false},
                    {data: 'sell_date_reg', searchable: false, orderable: false},
                    {data: 'date_exp', searchable: false, orderable: false},
                    {data: 'status_quote', searchable: false, orderable: false},
                    {
                        data: null,
                        render: function(data,type,row,meta){
                            return '<span onClick="showModalQuotes(\''+row.unique_transaction+'\')" class="nw-link showModalQuotesLink" data-unique="'+row.unique_transaction+'">'+row.quote+'</span>';
                        },
                        searchable: false,
                        orderable: false
                    },
                    {data: 'amount', searchable: false, orderable: false}
                ]
            });
        });

        showModalQuotes = (unique) => {
            $('#regIdModal').val(unique);
            $('#showQuotes').modal({backdrop: 'static', keyboard: false});
        }

        $('#showQuotes').on('show.bs.modal', function (event){
            // var button = $(event.relatedTarget),
            //     unique = button.data('unique');

            var unique = $('#regIdModal').val()

            if(unique && unique != ''){
                $('.preloader').show();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    async: true,
                    url: "{{route('getQuoteDetail')}}",
                    method: 'POST',
                    data: {unique: unique},
                    dataType: 'json',
                    success: function (res) {
                        $(".preloader").fadeOut();

                        if(res.success){
                            $('#showQuotes .modal-body').html(res.html);
                        }else{
                            alert(res.msg);
                            $('#showQuotes .close').trigger('click');
                        }
                    },
                    error: function (res) {
                        alert('No se pudo cargar el detalle de las cuotas.');
                        $(".preloader").fadeOut();
                        $('#showQuotes .close').trigger('click');
                    }
                });
            }
        });

        $('#showQuotes .close').on('click', function (event){
            $('#showQuotes .modal-body').html('');
        });

        $('#download').on('click', function(e){
            var data = $("#filterConc").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');

            $(".preloader").fadeIn();

            $.ajax({
                type: "POST",
                url: "{{route('downloadRepSalesInst')}}",
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
<script src="js/common-modals.js"></script>