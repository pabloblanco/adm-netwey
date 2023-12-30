$(document).ready(function () {
	$('#retentionTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        },
        processing: true,
        serverSide: true,
        ajax: 'api/client/datatable-retention-services/'+msisdn,
        order: [[ 5, "desc" ]],
        columns: [
            {data: 'service'},
            {data: 'user_creator'},
            {data: 'user_autorization'},
            {data: 'reason'},
            {data: 'sub_reason'},
            {data: 'date_reg'},
        ]
    });
});
// function downloadcsv(){
// 	$("#blimTablecsv").tableToCSV();
// }