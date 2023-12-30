$(document).ready(function () {
	if($.fn.DataTable.isDataTable('#dt-prospect'))
	    $('#dt-prospect').DataTable().destroy();

	$('#dt-prospect').DataTable({
        language: {
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
        },
        order: [[ 1, "desc" ]],
        processing: true,
        serverSide: true,
        deferRender: true,
        ajax: {
        	headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        	url: 'reports/prospects/dt',
        	data: function(d){
     			d.org = $('#org').val();
     			d.seller = $('#seller').val();
     			d.date_ini = $('#date_ini').val();
     			d.date_end = $('#date_end').val();
        	},
        	type: "POST"
        },
        columns: [
        	{data: 'date_reg', searchable: false},
        	{data: 'name', orderable: false},
        	{data: 'email', orderable: false},
        	{data: 'phone_home', orderable: false},
        	{data: 'address', orderable: false, searchable: false},
        	{data: 'note', orderable: false, searchable: false},
        	{data: 'contact_date', searchable: false},
        	{data: 'seller_name', orderable: false, searchable: false},
        	{data: 'name_coord', orderable: false, searchable: false},
        	{data: 'business_name', orderable: false, searchable: false},
        	{data: 'campaign', orderable: false, searchable: true}
        ]
    });

	$('#downloadR').on('click', function(e){
		var value = '';
		var data = $("#report_form").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content') + '&emails=' + value;

		$(".preloader").fadeIn();

		$.ajax({
		    type: "POST",
		    url: "reports/prospects/download",
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
					var data = $("#report_form").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content') + '&emails=' + value;

					$(".preloader").fadeIn();

					$.ajax({
					    type: "POST",
					    url: "reports/prospects/download",
					    data: data,
					    dataType: "json",
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