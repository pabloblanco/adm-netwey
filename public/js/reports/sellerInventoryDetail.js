function prepareModal (inventory) {
	if ($.fn.DataTable.isDataTable( '#myTableDetail' ) ) {
		$('#myTableDetail').DataTable().destroy();
	}
	$('#inventory_detail').html('');
	inventory = JSON.parse(inventory);
	inventory.forEach(function (item, key) {
		var html = '<td>'.concat(item.article).concat('</td>');
		html = html.concat('<td>'.concat(item.msisdn).concat('</td>'));
		html = html.concat('<td>'.concat(item.imei).concat('</td>'));
		html = html.concat('<td>'.concat(((item.iccid==null) ? 'N/A' : item.iccid)).concat('</td>'));
        html = html.concat('<td>'.concat(item.birth_modem).concat('</td>'));
        html = html.concat('<td>'.concat(item.first_assignment).concat('</td>'));
        html = html.concat('<td>'.concat(item.date_reg).concat('</td>'));
		$('#inventory_detail').html($('#inventory_detail').html().concat('<tr>'.concat(html).concat('</tr>')));
	});
	$('#myTableDetail').DataTable({
		"language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        }});
	//$('#open_modal_btn').click();
    $('#myModal').modal({backdrop: 'static', keyboard: false});
}
$(document).ready(function () {
	$('#myTable').DataTable({
        "columnDefs": [{
            "targets": 5,
            "orderable": false
        }],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        },
        processing: true,
        serverSide: true,
        ajax: 'api/datatable/seller_inventories/'+user+'/'+product+'/'+org,
        columns: [
            {data: 'user_name'},
            {data: 'user_email'},
            {data: 'coordinador'},
            {data: 'pro_id'},
            {data: 'pro_name'},
            {data: 'count'},
            {data: null, render: function(data,type,row,meta){
            	var html;
            	html ='<button type="button" class="btn btn-warning btn-md button" onclick="prepareModal(\''+row.inv+'\')">Ver detalles</button>'
                return html;
            }}
        ]
    });
});
function downloadcsv(){
	//$("#reportTable").tableToCSV();
    $(".preloader").fadeIn();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'view/reports/seller/inventories/download-report',
        type: 'post',
        data: {user: user, product: product, org: org},
        success: function(res){
            $(".preloader").fadeOut();

            if(res.success){
                var a = document.createElement("a");
                    a.target = "_blank";
                    a.href = res.url;
                    a.click();
            }
        },
        error: function (res){
            console.log(res);
            $(".preloader").fadeOut();
            alert('Ocurrio un error, no se pudo descargar el reporte.');
        }
    });
}