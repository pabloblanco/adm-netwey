function aprove() {
	var checkboxes = $('input:checkbox:checked');
	if (!((checkboxes.length == 0))) {
		var ids = new Array();
		for (i = 0; i < checkboxes.length; i++) {
			var item = checkboxes.get(i);
			var id = item.id.split('_')[1];
			ids.push(id);
		}
		var api = 'api/seller/comission/consolidate/'
			.concat(JSON.stringify(ids)).concat('/')
			.concat(getSelectObject('sellers').getValue());
		if (confirm('¿Seguro que deseas consolidar estas transacciones?')) {
			$(".preloader").fadeIn();
			request (api, 'PUT', null,
				function (res) {
			    	getview('seller/comission/'.concat(getSelectObject('sellers').getValue()), 'sales_table_area');
					alert(res.msg);
				},
				function (res) {
					$(".preloader").fadeOut();
					alert(res.msg);
					console.log('aprove(error)', res);
				}
			);
		}
	} else {
		alert('No ha seleccionado ninguna transacciones para consolidar');
	}
}

$('input:checkbox').change(function() {
});

$(document).ready(function () {
    $(".preloader").fadeOut();
});