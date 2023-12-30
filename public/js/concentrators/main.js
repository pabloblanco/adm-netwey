function swalalert(id_form){
    swal("Por favor ingrese su segunda contraseña", {
		content: {
		    element: "input",
		    attributes: {
		      placeholder: "",
		      type: "password",
		    },
		},
        closeOnClickOutside: false,
        closeOnEsc: false,
        buttons: {
            cancel: true,
            confirm: true,
        }
	})
	.then((value) => {
		if(value && value != ''){
			$('#second_pass').val(value);
			sav (id_form, function (res) {
				alert(res);
				getview('concent');
			},
			function (res) {
				alert('Ocurrio un error al realizar su operación');
				console.log('error');
				console.log(res);
			});
			$(id_form).submit();
		}else{
            if(value != null){
                swal('Debe ingresar su segunda contraseña.')
                .then(() => {
                    swalalert(id_form);
                });
            }
        }
	});
}

function save () {
	var id_form = '#concentrator_form';
	if ($(id_form).valid()) {
		$(id_form).submit(function (e) {
			e.preventDefault();
		});

		$('#dni').val($('#rfc').val());

		swalalert(id_form);


		//Validando clave para editar concentrador
		// swal("Por favor ingrese su segunda contraseña", {
		// 	content: {
		// 	    element: "input",
		// 	    attributes: {
		// 	      placeholder: "",
		// 	      type: "password",
		// 	    },
		// 	}
		// })
		// .then((value) => {
		// 	if(value && value != ''){
		// 		$('#second_pass').val(value);
		// 		sav ('#concentrator_form', function (res) {
		// 			alert(res);
		// 			getview('concent');
		// 		},
		// 		function (res) {
		// 			alert('Ocurrio un error al realizar su operación');
		// 			console.log('error');
		// 			console.log(res);
		// 		});
		// 		$('#concentrator_form').submit();
		// 	}else{
		// 		swal('Debe ingresar su segunda contraseña.');
		// 	}
		// });
	}

}

function update (object) {
	setModal(JSON.parse(object));
	//$('#open_modal_btn').click();
	$("#myModal").modal();
}

function deleteData (id, name) {
	if (confirm('¿desea eliminar al concentrador: '+name+'?')){
		request ('api/concentrator/'.concat(id), 'DELETE', null,
			function (res) {
				if (res){
					alert('fue eliminado satisfactoriamente al concentrador: '+name);
					getview('concent');
				}else{
					alert('error al eliminar al concentrador: '+name);
				}
			},
			function (res) {
				console.log('error: '.concat(res));
			});
	}
}

function setModal(object) {
	if (object != null) {
		$('h4.modal-title').text('Editar datos: '+object.name);
		$('#name').val(object.name);
		$('#rfc').val(object.rfc);
		$('#email').val(object.email);
		$('#dni').val(object.dni);
		$('#business_name').val(object.business_name);
		$('#phone').val(object.phone);
        if ($('#address')[0]) {
            new google.maps.places.Autocomplete($('#address')[0]);
        }
		$('#address').val(object.address);
		$('#balance').val(object.balance);
		$('#commissions').val(object.commissions);
		$('#status').val(object.status);
		if(object.id_channel)
			$('#id_channel').val(object.id_channel);
		else
			$('#id_channel').val('');

		//campos para post-pagos
		$('#postpaid').val(object.postpaid);
		if(object.postpaid == 'N'){
			$('#amount_alert').val('').attr('disabled',true);
			$('#amount_allocate').val('').attr('disabled',true);
		}else{
			$('#amount_alert').val(object.amount_alert).attr('disabled',null);
			$('#amount_allocate').val(object.amount_allocate).attr('disabled',null);
		}

		$('#concentrator_form').attr('action', 'api/concentrator/'+object.id);
		$('#concentrator_form').attr('method', 'PUT');
	} else {
		$('h4.modal-title').text('Crear concentrador');
		$('#name').val('');
		$('#rfc').val('');
		$('#email').val('');
		$('#dni').val('');
		$('#business_name').val('');
		$('#phone').val('');
		$('#address').val('');
		$('#balance').val('');
		$('#commissions').val('');
		$('#status').val('A');
		$('#id_channel').val('');

		$('#postpaid').val('N');
		$('#amount_alert').val('').attr('disabled',true);
		$('#amount_allocate').val('').attr('disabled',true);

		$('#concentrator_form').attr('action', 'api/concentrator/store');
		$('#concentrator_form').attr('method', 'POST');
	}
}

$(document).ready(function () {
	/*$('#concentrator_form').on('submit', function(e){
		e.preventDefault();
		console.log('esperando contraseña: '+$('#concentrator_form').attr('method'));
	});*/

	$('#myModal').on('hidden.bs.modal', function () {
	    setModal(null);
	});

	$('#concentrator_form').validate({
    	rules: {
    		name: {
    			required: true
    		},
    		rfc: {
    			required: true
    		},
    		email: {
    			required: true,
    			email: true
    		},
    		business_name: {
    			required: true
    		},
    		address: {
    			required: true
    		},
    		phone: {
    			required: true
    		},
    		balance: {
    			number: true,
    			range: [0, maxbalance]
    		},
    		commissions: {
    			number: true,
    			range: [0, maxcomissions]
    		}
    	},
    	messages: {
    		name: "Por favor especifique el nombre",
    		rfc: "Por Favor especifique el R.F.C",
    		email: {
    			required: "Por favor especifique el email",
                email: "Ingrese una dirección de correo valida"
    		},
    		business_name: "Por Favor especifique la razon social",
    		address: "Por favor especifique la direccón",
    		phone: "Por favor especifique un numero de telefono",
    		balance: {
    			number: "Ingrese solo numeros",
    			range: "Ingrese un valor comprendido entre de 0 y "+maxbalance
    		},
    		commissions: {
    			number: "Ingrese solo numeros",
    			range: "Ingrese un valor comprendido entre de 0 y "+maxcomissions
    		}
    	}
    });

    $('#postpaid').on('change', function(e){
    	var v = $('#postpaid').val();
    	if(v == 'N'){
    		$('#amount_alert').val('').attr('disabled',true);
    		$('#amount_allocate').val('').attr('disabled',true);
    	}else{
    		$('#amount_alert').attr('disabled',null);
    		$('#amount_allocate').attr('disabled',null);
    	}
    });

    $("#open_modal_btn").on('click',()=>{ $("#myModal").modal(); });

});