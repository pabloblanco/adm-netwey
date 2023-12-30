<style type="text/css">
    .selectize-input:after{
        content: none !important;
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Reporte RRE Abono</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reporte</a></li>
                <li class="active">Reporte RRE Abono</li>
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
                            <option value="V">Enviado aprobaci&oacute;n</option>
                            <option value="P">Aprobado</option>
                            <option value="I">Rechazado</option>
                            <option value="A">Conciliado</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Fecha desde</label>
                        <input type="text" name="dateb" id="dateb" class="form-control" placeholder="dd-mm-yyyy">
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Fecha hasta</label>
                        <input type="text" name="datee" id="datee" class="form-control" placeholder="dd-mm-yyyy">
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Alerta</label>
                        <select id="alert" name="alert" class="form-control">
                            <option value="">Seleccione una alerta</option>
                            <option value="B">Azul</option>
                            <option value="O">Naranja</option>
                            <option value="R">Rojo</option>
                            <option value="G">Gris</option>
                            <option value="Gr">Verde</option>
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

        <div class="col-md-12 col-sm-12" id="rep-rre" hidden>
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">
                        Reporte de Recepci&oacute;n de efectivo para ventas en cuotas
                    </h3>
                </div>

                <div class="col-md-12">
                    <button class="btn btn-success m-b-20" id="download" type="button">
                        Exportar Excel
                    </button>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table id="list-rre" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Transacción &uacute;nica</th>
                                    <th>MSISDN</th>
                                    <th>Organizaci&oacute;n</th>
                                    <th>Vendedor</th>
                                    <th>Coordinador</th>
                                    <th>Cuota</th>
                                    <th>Monto</th>
                                    <th>Fecha venta</th>
                                    <th>Fecha creación</th>
                                    <th>Estatus</th>
                                    <th>Fecha Aprob/Rechazo</th>
                                    <th>Alerta</th>
                                    <th>Fecha Conciliación</th>
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
            endDate: new Date()
        }

        $('#dateb').datepicker(config);

        $('#datee').datepicker(config);

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

            if(coor)
                coor.clearOptions();

            var sel = $('#seller')[0].selectize;

            if(sel)
                sel.clearOptions();
        });

        $('#coord').on('change', function(e){
            var sel = $('#seller')[0].selectize;

            if(sel)
                sel.clearOptions();
        });

        $('#org').selectize({
            valueField: 'id',
            labelField: 'business_name',
            searchField: 'business_name'
        });

        $('#alert').selectize();

        $('#status').selectize();

        $('#search').on('click', function(e){
            $('.preloader').show();

            if ($.fn.DataTable.isDataTable('#list-rre')){
                $('#list-rre').DataTable().destroy();
            }

            $('#list-rre').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('getRREInstDT')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.dateb = $('#dateb').val();
                        d.datee = $('#datee').val();
                        d.coord = $('#coord').val();
                        d.seller = $('#seller').val();
                        d.org = $('#org').val();
                        d.status = $('#status').val();
                        d.alert = $('#alert').val();
                    },

                    type: "POST"
                },
                initComplete: function(settings, json){
                    $(".preloader").fadeOut();
                    $('#rep-rre').attr('hidden', null);
                },
                deferRender: true,
                order: [[ 7, "desc" ]],
                columns: [
                    {data: 'unique_transaction', orderable: false},
                    {data: 'msisdn', orderable: false},
                    {data: 'org', searchable: false, orderable: false},
                    {data: 'seller', searchable: false, orderable: false},
                    {data: 'coordinador', searchable: false, orderable: false},
                    {data: 'quote', searchable: false, orderable: false},
                    {data: 'amount', searchable: false, orderable: false},
                    {data: 'date_sell', searchable: false},
                    {data: 'date_reg', searchable: false},
                    {data: 'status', searchable: false, orderable: false},
                    {data: 'date_proc', searchable: false},
                    {
                        data: null,
                        render: function(data,type,row,meta){
                            var label = '';

                            if(row.alert == 'Azul')
                                label = 'label-info';

                            if(row.alert == 'Rojo')
                                label = 'label-danger';

                            if(row.alert == 'Naranja')
                                label = 'label-warning';

                            if(row.alert == 'Verde')
                                label = 'label-success';

                            if(row.alert == 'Gris')
                                label = 'label-default';

                            return '<label class="label '+label+' label-rouded" style="width:75px">'+row.alert+'</label>';
                        },
                        searchable: false,
                        orderable: false,
                    },
                    {data: 'date_conc', orderable: false}
                ]
            });
        });

        $('#download').on('click', function(e){
            var data = $("#filterConc").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');

            $(".preloader").fadeIn();

            $.ajax({
                type: "POST",
                url: "{{route('downloadRepRREInst')}}",
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