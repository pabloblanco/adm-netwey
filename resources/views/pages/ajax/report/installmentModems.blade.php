<style type="text/css">
    .selectize-input:after{
        content: none !important;
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Reporte M&oacute;dems en abono</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reporte</a></li>
                <li class="active">Reporte M&oacute;dems en abono</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">
                        Reporte de M&oacute;dems en abono por coordinador
                    </h3>
                </div>

                <div class="col-md-12">
                    <button class="btn btn-success m-b-20" id="download" type="button">
                        Exportar Excel
                    </button>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table id="list-mi" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Coordinador</th>
                                    <th>M&oacute;dems autorizados por</th>
                                    <th>M&oacute;dems autorizados</th>
                                    <th>M&oacute;dems colocados vigentes</th>
                                    <th>Pago inicial</th>
                                    <th>Saldo por cobrar</th>
                                    <th>M&oacute;dems vencidos</th>
                                    <th>Saldo vencido</th>
                                    <th>Adeudo m&aacute;s antiguo (d&iacute;as)</th>
                                    <th>Total m&oacute;dems colocados</th>
                                    <th>M&oacute;dems disponibles</th>
                                    <th>Total m&oacute;dems colocados hist&oacute;rico</th>
                                    <th>Total m&oacute;dems no recuperados hist&oacute;rico</th>
                                    <th>Total m&oacute;dems colocados (&uacute;ltimos 30d)</th>
                                    <th>Total m&oacute;dems vencidos (&uacute;ltimos 30d)</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($coords as $coord)
                                <tr>
                                    <td>
                                        {{$coord->coord_name}} {{$coord->coord_last_name}}
                                    </td>
                                    <td>
                                        @if(empty($coord->sup_name))
                                        N/A
                                        @else
                                        {{$coord->sup_name}} {{$coord->sup_last_name}}
                                        @endif
                                    </td>
                                    <td>
                                        {{$coord->tokens_assigned}}
                                    </td>
                                    <td>
                                        {{ $coord->saleInsOK }}
                                    </td>
                                    <td>
                                        {{ '$'.number_format($coord->totalAmountI,2,'.',',') }}
                                    </td>
                                    <td>
                                        {{ '$'.number_format($coord->totalPending,2,'.',',') }}
                                    </td>
                                    <td>
                                        {{ $coord->saleInsEX }}
                                    </td>
                                    <td>
                                        {{ '$'.number_format($coord->totalExp,2,'.',',') }}
                                    </td>
                                    <td>
                                        {{ $coord->daysOld }}
                                    </td>
                                    <td>
                                        {{ $coord->saleInsT }}
                                    </td>
                                    <td>
                                        {{$coord->tokens_available}}
                                    </td>
                                    <td>
                                        {{ $coord->saleInsTH }}
                                    </td>
                                    <td>
                                        {{ $coord->expIntH }}
                                    </td>
                                    <td>
                                        {{ $coord->saleInsTM }}
                                    </td>
                                    <td>
                                        {{ $coord->expInt }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('#list-mi').DataTable({
            searching: true,
            initComplete: function(settings, json){
                $(".preloader").fadeOut();
            }
        });

        /*$('#search').on('click', function(e){
            $('.preloader').show();

            if ($.fn.DataTable.isDataTable('#list-rre')){
                $('#list-rre').DataTable().destroy();
            }

            $('#list-rre').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('getRREInstDT')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.dateb = $('#dateb').val();
                        d.datee = $('#datee').val();
                        d.coord = $('#coord').val();
                        d.seller = $('#seller').val();
                        d.org = $('#org').val();
                        d.status = $('#status').val();
                        d.alert = $('#alert').val();
                    },

                    type: "POST"
                },
                initComplete: function(settings, json){
                    $(".preloader").fadeOut();
                    $('#rep-rre').attr('hidden', null);
                },
                deferRender: true,
                order: [[ 7, "desc" ]],
                columns: [
                    {data: 'unique_transaction', orderable: false},
                    {data: 'msisdn', orderable: false},
                    {data: 'org', searchable: false, orderable: false},
                    {data: 'seller', searchable: false, orderable: false},
                    {data: 'coordinador', searchable: false, orderable: false},
                    {data: 'quote', searchable: false, orderable: false},
                    {data: 'amount', searchable: false, orderable: false},
                    {data: 'date_sell', searchable: false},
                    {data: 'date_reg', searchable: false},
                    {data: 'status', searchable: false, orderable: false},
                    {data: 'date_proc', searchable: false},
                    {
                        data: null, 
                        render: function(data,type,row,meta){
                            var label = '';

                            if(row.alert == 'Azul')
                                label = 'label-info';

                            if(row.alert == 'Rojo')
                                label = 'label-danger';

                            if(row.alert == 'Naranja')
                                label = 'label-warning';

                            if(row.alert == 'Verde')
                                label = 'label-success';

                            if(row.alert == 'Gris')
                                label = 'label-default';
                            
                            return '<label class="label '+label+' label-rouded" style="width:75px">'+row.alert+'</label>';
                        }, 
                        searchable: false, 
                        orderable: false,
                    },
                    {data: 'date_conc', orderable: false}
                ]
            });
        });*/

        $('#download').on('click', function(e){
            $(".preloader").fadeIn();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{route('downloadModInsReport')}}',
                type: 'post',
                data: {},
                success: function(res){
                    $(".preloader").fadeOut();

                    if(res.success){
                        var a = document.createElement("a");
                            a.target = "_blank";
                            a.href = res.url;
                            a.click();
                    }
                },
                error: function (res){
                    console.log(res);
                    $(".preloader").fadeOut();
                    alert('Ocurrio un error, no se pudo descargar el reporte.');
                }
            });
        });
    });
</script>