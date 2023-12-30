$(document).ready(function () {
	if($("#container-dash").is(':visible')){
		//Grafica de ventas HBB
		$.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "dashboard_grap_sales",
            type: 'post',
            dataType: "json",
            data: {type: 'H', typeS: 'U'},
            success: function (res) {
                if(res.success){
                	//Altas
                    $('#upsDHP').html(res.today);
                    $('#upsTHP').html(res.month);
					$('#upsZHP').html(res.tri);

					//Grafica de altas
					printbar(res.graf, 'ups', 'upsHP');
                    $("#loaderHP").fadeOut();
                }else{
                	alert('No se pudo cargar grafica de ventas.');
                    $("#loaderHP").fadeOut();
                }
            },
            error: function (res) {
                //console.log(res);
                $("#loaderHP").fadeOut();
            }
        });

        //Grafica de ventas MBB
		$.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "dashboard_grap_sales",
            data: {type: 'T', typeS: 'U'},
            type: 'post',
            dataType: "json",
            success: function (res) {
                if(res.success){
                	//Altas
                    $('#upsDTP').html(res.today);
                    $('#upsTTP').html(res.month);
					$('#upsZTP').html(res.tri);

					//Grafica de altas
					printbar(res.graf, 'ups', 'upsTP');
                    $("#loaderTP").fadeOut();
                }else{
                	alert('No se pudo cargar grafica de ventas.');
                    $("#loaderTP").fadeOut();
                }
            },
            error: function (res) {
                $("#loaderTP").fadeOut();
            }
        });

        //Grafica de ventas MIFI
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "dashboard_grap_sales",
            data: {type: 'M', typeS: 'U'},
            type: 'post',
            dataType: "json",
            success: function (res) {
                if(res.success){
                    //Altas
                    $('#upsDMP').html(res.today);
                    $('#upsTMP').html(res.month);
                    $('#upsZMP').html(res.tri);

                    //Grafica de altas
                    printbar(res.graf, 'ups', 'upsMP');
                    $("#loaderMP").fadeOut();
                }else{
                    alert('No se pudo cargar grafica de ventas.');
                    $("#loaderMP").fadeOut();
                }
            },
            error: function (res) {
                $("#loaderMP").fadeOut();
            }
        });

        //Grafica de ventas MIFI Huella Altan
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "dashboard_grap_sales",
            data: {type: 'MH', typeS: 'U'},
            type: 'post',
            dataType: "json",
            success: function (res) {
                if(res.success){
                    //Altas
                    $('#upsDMHP').html(res.today);
                    $('#upsTMHP').html(res.month);
                    $('#upsZMHP').html(res.tri);

                    //Grafica de altas
                    printbar(res.graf, 'ups', 'upsMHP');
                    $("#loaderMHP").fadeOut();
                }else{
                    alert('No se pudo cargar grafica de ventas.');
                    $("#loaderMHP").fadeOut();
                }
            },
            error: function (res) {
                $("#loaderMHP").fadeOut();
            }
        });

        //Grafica de altas por migracion MIFI Huella Altan
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "dashboard_grap_sales",
            data: {type: 'MH_M', typeS: 'U'},
            type: 'post',
            dataType: "json",
            success: function (res) {
                if(res.success){
                    //Altas
                    $('#upsDMH_MP').html(res.today);
                    $('#upsTMH_MP').html(res.month);
                    $('#upsZMH_MP').html(res.tri);

                    //Grafica de altas
                    printbar(res.graf, 'ups', 'upsMH_MP');
                    $("#loaderMH_MP").fadeOut();
                }else{
                    alert('No se pudo cargar grafica de ventas.');
                    $("#loaderMH_MP").fadeOut();
                }
            },
            error: function (res) {
                $("#loaderMH_MP").fadeOut();
            }
        });

        //Grafica de altas por Fibra
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "dashboard_grap_sales",
            data: {type: 'F', typeS: 'U'},
            type: 'post',
            dataType: "json",
            success: function (res) {
                if(res.success){
                    //Altas
                    $('#upsDFP').html(res.today);
                    $('#upsTFP').html(res.month);
                    $('#upsZFP').html(res.tri);

                    //Grafica de altas
                    printbar(res.graf, 'ups', 'upsFP');
                    $("#loaderFP").fadeOut();
                }else{
                    alert('No se pudo cargar grafica de ventas.');
                    $("#loaderFP").fadeOut();
                }
            },
            error: function (res) {
                $("#loaderFP").fadeOut();
            }
        });

        //Grafica de recargas HBB
		$.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "dashboard_grap_sales",
            type: 'post',
            dataType: "json",
            data: {type: 'H', typeS: 'R'},
            success: function (res) {
                if(res.success){
                	//Recargas
                    $('#upsDHR').html(res.today);
					$('#upsTHR').html(res.month);
					$('#upsZHR').html(res.tri);

					//Grafica de recargas
					printbar(res.graf, 're', 'upsHR');
                    $("#loaderHR").fadeOut();
                }else{
                	alert('No se pudo cargar grafica de recargas.');
                    $("#loaderHR").fadeOut();
                }
            },
            error: function (res) {
                $("#loaderHR").fadeOut();
            }
        });

        //Grafica de recargas MBB
		$.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "dashboard_grap_sales",
            type: 'post',
            dataType: "json",
            data: {type: 'T', typeS: 'R'},
            success: function (res) {
                if(res.success){
                	//Recargas
                    $('#upsDTR').html(res.today);
					$('#upsTTR').html(res.month);
					$('#upsZTR').html(res.tri);

					//Grafica de recargas
					printbar(res.graf, 're', 'upsTR');
                    $("#loaderTR").fadeOut();
                }else{
                	alert('No se pudo cargar grafica de recargas.');
                    $("#loaderTR").fadeOut();
                }
            },
            error: function (res) {
                $("#loaderTR").fadeOut();
            }
        });

        //Grafica de recargas MIFI
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "dashboard_grap_sales",
            type: 'post',
            dataType: "json",
            data: {type: 'M', typeS: 'R'},
            success: function (res) {
                if(res.success){
                    //Recargas
                    $('#upsDMR').html(res.today);
                    $('#upsTMR').html(res.month);
                    $('#upsZMR').html(res.tri);

                    //Grafica de recargas
                    printbar(res.graf, 're', 'upsMR');
                    $("#loaderMR").fadeOut();
                }else{
                    alert('No se pudo cargar grafica de recargas.');
                    $("#loaderMR").fadeOut();
                }
            },
            error: function (res) {
                $("#loaderMR").fadeOut();
            }
        });


         //Grafica de recargas MIFI Huella Altan
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "dashboard_grap_sales",
            type: 'post',
            dataType: "json",
            data: {type: 'MH', typeS: 'R'},
            success: function (res) {
                if(res.success){
                    //Recargas
                    $('#upsDMHR').html(res.today);
                    $('#upsTMHR').html(res.month);
                    $('#upsZMHR').html(res.tri);

                    //Grafica de recargas
                    printbar(res.graf, 're', 'upsMHR');
                    $("#loaderMHR").fadeOut();
                }else{
                    alert('No se pudo cargar grafica de recargas.');
                    $("#loaderMHR").fadeOut();
                }
            },
            error: function (res) {
                $("#loaderMHR").fadeOut();
            }
        });

        //Grafica de recargas Fibra
		$.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "dashboard_grap_sales",
            type: 'post',
            dataType: "json",
            data: {type: 'F', typeS: 'R'},
            success: function (res) {
                if(res.success){
                    //Recargas
                    $('#upsDFR').html(res.today);
                    $('#upsTFR').html(res.month);
                    $('#upsZFR').html(res.tri);

                    //Grafica de recargas
                    printbar(res.graf, 're', 'upsFR');
                    $("#loaderFR").fadeOut();
                }else{
                    alert('No se pudo cargar grafica de recargas.');
                    $("#loaderFR").fadeOut();
                }
            },
            error: function (res) {
                $("#loaderFR").fadeOut();
            }
        });

        //Clientes activos HBB
		$.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "dashboard_client",
            type: 'post',
            dataType: "json",
            data: {type: 'H'},
            success: function (res) {
                if(res.success){
                	$('#clientactH').html(res.active);//activos
					$('#clientnctH').html(res.inactive);//inactivos
					$('#UpsTH').html(res.total_up);//altas totales
					$('#RechTH').html(res.total_re);//recargas totales

                    $("#clientH").fadeOut();
                }else{
                	alert('No se pudo cargar grafica de clientes.');
                    $("#clientH").fadeOut();
                }
            },
            error: function (res) {
                $("#clientH").fadeOut();
            }
        });

		//Clientes activos MBB
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "dashboard_client",
            type: 'post',
            dataType: "json",
            data: {type: 'T'},
            success: function (res) {
                if(res.success){
                	$('#clientactT').html(res.active);//activos
					$('#clientnctT').html(res.inactive);//inactivos
					$('#UpsTT').html(res.total_up);//altas totales
					$('#RechTT').html(res.total_re);//recargas totales

                    $("#clientT").fadeOut();
                }else{
                	alert('No se pudo cargar grafica de clientes.');
                    $("#clientT").fadeOut();
                }
            },
            error: function (res) {
                 $("#clientT").fadeOut();
            }
        });

        //Clientes activos MIFI
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "dashboard_client",
            type: 'post',
            dataType: "json",
            data: {type: 'M'},
            success: function (res) {
                if(res.success){
                    $('#clientactM').html(res.active);//activos
                    $('#clientnctM').html(res.inactive);//inactivos
                    $('#UpsTM').html(res.total_up);//altas totales
                    $('#RechTM').html(res.total_re);//recargas totales

                    $("#clientM").fadeOut();
                }else{
                    alert('No se pudo cargar grafica de clientes.');
                    $("#clientM").fadeOut();
                }
            },
            error: function (res) {
                 $("#clientM").fadeOut();
            }
        });

        //Clientes activos MIFI
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "dashboard_client",
            type: 'post',
            dataType: "json",
            data: {type: 'MH'},
            success: function (res) {
                if(res.success){
                    $('#clientactMH').html(res.active);//activos
                    $('#clientnctMH').html(res.inactive);//inactivos
                    $('#UpsTMH').html(res.total_up);//altas totales
                    $('#RechTMH').html(res.total_re);//recargas totales

                    $("#clientMH").fadeOut();
                }else{
                    alert('No se pudo cargar grafica de clientes.');
                    $("#clientMH").fadeOut();
                }
            },
            error: function (res) {
                 $("#clientM").fadeOut();
            }
        });

        //Clientes activos FIBRA
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "dashboard_client",
            type: 'post',
            dataType: "json",
            data: {type: 'F'},
            success: function (res) {
                if(res.success){
                    $('#clientactF').html(res.active);//activos
                    $('#clientnctF').html(res.inactive);//inactivos
                    $('#UpsTF').html(res.total_up);//altas totales
                    $('#RechTF').html(res.total_re);//recargas totales

                    $("#clientF").fadeOut();
                }else{
                    alert('No se pudo cargar grafica de clientes.');
                    $("#clientF").fadeOut();
                }
            },
            error: function (res) {
                 $("#clientF").fadeOut();
            }
        });

        //Concentradores
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "dashboard_concentrator",
            type: 'post',
            dataType: "json",
            success: function (res) {
                if(res.success){
                	$.each( res.concentrator, function( key, value ) {
						$('#balance-concentrator tr:last').after('<tr><th>'+value.business_name+'</th><th>$'+value.balance+'</th></tr>');
					});

                    $("#conc-loader").fadeOut();
                }else{
                	alert('No se pudo cargar concentradores.');
                    $("#conc-loader").fadeOut();
                }
            },
            error: function (res) {
                console.log(res);
                $("#conc-loader").fadeOut();
            }
        });

        $('.intervalGrap').change(function(){
            var val = $(this).val(),
                type = $(this).data('type'),
                graph = $(this).data('g'),
                device = $(this).data('device');

            if (val){
                $("#"+graph+"-loader").fadeIn();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "dashboard_grap",
                    type: 'post',
                    data: {type: type, interval: val, device: device},
                    dataType: "json",
                    success: function (res) {
                        if(res.success){
                            $('#'+graph).empty();
                            printbar(res.data, type == 'P' ? 'ups' : 're', graph);
                            $("#"+graph+"-loader").fadeOut();
                        }else{
                            alert('No se pudo cargar la grafica.');
                            $("#"+graph+"-loader").fadeOut();
                        }
                    },
                    error: function (res) {
                        console.log(res);
                        $("#"+graph+"-loader").fadeOut();
                    }
                });
            }
        });
	}
});


function printbar(data, type, ele){
    conf = {
        element: ele,
        data: data,
        xkey: 'date',
        xLabels: 'month',
        ykeys: ['count'],
        labels: [type == 'ups' ? 'Altas' : 'Recargas'],
        pointSize: 3,
        fillOpacity: 0,
        pointStrokeColors:[type == 'ups' ? '#fb9678' : '#00bfc7'],
        behaveLikeLine: true,
        gridLineColor: '#e0e0e0',
        lineWidth: 3,
        hideHover: 'auto',
        barColors: [type == 'ups' ? '#fb9678' : '#00bfc7'],
        resize: true

    }

    return new Morris.Bar(conf);
}
