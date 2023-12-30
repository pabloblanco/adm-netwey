<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Recompra</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Clientes</a></li>
                <li class="active">Recompra</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
    <div class="white-box">
        <div class="row">
            <div class="col-md-12">
                <h3 class="text-center">
                    Carga de archivos recompras
                </h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <button class="btn btn-success" id="upload" type="button">
                    Cargar archivo
                </button>

                <input type="file" name="csv" id="csv" accept="csv, CSV" hidden>

                <div class="alert alert-warning m-t-10" style="padding: 5px;">
                    El archivo debe ser CSV separado por <b>","</b> y tener las siguientes columnas: <b>msisdn,contesto,acepto,comentario</b>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 p-t-20">
                <div class="table-responsive">
                    <table id="tablebt" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Archivo</th>
                                <th>Clientes llamados</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var drawTable = function(){
            //Si ya existe la tabla se elimina
            if ($.fn.DataTable.isDataTable('#tablebt')){
                $('#tablebt').DataTable().destroy();
            }

            $('.preloader').show();

            var tableClients = $('#tablebt').DataTable({
                language: {
                    sProcessing:     "Procesando...",
                    sLengthMenu:     "Mostrar _MENU_ registros",
                    sZeroRecords:    "No se encontraron resultados",
                    sEmptyTable:     "Ningún dato disponible en esta tabla",
                    sInfo:           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    sInfoEmpty:      "Mostrando registros del 0 al 0 de un total de 0 registros",
                    sInfoFiltered:   "(filtrado de un total de _MAX_ registros)",
                    sInfoPostFix:    "",
                    sSearch:         "Buscar:",
                    sUrl:            "",
                    sInfoThousands:  ",",
                    sLoadingRecords: "Cargando...",
                    oPaginate: {
                        sFirst:    "Primero",
                        sLast:     "Último",
                        sNext:     "Siguiente",
                        sPrevious: "Anterior"
                    },
                    oAria: {
                        sSortAscending:  ": Activar para ordenar la columna de manera ascendente",
                        sSortDescending: ": Activar para ordenar la columna de manera descendente"
                    }
                },
                searching: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "api/client/buy_back/get_table",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                    },
                    type: "POST"
                },
                initComplete: function(settings, json){
                    $(".preloader").fadeOut();
                },
                order: [[ 2, "DESC" ]],
                deferRender: true,
                columns: [
                    {data: 'file'},
                    {data: 'clients_ok'},
                    {data: 'date_reg'}
                ]
            });
        }

        $('#upload').on('click', function(e){
            $('#csv').trigger('click');
        });

        $('#csv').on('change', function(e){
            let file = document.getElementById('csv');

            if(file.value && file.value != '' && file.files[0].type == 'text/csv'){
                $(".preloader").fadeIn();

                let params = new FormData();
                params.append('csv', file.files[0]);

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    contentType: false,
                    processData: false,
                    cache: false,
                    async: true,
                    url: 'api/client/buy_back/process_file',
                    method: 'POST',
                    data: params,
                    success: function (res) {
                        alert(res.msg);
                        $(".preloader").fadeOut();
                        drawTable();
                    },
                    error: function (res) {
                        alert('ocurrio un error');
                        $(".preloader").fadeOut();
                    }
                });
            }else{
                alert('Debe seleccionar un archivo csv.');
            }

            file.value = '';
        });

        drawTable();
    });
</script>