function save (id) {
	if ($('#server_form').valid()) {
		sav ('#server_form', function (res) {
			alert(res);
			getview('servers/'.concat(id));
		},
		function (res) {
			alert('Ocurrio un error al realizar su operación');
			console.log('error');
			console.log(res);
		});
	}else{
		$('#server_form').submit(function (e) {
			e.preventDefault();
		})
	}

}

function update (object) {
	setModal(JSON.parse(object));
	$("#myModal").modal();
}

function deleteData (id, name, conc) {
	if (confirm('¿desea eliminar el servidor: '+name+'?')){
		request ('api/servers/'.concat(id), 'DELETE', null,
			function (res) {
				if (res){
					alert('fue eliminado satisfactoriamente el servidor: '+name);
					getview('servers/'.concat(conc));
				}else{
					alert('error al eliminar el servidor: '+name);
				}
			},
			function (res) {
				console.log('error: '.concat(res));
			});
	}
}

function setModal(object) {
	if (object != null) {
		$('h4.modal-title').text('Editar datos: '.concat(object.ip));
		$('#concentrator').val(object.concentrator);
		$('#ip').val(object.ip);
		$('#type').val(object.type);
		$('#status').val(object.status);
		$('#server_form').attr('action', 'api/servers/'.concat(object.id));
		$('#server_form').attr('method', 'PUT');
	} else {
		$('h4.modal-title').text('Crear servidor');
		$('#concentrator').val('');
		$('#ip').val('');
		$('#type').val('');
		$('#status').val('A');
		$('#server_form').attr('action', 'api/servers/store');
		$('#server_form').attr('method', 'POST');
	}
}

$('#myModal').on('hide.bs.modal', function () {
    setModal(null);
});

jQuery.validator.addMethod("ipAddressFormat",function(value, element){
    theName = "IPaddress";
    var ipPattern = /^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/;
    var ipArray = value.match(ipPattern);
    if (value == "0.0.0.0" || value == "255.255.255.255" ||
        value == "10.1.0.0" || value == "10.1.0.255" || ipArray == null)
        return false;
    else {
        for (i = 0; i < 4; i++) {
            thisSegment = ipArray[i];
            if (thisSegment > 254) {
                return false;
            }
            if ((i == 0) && (thisSegment > 254)) {
                return false;
            }
        }
    }
    return true;
}, "Direccón de ip invalida");

$(document).ready(function () {
	$('#concentrator').selectize();
	$(".preloader").fadeOut();
	$('#server_form').validate({
    	rules: {
    		ip: {
    			required: true,
    			ipAddressFormat: true
    		}
    	},
    	messages: {
    		ip: {
    			required: "Por favor especifique una ip"
    		}
    	}
    });

    $("#open_modal_btn").on('click',()=>{ $("#myModal").modal(); });
});