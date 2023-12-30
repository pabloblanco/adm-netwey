<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Estádisticas de consultas a cobertura</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reportes Ventas Online</a></li>
                <li class="active">Consultas a cobertura</li>
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
                    <div class="row justify-content-around mx-0">
                        <div class="col-md-5">
                          <div class="form-group">
                            <label class="control-label">Fecha de Consulta Desde</label>
                            <div class="input-group">
                              <input autocomplete="off" type="text" id="date_ini" name="date_ini" class="form-control" placeholder="Fecha Desde">
                              <span class="input-group-addon"><i class="icon-calender"></i></span>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-5">
                          <div class="form-group">
                            <label class="control-label">Fecha de Consulta Hasta</label>
                            <div class="input-group">
                              <input autocomplete="off" type="text" id="date_end" name="date_end" class="form-control" placeholder="Fecha Hasta">
                              <span class="input-group-addon"><i class="icon-calender"></i></span>
                            </div>
                          </div>
                        </div>
                    </div> 
                    <div class="row justify-content-around mx-0">
                         <div class="col-md-5">
                            <div class="form-group">
                              <label class="control-label">Cliente</label>
                              <select id="client_type" name="client_type" class="form-control">
                                <option value="" selected="">Todos</option>
                                <option value="A">Anónimos</option>
                                <option value="R">Registrados</option>
                              </select>
                            </div>
                          </div> 

                          <div class="col-md-5">
                            <div class="form-group">
                              <label class="control-label">Resultado</label>
                              <select id="result_type" name="result_type" class="form-control">
                                <option value="" selected="">Todos</option>
                                <option value="NC">Direcciones no Coinciden</option>
                                <option value="DI">Dirección Inválida</option>
                                <option value="SD">Sin Entrega a Domicilio</option>
                                <option value="SC">Sin Cobertura</option>
                                <option value="OK">Consulta Exitosa</option>
                              </select>
                            </div>
                          </div>
                    </div>
                    <div class="row justify-content-around mx-0">
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

            <div class="row white-box align-items-center" id='charts-box'>
                <div class="col-md-12">
                    <h3 class="text-center">
                         Estádisticas de Consultas a Cobertura
                    </h3>
                    <p class="text-center mb-0" id="filters-details1" style="display: none;">
                        <span style="display: none;" class='title-desde'></span> 
                        <span style="display: none;" class='title-hasta'></span>
                    </p>
                </div>
                <hr class="w-100">
                <div class="col-md-6 white-box">
                    <h3 class="box-title">Estádisticas Generales</h3>
                    <div id="sparkline1" class="text-center"></div>
                </div>
                <div class="col-md-6">
                   <div class="row">
                        <div class="col-md-6">
                            <div class="white-box analytics-info pb-0 pt-3">
                                <h3 class="box-title" id="peitychart-0-title">Direcciones no Coinciden</h3>
                                <ul class="list-inline two-part">
                                    <li class="text-left d-inline-flex align-items-center">
                                        <span class="pie" id="peitychart-0" >0/1</span>
                                        <span class="pl-2" id="peitychart-0-porc" style="font-size: 16px;">0%</span>
                                    </li>
                                    <li class="text-right" id="peitychart-0-text"><span class="counter" id="peitychart-0-value">0</span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="white-box analytics-info pb-0 pt-3">
                                <h3 class="box-title" id="peitychart-1-title">Dirección Inválida</h3>
                                <ul class="list-inline two-part">
                                    <li class="text-left d-inline-flex align-items-center">
                                        <span class="pie" id="peitychart-1" >0/1</span>
                                        <span class="pl-2" id="peitychart-1-porc" style="font-size: 16px;">0%</span>
                                    </li>
                                    <li class="text-right" id="peitychart-1-text"><span class="counter" id="peitychart-1-value">0</span></li>
                                </ul>
                            </div>
                        </div>
                   </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="white-box analytics-info pb-0 pt-3">
                                <h3 class="box-title" id="peitychart-2-title">Sin Entrega a Domicilio</h3>
                                <ul class="list-inline two-part">
                                    <li class="text-left d-inline-flex align-items-center">
                                        <span class="pie" id="peitychart-2" >0/1</span>
                                        <span class="pl-2" id="peitychart-2-porc" style="font-size: 16px;">0%</span>
                                    </li>
                                    <li class="text-right" id="peitychart-2-text"><span class="counter" id="peitychart-2-value">0</span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="white-box analytics-info pb-0 pt-3">
                                <h3 class="box-title" id="peitychart-3-title">Sin Cobertura</h3>
                                <ul class="list-inline two-part">
                                    <li class="text-left d-inline-flex align-items-center">
                                        <span class="pie" id="peitychart-3" >0/1</span>
                                        <span class="pl-2" id="peitychart-3-porc" style="font-size: 16px;">0%</span>
                                    </li>
                                    <li class="text-right" id="peitychart-3-text"><span class="counter" id="peitychart-3-value">0</span></li>
                                </ul>
                            </div>
                        </div>
                   </div>
                    <div class="row">
                        <div class="col-md-6">
                             <div class="white-box analytics-info pb-0 pt-3">
                                <h3 class="box-title" id="peitychart-4-title">Consulta Exitosa</h3>
                                <ul class="list-inline two-part">
                                    <li class="text-left d-inline-flex align-items-center">
                                        <span class="pie" id="peitychart-4">0</span>
                                        <span class="pl-2" id="peitychart-4-porc" style="font-size: 16px;">0%</span>
                                    </li>
                                    <li class="text-right" id="peitychart-4-text"><span class="counter" id="peitychart-4-value">0</span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="white-box analytics-info pb-0 pt-3">
                                <h3 class="box-title" id="sparklinedash-5-title">Total</h3>
                                <ul class="list-inline two-part">
                                    <li>
                                        <div id="sparklinedash-5"></div>
                                    </li>
                                    <li class="text-right" id="sparklinedash-5-text"><span class="counter" id="sparklinedash-5-value">0</span></li>
                                </ul>
                            </div>
                        </div>
                   </div>
                </div>

                <hr class="w-100 sccty-box">
                <div class="col-md-6 white-box sccty-box">
                    <h3 class="box-title">Ciudades consultadas sin Cobertura</h3>
                    <div id="sparkline2" class="text-center"></div>
                </div>
                <div class="col-md-6 sccty-box">
                   <div class="row">
                        <div class="col-md-6">
                            <div class="white-box analytics-info pb-0 pt-3">
                                <h3 class="box-title sccty-title" id="peitychart-sccty-0-title"></h3>
                                <ul class="list-inline two-part">
                                    <li class="text-left d-inline-flex align-items-center">
                                        <span class="pie" id="peitychart-sccty-0" >0/1</span>
                                        <span class="pl-2" id="peitychart-sccty-0-porc" style="font-size: 16px;">0%</span>
                                    </li>
                                    <li class="text-right" id="peitychart-sccty-0-text"><span class="counter" id="peitychart-sccty-0-value">0</span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="white-box analytics-info pb-0 pt-3">
                                <h3 class="box-title sccty-title" id="peitychart-sccty-1-title"></h3>
                                <ul class="list-inline two-part">
                                    <li class="text-left d-inline-flex align-items-center">
                                        <span class="pie" id="peitychart-sccty-1" >0/1</span>
                                        <span class="pl-2" id="peitychart-sccty-1-porc" style="font-size: 16px;">0%</span>
                                    </li>
                                    <li class="text-right" id="peitychart-sccty-1-text"><span class="counter" id="peitychart-sccty-1-value">0</span></li>
                                </ul>
                            </div>
                        </div>
                   </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="white-box analytics-info pb-0 pt-3">
                                <h3 class="box-title sccty-title" id="peitychart-sccty-2-title"></h3>
                                <ul class="list-inline two-part">
                                    <li class="text-left d-inline-flex align-items-center">
                                        <span class="pie" id="peitychart-sccty-2" >0/1</span>
                                        <span class="pl-2" id="peitychart-sccty-2-porc" style="font-size: 16px;">0%</span>
                                    </li>
                                    <li class="text-right" id="peitychart-sccty-2-text"><span class="counter" id="peitychart-sccty-2-value">0</span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="white-box analytics-info pb-0 pt-3">
                                <h3 class="box-title sccty-title" id="peitychart-sccty-3-title"></h3>
                                <ul class="list-inline two-part">
                                    <li class="text-left d-inline-flex align-items-center">
                                        <span class="pie" id="peitychart-sccty-3" >0/1</span>
                                        <span class="pl-2" id="peitychart-sccty-3-porc" style="font-size: 16px;">0%</span>
                                    </li>
                                    <li class="text-right" id="peitychart-sccty-3-text"><span class="counter" id="peitychart-sccty-3-value">0</span></li>
                                </ul>
                            </div>
                        </div>
                   </div>
                    <div class="row">
                        <div class="col-md-6">
                             <div class="white-box analytics-info pb-0 pt-3">
                                <h3 class="box-title sccty-title" id="peitychart-sccty-4-title"></h3>
                                <ul class="list-inline two-part">
                                    <li class="text-left d-inline-flex align-items-center">
                                        <span class="pie" id="peitychart-sccty-4">0</span>
                                        <span class="pl-2" id="peitychart-sccty-4-porc" style="font-size: 16px;">0%</span>
                                    </li>
                                    <li class="text-right" id="peitychart-sccty-4-text"><span class="counter" id="peitychart-sccty-4-value">0</span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="white-box analytics-info pb-0 pt-3">
                                <h3 class="box-title sccty-title" id="sparklinedash-sccty-5-title">Total</h3>
                                <ul class="list-inline two-part">
                                    <li>
                                        <div id="sparklinedash-sccty-5"></div>
                                    </li>
                                    <li class="text-right" id="sparklinedash-sccty-5-text"><span class="counter" id="sparklinedash-sccty-5-value">0</span></li>
                                </ul>
                            </div>
                        </div>
                   </div>
                </div>
            </div>            

            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">
                         Consultas a Cobertura
                    </h3>
                     <p class="text-center mb-0" id="filters-details2" style="display: none;">
                        <span style="display: none;" class='title-desde'></span> 
                        <span style="display: none;" class='title-hasta'></span>
                        <span style="display: none;" class='title-clients'></span> 
                        <span style="display: none;" class='title-results'></span> 
                    </p>
                    <button class="btn btn-success" id="download" type="button">
                        Exportar Excel
                    </button>
                </div>
                <hr class="w-100">
                <div class="col-md-12 p-t-20">
                    <div class="table-responsive">
                        <div>
                        <table id="tablebt" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Cliente</th>                                    
                                    <th>Email</th> 
                                    <th>Telefono</th> 
                                    <th>Direccion Consultada</th> 
                                    <th>Colonia</th> 
                                    <th>Ciudad</th> 
                                    <th>Estado</th> 
                                    <th>Código Póstal</th> 
                                    <th>Resultado</th> 
                                    <th>Fecha Consulta</th>  
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


