var fv;

function save () {
	
	$('input#name').val($.trim($('input#name').val()));


	let rules = {
		name: {
			required: true,
		    minlength: 3,
		    maxlength: 45
		},
		service_id: {
			required: true
		},
		qty: {
			required: true,
		    min: 1
		},	
		period_days: {
			required: true,
		    min: 1
		},		
	};

	let messages = {
		name: "Por Favor especifique el Nombre",
		service_id: "Por Favor seleccione un servicio",
		qty: "Por Favor ingrese una cantidad de activaciones",
		period_days: "Por Favor ingrese el periodo de las activaciones"	
	}

	if(fv){
		fv.destroy();
	}

	fv = $('#service_form').validate({
    	rules: rules,
    	messages: messages
    });

	if ($('#service_form').valid()){
		sav('#service_form', function (res) {
			if ( res.success ) {
				getview('services_prom');
				alert(res.msg);
			} else {
				$(".preloader").fadeOut();
				alert(res.msg);
				console.log('error', res.errorMsg);
			}
		},
		function (res) {
			alert(res.msg);
			console.log('error', res.errorMsg);
		});
	}else{
		$('#service_form').submit(function (e) {
			e.preventDefault();
		});
	}
}

function update (object) {
	setModal(JSON.parse(object));
	$('#open_modal_btn').click();
}

function deleteData (id, name) {
	if (confirm('¿desea eliminar el servicio promocional: "'+name+'"?')){
		request ('api/servicesprom/'.concat(id), 'DELETE', null,
			function (res) {
				if ( res.success ) {
					getview('services_prom');
					alert(res.msg);
				} else {
					alert(res.msg);
					console.log('error', res.errorMsg);
				}
			},
			function (res) {
				alert(res.msg);
				console.log('error', res.errorMsg);
			});
	}
}

function setModal(object) {
	if (object != null) {
		$('h4.modal-title').text('Editar datos: '.concat(object.name));
		$('#id').val(object.id);
		$('#name').val(object.name);
		$('#service_id').val(object.service_id);
		$('#qty').val(object.qty);	
		$('#period_days').val(object.period_days);		
		$('#status').val(object.status);
		$('#service_form').attr('action', 'api/servicesprom/'.concat(object.id));
		$('#service_form').attr('method', 'PUT');

	} else {
		$('h4.modal-title').text('Crear servicio');
		$('#id').val('');
		$('#service_form')[0].reset();
		$('#service_form').attr('action', 'api/servicesprom/store');
		$('#service_form').attr('method', 'POST');		
	}
}

$('#myModal').on('hidden.bs.modal', function () { 
    setModal(null);
});

$(document).ready(function () {
	
	$(".preloader").fadeOut();
	if ( ! $.fn.DataTable.isDataTable('#myTable') ) {
		$('#myTable').DataTable({
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
	        searching: false,
	        order: false,
	    });
	}
});