<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">ARPU Altas</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reportes BI</a></li>
                <li class="active">ARPU Altas</li>
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
                            @php($m = date('m', strtotime('-1 month', strtotime(date('Y-m-d')))))
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

                    <div class="col-md-12 text-center">
                        <button class="btn btn-success" type="button" id="btn-report">Consultar</button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 col-xs-12 m-t-40">
            <div class="white-box">
                <h3 class="box-title"> ARPU Altas (mm/aaaa) <span id="date-total">({{date('m/Y', strtotime($m))}})</span></h3>
                <ul class="list-inline two-part">
                    <li><i class="ti-wallet text-success"></i></li>
                    <li class="text-right"><span class="counter" id="total">0</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function () {
        getMetric = function(){
            $(".preloader").fadeIn();

            var data = $("#report_tb_form").serialize();

            $.ajax({
                type: "POST",
                url: "{{route('getArpuUp')}}",
                data: data,
                dataType: "json",
                success: function(response){
                    $(".preloader").fadeOut();
                    $('#date-total').text(response.date);
                    $('#total').text(response.total);
                },
                error: function(err){
                    $(".preloader").fadeOut();
                }
            });
        }

        getMetric();

        $('#btn-report').on('click', function(){
            getMetric();
        })
    });
</script>