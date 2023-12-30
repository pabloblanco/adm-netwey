$(document).ready(function () {
	$('#myTable').DataTable();
});
function downloadcsv(){
    $("#reportTable").tableToCSV();
}
function setModal(object) {
    if (object != null) {
        $('#id').text(object.id);
        $('#user').text(object.users_email);
        $('#banco').text(object.name+' n°: '+object.numAcount);
        $('#amount').text(object.amount);
        $('#description').text(object.description);
        $('#dateDep').text(object.date_deposit);
        $('#dateAssig').text(object.date_asigned);
        $('#status').text(object.deposit_status);
    }
}
function detail (object) {
    setModal(object);
    //$('#open_modal_btn').click();
    $('#myModal').modal({backdrop: 'static', keyboard: false});
}