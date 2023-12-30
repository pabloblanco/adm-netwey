<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Base Recargadora</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reportes BI</a></li>
                <li class="active">Base Recargadora</li>
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
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">Mes</label>
                            @php($m = date('m'))
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
                                @php($años = date('Y') - 2018)
                                @for($i = 0; $i <= $años; $i++)
                                    <option value="{{date('Y', strtotime('-'.$i.' year', strtotime(date('Y'))))}}">
                                        {{date('Y', strtotime('-'.$i.' year', strtotime(date('Y'))))}}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">Tipo</label>
                            <select id="type" name="type" class="form-control">
                                @php($tipos = ['H' => 'HBB', 'T' => 'Teléfonia', 'M' => 'MiFi', 'MH' => 'MiFi Huella Altan', 'F' => 'Fibra'])
                                @foreach ($tipos as $key => $value)
                                    <option value="{{ $key }}">
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12 text-center">
                        <button class="btn btn-success" id="btn-report" type="button">Consultar</button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 col-xs-12 m-t-40">
            <div class="white-box">
                <h3 class="box-title">Base recargadora</h3>
                <ul class="list-inline two-part">
                    <li><i class="ti-wallet text-success"></i></li>
                    <li class="text-right"><span class="counter" id="totalClients">0</span></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="white-box">
        <div class="row">
            <div class="col-md-12">
                <h3 class="text-center">
                    Base recargadora
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
                                <th>MSISDN</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Teléfono de oficina</th>
                                <th>Email</th>
                                <th>I.N.E.</th>
                                <th>Fecha de registro</th>
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
                    url: "{{route('getClientsRechargeBase')}}",
                    data: function (d) {
                        d.month = $('#month').val();
                        d.year = $('#year').val();
                        d.type = $('#type').val();
                        d._token = $('meta[name="csrf-token"]').attr('content');
                    },
                    type: "POST"
                },
                initComplete: function(settings, json){
                    $('#totalClients').text(json.recordsTotal);
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
                    {data: 'date_reg',searchable: false}
                ]
            });
        }

        //mustra la primera tabla cuando carga la pagina
        drawTable();

        $('#btn-report').on('click', function(){
            //actualiza la tabla dependiendo del filtro
            drawTable();
        });

        $('#download').on('click', function(){
            $(".preloader").fadeIn();

            var data = $("#report_tb_form").serialize();

            $.ajax({
                type: "POST",
                url: "{{route('downloadClientsRechargeBase')}}",
                data: data,
                dataType: "json",
                success: function(response){
                    $(".preloader").fadeOut();

                    if(!response.error){
                        swal('Generando reporte','El reporte estara disponible en unos minutos.','success');
                    }else{
                        swal('Error','No se pudo generar el reporte.','error');
                    }

                    {{-- var a = document.createElement("a");
                        a.target = "_blank";
                        a.href = "{{route('downloadFile',['delete' => 1])}}?p=" + response.url;
                        a.click(); --}}
                },
                error: function(err){
                    $(".preloader").fadeOut();
                    swal('Error','No se pudo generar el reporte.','error');
                }
            });
        });
    });
</script>