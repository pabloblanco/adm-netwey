$(document).ready(function() {
    $('#blimTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        },
        processing: true,
        serverSide: true,
        ajax: 'api/client/datatable-blim/' + msisdn,
        order: [
            [1, "desc"]
        ],
        columns: [{
            data: 'pin'
        }, {
            data: 'date_reg'
        }, {
            data: 'redeemed'
        }, ]
    });
});
/*function downloadcsv(){
    $("#blimTablecsv").tableToCSV();
}*/