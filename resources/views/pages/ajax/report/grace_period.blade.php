<style type="text/css">
    .selectize-input:after{
        content: none !important;
    }
</style>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">
                Reporte Periodo de Gracia
            </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li>
                    <a href="#">
                        Reporte
                    </a>
                </li>
                <li class="active">
                    Reporte Periodo de Gracia
                </li>
            </ol>
        </div>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            {{--
            <form class=" text-left" id="filterConc" method="POST" name="filterConc">
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <label class="control-label">
                                Fecha desde
                            </label>
                            <input class="form-control" id="dateb" name="dateb" placeholder="dd-mm-yyyy" type="text" value="{{ date('d-m-Y', strtotime('- 30 days', time())) }}">
                            </input>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <label class="control-label">
                                Fecha hasta
                            </label>
                            <input class="form-control" id="datee" name="datee" placeholder="dd-mm-yyyy" type="text" value="{{ date('d-m-Y', strtotime('- 1 days', time())) }}">
                            </input>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 p-t-20 text-center">
                    <div class="form-group">
                        <button class="btn btn-success" id="search" type="button">
                            Buscar
                        </button>
                    </div>
                </div>
            </form>
            --}}
            <h3>
                Configuración del reporte
            </h3>
            <form action="" class="form-horizontal" id="report_tb_form" method="POST" name="report_tb_form">
                {{ csrf_field() }}
                <div class="col-md-12" id="msisdn_select_container">
                    <div class="form-group">
                        <label class="control-label">MSISDN</label>
                        <select id="msisdn_select" name="msisdn_select" class="form-control" multiple>
                            <option value="">Seleccione el(los) msisdn(s)</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">
                            Fecha Desde
                        </label>
                        <div class="input-group">
                            <input class="form-control" id="dateStar" name="dateStar" placeholder="dd-mm-yyyy" type="text" value="{{ date('d-m-Y', strtotime('- 30 days', time())) }}">
                                <span class="input-group-addon">
                                    <i class="icon-calender">
                                    </i>
                                </span>
                            </input>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">
                            Fecha Hasta
                        </label>
                        <div class="input-group">
                            <input class="form-control" id="dateEnd" name="dateEnd" placeholder="dd-mm-yyyy" type="text" value="{{ date('d-m-Y') }}">
                                <span class="input-group-addon">
                                    <i class="icon-calender">
                                    </i>
                                </span>
                            </input>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 text-center">
                    <button class="btn btn-success" id="search" type="button">
                        Consultar
                    </button>
                </div>
            </form>
        </div>
        <div class="col-md-12 col-sm-12" hidden="" id="rep-sc">
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">
                        Reporte de Periodo de Gracia
                    </h3>
                </div>
                <div class="col-md-12">
                    <button class="btn btn-success m-b-20" id="download" type="button">
                        Exportar Excel
                    </button>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped" id="list-com">
                            <thead>
                                <tr>
                                    <th>
                                        msisdn
                                    </th>
                                    <th>
                                        Inicia Periodo de Gracia
                                    </th>
                                    <th>
                                        Coordenada Activación
                                    </th>
                                    <th>
                                        Coordenada de Uso
                                    </th>
                                    <th>
                                        Distancia (Km)
                                    </th>
                                    <th>
                                        Vendedor
                                    </th>
                                    <th>
                                        Estatus
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="js/reports/rangePicker.js">
</script>
<script type="text/javascript">
    $(document).ready(function () {
          /**
     * crear reporte
     */

        var configSelect = {
            valueField: 'msisdn',
            labelField: 'msisdn',
            searchField: 'msisdn',
            options: [],
            create: false,
            render: {
                option: function(item, escape) {
                    return '<p>'+item.msisdn+'</p>';
                }
            },
            load: function(query, callback) {
                if (!query.length) return callback();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: 'view/reports/clients/get-dns',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        q: query
                    },
                    error: function() {
                        callback();
                    },
                    success: function(res){
                        if(res.success)
                            callback(res.clients);
                        else
                            callback();
                    }
                });
            }
        };

        $('#msisdn_select').selectize(configSelect);


        $('#search').on('click', function(e){


            $('.preloader').show();

            if ($.fn.DataTable.isDataTable('#list-com')){
                $('#list-com').DataTable().destroy();
            }

            $('#list-com').DataTable({
                searching: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('getDTGracePeriod')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.dateb = $('#dateb').val();
                        d.datee = $('#datee').val();
                        d.msisdn_select = getSelectObject('msisdn_select').getValue();
                    },
                    type: "POST"
                },
                initComplete: function(settings, json){
                    $(".preloader").fadeOut();
                    $('#rep-sc').attr('hidden', null);
                },
                deferRender: true,
                //order: [[ 6, "desc" ]],
                ordering: false,
                columns: [
                    {data: 'msisdn', searchable: false, orderable: false},
                    {data: 'date_pg', searchable: false, orderable: false},
                    {data: 'point_act', searchable: false, orderable: false},
                    {data: 'point_pg', searchable: false, orderable: false},
                    {data: 'distance', searchable: false, orderable: false},
                    {data: 'vendor', searchable: false, orderable: false},
                    {data: 'status',searchable: false,orderable: false,
                        render: function(data) {
                            if(data == "A") {
                                return "Activo";
                            }
                            else{
                                return "Inactivo";
                            }
                        }
                    }
                ]
            });
        });

        $('#download').on('click', function(){
            $(".preloader").fadeIn();

            var data = $("#report_tb_form").serialize();

            $.ajax({
                type: "POST",
                url: "{{route('downloadDTGracePeriod')}}",
                data: {
                    _token : $('meta[name="csrf-token"]').attr('content'),
                    dateb : $('#dateb').val(),
                    datee : $('#datee').val(),
                    msisdn_select : getSelectObject('msisdn_select').getValue()
                },
                dataType: "json",
                success: function(response){
                    $(".preloader").fadeOut();

                    var a = document.createElement("a");
                        a.target = "_blank";
                        a.href = "{{route('downloadFile',['delete' => 1])}}?p=" + response.url;
                        a.click();
                },
                error: function(err){
                    console.log("error error")
                    $(".preloader").fadeOut();
                }
            });
        });

    });
</script>