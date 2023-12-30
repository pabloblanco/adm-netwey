let fv;

let rules= {
	name: {
		required: true
	},
	address: {
		required: true
	}
};

let messages = {
	name: "Por favor especifique el nombre",
	address: "Por favor especifique la direccón"
};

function save () {
	if ($('#warehouse_form').valid()){
		var l = '';
		var i = 0;
		$('#users_email option:selected').each(function(){
			i = i + 1;
			if (i > 1) {
				l = l.concat(';');
			}
			l = l.concat($(this).val());
		// console.log(l);
		});
		$('#users_email_list').val(l);
		sav ('#warehouse_form', function (res) {
			alert(res);
			getview('warehouses');
		},
		function (res) {
			alert('Ocurrio un error al realizar su operación');
			console.log('error');
			console.log(res);
		});
	}else{
		$('#warehouse_form').submit(function (e) {
			e.preventDefault();
		})
	}

}

function update (object) {
	setModal(JSON.parse(object));
	$('#myModal').modal('show');
	//$('#open_modal_udp').click();
}

function deleteData (id, name) {
	del('api/warehouses/'.concat(id),
		name,
		function (res) {
			console.log('success', res);
			if (res) {
				alert('fue eliminado satisfactoriamente la bodega: '.concat(name));
				getview('warehouses');
			} else {
				alert('error al eliminar la bodega: '.concat(name));
			}
		},
		function (res) {
			alert('error al eliminar la bodega: '.concat(name));
		});
}

function setModal(object) {
	$('.group-log').attr('hidden', true);

	if (object != null) {
		$('h4.modal-title').text('Editar datos: '.concat(object.name));
		$('#name').val(object.name);
		$('#phone').val(object.phone);
		/*if ($('#address')[0]) {
				new google.maps.places.Autocomplete($('#address')[0]);
		}*/
		$('#address').val(object.address);
		$('#phone').val(object.phone);
		$('#lat').val(object.lat);
		$('#lng').val(object.lng);
		$('#position').val('POINT('.concat(object.lat.concat(','.concat(object.lng.concat(')')))));
    $('#users_email_list').val('');
		$('#org').val(object.org);
		
		if (object.users.length > 0) {
			var arr = [];
			for(var index = 0; index < object.users.length; index++) {
				var user = object.users[index];
				arr.push(user.email);
				if (index == 0) {
					$('#users_email_list').val(user.email);
				} else {
					$('#users_email_list').val($('#users_email_list').val().concat(';'.concat(user.email)));
				}
			}
			
			setSelect('users_email', arr);
		}

		if(object.group){
			$('#group_log').val(object.group);
			$('#route').val(object.route);
			$('#street_n').val(object.street_number);
			$('#neighb').val(object.neighborhood);
			$('#locality').val(object.locality);
			$('#sublocality').val(object.subLocality);
			$('#state').val(object.state);
			$('#pc').val(object.cp);

			$('#group_log').trigger('change');
		}
		
		$('#status').val(object.status);
		$('#warehouse_form').attr('action', 'api/warehouses/'.concat(object.id));
		$('#warehouse_form').attr('method', 'PUT');
	} else {
		$('h4.modal-title').text('Crear almacén');
		$('#name').val('');
		$('#address').val('');
		$('#phone').val('');
		$('#lat').val('0');
		$('#lng').val('0');
		$('#position').val('POINT(0,0)');
		$('#users_email_list').val('');
		$('#org').val('');
		$('#group_log').val('');
		setSelect('users_email', null);
		$('#status').val('A');
		$('#warehouse_form').attr('action', 'api/warehouses/store');
		$('#warehouse_form').attr('method', 'POST');
	}
}

$('#myModal').on('hide.bs.modal', function () {
    setModal(null);
});
/*
$('button[type="submit"]').attr('disabled','disabled');
$('input').blur(function() {
    if(
    	($('input[name=name]').val().length != 0) &&
    	($('input[name=address]').val().length != 0) &&
    	($('input[name=phone]').val().length != 0)
    	){

        $('button[type="submit"]').removeAttr('disabled');
	}
});
*/
$(document).ready(function () {
	$('#users_email').selectize({
		valueField: 'article',
		delimiter: ';',
		maxItems: null
	});

	$(".preloader").fadeOut();

	$('#group_log').on('change', function(e){
		if($(this).val() != ''){
			$('.group-log').attr('hidden', null);

			rules.route = {
				required: true
			};
			messages.route = {
				required: 'Por favor indique la calle'
			}
			rules.street_n = {
				required: true
			};
			messages.street_n = {
				required: 'Por favor indique el número de calle'
			}
			rules.neighb = {
				required: true
			};
			messages.neighb = {
				required: 'Por favor indique la población'
			}
			rules.locality = {
				required: true
			};
			messages.locality = {
				required: 'Por favor indique la localidad'
			}
			rules.sublocality = {
				required: true
			};
			messages.sublocality = {
				required: 'Por favor indique la colonia'
			}
			rules.state = {
				required: true
			};
			messages.state = {
				required: 'Por favor indique el estado'
			}
			rules.pc = {
				required: true,
				minlength: 5
			};
			messages.pc = {
				required: 'Por favor indique el código postal',
				minlength: 'Código postal no válido'
			}
		}else{
			$('.group-log').attr('hidden', true);

			rules.route = undefined;
			messages.route = undefined;
			rules.street_n = undefined;
			messages.street_n = undefined;
			rules.neighb = undefined;
			messages.neighb = undefined;
			rules.locality = undefined;
			messages.locality = undefined;
			rules.sublocality = undefined;
			messages.sublocality = undefined;
			rules.state = undefined;
			messages.state = undefined;
			rules.pc = undefined;
			messages.pc = undefined;
		}

		if (fv) {
			fv.destroy();
		}

		fv = $('#warehouse_form').validate({
			rules: rules,
			messages: messages
		});
	});

	fv = $('#warehouse_form').validate({
		rules: rules,
		messages: messages
	});

  $("#open_modal_btn").on('click',()=>{ $("#myModal").modal(); });
});