function save () {
	if ($('#organization_form').valid()) {
		sav ('#organization_form', function (res) {
			alert(res.message);
			getview('organization');
		},
		function (res) {
			alert('Ocurrio un error al realizar su operación');
			console.log('error');
			console.log(res);
		});
	}else{
		$('#organization_form').submit(function (e) {
			e.preventDefault();
		})
	}

}
/*function responsible(rfc){
	$('#rfcr').val(rfc);
	$.ajax({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		async: true,
		url: 'api/organization/get/responsible/'+rfc,
		method: 'GET',
		dataType: "json",
		success: function (res) {
			if(!res.error){
				if(res.users.length > 0){
					$('#responsible').append($('<option>', {value: '', text: 'Seleccione un responsable'}));
					res.users.forEach(function(user){
						$('#responsible').append($('<option>', {value: user.email, text: user.name+' '+user.last_name}));
					});
				}
				if(res.resposible){
					$('#responNow p').text(res.resposible.name);
					$('#responNow').show();
				}
			}
			else
				alert('Ocurrio un error al conectar con el servidor. Por favor intente mas tarde');
		},
		error: function (res) {
			alert('Ocurrio un error al conectar con el servidor. Por favor intente mas tarde');
		}
	});
	$('#responsible').val('');
	$('#open_responsible_btn').click();
}
function saveResponsible(){
	if ($('#responsible_form').valid()) {
		//console.log($('#rfcr').val());
		if (confirm('¿Esta seguro de realizar esta acción?')){
			sav ('#responsible_form', function (res) {
					$('#responsible_close_btn').click();
					alert(res.message);
				},
				function (res) {
					alert('Ocurrio un error al realizar su operación');
					console.log('error');
					console.log(res);
				}
			);
		}else {
			$('#chpass_form').submit(function (e) {
				e.preventDefault();
			})
		}
	}
}*/

function update (object) {
	setModal(object);
	// $('#open_modal_btn').click();
	$("#myModal").modal();
}

function deleteData (rfc, name) {
	if (confirm('¿desea eliminar la organización: '+name+'?')){
		request ('api/organization/'.concat(rfc), 'DELETE', null,
			function (res) {
				if (res){
					alert('fue eliminado satisfactoriamente la organización: '+name);
					getview('organization');
				}else{
					alert('error al eliminar la organización: '+name);
				}
			},
			function (res) {
				console.log('error: '.concat(res));
			});
	}
}

function setModal(object) {
	if (object != null) {
		var object = object.replace(/\\\'/g, '"').replace(/\'/g, '"');

		object = JSON.parse(object);

		//$('#rfcr').val(rfc);

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			async: true,
			url: 'api/organization/get/responsible/'+object.rfc,
			method: 'GET',
			dataType: "json",
			success: function (res) {
				if(!res.error){
					$('h4.modal-title').text('Editar datos: '+object.business_name);
					$('#business_name').val(object.business_name);
					$('#rfc').val(object.rfc);
					$('#address').val(object.address);
					$('#status').val(object.status);
					$('#organization_form').attr('action', 'api/organization/update/'+object.rfc);

					$('#contact_name').val(object.contact_name);
					$('#contact_phone').val(object.contact_phone);
					$('#contact_email').val(object.contact_email);
					$('#contact_address').val(object.contact_address);
					$('#type').val(object.type);

					if(res.users.length > 0){
						$('#responsible').append($('<option>', {value: '', text: 'Seleccione un responsable'}));
						res.users.forEach(function(user){
							$('#responsible').append($('<option>', {value: user.email, text: user.name+' '+user.last_name}));
						});
					}
					if(res.resposible){
						$('#responNow p').text(res.resposible.name);
						$('#responNow').show();
					}
					$('#panel-responsable').show();
				}
				else
					alert('Ocurrio un error al conectar con el servidor. Por favor intente mas tarde');
			},
			error: function (res) {
				alert('Ocurrio un error al conectar con el servidor. Por favor intente mas tarde');
			}
		});
	} else {
		$('h4.modal-title').text('Organización');
		$('#business_name').val('');
		$('#rfc').val('');
		$('#address').val('');
		$('#status').val('A');
		$('#type').val('N');
		$('#organization_form').attr('action', 'api/organization/store');

		$('#contact_name').val('');
		$('#contact_phone').val('');
		$('#contact_email').val('');
		$('#contact_address').val('');

		$('#responsible').val('');
		$('#responNow').hide();
		$('#panel-responsable').hide();
	}
}

$('#myModal').on('hidden.bs.modal', function () {
    setModal(null);
});

$(document).ready(function () {
	$('#organization_form').validate({
    	rules: {
    		business_name: {
    			required: true
    		},
    		rfc: {
    			required: true
    		},
    		address: {
    			required: true
    		},
    		contact_email: {
    			email: true
    		}
    	},
    	messages: {
    		rfc: "Por Favor especifique el R.F.C",
    		business_name: "Por Favor especifique la razon social",
    		address: "Por favor especifique la direccón",
    		contact_email: "Por favor escriba una direccion de correo válida",
    	}
    });
    $('#responsible_form').validate({
    	rules: {
    		responsible: {
    			required: true
    		}
    	},
    	messages: {
    		responsible: "Por favor especifique el responsable"
    	}
    });
    $('#myTable').DataTable({
        "columnDefs": [
			{
				"targets": 4,
				"orderable": false
			}
		],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        },
        processing: true,
        serverSide: true,
        ajax: 'api/organization/get/datatable',
        deferRender: true,
        columns: [
        	/*{data: 'actions', render: function(data,type,row,meta){
            	return row.actions;
            }},*/
            {data: 'rfc', render: function(data,type,row,meta){
            	var html = '';

            	if(row.action_edit)
            		html = html + '<button type="button" class="btn btn-warning btn-sm button" onclick="update(\''+JSON.stringify(row).replace(/"/g, '\\\'')+'\')">Editar</button>';
            	if(row.action_delete)
            		html = html + '<button type="button" class="btn btn-danger btn-sm button" onclick="deleteData(\''+row.rfc+'\',\''+row.business_name+'\')">Eliminar</button>';
        		//html = html + '<button type="button" class="btn btn btn-sm button" onclick="responsible(\''+row.rfc+'\')">Responsable</button>';

            	return html;
            }},
            {data: 'business_name'},
            {data: 'address'},
            {data: 'rfc'},
            {data: 'status'}
        ]
    });

    $("#open_modal_btn").on('click',()=>{ $("#myModal").modal(); });
});