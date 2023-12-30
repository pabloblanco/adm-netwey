<style>
  
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Listado de Solicitudes de Bajas</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Usuarios</a></li>
                <li class="active">Listado de Solicitudes de bajas</li>
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
                        Solicitudes Realizadas
                    </h3>
                </div>
                <div class="col-md-12 p-t-20">
                    <div class="row">
                      <div class="col-md-8"></div>
                      <div class="col-md-4">
                        <div class="form-group">
                      <label for="filterStatus">Busqueda por Estatus</label>
                      <select class="form-control" id="filterStatus" name=filterStatus>
                        <option value="">Seleccionar...</option>
                        <option value="R">Solicitado</option>
                        <option value="P">En Proceso</option>
                        <option value="D">Rechazado</option>
                      </select>
                    </div>
                      </div>
                    </div>
                    <div class="table-responsive">
                        <table id="tablebt" class="table table-striped">
                          <thead>
                            <tr>
                                <th>Fecha Solicitud</th>
                                <th>Correo Electronico</th>
                                <th>Nombre y Apellido</th>
                                <th>Motivo de Baja</th>
                                <th>Deuda en Efectivo</th>
                                <th>Deuda en Equipos</th>
                                <th>Deuda Total</th>
                                <th>Estatus</th>
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

    var data = {};

    $(document).ready(function () {
        drawTable = function(status=null){
            //Si ya existe la tabla se elimina
            if ($.fn.DataTable.isDataTable('#tablebt')){
                $('#tablebt').DataTable().destroy();
            }

            $('.preloader').show();

            var tableUsers = $('#tablebt').DataTable({
                initComplete: function () {
                    this.api().columns().every( function (i) {
                        if ( i == 8 ) {
                            var column = this;
                            var select = $('<select><option value=""></option></select>')
                                .appendTo( $('.searchItem') )
                                .on( 'change', function () {
                                    var val = $.fn.dataTable.util.escapeRegex(
                                        $(this).val()
                                    );

                                    column
                                        .search( val ? '^'+val+'$' : '', true, false )
                                        .draw();
                                } );


                            column.data().unique().sort().each( function ( d, j ) {
                                if ( d == null ) {
                                    select.append( '<option value="">Todos</option>' )
                                }
                                else {
                                    select.append( '<option value="'+d+'">'+d+'</option>' )
                                }
                            });
                        }
                    });
                },
                searching: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('listRequestLeave')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.status=status;
                    },
                    type: "POST"
                },
                initComplete: function(settings, json){
                    $(".preloader").fadeOut();
                },
                deferRender: true,
                columns: [
                    {data: 'date_reg_req',searchable: true},
                    {data: 'user_dismissal',searchable: true},
                    {data: 'fullname',searchable: true},
                    {data: 'reason',searchable: false},
                    {data: 'cash_request',searchable: false},
                    {data: 'article_request',searchable: false},
                    {data: 'cash_total',searchable: false},
                    {data: 'status_req',searchable: true},
                ]
            });
        }

        //mustra la primera tabla cuando carga la pagina
        drawTable();

        $('#filterStatus').on('change', function(){

          let status = [];

          if ( $('#filterStatus').val() != "" ) {
            status.push($('#filterStatus').val());
            drawTable(status);
          }

        })
    });
</script>