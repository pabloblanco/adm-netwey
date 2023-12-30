<style>
    #table-detail {
        display: none;
    }

    .text-data {
        text-align: center;
        background-color: #6786ff;
    }

    #list_detail_leave_request {
        display: none;
    }

    #alert_leave_request {
        display: none;
        text-align: center;
        font-weight: bold;
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Solicitud de Bajas</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Usuarios</a></li>
                <li class="active">Solicitud de bajas</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
    <div class="row" id="list_users">
        <section class="m-t-40">
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">
                        Listado de Usuarios
                    </h3>
                </div>
                <div class="col-md-12 p-t-20">
                    <div class="table-responsive">
                        <table id="tablebt" class="table table-striped">
                            <thead>
                            <tr>
                                <th>Correo Electronico</th>
                                <th>Nombre y Apellido</th>
                                <th>Cargo</th>
                                <th></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="alert alert-warning" role="alert" id="alert_leave_request">
      Este usuario ya posee una solicitud de baja.
    </div>

    <div class="row" id="list_detail_leave_request">
        <section class="m-t-40">
            <div class="row white-box">
                <div class="col-md-2 text-center">
                    <button onclick="showModal(null, 2)">Regresar</button>
                </div>
                <div class="col-md-8">
                    <h3 class="text-center">
                        Detalles de la Solicitud de Bajas
                    </h3>
                </div>

                <div class="col-md-2"></div>

                <div class="col-md-12 p-t-20">
                    
                    <input type="hidden" id="user_email">
                    <div class="form-group col-md-12">
                        <label for="exampleFormControlSelect1">Motivo Solicitud de Baja</label>
                        <select class="form-control" id="reason">
                            <option value="">Seleccionar...</option>
                            @foreach($reason_dimisal as $reason)
                                <option value="{{$reason->id}}">{{$reason->reason}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="table-detail">
                        <div class="card">
                            <div class="card-header">
                                <h4>Ventas de las  Ultimas 2 Semanas</h4>
                            </div>
                            <div class="card-body">
                                <div class="col-md-10 offset-md-1">
                                    <table class="table table-striped" id="detail_sales">
                                        <thead>
                                        <tr>
                                            <th scope="col">Equipo</th>
                                            <th scope="col">Tipo Producto</th>
                                            <th scope="col">MSISDN</th>
                                            <th scope="col">Monto</th>
                                            <th scope="col">Fecha</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <br>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-header">Efectivo en Ventas</div>
                                <span id="cash_request"></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-header">Efectivo en Equipos</div>
                                <span id="cash_article_total"></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-header">Total en Efectivo</div>
                                <span id="cash_total"></span>
                            </div>
                        </div>
                    </div>

                    <br>

                    <div class="card">
                        <div class="card-header">
                            <h4>Inventario Asignado</h4>
                        </div>
                        <div class="card-body">
                            <div class="col-md-10 offset-md-1">
                                <table class="table table-striped" id="detail_inventory">
                                    <thead>
                                        <tr>
                                            <th scope="col">Equipo</th>
                                            <th scope="col">MSISDN</th>
                                            <th scope="col">Tipo de Producto</th>
                                            <th scmope="col">Fecha Asignacion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <br><br>

                    <div class="card">
                      <div class="card-header">
                        <h5 class="text-center">Carga de Comprobantes</h5>
                      </div>
                      <div class="card-body">
                        
                        <form id="files_form"  enctype="multipart/form-data">
                          <div class="form-group col-md-6">
                            <label for="file1">Comprobante Nro 1</label>
                            <input type="file" class="form-control-file" id="file1" name="file1">
                          </div>

                          <div class="form-group col-md-6">
                            <label for="file2">Comprobante Nro 2</label>
                            <input type="file" class="form-control-file" id="file2" name="file2">
                          </div>

                          <div class="form-group col-md-6">
                            <label for="file3">Comprobante Nro 3</label>
                            <input type="file" class="form-control-file" id="file3" name="file3">
                          </div>
                        </form>
                      </div>
                    </div>

                    <br>

                    <div class="row">
                        <div class="col-md-1 offset-md-11">
                            <button type="button" class="btn btn-primary" id="send_request_leave" disabled>Solicitar</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script type="text/javascript">

    var data = {};

    function showModal(email, action) {
        $('.preloader').show();

        if ( action == 1 ) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                url: "{{route('getUserDetail')}}",
                data: {
                    typeSearch: 2,
                    email: email,
                },
                dataType: "json",
                success: function(response){

                    $(".preloader").fadeOut();

                    $('#cash_request').text(response.cash_request)
                    $('#cash_article_total').text(response.article_request)
                    $('#cash_total').text(response.cash_total)

                    $("#detail_inventory tbody").empty();
                    $("#detail_inventory tbody").append(response.tr_articles);

                    if ( response.check_user_req == 1 ) {
                        $('#alert_leave_request').css('display', 'block');
                    }

                    data = response;
                },
                error: function(err){
                    $(".preloader").fadeOut();
                    swal('Error','No se pudo leer la informacion.','error');
                }
            });

            if ( $('#reason').val() != "" ) {
                $('#send_request_leave').prop('disabled', false);
            } else {
                $('#send_request_leave').prop('disabled', true);
            }

            $('#user_email').val(email)
            $('#list_users').css('display', 'none');
            $('#list_detail_leave_request').css('display', 'block');
        }
        else if ( action == 2 ) {
            clearValues();
            $('#list_users').css('display', 'block');
            $('#list_detail_leave_request').css('display', 'none');
            $(".preloader").fadeOut();
        }
    }

    function sendFiles(req_leave_id) {

        let formData = new FormData();  
        for (let i=1; i<=3; i++) {
            var file = document.getElementById('file'+i).files[0];

            if ( file ) {
                formData.append('file'+i, document.getElementById('file'+i).files[0]);
            }
        }     

        formData.append('req_leave_id', req_leave_id);    
        formData.append('type', 'document');

         $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                url: "{{route('requestLeave')}}",
                data: formData,
                dataType: "urlencode",
                processData: false,
                contentType: false,
                success: function(response){
                    $(".preloader").fadeOut();
                    swal('Solicitud de Baja','Solicitud realizada con exito.!','success');
                },
                error: function(err){
                    $(".preloader").fadeOut();

                    if ( err.status ) {
                      swal('Solicitud de Baja','Solicitud realizada con exito.!','success');      
                    }
                    else {
                      swal('Error','Problemas con la carga de comprobantes.','error');
                    }
                }
            });
    }

    function clearValues() {
        $('#reason').prop('selectedIndex',0);
        $("#detail_sales tbody").empty();
        $('#cash_request').text("")
        $('#cash_article_total').text("")
        $('#cash_total').text("")
        $('#cash_article_total').text("")
        $("#detail_inventory tbody").empty();
        $('#table-detail').css('display', 'none');
        $('#alert_leave_request').css('display', 'none');
    }

    $(document).ready(function () {
        drawTable = function(){
            //Si ya existe la tabla se elimina
            if ($.fn.DataTable.isDataTable('#tablebt')){
                $('#tablebt').DataTable().destroy();
            }

            $('.preloader').show();

            var tableUsers = $('#tablebt').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('getUserRequestLeave')}}",
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
                    {data: 'email',searchable: true},
                    {data: 'fullname',searchable: true},
                    {data: 'platform',searchable: false},
                    {data: 'action',searchable: false}
                ]
            });
        }

        $('#send_request_leave').on('click', function() {
            $(".preloader").fadeIn();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                url: "{{route('requestLeave')}}",
                data: {
                    email: $('#user_email').val(),
                    reason: $('#reason').val(),
                    article_request: data['article_request'],
                    cant_abonos: data['cant_abonos'],
                    cash_abonos: data['cash_abonos'],
                    cash_fibra: data['cash_fibra'],
                    cash_hbb: data['cash_hbb'],
                    cash_request: data['cash_request'],
                    cash_telf: data['cash_telf'],
                    cash_total: data['cash_total'],
                    days_cash_request: data['days_cash_request'],
                    type: 'request'
                },
                dataType: "json",
                success: function(response){

                  if ( response.message ) {
                    $(".preloader").fadeOut();

                    swal('Solicitud de Baja',response.message, 'warning');
                    return false;
                  }
                  else {
                    sendFiles(response.req_leave_id)
                  }
                    
                },
                error: function(err){
                  $(".preloader").fadeOut();
                  swal('Error','No se pudo generar la solicitud de baja.','error');
                }
            });            
        })

        //mustra la primera tabla cuando carga la pagina
        drawTable();

        $('#reason').on('change', function () {

            if ( $('#reason').val() != "" ) {
                $('#send_request_leave').prop('disabled', false);
            } else {
                $('#send_request_leave').prop('disabled', true);
            }
            //request for get the user  sales from the last 2 week
            if ( $('#reason').val() == 6 ) {

                $(".preloader").fadeIn();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "POST",
                    url: "{{route('getUserDetail')}}",
                    data: {
                        typeSearch: 1,
                        email: $('#user_email').val(),
                    },
                    dataType: "json",
                    success: function(response){
                        $(".preloader").fadeOut();
                        $("#detail_sales tbody").empty();
                        $("#detail_sales tbody").append(response.data);
                    },
                    error: function(err){
                        $(".preloader").fadeOut();
                        swal('Error','No se pudo generar la solicitud de baja.','error');
                    }
                });

                $('#table-detail').css('display', 'block');

            } else {
                $('#table-detail').css('display', 'none');
            }
        })
    });
</script>