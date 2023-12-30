<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Leads Promo Envío Cero</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reportes Ventas Online</a></li>
                <li class="active">Leads Promo Envío Cero</li>
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

                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="control-label">Fecha de Registro Desde</label>
                        <div class="input-group">
                          <input type="text" id="date_ini" name="date_ini" class="form-control" placeholder="Fecha Desde">
                          <span class="input-group-addon"><i class="icon-calender"></i></span>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="control-label">Fecha de Registro Hasta</label>
                        <div class="input-group">
                          <input type="text" id="date_end" name="date_end" class="form-control" placeholder="Fecha Hasta">
                          <span class="input-group-addon"><i class="icon-calender"></i></span>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-12 text-center">
                        <button class="btn btn-success" type="button" id="btn-report">Consultar</button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <div class="row report">
        <section class="m-t-40" style="width: 100%;">
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">
                        Leads Agregados desde Promo "Envío Cero"
                    </h3>
                    <button class="btn btn-success" id="download" type="button">
                        Exportar Excel
                    </button>
                </div>
            </div>
            <div class="row white-box">
                <div class="col-md-12 p-t-20">
                    <div class="table-responsive">
                        <div>
                        <table id="tablebt" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre</th>                                    
                                    <th>Apellido</th> 
                                    <th>Telefono</th> 
                                    <th>Email</th> 
                                    <th>Ciudad</th> 
                                    <th>Fecha_Registro</th>
                                </tr>
                            </thead>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>



<script type="text/javascript">
    
    $(document).ready(function () {

        var getDate = function (input) {
            return new Date(input.date.valueOf());
        }

        $('#date_ini, #date_end').datepicker({
            format: "dd/mm/yyyy",
            autoclose: true,
            language: 'es'
        });

        $('#date_ini').on('changeDate',
            function (selected) {
                $('#date_end').datepicker('setStartDate', getDate(selected));
            });

        $('#date_end').on('changeDate',
        function (selected) {
            $('#date_ini').datepicker('setEndDate', getDate(selected));
        });

        $('#date_ini').datepicker('setEndDate', new Date());
        $('#date_end').datepicker('setEndDate', new Date());


        $(".preloader").fadeOut();
        $(".report").fadeOut();
    }); 
   
    $(document).ready(function (){

        drawTable = function(){
            //Si ya existe la tabla se elimina
            if ($.fn.DataTable.isDataTable('#tablebt')){
                $('#tablebt').DataTable().destroy();
            }

            date_ini=$('#date_ini').val();

            if(date_ini == ""){
                date_ini = moment('01/01/1900','DD/MM/YYYY').format('DD/MM/YYYY');
            }

            date_end=$('#date_end').val();

            if(date_end == ""){
                date_end = moment().format('DD/MM/YYYY');
            }

            $('.preloader').fadeIn();

            var tableClients = $('#tablebt').DataTable({
                 "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                },
                searching: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('getLeadsPromoECForReportOS')}}",
                    data: function (d) {
                        d.date_ini = date_ini;
                        d.date_end = date_end;                   
                        d._token = $('meta[name="csrf-token"]').attr('content');
                    },
                    type: "POST"
                },
                initComplete: function(settings, json){
                    if(json.recordsTotal>0){
                        $('#totalClients').text(json.recordsTotal);

                        $('#tablebt_length').addClass('col-12');
                        if ( $("#beforetable").length == 0) {
                           $('#tablebt').before('<div id="beforetable" class="col-12" style="overflow-x:auto"></div>');
                        }
                        $("#tablebt").detach().appendTo('#beforetable');
                        $(".report").fadeIn();                        
                    }
                    else{
                        alert('No se encontraron resultados par los filtros seleccionados');
                        $(".report").fadeOut();            
                    }
                    $(".preloader").fadeOut();
                   

                },
                deferRender: true,
                columns: [
                    {data: 'Nombre',searchable: false},
                    {data: 'Apellido',searchable: false},
                    {data: 'Telefono',searchable: false},
                    {data: 'Email',searchable: false},
                    {data: 'Ciudad',searchable: false},
                    {data: 'Fecha_Registro',searchable: false},               
                ],
                order: [[ 5, "asc" ]]
            });
        }

        $('#btn-report').on('click', function(){
            //if($('#date_ini').val() != "" && $('#date_end').val()!="")
                drawTable();
            //else
                //alert('Las Fechas no pueden estar vacias');
        });


        $('#download').on('click', function(){
            $(".preloader").fadeIn();

            var data = $("#report_tb_form").serialize();


            $.ajax({
                type: "POST",
                url: "{{route('downloadLeadsPromoECForReportOS')}}",
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
                    console.log("error error")
                    $(".preloader").fadeOut();
                }
            });
        });

    });  

</script>