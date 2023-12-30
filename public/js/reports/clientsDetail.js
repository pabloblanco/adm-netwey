$(document).ready(function () {
	var files = document.getElementById('msisdn_file');
    var params = new FormData();

    params.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if($('#client_manual_check').is(':checked'))
        params.append('msisdn_select', getSelectObject('msisdn_select').getValue());
    else
        params.append('msisdns_file', files.files[0]);

    params.append('service', $('#service').val());
    params.append('date_ini', $('#date_ini').val());
    params.append('date_end', $('#date_end').val());
    params.append('type_line', $('#type_line').val());

	if($.fn.DataTable.isDataTable('#dt-client')){
	    $('#dt-client').DataTable().destroy();
	}

	$('#dt-client').DataTable({
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
        	url: 'view/reports/clients/dt',
        	contentType: false,
            processData: false,
            async: false,
            cache: false,
        	data: function(d){
     			buildFormData(params, d);
     			return params;
        	},
        	type: "POST"
        },
        columns: [
        	{data: 'client_date', searchable: false},
        	{data: 'prospect_date', searchable: false},
        	{data: 'name', orderable: false},
        	{data: 'email', orderable: false},
        	{data: 'msisdn', orderable: false},
        	{data: 'dn_type', orderable: false},
        	{data: 'phone_home', orderable: false},
        	{data: 'address', orderable: false, searchable: false},
        	{data: 'service', orderable: false, searchable: false},
        	{data: 'speed', searchable: false},
					{data: 'typePayment', searchable: false, orderable: false}
        ]
    });

    $('#downloadR').on('click', function(e){
    	$(".preloader").fadeIn();

		$.ajax({
		    type: "POST",
		    url: "view/reports/clients/download",
		    contentType: false,
            processData: false,
            async: false,
            cache: false,
		    data: params,
		    success: function(res){
		    	$(".preloader").fadeOut();
		    	swal('Generando reporte','El reporte estara disponible en unos minutos.','success');
		    },
		    error: function(){
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
					params.append('emails', value);

					$(".preloader").fadeIn();

					$.ajax({
					    type: "POST",
					    url: "view/reports/clients/download",
					    contentType: false,
			            processData: false,
			            async: false,
			            cache: false,
					    data: params,
					    //dataType: "json",
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

//Funcion recursiva para parsear objecto JSON a un formData
function buildFormData(formData, data, parentKey) {
  if (data && typeof data === 'object' && !(data instanceof Date) && !(data instanceof File)){
    Object.keys(data).forEach(key => {
      buildFormData(formData, data[key], parentKey ? `${parentKey}[${key}]` : key);
    });
  } else {
    const value = data == null ? '' : data;

    formData.append(parentKey, value);
  }
}