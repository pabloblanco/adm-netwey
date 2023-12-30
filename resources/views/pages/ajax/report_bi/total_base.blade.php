<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Base Total</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reportes BI</a></li>
                <li class="active">Base Total</li>
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
                        <button class="btn btn-success" type="button" id="btn-report">Consultar</button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <div class="row m-t-40">
        <div class="col-md-4 col-xs-12">
            <div class="white-box">
                <h3 class="box-title">Recuperados</h3>
                <ul class="list-inline two-part">
                    <li style="width: 55px !important;"><i class="icon-people text-success"></i></li>
                    <li class="text-right"><span class="counter" id="rec">0</span></li>
                </ul>
            </div>
        </div>

        <div class="col-md-4 col-xs-12">
            <div class="white-box">
                <h3 class="box-title">Base BoP</h3>
                <ul class="list-inline two-part">
                    <li style="width: 55px !important;"><i class="icon-people text-success"></i></li>
                    <li class="text-right"><span class="counter" id="bop">0</span></li>
                </ul>
            </div>
        </div>

        <div class="col-md-4 col-xs-12">
            <div class="white-box">
                <h3 class="box-title">Base EoP</h3>
                <ul class="list-inline two-part">
                    <li style="width: 55px !important;"><i class="icon-people text-success"></i></li>
                    <li class="text-right"><span class="counter" id="eop">0</span></li>
                </ul>
            </div>
        </div>

        <div class="col-md-4 col-xs-12">
            <div class="white-box">
                <h3 class="box-title">Base AoP</h3>
                <ul class="list-inline two-part">
                    <li style="width: 55px !important;"><i class="icon-people text-success"></i></li>
                    <li class="text-right"><span class="counter" id="aop">0</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function (){
        getMetrics = function(){
            $(".preloader").fadeIn();

            var data = $("#report_tb_form").serialize();

            $('#bop').text('cargando...');
            $('#eop').text('cargando...');
            $('#aop').text('cargando...');

            $.ajax({
                type: "POST",
                url: "{{route('getKPIs')}}",
                data: data,
                dataType: "json",
                success: function(response){
                    $(".preloader").fadeOut();
                    $('#rec').text(response.rec);
                    $('#bop').text(response.bop);
                    $('#eop').text(response.eop);
                    $('#aop').text(response.aop);
                },
                error: function(err){
                    $('#bop').text('error');
                    $('#eop').text('error');
                    $('#aop').text('error');
                    $(".preloader").fadeOut();
                }
            });
        }

        getMetrics();

        $('#btn-report').on('click', function(){
            getMetrics();
        });
    });
</script>