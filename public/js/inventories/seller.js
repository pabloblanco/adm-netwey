currentModuleAPI = 'api/seller/inventories/';
currentModuleViewAPI = 'seller_inventories';

function save () {
	sav ('#inventory_form', function (res) {
		alert(res);
		getview(currentModuleViewAPI);
	},
	function (res) {
	});
}

function update (object) {
	setModal(JSON.parse(object));
	$('#open_modal_btn').click();
}

function deleteData (id, name) {
	del(currentModuleAPI.concat(id),
		name,
		function (res) {
			console.log('success: '.concat(res));
			if (res) {
				alert('fue eliminado satisfactoriamente el registro: '.concat(name));
				getview(currentModuleViewAPI);
			} else {
				alert('error al eliminar el registro: '.concat(name));
			}
		},
		function (res) {
			alert('error al eliminar el registro: '.concat(name));
		});
}

function setModal(object) {
	var frm = $('#inventory_form');
	if (object != null) {
		$('h4.modal-title').text('Editar datos: inventario general N° '.concat(object.id));
		$('#cb_parent_id_container').attr('checked', object.parent_id != null ? 'checked' : false);
		$('#parent_id').val(object.parent_id);
		$('#inv_article_id').val(object.inv_article_id);
		$('#warehouses_id').val(object.warehouses_id);
		$('#serial').val(object.serial);
		$('#msisdn').val(object.msisdn);
		$('#iccid').val(object.iccid);
		$('#imsi').val(object.imsi);
		$('#date_reception').val(object.date_reception);
		$('#date_sending').val(object.date_sending);
		$('#price_pay').val(object.price_pay);
		$('#obs').val(object.obs);
		$('#status').val(object.status);
		frm.attr('action', currentModuleAPI.concat(object.id));
		frm.attr('method', 'PUT');
	} else {
		$('h4.modal-title').text('Crear inventario general');
		$('#cb_parent_id_container').attr('checked', false);
		$('#parent_id').val('');
		$('#inv_article_id').val('');
		$('#warehouses_id').val('');
		$('#serial').val('');
		$('#msisdn').val('');
		$('#iccid').val('');
		$('#imsi').val('');
		$('#date_reception').val('');
		$('#date_sending').val('');
		$('#price_pay').val('');
		$('#obs').val('');
		$('#status').val('A');
		frm.attr('action', currentModuleAPI.concat('store'));
		frm.attr('method', 'POST');
	}
}

$('#myModal').on('hide.bs.modal', function () { 
    setModal(null);
});

function CBOnClick(id) {
	if($(id).is(':checked')) {
		$('#parent_id_container').show();
	} else {
		$('#parent_id_container').hide();
	}
}

$(document).ready(function () {
	$('#parent_id').selectize();
	$('#inv_article_id').selectize();
	$('#warehouses_id').selectize();
	$(".preloader").fadeOut();
	$('h4.modal-title').text('Crear inventario general');
	$('#inventory_form').attr('action', currentModuleAPI.concat('store'));
	CBOnClick('#cb_parent_id_container');
	$('#cb_parent_id_container').click(function(){
		CBOnClick('#cb_parent_id_container');
	});
});