<script src="js/reportsos/charts.js"></script>

<script src="plugins/bower_components/peity/jquery.peity.min.js"></script>
{{-- <script src="plugins/bower_components/peity/jquery.peity.init.js"></script> --}}

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


        $(".preloader").fadeOut();
        $(".report").fadeOut();
    }); 
   
    $(document).ready(function (){

        drawChart = function(date_ini,date_end){
            
            var URL = '{{ route('getCoverageStatsCharts') }}';

            var params = new FormData();
            params.append('token', $('meta[name="csrf-token"]').attr('content'));
            params.append('date_ini', date_ini);
            params.append('date_end', date_end);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                contentType: false,
                processData: false,
                async: true,
                url: URL,
                method: "POST",
                data: params,
                dataType: "json",
                success: function (res) {
                    if(res.success){
                        elem=$('#sparkline1');
                        tags={
                            0: 'Direcciones no Coinciden',
                            1: 'Dirección Inválida',
                            2: 'Sin Entrega a Domicilio',
                            3: 'Sin Cobertura',
                            4: 'Consulta Exitosa'
                            // Add more here
                        };

                        values=[0, 0, 0, 0, 0];
                        colors=['#FB9678','#00CCFF', '#AA8CE4','#F96261','#01C292'];
                        res.data.forEach(function(element){
                            switch (element.Resultado) {
                                case 'NC':
                                    values[0] = element.Cantidad
                                    // console.log('NC');
                                break;
                                case 'DI':
                                    values[1] = element.Cantidad
                                    // console.log('DI');
                                break;
                                case 'SD':
                                    values[2] = element.Cantidad
                                    // console.log('SD');
                                break;
                                case 'SC':
                                    values[3] = element.Cantidad
                                    // console.log('SC');
                                break;
                                case 'OK':
                                    values[4] = element.Cantidad
                                    // console.log('OK');
                                break;
                            }
                        }); 
                        total=0;   
                        for(let i of values) total+=i;

                        sparklinePie(elem,values,colors,tags,220,220); 
           
                        acum=0;
                        values.forEach(function(elem,idx){
                            porc=elem/total;
                            $('#peitychart-'+idx).text(porc+'/1');
                            $('#peitychart-'+idx).peity("pie", {
                                                        width: 30,
                                                        height: 30, 
                                                        fill: [colors[idx], "#f2f2f2"]
                                                    });
                            //porc=Math.round(porc * 10) / 10;
                            porc=parseFloat(porc * 100).toFixed(1);
                            $('#peitychart-'+idx+'-porc').text(porc+'%');
                            $('#peitychart-'+idx+'-value').text(elem);
                            $('#peitychart-'+idx+'-porc').css('color',colors[idx]);
                            $('#peitychart-'+idx+'-text').css('color',colors[idx]);
                            
                            $('#peitychart-'+idx).parent().find('svg').css('transform','rotate('+((acum*360)/100)+'deg)');
                            acum+=parseFloat(porc);

                        });

                        sparklineDash($('#sparklinedash-5'),'#01A9F3');
                            $('#sparklinedash-5-text').css('color','#01A9F3');
                            $('#sparklinedash-5-value').text(total);

                       
                        $('#charts-box').fadeIn();                       
                    }
                    else{
                        $('#charts-box').fadeOut();
                    }
                    
                },
                error: function (res) {                 
                    console.log(res);
                    $('#charts-box').fadeOut();
                }
            });
        }

        drawChartSC = function(date_ini,date_end){
            
            $('.sccty-title').css('font-size','12px');
            $('.sccty-title').css('line-height','normal');

            var URL = '{{ route('getNotCoverageStatsCharts') }}';

            var params = new FormData();
            params.append('token', $('meta[name="csrf-token"]').attr('content'));
            params.append('date_ini', date_ini);
            params.append('date_end', date_end);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                contentType: false,
                processData: false,
                async: true,
                url: URL,
                method: "POST",
                data: params,
                dataType: "json",
                success: function (res) {
                    if(res.success){

                        elem=$('#sparkline2');
                        tags={
                            0: '',
                            1: '',
                            2: '',
                            3: '',
                            4: ''
                            // Add more here
                        };
                        values=[0, 0, 0, 0, 0];
                        colors=['#FB9678','#00CCFF', '#AA8CE4','#F96261','#01C292'];

                        res.data.forEach(function(element,idx){
                            values[idx] = element.Cantidad;
                            tags[idx] = element.Ciudad;                            
                        });

                        total=0;   
                        for(let i of values) total+=i;

                        sparklinePie(elem,values,colors,tags,220,220);  


                        acum=0;
                        values.forEach(function(elem,idx){
                            porc=elem/total;
                            $('#peitychart-sccty-'+idx).text(porc+'/1');
                            $('#peitychart-sccty-'+idx).peity("pie", {
                                                        width: 30,
                                                        height: 30, 
                                                        fill: [colors[idx], "#f2f2f2"]
                                                    });
                            //porc=Math.round(porc * 10) / 10;
                            porc=parseFloat(porc * 100).toFixed(1);
                            $('#peitychart-sccty-'+idx+'-title').text(tags[idx]);
                            $('#peitychart-sccty-'+idx+'-porc').text(porc+'%');
                            $('#peitychart-sccty-'+idx+'-value').text(elem);
                            $('#peitychart-sccty-'+idx+'-porc').css('color',colors[idx]);
                            $('#peitychart-sccty-'+idx+'-text').css('color',colors[idx]);
                            
                            $('#peitychart-sccty-'+idx).parent().find('svg').css('transform','rotate('+((acum*360)/100)+'deg)');
                            acum+=parseFloat(porc);

                        });

                        sparklineDash($('#sparklinedash-sccty-5'),'#01A9F3');
                            $('#sparklinedash-sccty-5-text').css('color','#01A9F3');
                            $('#sparklinedash-sccty-5-value').text(total);

                        if(total<=0)
                            $('.sccty-box').fadeOut();
                        else
                            $('.sccty-box').fadeIn();
                        //console.log('ok');
                    }
                    else{
                        $('.sccty-box').fadeOut();
                        //console.log('ko');
                    }
                    
                },
                error: function (res) {                 
                    console.log(res);
                    $('.sccty-box').fadeOut();
                }
            });  
        }

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
                    url: "{{route('getCoverageStats')}}",
                    data: function (d) {
                        d.date_ini = date_ini;
                        d.date_end = date_end;   
                        d.client_type = $('#client_type').val();  
                        d.result_type = $('#result_type').val();               
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
                        drawChart(date_ini,date_end);
                        drawChartSC(date_ini,date_end);
                        $(".report").fadeIn();                        
                    }
                    else{
                        alert('No se encontraron resultados para los filtros seleccionados');
                        $(".report").fadeOut();            
                    }
                    $(".preloader").fadeOut();
                   
                    $('#tablebt_wrapper > #beforetable > #tablebt').css('max-width','180%');
                    $('#tablebt_wrapper > #beforetable > #tablebt').css('width','180%');
                },
                deferRender: true,
                order: [[ 4, "desc" ]],
                columns: [
                    {data: 'Cliente',width: "10%", searchable: false},
                    {data: 'Email', width: "10%", searchable: false},
                    {data: 'Telefono',width: "8%",searchable: false},
                    {data: 'Direccion_Consultada',width: "20%", searchable: false}, 
                    {data: 'Colonia_Consultada',width: "8%",searchable: false}, 
                    {data: 'Ciudad_Consultada',width: "9%",searchable: false}, 
                    {data: 'Estado_Consultado',width: "9%",searchable: false}, 
                    {data: 'ZIP_Consultado',width: "8%",searchable: false}, 
                    {data: 'Resultado',width: "9%",searchable: false},
                    {data: 'Fecha_Consulta',width: "9%",searchable: false},                 
                ],
                autoWidth: false
            });
        }

        $('#btn-report').on('click', function(){
            drawTable();
            var sep='<pre style="display:inline;color: #686868;font-family: Poppins,sans-serif;">   |   </pre> ';
            if($('#date_ini').val() != "")
                $('.title-desde').html('Desde: '+$('#date_ini').val()).show();            
            else
                $('.title-desde').html('').hide();

            if($('#date_end').val() != "")
                if($('#date_ini').val() != "")
                    $('.title-hasta').html(sep+'Hasta: '+$('#date_end').val()).show();
                else
                    $('.title-hasta').html('Hasta: '+$('#date_end').val()).show();
            else
                $('.title-hasta').html('').hide();  


            if($('#client_type').val() != "")
                if($('#date_ini').val() != "" || $('#date_end').val() != "")
                    $('.title-clients').html(sep+'Clientes: '+$('#client_type option:selected').text()).show();
                else
                    $('.title-clients').html('Clientes: '+$('#client_type option:selected').text()).show();
            else
                $('.title-clients').html('').hide();

            if($('#result_type').val() != "")
                if($('#date_ini').val() != "" || $('#date_end').val() != "" || $('#client_type').val() != "")
                    $('.title-results').html(sep+'Resultado: '+$('#result_type option:selected').text()).show();
                else
                    $('.title-results').html('Resultado: '+$('#result_type option:selected').text()).show();
            else
                $('.title-results').html('').hide();
            


            if($('#date_ini').val() != "" || $('#date_end').val() != "")
                $('#filters-details1').show();                    
            else
                $('#filters-details1').hide();

            if($('#date_ini').val() != "" || $('#date_end').val() != "" || $('#client_type').val() != "" || $('#result_type').val() != "")
                $('#filters-details2').show();                    
            else
                $('#filters-details2').hide();
        });


        $('#download').on('click', function(){
            $(".preloader").fadeIn();

            var data = $("#report_tb_form").serialize();


            $.ajax({
                type: "POST",
                url: "{{route('downloadCoverageStats')}}",
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