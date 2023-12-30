<style type="text/css">
    .selectize-input:after{
        content: none !important;
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Reporte de RRE</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reporte</a></li>
                <li class="active">Reporte de RRE</li>
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
            </div>
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Vendedor</label>
                        <select id="opefec" name="opefec" class="form-control">
                            <option value="">Seleccione un vendedor</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Estatus</label>
                        <select id="status" name="status" class="form-control">
                            <option value="">Seleccione un estatus</option>
                            <option value="V">Enviado aprobaci&oacute;n</option>
                            <option value="P">Aprobado</option>
                            <option value="I">Rechazado</option>
                            <option value="A">Conciliado</option>
                        </select>
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

        <section class="m-t-40" id="rep-rre" hidden>
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">
                        Reporte de RRE
                    </h3>
                </div>

                <div style="margin: 0px auto;">
                    <div class="col-md-12 p-t-20">
                        <button class="btn btn-success m-b-20" id="download" type="button">
                            Exportar Excel
                        </button>
                        <div class="table-responsive">
                            <table id="listrre" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Vendedor</th>
                                        <th>Fecha creación</th>
                                        <th>monto</th><!--mostrar detalle de ventas-->
                                        <th>Coordinador</th>
                                        <th>Estatus</th>
                                        <th>Fecha Aprob/Rechazo</th>
                                        <th>Alerta</th>
                                        <th>Fecha Conciliaci&oacute;n</th>
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

<div class="modal modalAnimate" id="showSales" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <input type="hidden" id="regIdModal" name="regIdModal">
            <div class="modal-header">
                <button type="button" class="modal_close_btn close" id="modal_close_x" data-modal="#showSales">&times;</button>
                <h4 class="modal-title">Detalle de venta</h4>
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
                        url: '{{route("get_user_by_deposit")}}',
                        type: 'POST',
                        dataType: 'json',
                        cache: false,
                        data: {
                            q: query,
                            org: $('#org').val()
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
                            q: query,
                            org: $('#org').val(),
                            coord: $('#coord').val()
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
        $('#opefec').selectize(configSelect);

        $('#org').on('change', function(e){
            var sel = $('#coord')[0].selectize;

            if(sel)
                sel.clearOptions();

            var ope = $('#opefec')[0].selectize;

            if(ope)
                ope.clearOptions();
        });

        $('#coord').on('change', function(e){
            var sel = $('#opefec')[0].selectize;

            if(sel)
                sel.clearOptions();
        });


        $('#search').on('click', function(e){
            $('.preloader').show();

            if ($.fn.DataTable.isDataTable('#listrre')){
                $('#listrre').DataTable().destroy();
            }

            $('#listrre').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('repRre')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.dateb = $('#dateb').val();
                        d.datee = $('#datee').val();
                        d.coord = $('#coord').val();
                        d.seller = $('#opefec').val();
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
                order: [[ 0, "desc" ]],
                columns: [
                    {data: 'id'},
                    {data: 'seller', searchable: false, orderable: false},
                    {data: 'date_reg', searchable: false},
                    //{data: 'amount', searchable: false},
                    {
                        data: null,
                        render: function(data,type,row,meta){
                            return '<span onClick="showSales(\''+row.id+'\')" class="nw-link showSalesLink">'+row.amount+'</span>';
                        },
                        searchable: false,
                    },
                    {data: 'coord', searchable: false, orderable: false},
                    {data: 'status', searchable: false},
                    {data: 'date_step2', searchable: false, orderable: false},
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
                        orderable: false
                    },
                    {data: 'date_process', searchable: false}
                ]
            });
        });
        // $('.showSalesLink').on('click',()=>{
        //     console.log('okokoko');
        // });
        showSales = (id) => {
            $('#regIdModal').val(id);
            $('#showSales').modal({backdrop: 'static', keyboard: false});
        }

        $('#showSales').on('show.bs.modal', function (event){
            // var button = $(event.relatedTarget),
            //     id = button.data('id');
            var id = $('#regIdModal').val();
            if(id && id != ''){
                $('.preloader').show();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    async: true,
                    url: "{{route('getSalesDetail')}}",
                    method: 'POST',
                    data: {id: id},
                    dataType: 'json',
                    success: function (res) {
                        $(".preloader").fadeOut();

                        if(res.success){
                            $('#showSales .modal-body').html(res.html);
                        }else{
                            alert(res.msg);
                            $('#showSales .close').trigger('click');
                        }
                    },
                    error: function (res) {
                        alert('No se pudo cargar el detalle de venta.');
                        $(".preloader").fadeOut();
                        $('#showSales .close').trigger('click');
                    }
                });
            }
        });

        $('#showSales .close').on('click', function (event){
            $('#showSales .modal-body').html('');
            $('#regIdModal').val('');
        });

        $('#download').on('click', function(e){
            var value = '';
            var data = $("#filterConc").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content') + '&emails=' + value;

            $(".preloader").fadeIn();

            $.ajax({
                type: "POST",
                url: "{{route('downloadRepConc')}}",
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
<script src="js/common-modals.js"></script>