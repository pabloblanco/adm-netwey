$(document).ready(function () {

    const columns = (dtclient != null && dtclient.dn_type == 'F') ? 
        [
            {data: 'unique_transaction'},
            {data: 'date_reg'},
            {data: 'user_name'},
            {data: 'service'},
            {data: 'service_desc'},
            {data: 'client'},
            {data: 'msisdn'},
            {data: 'client_phone'},
            {data: 'amount'},
            {data: 'concentrator'},
            {data: 'conciliation'},
            {data: 'date_init815'},
            {data: 'date_end815'}
        ] :
        [
            {data: 'unique_transaction'},
            {data: 'date_reg'},
            {data: 'user_name'},
            {data: 'service'},
            {data: 'client'},
            {data: 'msisdn'},
            {data: 'client_phone'},
            {data: 'amount'},
            {data: 'concentrator'},
            {data: 'conciliation'}
        ];

    $('#rechargerTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        },
        processing: true,
        serverSide: true,
        ajax: 'api/client/datatable/' + msisdn,
        order: [[ 1, "desc" ]],
        columns: columns
    });

});
function downloadcsv(){
    $("#rechargerTablecsv").tableToCSV();
}