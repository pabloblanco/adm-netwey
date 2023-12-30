$(document).ready(function () {
	$(".preloader").fadeOut();
	if ( ! $.fn.DataTable.isDataTable( '#myTableInventory' ) ) {
		$('#myTableInventory').DataTable({
	        "language": {
	            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
	        }
	    });
	}
	if ( ! $.fn.DataTable.isDataTable( '#myTablecash' ) ) {
		$('#myTablecash').DataTable({
	        "language": {
	            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
	        }
	    });
	}
});
function downloadInventorycsv(){
	$("#reportTableInventory").tableToCSV();
}
function downloadCashcsv(){
	$("#reportTableCash").tableToCSV();
}