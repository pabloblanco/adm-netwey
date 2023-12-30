<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Art&iacute;culos no activos</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Clientes</a></li>
                <li class="active">Art&iacute;culos no activos</li>
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
                        Listado de art&iacute;culos no activos
                    </h3>
                    <button class="btn btn-success" id="download" type="button">
                        Exportar Excel
                    </button>
                </div>
                <div class="col-md-12 p-t-20">
                    <div class="table-responsive">
                        <table id="tablebt" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Producto</th>
                                    <th>MSISDN</th>
                                    <th>IMEI</th>
                                    <th>Vendedor</th>
                                    <th>Fecha Venta</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function () {
        drawTable = function(){
            //Si ya existe la tabla se elimina
            if ($.fn.DataTable.isDataTable('#tablebt')){
                $('#tablebt').DataTable().destroy();
            }

            $('.preloader').show();

            var tableClients = $('#tablebt').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('articBuyDT')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                    },
                    type: "POST"
                },
                initComplete: function(settings, json){
                    $(".preloader").fadeOut();
                },
                deferRender: true,
                columns: [
                    {data: 'id',searchable: false},
                    {data: 'title',searchable: false},
                    {data: 'msisdn',searchable: true},
                    {data: 'imei',searchable: false},
                    {data: 'seller',searchable: false},
                    {data: 'date',searchable: false}
                ]
            });
        }

        //mustra la primera tabla cuando carga la pagina
        drawTable();

        $('#download').on('click', function(){
            var value = '';
            var data = '_token=' + $('meta[name="csrf-token"]').attr('content') + '&emails=' + value;

            $(".preloader").fadeIn();

            $.ajax({
                type: "POST",
                url: "{{route('articBuyDW')}}",
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
                        var data = '_token=' + $('meta[name="csrf-token"]').attr('content') + '&emails=' + value;

                        $(".preloader").fadeIn();

                        $.ajax({
                            type: "POST",
                            url: "{{route('articBuyDW')}}",
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