<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Ventas Tienda Online</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reportes Ventas Online...</a></li>
                <li class="active">Ventas</li>
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
                        <div class="col-md-1">
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label">Fecha de Venta Desde</label>
                            <div class="input-group">
                              <input type="text" id="date_ini" name="date_ini" class="form-control" placeholder="Fecha Desde" readonly style="background: #FFFFFF">
                              <span class="input-group-addon"><i class="icon-calender"></i></span>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-2">
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label">Fecha de Venta Hasta</label>
                            <div class="input-group">
                              <input type="text" id="date_end" name="date_end" class="form-control" placeholder="Fecha Hasta" readonly style="background: #FFFFFF">
                              <span class="input-group-addon"><i class="icon-calender"></i></span>
                            </div>
                          </div>
                        </div>
                         <div class="col-md-1">
                        </div>
                    </div>
                    <div class="row"> 

                        <div class="col-md-1">
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label">Hora de Venta Desde</label>                           
                             <div class="input-group clockpicker " data-placement="bottom" data-align="top" data-autoclose="true">
                              <input type="text" id="time_ini" name="time_ini" class="form-control" placeholder="Hora Desde" value="00:00" readonly style="background: #FFFFFF">
                              <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
                            </div> 
                          </div>
                        </div>
                        <div class="col-md-2">
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label">Hora de Venta Hasta</label>
                             <div class="input-group clockpicker " data-placement="bottom" data-align="top" data-autoclose="true">
                              <input type="text" id="time_end" name="time_end" class="form-control" placeholder="Hora Hasta" value="23:59" readonly style="background: #FFFFFF">
                              <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
                            </div>
                          </div>
                        </div>
                         <div class="col-md-1">
                        </div>
                    </div>

                    <div class="row"> 
                        <div class="col-md-1">
                        </div>

                         <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Código Promocional</label>
                                <select id="cod_prom" name="cod_prom" class="form-control">
                                    <option value="0">Todos</option>
                                    <option value="1">EnvioCero</option>
                                    <option value="2">Referidos</option>                                   
                                </select>
                            </div>
                        </div>
                    </div>




                    <div class="row"> 
                         <div class="col-md-12 text-center">
                            <button class="btn btn-success" type="button" id="btn-report">Consultar</button>
                        </div>
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
                        Ventas Netwey OnLine
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
                                    <th>Orden_Proveedor</th>
                                    <th>Nombre</th>
                                    <th>Apellido</th> 
                                    <th>Telefono</th> 
                                    <th>Email</th> 
                                    <th>Ciudad</th> 
                                    <th>Codigo Postal</th> 
                                   {{--  <th>Tipo de Persona</th>
                                    <th>Requiere Factura</th>  --}}
                                    <th>RFC</th> 
                                    <th>Fecha_Registro</th> 
                                    <th>Equipo_Comprado</th> 
                                    <th>Plan</th> 
                                    <th>Fecha_Compra</th> 
                                    <th>Fecha_Entrega</th>
                                    <th>Dias_en_Entregar</th>
                                    <th>Metodo_Pago</th>
                                    <th>Id_Orden</th>
                                    <th>MSISDN</th> 
                                    <th>Direccion_Entrega</th>
                                    <th>Proveedor</th>
                                    <th>Precio delivery</th>
                                    <th>Estado</th>
                                    <th>Campaña</th>    
                                    <th>Codigo_Promo</th>  
                                    <th>Fecha_Activacion</th>                                    
                                    <th>Dias_en_Activar</th>  
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

        //$('#date_ini').datepicker('setStartDate', new Date(new Date().getFullYear(),new Date().getMonth()-2,1));
        $('#date_ini').datepicker('setEndDate', new Date());
        //$('#date_end').datepicker('setStartDate', new Date(new Date().getFullYear(),new Date().getMonth()-2,1));
        $('#date_end').datepicker('setEndDate', new Date());

        $('#cod_prom').selectize();


        // Clock pickers
        $('#single-input').clockpicker({
            placement: 'bottom',
            align: 'left',
            autoclose: true,
            'default': 'now'

        });

        $('.clockpicker').clockpicker({
                donetext: 'Done',

            })
            .find('input').change(function() {
                //console.log(this.value);
            });

        $('#check-minutes').click(function(e) {
            // Have to stop propagation here
            e.stopPropagation();
            input.clockpicker('show')
                .clockpicker('toggleView', 'minutes');
        });
        if (/mobile/i.test(navigator.userAgent)) {
            $('input').prop('readOnly', true);
        }





        $(".preloader").fadeOut();
        $(".report").fadeOut();
    }); 
   
    $(document).ready(function (){
        
        drawTable = function(){

            //Si ya existe la tabla se elimina
            if ($.fn.DataTable.isDataTable('#tablebt')){
                $('#tablebt').DataTable().destroy();
            }

            $('.preloader').fadeIn();

            /*date_ini=$('#date_ini').val();

            if(date_ini == ""){
                date_ini = moment('01/01/1900','DD/MM/YYYY').format('DD/MM/YYYY');
            }

            date_end=$('#date_end').val();

            if(date_end == ""){
                date_end = moment().format('DD/MM/YYYY');
            }*/

            var tableClients = $('#tablebt').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                },
                searching: false,
                processing: true,
                serverSide: true,
                order: [[ 12, "desc" ]],
                ajax: {
                    url: "{{route('getSalesForReportOS')}}",
                    data: function (d) {
                        d.date_ini = $('#date_ini').val();
                        d.date_end = $('#date_end').val();    
                        d.time_ini = $('#time_ini').val();
                        d.time_end = $('#time_end').val();   
                        d.cod_prom = $('#cod_prom').val();                 
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
                        alert('No se encontraron resultados para los filtros seleccionados');
                        $(".report").fadeOut();            
                    }
                    $(".preloader").fadeOut();
                   

                },
                deferRender: true,
                columns: [
                    {data: 'Nro_Orden',searchable: false},
                    {data: 'Nombre',searchable: false},
                    {data: 'Apellido',searchable: false},
                    {data: 'Telefono',searchable: false},
                    {data: 'Email',searchable: false},
                    {data: 'Ciudad',searchable: false},
                    {data: 'Codigo_Postal',searchable: false},
                    // {data: 'Tipo_Persona',searchable: false},
                    // {data: 'Requiere_Factura',searchable: false},
                    {data: 'RFC',searchable: false},
                    {data: 'Fecha_Registro',searchable: false},
                    {data: 'Equipo_Comprado',searchable: false},                    
                    {data: 'Plan',searchable: false},
                    {data: 'Fecha_Compra',searchable: false},
                    {data: 'Fecha_Entrega',searchable: false},
                    {data: null,searchable: false,
                        render: function(data, type, row, meta) {
                            var html = "";
                            if (row.Dias_en_Entregar >= 6) {
                                html = "<p style='color:red; font-weight:700;' >" + row.Dias_en_Entregar + " días</p>";
                            } else {
                                html = "<p>" + row.Dias_en_Entregar + " días</p>";
                            }
                            return html;
                        }
                    },
                    {data: 'Metodo_Pago',searchable: false, orderable:false},
                    {data: 'Orden_id',searchable: false},
                    {data: 'MSISDN',searchable: false},
                    {data: 'Direccion_Entrega',searchable: false},
                    {data: 'Id_Estafeta',searchable: false},
                    {data: 'price_del',searchable: false},
                    {data: 'Estado',searchable: false},
                    {data: 'Campaña',searchable: false},
                    {data: 'cod_prom',searchable: false},
                    {data: 'Fecha_Activacion',searchable: false},
                    {data: 'Dias_en_Activar',searchable: false},
                ]
            });
        }

        $('#btn-report').on('click', function(){
            //if($('#date_ini').val() != "" && $('#date_end').val()!="")
                drawTable();
            //else
            //    alert('Las Fechas no pueden estar vacias');
        });


        $('#download').on('click', function(){
            $(".preloader").fadeIn();

            var data = $("#report_tb_form").serialize();

            $.ajax({
                type: "POST",
                url: "{{route('downloadSalesForReportOS')}}",
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