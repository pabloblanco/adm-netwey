function prepareModal (inventory) {
	if ($.fn.DataTable.isDataTable( '#myTableDetail' ) ) {
		$('#myTableDetail').DataTable().destroy();
	}
	$('#inventory_detail').html('')
	inventory = JSON.parse(inventory);
	inventory.forEach(function (item, key) {
		var html= '';
		html = html.concat('<td>'.concat(item.msisdn).concat('</td>'));
		html = html.concat('<td>'.concat(item.imei).concat('</td>'));
		html = html.concat('<td>'.concat(((item.iccid==null) ? 'N/A' : item.iccid)).concat('</td>'));
		html = html.concat('<td>'.concat(item.date_reg).concat('</td>'));
		$('#inventory_detail').html($('#inventory_detail').html().concat('<tr>'.concat(html).concat('</tr>')));
	});
	$('#myTableDetail').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        }
	});
	// $('#open_modal_btn').click();
	$('#myModal').modal({backdrop: 'static', keyboard: false});
}
$(document).ready(function () {
	$('#myTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        }
    });
});
function downloadcsv(){
	$("#reportTable").tableToCSV();
}