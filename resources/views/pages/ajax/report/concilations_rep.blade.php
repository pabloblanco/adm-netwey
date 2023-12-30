<style type="text/css">
    .selectize-input:after{
        content: none !important;
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Reporte de conciliaciones</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reporte</a></li>
                <li class="active">Reporte de conciliaciones</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <form id="filterConc" name="filterConc" class=" text-left" method="POST">
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Fecha desde</label>
                        <input type="text" name="dateb" id="dateb" class="form-control" placeholder="dd-mm-yyyy" value="{{ date('d-m-Y', strtotime('- 30 days', time())) }}">
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Fecha hasta</label>
                        <input type="text" name="datee" id="datee" class="form-control" placeholder="dd-mm-yyyy" value="{{ date('d-m-Y') }}">
                    </div>
                </div>


                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Coordinador</label>
                        <select id="coord" name="coord" class="form-control" placeholder="Seleccione un Coordinador" data-msg="Debe seleccionar un usuario." required style="width: 100% !important;">
                            <option value="">Seleccione un Coordinador</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Ope. Efectivo</label>
                        <select id="opefec" name="opefec" class="form-control" placeholder="Seleccione un Coordinador" data-msg="Debe seleccionar un usuario." required style="width: 100% !important;">
                            <option value="">Seleccione un Operario</option>
                        </select>
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

        <section class="m-t-40" id="rep-conc" hidden>
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">
                        Reporte de conciliaciones
                    </h3>
                </div>

                <div style="margin: 0px auto;">
                    <div class="col-md-12 p-t-20">
                        <button class="btn btn-success m-b-20" id="download" type="button">
                            Exportar Excel
                        </button>
                        <div class="table-responsive">
                            <table id="listConc" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Id depósito</th>
                                        <th>Monto</th>
                                        <th>Banco</th>
                                        <th>Ope. Efectivo</th>
                                        <th>Coordinador</th>
                                        <th>Supervisor</th>
                                        <th>Cod. Depósito</th>
                                        <th>Fecha</th>
                                        <th>Motivo</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script type="text/javascript">
    $('#dateb').prop('readonly', true);
    $('#datee').prop('readonly', true);

    $(document).ready(function () {
        var config = {
            autoclose: true,
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            language: 'es',
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
                        url: '{{route("get_user_by_deposit")}}',
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
                        url: '{{route("getOpeEfec")}}',
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
            render: {
                option: function(item, escape) {
                    return '<p>'+item.name+' '+item.last_name+'</p>';
                }
            }
        };

        configSelect.load = ajax1;
        $('#coord').selectize(configSelect);

        configSelect.load = ajax2;
        $('#opefec').selectize(configSelect);

        $('#search').on('click', function(e){
            $('.preloader').show();

            if ($.fn.DataTable.isDataTable('#listConc')){
                $('#listConc').DataTable().destroy();
            }

            $('#listConc').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('getRepConc')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.dateb = $('#dateb').val();
                        d.datee = $('#datee').val();
                        d.coord = $('#coord').val();
                        d.opefec = $('#opefec').val();
                    },
                    type: "POST"
                },
                initComplete: function(settings, json){
                    $(".preloader").fadeOut();
                    $('#rep-conc').attr('hidden', null);
                },
                deferRender: true,
                order: [[ 6, "desc" ]],
                columns: [
                    {data: 'dep'},
                    {data: 'amount', searchable: false},
                    {data: 'bank'},
                    {data: 'ope_user'},
                    {data: 'coord'},
                    {data: 'sup_name'},
                    {data: 'cod_dep'},
                    {data: 'date', searchable: false},
                    {data: 'reason_deposit', searchable: false}
                ]
            });
        });

        $('#download').on('click', function(e){
            var value = '';
            var data = $("#filterConc").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content') + '&emails=' + value;

            $(".preloader").fadeIn();

            $.ajax({
                type: "POST",
                url: "{{route('downloadRepConc')}}",
                data: data,
                dataType: "json",
                success: function(response){
                    $(".preloader").fadeOut();
                    swal('Generando reporte','El reporte estara disponible en unos minutos.','success');
                },
                error: function(err){
                    $(".preloader").fadeOut();
                    swal('Error','No se pudo generar el reporte.','error');
                }
            });
            /*swal('Por favor ingrese el o los email(s) separados por ","', {
                content: {
                    element: "input",
                    attributes: {
                      placeholder: "ejm@correo.com, ejm2@correo.com",
                      type: "email",
                    }
                },
                buttons: {
                    cancel: true,
                    confirm: "Enviar",
                },
                closeOnClickOutside: true,
            })
            .then((value) => {
                if(value !== null){
                    if(value && value != ''){
                        var data = $("#filterConc").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content') + '&emails=' + value;

                        $(".preloader").fadeIn();

                        $.ajax({
                            type: "POST",
                            url: "{{route('downloadRepConc')}}",
                            data: data,
                            dataType: "text",
                            success: function(response){
                                $(".preloader").fadeOut();

                                swal('El reporte sera enviado a los correos especificados.');
                            },
                            error: function(err){
                                $(".preloader").fadeOut();
                            }
                        });
                    }else{
                        swal('Debe ingresar uno o mas emails.');
                    }
                }
            });*/
        });
    });
</script>