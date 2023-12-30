@php
    $accessPermission = 0;
    foreach (session('user')->policies as $policy) {
      if ($policy->code == 'BIL-RFM')
        $accessPermission = $policy->value;
    }

    $flimit='2020-01-01 00:00:00';
    $fini = date('d-m-Y', strtotime('- 30 days', time()));
    if(strtotime($fini) < strtotime($flimit))
        $fini = date('d-m-Y',strtotime($flimit));

    $fend = date('d-m-Y', strtotime('- 0 days', time()));
    if(strtotime($fend) < strtotime($flimit))
        $fend = date('d-m-Y',strtotime($flimit));
@endphp
@if ($accessPermission > 0)
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">
                    Reporte de Facturación Masiva
                </h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li>
                        <a href="/islim/">
                            Dashboard
                        </a>
                    </li>
                    <li class="active">
                        Reporte de Facturación Masiva
                    </li>
                </ol>
            </div>
        </div>
    </div>
    <div class="container">
        <form id="billing_masive" method="GET" action="view/reports/billing_masive_detail_report">
        
            <div class="row">
                <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Lugares: </label>
                        <select id="place" name="place" class="form-control">
                            <option value="">Todos los Lugares</option>
                            @foreach($places as $place)
                                <option value="{{$place->place}}">{{$place->place}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Estatus de Pago</label>
                        <select id="status_pay" name="status_pay" class="form-control">
                            <option value="Y" selected>Pago Completo</option>
                            <option value="N">No Pagado</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <label class="control-label">
                                    Fecha desde
                                </label>
                                <div class="input-group">
                                    <input autocomplete="off" class="form-control" id="dateb" name="dateb"
                                           placeholder="dd-mm-yyyy" type="text" value="{{ $fini }}">
                                        <span class="input-group-addon">
                                            <i class="icon-calender"></i>
                                        </span>
                                    </input>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <label class="control-label">
                                    Fecha hasta
                                </label>
                                <div class="input-group">
                                    <input autocomplete="off" class="form-control" id="datee" name="datee"
                                           placeholder="dd-mm-yyyy" type="text" value="{{ $fend }}">
                                        <span class="input-group-addon">
                                            <i class="icon-calender"></i>
                                        </span>
                                    </input>
                                </div>
                            </div>
                        </div>
            </div>
        
            <div class="row">
                <div class="col-md-4"></div>
                <div class="col-md-2 p-t-20 text-center">
                    <div class="input-group">
                        <button type="button" class="btn btn-success" onclick="drawTable();">
                        <i class="fa fa-check"></i> Generar Reporte
                        </button>
                    </div>
                </div>

                <div class="col-md-2 p-t-20 text-center">

                    <div class="input-group">
                        <button type="button" class="btn btn-success" onclick="exportReport();">
                        <i class="fa fa-check"></i> Exportar Reporte
                        </button>
                    </div>
                </div>
                <div class="col-md-4"></div>
            </div>
        </form>
      
        <hr>

        <div class="container">
                <section class="m-t-40">
                    <div class="row white-box">
                        <div class="col-md-12">
                            <h3 class="text-center">
                                Listado de Facturas
                            </h3>
                        </div>
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="list_billings" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Lugar</th>
                                        <th>Fecha Expiracion</th>
                                        <th>Term</th>
                                        <th>Fecha Folio OXXO</th>
                                        <th>ID Folio OXXO</th>
                                        <th>Nro Folio OXXO</th>
                                        <th>Fecha de Pago</th>
                                        <th>Documento de Pago</th>
                                        <th>Estatus de Pago</th>
                                        <th>Sub Total</th>
                                        <th>Tax</th>
                                        <th>Total</th>
                                        <th>Tipo de Pago</th>
                                        <th>Serie MK</th>
                                        <th>Folio MK</th>
                                    </tr>
                                </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
        </div>


    </div>

@else
    <h3>
        Lo sentimos, usted no posee permisos suficientes para acceder a este módulo
    </h3>
@endif


<script type="text/javascript">

    function exportReport() {
        
        $(".preloader").fadeIn();

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "POST",
            url: "{{route('downloadBillingsMasiveReport')}}",
            data: {
                place: $('#place').val(),
                status_pay: $('#status_pay').val(),
                dateb: $('#status_pay').val() == 'Y' ? $('#dateb').val() : null,
                datee: $('#status_pay').val() == 'Y' ? $('#datee').val() : null
            },
            dataType: "json",
            success: function(response){

                $(".preloader").fadeOut();
                swal({
                    title: "Bien",
                    text: "Reporte generado con exito.!",
                    icon: "success",
                });
            },
            error: function(err){
                $(".preloader").fadeOut();
                swal({
                    title: "Error",
                    text: "Error no se pudo exportar el reporte.",
                    icon: "error",
                });
            }
        });
    }

    $(document).ready(function () {
        maxdays = 2 * 365;
        flimit = new Date(Date.parse("{{$flimit}}"));
        var config = {
            autoclose: true,
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            language: 'es',
            startDate: flimit
        }
        $('#dateb').datepicker(config);
        $('#datee').datepicker(config);

        $('#status_pay').on('change', function(){
            if ( $('#status_pay').val() == 'Y' ) {
                $('#dateb').prop("disabled", false);
                $('#datee').prop("disabled", false);
            }
            
            if ( $('#status_pay').val() == 'N' ) {
                $('#dateb').prop("disabled", true);
                $('#datee').prop("disabled", true);
            }
        })

        drawTable = function(){
            //Si ya existe la tabla se elimina
            if ($.fn.DataTable.isDataTable('#list_billings')){
                $('#list_billings').DataTable().destroy();
            }

            $('.preloader').show();

            var tableUsers = $('#list_billings').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('billing_masive_detail_report')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.place=$('#place').val();
                        d.status_pay=$('#status_pay').val();
                        d.dateb = $('#status_pay').val() == 'Y' ? $('#dateb').val() : null;
                        d.datee = $('#status_pay').val() == 'Y' ? $('#datee').val() : null;
                    },
                    type: "POST"
                },
                initComplete: function(settings, json){
                    $(".preloader").fadeOut();
                },
                deferRender: true,
                columns: [
                    {data: 'place',searchable: true},
                    {data: 'date_expired',searchable: true},
                    {data: 'term',searchable: true},
                    {data: 'oxxo_folio_date',searchable: true},
                    {data: 'oxxo_folio_id',searchable: true},
                    {data: 'oxxo_folio_nro',searchable: true},
                    {data: 'date_pay',searchable: true},
                    {data: 'doc_pay',searchable: true},
                    {data: 'status_pay',searchable: true},
                    {data: 'sub_total',searchable: true},
                    {data: 'tax',searchable: true},
                    {data: 'total',searchable: true},
                    {data: 'pay_type',searchable: true},
                    {data: 'mk_serie',searchable: true},
                    {data: 'mk_folio',searchable: true},
                ]
            });
        }

        //mustra la primera tabla cuando carga la pagina
        drawTable();

    });
</script>
