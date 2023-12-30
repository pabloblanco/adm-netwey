$(document).ready(function () {
	var view = $('#view').val();
	if(view && view != ''){
		var lang = {
			sProcessing:     "Procesando...",
			sLengthMenu:     "Mostrar _MENU_ registros",
			sZeroRecords:    "No se encontraron resultados",
			sEmptyTable:     "Ningún dato disponible en esta tabla",
			sInfo:           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
			sInfoEmpty:      "Mostrando registros del 0 al 0 de un total de 0 registros",
			sInfoFiltered:   "(filtrado de un total de _MAX_ registros)",
			sInfoPostFix:    "",
			sSearch:         "Buscar:",
			sUrl:            "",
			sInfoThousands:  ",",
			sLoadingRecords: "Cargando...",
			oPaginate: {
				sFirst:    "Primero",
				sLast:     "Último",
				sNext:     "Siguiente",
				sPrevious: "Anterior"
			},
			oAria: {
				sSortAscending:  ": Activar para ordenar la columna de manera ascendente",
				sSortDescending: ": Activar para ordenar la columna de manera descendente"
			}
		};

		if($.fn.DataTable.isDataTable('#dt-'+view)){
	        $('#dt-'+view).DataTable().destroy();
		}

	    if(view == 'recharges'){
	    	var url = 'view/reports/ur/detail-dt/recharge';
	    	var colums = [
	    		{data: 'unique_transaction', orderable: false},
	    		{data: 'folio', orderable: false, searchable: false},
	            {data: 'date_reg', searchable: false},
	            {data: 'concentrator', searchable: false, orderable: false},
	            {data: 'user_name', searchable: false, orderable: false},

	            {data: 'article', searchable: false},
	            {data: 'msisdn', orderable: false},
	            {data: 'is_migration', orderable: false},
	            {data: 'sale_type', orderable: false},
	            {data: 'imei', name: 'islim_inv_arti_details.imei', orderable: false},
	            {data: 'iccid', name: 'islim_inv_arti_details.iccid', orderable: false},
	            {data: 'service', searchable: false},
	            {data: 'client_name', name: 'islim_clients.name', orderable: false},
	            {data: 'client_phone', name: 'islim_clients.phone_home', orderable: false},
	            {data: 'client_phone2', name: 'islim_clients.phone', orderable: false},
	            {data: 'zone_name', searchable: false, orderable: false},
	            {data: 'amount', searchable: false, orderable: false},
	            {data: 'type_buy', searchable: false},
	            {data: 'conciliation', searchable: false},
	            {data: 'lat', searchable: false, orderable: false},
	            {data: 'lng', searchable: false, orderable: false},
	            {data: 'billing', searchable: false, orderable: false},
	            {data: 'installer_name', searchable: false, orderable: false},
	            {data: 'installer_email', searchable: false, orderable: false}
	    	];
	    }
	    if(view == 'ups'){
	    	var url = 'view/reports/ur/detail-dt/ups';
	    	var colums = [
	    		{data: 'unique_transaction', orderable: false},
	            {data: 'date_reg', name: 'islim_sales.date_reg', searchable: false},
	            {data: 'business_name', searchable: false, orderable: false},
	            {data: 'user_name', searchable: false, orderable: false},
	            {data: 'coord_name', searchable: false, orderable: false},
	            {data: 'pack', searchable: false},
	            {data: 'article', searchable: false},
	            {data: 'msisdn', orderable: false},
	            {data: 'is_migration', orderable: false},
	            {data: 'sale_type', orderable: false, searchable: false},
	            {data: 'imei', name: 'islim_inv_arti_details.imei', orderable: false},
	            {data: 'iccid', name: 'islim_inv_arti_details.iccid', orderable: false},
	            {data: 'service', searchable: false},
	            {data: 'client_name', name: 'islim_clients.name', orderable: false},
	            {data: 'client_phone', name: 'islim_clients.phone_home', searchable: false, orderable: false},
	            {data: 'client_phone2', name: 'islim_clients.phone', searchable: false, orderable: false},
	            {data: 'zone_name', searchable: false, orderable: false},
	            {data: 'amount', searchable: false, orderable: false},
	            {data: 'type_buy', searchable: false},
	            {data: 'conciliation', searchable: false},
	            {data: 'lat', searchable: false, orderable: false},
	            {data: 'lng', searchable: false, orderable: false},
	            {data: 'billing', searchable: false, orderable: false},
	            {data: 'campaign', name: 'islim_clients.campaign', searchable: false, orderable: false},
	            {data: 'from', searchable: false, orderable: false},
	            {data: 'user_email', searchable: false, orderable: false},
	            {data: 'coord_email', searchable: false, orderable: false},
				{data: 'user_locked', searchable: false, orderable: false},
				{data: 'installer_name', searchable: false, orderable: false},
				{data: 'installer_email', searchable: false, orderable: false},
				{data: 'typePayment', searchable: false, orderable: false}
	    	];
	    }

	    if(url){
	    	//$.fn.dataTable.moment('YYYY-MM-DD HH:mm:ss');

	    	$('#dt-'+view).DataTable({
		        language: lang,
		        order: [[ 1, "desc" ]],
		        processing: true,
		        serverSide: true,
		        deferRender: true,
		        ajax: {
		        	url: url,
		        	data: function (d) {
		                d._token = $('meta[name="csrf-token"]').attr('content');
		                d.supervisor = $('#coord').val();
		                d.seller = $('#seller').val();
		                d.date_ini = $('#date_ini').val();
		                d.date_end = $('#date_end').val();
		                d.service = $('#service').val();
		                d.product = $('#product').val();
		                d.conciliation = $('#conciliation').val();
		                d.serviceability = $('#serviceability').val();
		                d.org = $('#org').val();
		                d.type_buy = $('#type_buy').val();
		                d.type_line = $('#type_line').val();
		                d.coverage_area = $('#coverage_area').val();
		            },
		        	type: "POST"
		        },
		        columns: colums
		    });
	    }
	}


	$('#downloadR').on('click', function(e){
		var value = '';
		var data = $("#uporrecharge_form").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content') + '&emails=' + value;

		$(".preloader").fadeIn();

		$.ajax({
		    type: "POST",
		    url: "view/reports/ur/download/detail",
		    data: data,
		    dataType: "json",
		    success: function(response){
		    	$(".preloader").fadeOut();
		    	swal('Generando reporte','El reporte estara disponible en unos minutos.','success');
		    },
		    error: function(err){
		    	$(".preloader").fadeOut();
		    	swal('Error','No se pudo generar el reporte.','error');
		    }
		});
		/*swal('Por favor ingrese el o los email(s) separados por ","', {
			content: {
			    element: "input",
			    attributes: {
			      placeholder: "ejm@correo.com, ejm2@correo.com",
			      type: "email",
			    }
			},
			buttons: {
				cancel: true,
				confirm: "Enviar",
			},
			closeOnClickOutside: true,
		})
		.then((value) => {
			if(value !== null){
				if(value && value != ''){
					var data = $("#uporrecharge_form").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content') + '&emails=' + value;

					$(".preloader").fadeIn();

					$.ajax({
					    type: "POST",
					    url: "view/reports/ur/download/detail",
					    data: data,
					    dataType: "text",
					    success: function(response){
					    	$(".preloader").fadeOut();

					    	swal('El reporte sera enviado a los correos especificados.');
					    },
					    error: function(err){
					    	$(".preloader").fadeOut();
					    }
					});
				}else{
					swal('Debe ingresar uno o mas emails.');
				}
			}
		});*/
	});
});