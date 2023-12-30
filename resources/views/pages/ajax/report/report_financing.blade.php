<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Clientes Financiados</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reportes</a></li>
                <li class="active">Clientes Financiados</li>
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
                        {{-- @if (session('user')->profile->type == 'master' && count($orgs)) --}}
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

                        @if(count($financing))
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">Financiamientos</label>
                                <select id="financing" name="financing" class="form-control">
                                    <option value="">Todos</option>
                                    @foreach ($financing as $fin)
                                        <option value="{{$fin->id}}">{{$fin->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif

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
                        Clientes Financiados
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
                                    <th>msisdn</th>
                                    <th>Fecha Alta</th>
                                    <th>Monto financiado</th>
                                    <th>Monto total deuda</th>
                                    <th># Recargas</th>
                                    <th>Pago a la fecha</th>
                                    <th>Deuda remanente</th>
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
                    url: "{{route('financingDT')}}",
                    data: function (d) {
                        d.org = $('#org').val();
                        d.fi = $('#financing').val();
                        d.db = $('#date_ini').val();
                        d.de = $('#date_end').val();
                        d._token = $('meta[name="csrf-token"]').attr('content');
                    },
                    type: "POST"
                },
                initComplete: function(settings, json){
                    $('#table-content').attr('hidden', null);
                    $(".preloader").fadeOut();
                },
                deferRender: true,
                columns: [
                    {data: 'msisdn',searchable: true},
                    {data: 'date_reg',searchable: false},
                    {data: 'amount_financing',searchable: false},
                    {data: 'total_amount',searchable: false},
                    {data: 'num_dues',searchable: false},
                    {data: 'pay',searchable: false},
                    {data: 'price_remaining',searchable: true}
                ]
            });
        }

        $('#btn-report').on('click', function(){
            //actualiza la tabla dependiendo del filtro
            drawTable();
        });

        $('#download').on('click', function(){
            var value = '';
            var data = $("#report_tb_form").serialize() + '_token=' + $('meta[name="csrf-token"]').attr('content') + '&emails=' + value;

            $(".preloader").fadeIn();

            $.ajax({
                type: "POST",
                url: "{{route('financingDW')}}",
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
                            url: "{{route('financingDW')}}",
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