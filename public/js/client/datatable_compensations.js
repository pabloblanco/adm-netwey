$(document).ready(function () {
	$('#compensationsTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        },
        processing: true,
        serverSide: true,
        ajax: 'api/client/datatable-compensations/'+msisdn,
        order: [[ 0, "desc" ]],
        columns: [
            {data: 'date_bonus'},
            {data: 'name_offer'},            
            {data: 'ajuste_mb'},
            {data: 'date_expire'},
            {data: 'incident_id'},
            {data: 'incident_date'},
            {data: 'inc_hours'},
            {data: 'result'}
        ]
    });
});
function downloadcsv(){
	$("#compensationsTablecsv").tableToCSV();
}