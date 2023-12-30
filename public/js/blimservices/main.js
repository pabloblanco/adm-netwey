var fv;

function save () {
	
	$('input#name').val($.trim($('input#name').val()));
	$('input#description').val($.trim($('input#description').val()));
	$('input#sku').val($.trim($('input#sku').val()));

	let rules = {
		name: {
			required: true,
		    minlength: 3,
		    maxlength: 45
		},
		description: {
			required: true,
		    minlength: 3,
		    maxlength: 255
		},
		sku: {
			required: true,
		    minlength: 6,
		    maxlength: 45
		}
	};

	let messages = {
		name: "Por Favor especifique el Nombre",
		description: "Por Favor ingrese una descripción",
		sku: "Por Favor ingrese un SKU"	
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
				getview('blim/services');
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
	if (confirm('¿desea eliminar el servicio blim: "'+name+'"?')){
		request ('api/blimservices/'.concat(id), 'DELETE', null,
			function (res) {
				if ( res.success ) {
					getview('blim/services');
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
		$('#description').val(object.description);
		$('#sku').val(object.sku);	
		$('#price').val(object.price);		
		$('#status').val(object.status);
		$('#service_form').attr('action', 'api/blimservices/'.concat(object.id));
		$('#service_form').attr('method', 'PUT');

	} else {
		$('h4.modal-title').text('Crear servicio');
		$('#id').val('');
		$('#name').val('');
		$('#description').val('');
		$('#price').val('');
		$('#sku').val('');
		$('#status').val('A');
		$('#service_form').attr('action', 'api/blimservices/store');
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