<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Art&iacute;culos vendidos</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reportes</a></li>
                <li class="active">Art&iacute;culos vendidos</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <section class="m-t-40">
                <h3>Configuración del reporte</h3>
                <form class="form-horizontal" id="report_tb_form" method="POST" action="">
                    {{ csrf_field() }}
                    <div class="row">
                        {{-- @if (session('user')->profile->type == 'master') --}}
                            @if($orgs->count() > 1)
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">Organizaciones</label>
                                    <select id="org" name="org" class="form-control">
                                        <option value="">Todas</option>
                                        @foreach ($orgs as $org)
                                        <option value="{{$org->id}}">{{$org->business_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @endif

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">Coordinadores</label>
                                    <select id="supervisor" name="supervisor" class="form-control">
                                        <option value="">Todos</option>
                                        @foreach ($supervisors as $user)
                                        <option value="{{$user->email}}">{{$user->name}} {{$user->last_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        {{-- @endif --}}

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">Vendedores</label>
                                <select id="seller" name="seller" class="form-control">
                                    <option value="">Todos</option>
                                    <option value="only_supervisor">SÓLO TRANSACCIONES DEL SUPERVISOR</option>
                                    @foreach ($sellers as $user)
                                    <option value="{{$user->email}}" parent="{$user->parent_email}">{{$user->name}} {{$user->last_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                          <div class="form-group">
                            <label class="control-label">Conciliadas</label>
                            <select id="conciliation" name="conciliation" class="form-control">
                              <option value="">Todos</option>
                              <option value="Y">Si</option>
                              <option value="N">No</option>
                            </select>
                          </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">Fecha desde</label>
                                <div class="input-group">
                                    <input type="text" id="date_ini" name="date_ini" class="form-control" placeholder="Fecha de recepción">
                                    <span class="input-group-addon"><i class="icon-calender"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">Fecha hasta</label>
                                <div class="input-group">
                                    <input type="text" id="date_end" name="date_end" class="form-control" placeholder="Fecha de recepción">
                                    <span class="input-group-addon"><i class="icon-calender"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">Plan</label>
                                <select id="service" name="service" class="form-control">
                                    <option value="">Todos</option>
                                    @foreach ($services as $service)
                                    <option value="{{$service->id}}">{{$service->title}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">Producto</label>
                                <select id="product" name="product" class="form-control">
                                    <option value="">Todos</option>
                                    @foreach ($products as $product)
                                    <option value="{{$product->id}}">{{$product->description}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 text-center">
                        <button class="btn btn-success" id="btn-report" type="button">Consultar</button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <div class="container" id="table-content" hidden>
        <div class="white-box m-t-40">
            <div class="row">
                <div class="col-md-12">
                    <h3 class="text-center">
                        Art&iacute;culos vendidos
                    </h3>
                    <button class="btn btn-success" id="download" type="button">
                        Exportar Excel
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 p-t-20">
                    <div class="table-responsive">
                        <table id="tablebt" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Transacción unica</th>
                                    <th>MSISDN</th>
                                    <th>Art&iacute;culo</th>
                                    <th>Servicio</th>
                                    <th>Vendedor</th>
                                    <th>Coordinador</th>
                                    <th>Organizaci&oacute;n</th>
                                    <th>Fecha de venta</th>
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
        var format = {autoclose: true, format: 'yyyy-mm-dd'};

        $('#date_ini').datepicker(format);
        $('#date_end').datepicker(format);

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
                    url: "{{route('saleArticDT')}}",
                    data: function (d) {
                        d.org = $('#org').val();
                        d.sup = $('#supervisor').val();
                        d.sell = $('#seller').val();
                        d.con = $('#conciliation').val();
                        d.db = $('#date_ini').val();
                        d.de = $('#date_end').val();
                        d.ser = $('#service').val();
                        d.pro = $('#product').val();
                        d._token = $('meta[name="csrf-token"]').attr('content');
                    },
                    type: "POST"
                },
                initComplete: function(settings, json){
                    $('#table-content').attr('hidden', null);
                    $(".preloader").fadeOut();
                },
                deferRender: true,
                order: [[ 7, "desc" ]],
                columns: [
                    {data: 'unique_transaction',searchable: true},
                    {data: 'msisdn',searchable: true},
                    {data: 'title',searchable: false},
                    {data: 'service',searchable: false},
                    {data: 'seller',searchable: false},
                    {data: 'supervisor',searchable: false},
                    {data: 'business_name',searchable: false},
                    {data: 'date_reg',searchable: false}
                ]
            });
        }

        $('#btn-report').on('click', function(){
            //actualiza la tabla dependiendo del filtro
            drawTable();
        });

        {{-- @if(session('user')->platform == 'admin' || session('user')->platform == 'call') --}}
            function getFilters(data){
                $(".preloader").fadeIn();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{route('saleArticF')}}",
                    type: 'post',
                    dataType: "json",
                    data: data,
                    success: function (res) {
                        if(res.success){
                            $("#seller [value!='']").remove();
                            $("#supervisor [value!='']").remove();
                            res.cs.forEach(function(c){
                                var optVal = {
                                                value: c.email,
                                                text : c.name+' '+c.last_name
                                             }
                                if(data.coo == c.email) optVal.selected = true;
                                $('#supervisor').append($('<option>', optVal));
                            });
                            res.ss.forEach(function(s){
                                $('#seller').append($('<option>', {
                                                    value: s.email,
                                                    text : s.name+' '+s.last_name
                                                }));
                            });
                        }
                        $(".preloader").fadeOut();
                    },
                    error: function (res) {
                        console.log(res);
                        $(".preloader").fadeOut();
                    }
                });
            }

            if($('#org').length){
                $('#org').on('change', function(e){
                    var data = {
                        org: $('#org').val(),
                        coo: $('#supervisor').val()
                    }
                    getFilters(data);
                });
            }

            $('#supervisor').on('change', function(e){
                var data = {
                    org: $('#org').val(),
                    coo: $('#supervisor').val()
                }
                getFilters(data);
            });
        {{-- @endif --}}

        $('#download').on('click', function(){
            var value = '';
            var data = $("#report_tb_form").serialize() + '_token=' + $('meta[name="csrf-token"]').attr('content') + '&emails=' + value;

            $(".preloader").fadeIn();

            $.ajax({
                type: "POST",
                url: "{{route('saleArticDW')}}",
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
                        var data = $("#report_tb_form").serialize() + '_token=' + $('meta[name="csrf-token"]').attr('content') + '&emails=' + value;

                        $(".preloader").fadeIn();

                        $.ajax({
                            type: "POST",
                            url: "{{route('saleArticDW')}}",
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