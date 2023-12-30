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

$('#detail').on('hidden.bs.modal', function () {
	$('#modal_report_container').html('');
});

function detail(email){
	$(".preloader").fadeIn();
	requestView ('reports/users/status/' + email,
		'GET',
		function (res) {
			$('#modal_report_container').html(res.msg);
        	//$('#open_detail_btn').click();
        	$('#detail').modal({backdrop: 'static', keyboard: false});
			$(".preloader").fadeOut();
		},
		function (res) {
			$(".preloader").fadeOut();
		});
}