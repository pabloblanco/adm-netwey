function aprove(params) {
	var checkboxes = $('input:checkbox:checked');
	var api = '';
	var ids = JSON.parse(params);
	if (!((checkboxes.length == 0) || (checkboxes.length == ids.length))) {
		if (validateAmounts(checkboxes)) {
			ids = getIdsFromCheckboxes(checkboxes);
		} else {
			alert('Debe indicar los montos recibidos por venta');
		}
	}
	var receiveds = new Array();
	ids.forEach(function (item, key) {
		receiveds.push($('#received_'.concat(item)).val());
	});
	params = JSON.stringify(ids);
	var received = JSON.stringify(receiveds);
	api = 'api/seller_reception/sales/aprove/'
		.concat(params).concat('/')
		.concat(received).concat('/')
		.concat(getSelectObject('sellers').getValue());
	if (confirm('¿Seguro que deseas aprobar estas transacciones? El total debe coincidir con la cantidad de efectivo recibido')) {
		$(".preloader").fadeIn();
		request (api, 'PUT', null,
			function (res) {
		    	getview('seller_reception/sales/'.concat(getSelectObject('sellers').getValue()), 'sales_table_area');
				alert(res.msg);
			},
			function (res) {
				$(".preloader").fadeOut();
				alert(res.msg);
				console.log('aprove(error)', res);
			}
		);
	}
}
function validateAmounts (checkboxes) {
	var flag = true;
	for (i = 0; i < checkboxes.length; i++) {
		var item = checkboxes.get(i);
		var id = item.id.split('_')[1];
		if (!($('#amount_'.concat(id)).val() == $('#received_'.concat(id)).val())) {
			flag = false;
		}
	}
	return flag;
}
function getIdsFromCheckboxes (checkboxes) {
	var ids = new Array();
	for (i = 0; i < checkboxes.length; i++) {
		var item = checkboxes.get(i);
		var id = item.id.split('_')[1];
		if ($('#amount_'.concat(id)).val() == $('#received_'.concat(id)).val()) {
			ids.push(id);
		}
	}
	return ids;
}

$('input:checkbox').change(function() {
	var id = this.id.split('_')[1];
	var receivedField = $('#received_'.concat(id));
	receivedField.prop('disabled', !this.checked);
	var amountSetted = $('#amount_'.concat(id));
	receivedField.val(amountSetted.val());
});

$(document).ready(function () {
    $(".preloader").fadeOut();
    /*
	if ( ! $.fn.DataTable.isDataTable('#myTable') ) {
		$('#myTable').DataTable({
			"columnDefs": [{
				"targets": 0,
				"orderable": false
			},{
				"targets": 7,
				"orderable": false
			}],
	        "language": {"url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"}
	    });
	}
    */
});