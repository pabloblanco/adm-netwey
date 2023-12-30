<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Churn (Churn30)</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reportes BI</a></li>
                <li class="active">Churn30</li>
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
                    @php($time = strtotime($date->date_reg))
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">Mes</label>
                            @php($m = date('m', $time))
                            <select name="month" id="month" class="form-control">
                                <option value="01" @if($m == '01') selected @endif>Enero</option>
                                <option value="02" @if($m == '02') selected @endif>Febrero</option>
                                <option value="03" @if($m == '03') selected @endif>Marzo</option>
                                <option value="04" @if($m == '04') selected @endif>Abril</option>
                                <option value="05" @if($m == '05') selected @endif>Mayo</option>
                                <option value="06" @if($m == '06') selected @endif>Junio</option>
                                <option value="07" @if($m == '07') selected @endif>Julio</option>
                                <option value="08" @if($m == '08') selected @endif>Agosto</option>
                                <option value="09" @if($m == '09') selected @endif>Septiembre</option>
                                <option value="10" @if($m == '10') selected @endif>Octubre</option>
                                <option value="11" @if($m == '11') selected @endif>Noviembre</option>
                                <option value="12" @if($m == '12') selected @endif>Diciembre</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">Año</label>
                            <select id="year" name="year" class="form-control">
                                @php($años = (date('Y', $time) - 2018))
                                @for($i = 0; $i <= $años; $i++)
                                    <option value="{{date('Y', strtotime('-'.$i.' year', strtotime(date('Y-m-d', $time))))}}">
                                        {{date('Y', strtotime('-'.$i.' year', strtotime(date('Y-m-d', $time))))}}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12 text-center">
                        <button class="btn btn-success" type="button" id="btn-report">Consultar</button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <div class="row m-t-40">
        <div class="col-md-4 col-xs-12">
            <div class="white-box">
                <h3 class="box-title"> Churn (mm/aaaa) <span class="date-total">({{date('m/Y', $time)}})</span></h3>
                <ul class="list-inline two-part">
                    <li><i class="icon-people text-success"></i></li>
                    <li class="text-right"><span class="counter" id="totalClients" style="font-size: 32px;">0</span></li>
                </ul>
            </div>
        </div>

        <div class="col-md-4 col-xs-12">
            <div class="white-box">
                <h3 class="box-title"> Churn (mm/aaaa) <span class="date-total">({{date('m/Y', $time)}}) </h3>
                <ul class="list-inline two-part">
                    <li><i class="fa fa-percent text-success"></i></li>
                    <li class="text-right">
                        <span class="counter" id="por" style="font-size: 32px;">0</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <hr style="border-top: 3px solid #c5c5c5;">

    <div class="row">
        <section class="m-t-40">
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">
                        Clientes Churn30 del <b>{{date('m/Y', $time)}}</b>
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
                                    <th>MSISDN</th>
                                    <th>Nombre</th>
                                    <th>Teléfono</th>
                                    <th>Teléfono de oficina</th>
                                    <th>Email</th>
                                    <th>I.N.E.</th>
                                    <th>Fecha de registro</th>
                                    <th>Fecha churn</th>
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
                searching: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('getClientsChurnTh')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                    },
                    type: "POST"
                },
                initComplete: function(settings, json){
                    //$('#totalClients').text(json.recordsTotal);
                    $(".preloader").fadeOut();
                },
                deferRender: true,
                columns: [
                    {data: 'msisdn',searchable: false},
                    {data: 'name',searchable: false},
                    {data: 'phone_home',searchable: false},
                    {data: 'phone',searchable: false},
                    {data: 'email',searchable: false},
                    {data: 'dni',searchable: false},
                    {data: 'date_reg',searchable: false},
                    {data: 'date_churn',searchable: false}
                ]
            });
        }

        getMetric = function(){
            $(".preloader").fadeIn();

            var data = $("#report_tb_form").serialize();

            $('#totalClients').text('cargando...');
            $('#por').text('cargando...');

            $.ajax({
                type: "POST",
                url: "{{route('getMetricChurnTh')}}",
                data: data,
                dataType: "json",
                success: function(response){
                    $(".preloader").fadeOut();
                    $('.date-total').text(response.date);
                    $('#totalClients').text(response.total);
                    $('#por').text(response.porcentaje);
                },
                error: function(err){
                    $('#totalClients').text('error');
                    $('#por').text('error');
                    $(".preloader").fadeOut();
                }
            });
        }

        drawTable();
        getMetric();

        $('#btn-report').on('click', function(){
           getMetric();
        });

        $('#download').on('click', function(){
            $(".preloader").fadeIn();

            var data = $("#report_tb_form").serialize();

            $.ajax({
                type: "POST",
                url: "{{route('downloadChurnTh')}}",
                data: data,
                dataType: "json",
                success: function(response){
                    $(".preloader").fadeOut();

                    var a = document.createElement("a");
                        a.target = "_blank";
                        a.href = "{{route('downloadFile',['delete' => 1])}}?p=" + response.url;
                        a.click();
                },
                error: function(err){
                    $(".preloader").fadeOut();
                }
            });
        });
    });
</script>