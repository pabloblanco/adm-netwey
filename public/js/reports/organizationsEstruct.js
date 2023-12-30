var arrOrgs = [];

$(document).ready(function () {
	$(".preloader").fadeOut();
    $('#org').selectize();

    $('#exportCsv').on('click', function(e){
        e.preventDefault();

        if(arrOrgs.length > 0){
            $.ajax({
                url: "view/organization_users/download",
                type: 'POST',
                data:{
                    org : arrOrgs,
                    _token : $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'text',
                success: function(result) {
                    var uri = 'data:application/csv;charset=UTF-8,' + encodeURIComponent(result);
                    var download = document.getElementById('downloadfile');
                    download.setAttribute('href', uri);
                    download.setAttribute('download', 'usuarios_organizacion.csv');
                    download.click();
                }
            });
        }
    });
});

function getReport () {
    arrOrgs = getSelectObject('org').getValue();
    if(arrOrgs.length > 0){
        if ($.fn.DataTable.isDataTable('#users-table')){
            $('#users-table').DataTable().destroy();
        }

        $('#users-table').DataTable({
            "columnDefs": [{
                "targets": 0,
                "orderable": false
            }],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
            },
            processing: true,
            serverSide: true,
            ajax: {
                url: 'view/organization_users/detail',
                data: function (d) {
                    d.org = arrOrgs;
                    d._token = $('meta[name="csrf-token"]').attr('content');
                },
                type: "POST"
            },
            columns: [
                { data: 'pos'},
                { data: 'org'},
                { data: 'name'},
                { data: 'phone'},
                { data: 'position'},
                { data: 'profile'}
            ]
        });

        $('#report_container').show();
    }else{
        alert('Debe seleccionar al menos una organización');
    }
    
}