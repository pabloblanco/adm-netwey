<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Listado de financiamiento</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Inventario</a></li>
                <li class="active">lista de financiamiento</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <section class="m-t-40">
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">
                        Financiamientos
                    </h3>
                    <button class="btn btn-success" id="show-modal" type="button" data-toggle="modal" data-target="#creUpdFin">
                        Crear financiamiento
                    </button>
                </div>
                <div class="col-md-12 p-t-20">
                    <div class="table-responsive">
                        <table id="listfinancing" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Acciones</th>
                                    <th>Nombre</th>
                                    <th>Monto Finaciado</th>
                                    <th>Cuota semanal</th>
                                    <th>Cuota quincenal</th>
                                    <th>Cuota mensual</th>
                                    <th>Total a pagar</th>
                                    <th>Estatus</th>
                                    <th>Fecha de registro</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="modal fade" id="creUpdFin" role="dialog">
    <div class="modal-dialog" id="modal01">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Crear Financiamiento</h4>
            </div>
            <div class="modal-body">
                <form id="fin_form">
                    <input type="hidden" name="financing" id="financing">
                    <div class="form-body">
                        <div class="row">
                        <div class="col-md-12">
                          <div class="panel panel-info">
                            <div class="panel-wrapper collapse in" aria-expanded="true">
                                <div class="panel-body">
                                    <h3 class="box-title">Datos del financiamiento</h3>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Nombre*</label>
                                                <input type="text" id="name" name="name" class="form-control" placeholder="Nombre...">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Monto financiado*</label>
                                                <input type="number" id="amountF" name="amountF" class="form-control" placeholder="0">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Monto total*</label>
                                                <input type="number" id="amountT" name="amountT" class="form-control" placeholder="Monto total a pagar">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Pago semanal*</label>
                                                <input type="number" id="pays" name="pays" class="form-control" placeholder="0">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Pago Quincenal*</label>
                                                <input type="number" id="payq" name="payq" class="form-control" placeholder="0">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Pago Mensual*</label>
                                                <input type="number" id="paym" name="paym" class="form-control" placeholder="0">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Estatus*</label>
                                                <select id="status" name="status" class="form-control">
                                                    <option value="A" selected>Activo</option>
                                                    <option value="I">Inactivo</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                          </div>
                        </div>
                        </div>
                        <div class="form-actions modal-footer">
                            <button type="button" class="btn btn-success" id="save-fin">Guardar</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {

        deleteFinan = function(e){
            var fi = $(e.currentTarget).data('fi');

            if(fi && fi != ''){
                if (confirm('¿Esta seguro de eliminar el financiamiento?')){
                    $('.preloader').show();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        async: true,
                        url: "{{route('financing.delete')}}",
                        method: 'POST',
                        dataType: 'json',
                        data: {financing: fi},
                        success: function (res) {
                            if(!res.error){
                                alert('Financiamiento elminado exitosamente.');
                                drawTable();
                            }else{
                                alert(res.msg);
                            }
                            
                            $(".preloader").fadeOut();
                        },
                        error: function (res) {
                            $(".preloader").fadeOut();
                            alert('No se pudo eliminar el financiamiento, por favor intente mas tarde.');
                        }
                    });
                }
            }
        }

        editFinan = function(e){
            var fi = $(e.currentTarget).data('fi');

            if(fi && fi != ''){
                $('.preloader').show();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    async: true,
                    url: "{{route('financing.edit')}}",
                    method: 'POST',
                    dataType: 'json',
                    data: {financing: fi},
                    success: function (res) {
                        $(".preloader").fadeOut();
                        if(!res.error){
                            $('#creUpdFin').find('#name').val(res.data.name);
                            $('#creUpdFin').find('#amountF').val(res.data.amount_financing);
                            $('#creUpdFin').find('#amountT').val(res.data.total_amount);
                            $('#creUpdFin').find('#pays').val(res.data.SEMANAL);
                            $('#creUpdFin').find('#paym').val(res.data.MENSUAL);
                            $('#creUpdFin').find('#payq').val(res.data.QUINCENAL);
                            $('#creUpdFin').find('#status').val(res.data.status);
                            $('#creUpdFin').find('#financing').val(res.data.id);

                            $('#show-modal').trigger('click');
                        }else{
                            alert(res.msg);
                        }
                    },
                    error: function (res) {
                        $(".preloader").fadeOut();
                        alert('No se pudo eliminar el financiamiento, por favor intente mas tarde.');
                    }
                });
            }
        }

        drawTable = function(){
            if ($.fn.DataTable.isDataTable('#listfinancing')){
                $('#listfinancing').DataTable().destroy();
            }

            $('.preloader').show();

            $('#listfinancing').DataTable({
                searching: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('financing.listDT')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                    },
                    type: "POST"
                },
                initComplete: function(settings, json){
                    $('.delete-fi').bind('click', deleteFinan);
                    $('.edit-fi').bind('click', editFinan);
                    $(".preloader").fadeOut();
                },
                deferRender: true,
                order: [[ 7, "desc" ]],
                columns: [
                    {data: null, render: function(data,type,row,meta){
                        var html = '<button type="button" class="btn btn-info btn-md edit-fi" data-fi="'+row.id+'">Editar</button>';
                        html += '<button type="button" class="btn btn-danger btn-md delete-fi" data-fi="'+row.id+'">Eliminar</button>';

                        return html;
                    }, searchable: false, orderable: false},
                    {data: 'name'},
                    {data: 'amount_financing'},
                    {data: 'SEMANAL'},
                    {data: 'QUINCENAL'},
                    {data: 'MENSUAL'},
                    {data: 'total_amount'},
                    {data: 'status'},
                    {data: 'date_reg', searchable: false}
                ]
            });
        }

        drawTable();

        $('#creUpdFin').on('hide.bs.modal', function(e){
            $('#creUpdFin').find('#name').val('');
            $('#creUpdFin').find('#amountF').val('');
            $('#creUpdFin').find('#amountT').val('');
            $('#creUpdFin').find('#pays').val('');
            $('#creUpdFin').find('#paym').val('');
            $('#creUpdFin').find('#payq').val('');
            $('#creUpdFin').find('#status').val('A');
            $('#creUpdFin').find('#financing').val('');
        });


        $('#save-fin').on('click', function(e){
            if($('#fin_form').valid()){
                $('.preloader').show();
                var data = $('#fin_form').serialize();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    async: true,
                    url: "{{route('financing.create')}}",
                    method: 'POST',
                    data: data,
                    success: function (res) {
                        $('#creUpdFin').modal('hide');

                        alert(res.msg);
                        
                        if(!res.error){
                            drawTable();
                        }
                        $(".preloader").fadeOut();
                    },
                    error: function (res) {
                        $(".preloader").fadeOut();
                        alert('No se pudo crear el canal, por favor intente mas tarde.');
                    }
                });
            }
        });

        $('#fin_form').validate({
            rules: {
                name: {
                    required: true
                },
                amountF: {
                    required: true,
                    number: true
                },
                amountT: {
                    required: true,
                    number: true
                },
                pays: {
                    required: true,
                    number: true
                },
                paym: {
                    required: true,
                    number: true
                },
                payq: {
                    required: true,
                    number: true
                }
            },
            messages: {
                name: {
                    required: "debe escribir el nombre del financiamiento"
                },
                amountF: {
                    required: "Debe escribir el monto financiado",
                    number: "El monto debe ser númerico"
                },
                amountT: {
                    required: "Debe escribir el monro total a pagar",
                    number: "El monto debe ser númerico"
                },
                pays: {
                    required: "Debe escribir el monto a pagar con plan semanal",
                    number: "El monto debe ser númerico"
                },
                paym: {
                    required: "Debe escribir el monto a pagar con plan mensual",
                    number: "El monto debe ser númerico"
                },
                payq: {
                    required: "Debe escribir el monto a pagar con plan quincenal",
                    number: "El monto debe ser númerico"
                }
            }
        });
    });
</script>