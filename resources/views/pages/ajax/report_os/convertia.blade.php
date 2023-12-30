<style type="text/css">
    .selectize-input:after{
        content: none !important;
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Reporte de ventas de convertia</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reporte</a></li>
                <li class="active">Reporte de ventas de convertia</li>
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
                        <input type="text" name="dateb" id="dateb" class="form-control" placeholder="dd-mm-yyyy">
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Fecha hasta</label>
                        <input type="text" name="datee" id="datee" class="form-control" placeholder="dd-mm-yyyy">
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Estatus</label>
                        <select id="status" name="status" class="form-control">
                            <option value="">Seleccione un status</option>
                            <option value="1">Creado</option>
                            <option value="2">Recolectado</option>
                            <option value="3">En camino</option>
                            <option value="6">Entregado</option>
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
                        Ventas de Convertia
                    </h3>
                </div>

                <div class="col-md-12">
                    <button class="btn btn-success m-b-20" id="download" type="button">
                        Exportar Excel
                    </button>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table id="list-sc" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Transacci&oacute;n</th>
                                    <th>Nombre</th>
                                    <th>Tel&eacute;fono</th>
                                    <th>Correo</th>
                                    <th>Requiere Factura</th>
                                    <th>RFC/INE</th>
                                    <th>DN</th>
                                    <th>Pack</th>
                                    <th>Fecha compra</th>
                                    <th>Orden Netwey</th>
                                    <th>Orden env&iacute;o</th>
                                    <th>Estatus env&iacute;o</th>
                                    <th>PDF 99min</th>
                                    <th>Estatus DN</th>
                                    <th>Monto env&iacute;o</th>
                                    <th>Monto pack</th>
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

        $('#status').selectize();

        $('#search').on('click', function(e){
            $('.preloader').show();

            if ($.fn.DataTable.isDataTable('#list-sc')){
                $('#list-sc').DataTable().destroy();
            }

            $('#list-sc').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('getDTconvertiaSales')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.dateb = $('#dateb').val();
                        d.datee = $('#datee').val();
                        d.status = $('#status').val();
                    },

                    type: "POST"
                },
                initComplete: function(settings, json){
                    $(".preloader").fadeOut();
                    $('#rep-sc').attr('hidden', null);
                },
                deferRender: true,
                order: [[ 8, "desc" ]],
                columns: [
                    {data: 'transaction', orderable: false},
                    {data: 'name', orderable: false},
                    {data: 'phone_home', orderable: false},
                    {data: 'email', orderable: false},
                    {data: 'invoice', searchable: false, orderable: false},
                    {data: 'dni', orderable: false},
                    {data: 'msisdn', orderable: false},
                    {data: 'pack', searchable: false, orderable: false},
                    {data: 'date_buy', searchable: false},
                    {data: 'order', searchable: false, orderable: false},
                    {data: 'order_del', orderable: false},
                    {data: 'status_del', searchable: false, orderable: false},
                    {
                        data: null,
                        render: function(data,type,row,meta){
                            if(row.url_pdf)
                                return '<a href="'+row.url_pdf+'" target="_blank">Descargar</a>';
                            return 'N/A';
                        }, 
                        searchable: false,
                        orderable: false
                    },
                    {data: 'status_dn', searchable: false, orderable: false},
                    {data: 'amount_del', searchable: false, orderable: false},
                    {data: 'amount', searchable: false, orderable: false}
                ]
            });
        });

        $('#download').on('click', function(e){
            var data = $("#filterConc").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');

            $(".preloader").fadeIn();

            $.ajax({
                type: "POST",
                url: "{{route('downloadDTconvertiaSales')}}",
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