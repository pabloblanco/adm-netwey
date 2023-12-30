function save () {
	if ($('#provider_form').valid()) {
		$('#dni').val($('#rfc').val());
		sav ('#provider_form', function (res) {
			alert(res);
			getview('provider');
		},
		function (res) {
			alert('Ocurrio un error al realizar su operación');
			console.log('error');
			console.log(res);
		});
	}else{
		$('#provider_form').submit(function (e) {
			e.preventDefault();
		})
	}

}

function update (object) {
	setModal(JSON.parse(object));
	$("#myModal").modal();
	//$('#open_modal_btn').click();
}

function deleteData (id, name) {
	del('api/provider/'.concat(id),
		name,
		function (res) {
			//console.log('success: '.concat(res));
			if (res) {
				alert('fue eliminado satisfactoriamente el registro: '.concat(name));
				getview('provider');
			} else {
				alert('error al eliminar el registro: '.concat(name));
			}
		},
		function (res) {
			alert('error al eliminar el registro: '.concat(name));
		});
}

function setModal(object) {
	if (object != null) {
		$('h4.modal-title').text('Editar datos: '.concat(object.name));
		$('#dni').val(object.dni);
		$('#name').val(object.name);
		$('#rfc').val(object.rfc);
		$('#email').val(object.email).prop('readonly', true);
		$('#business_name').val(object.business_name);
		$('#phone').val(object.phone);
		$('#address').val(object.address);
		$('#responsable').val(object.responsable);
		$('#status').val(object.status);
		$('#provider_form').attr('action', 'api/provider/'.concat(object.dni));
		$('#provider_form').attr('method', 'PUT');
	} else {
		$('h4.modal-title').text('Crear proveedor');
		$('#dni').val('');
		$('#name').val('');
		$('#rfc').val('');
		$('#email').val('').prop('readonly', false);
		$('#business_name').val('');
		$('#phone').val('');
		$('#address').val('');
		$('#status').val('A');
		$('#provider_form').attr('action', 'api/provider/store');
		$('#provider_form').attr('method', 'POST');
	}
}

$('#myModal').on('hidden.bs.modal', function () {
    setModal(null);
});
/*
$('button[type="submit"]').attr('disabled','disabled');
$('input').blur(function() {
    if(
    	($('input[name=name]').val().length != 0) &&
    	($('input[name=rfc]').val().length != 0) &&
    	($('input[name=business_name]').val().length != 0) &&
    	($('input[name=address]').val().length != 0) &&
    	($('input[name=phone]').val().length != 0) &&
    	($('input[name=responsable]').val().length != 0) &&
    	($('input[name=email]').val().length != 0)
    	){

        $('button[type="submit"]').removeAttr('disabled');
	}
});
$('#email').blur(function() {
	var regex = /[\w-\.]{2,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/;
    if (!regex.test($('#email').val().trim())) {
        alert('La direccón de correo no es válida');
    }
});
*/
$(document).ready(function () {
	$(".preloader").fadeOut();
	$('#provider_form').validate({
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
    		responsable: {
    			required: true
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
    		responsable: "Por favor especifique el nombre del responsable"
    	}
    });
    $("#open_modal_btn").on('click',()=>{ $("#myModal").modal(); });
});