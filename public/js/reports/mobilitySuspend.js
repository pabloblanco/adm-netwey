$(document).ready(function () {

    $('#clients-table').DataTable({
        /*"language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        },*/
        processing: true,
        serverSide: true,
        deferRender: true,
        ajax: {
            url: 'view/reports/mobility/dt',
            data: function (d) {
                d._token = $('meta[name="csrf-token"]').attr('content');
            },
            type: "POST"
        },
        initComplete: function(settings, json) {
            $(".preloader").fadeOut();
        },
        columns: [
            { data: 'name'},
            { data: 'phone_home'},
            { data: 'msisdn'},
            { data: 'lat',searchable: false},
            { data: 'lng',searchable: false},
            { data: 'date',searchable: false}
        ]
    });

    $('#exportCsv').on('click', function(e){
        $(".preloader").fadeIn();

        $.ajax({
            type: "POST",
            url: 'view/reports/mobility/dt_download',
            data: {_token: $('meta[name="csrf-token"]').attr('content')},
            dataType: "text",
            success: function(response) {
                $(".preloader").fadeOut();
                var uri = 'data:application/csv;charset=UTF-8,' + encodeURIComponent(response);

                var link = document.createElement('a');
                link.href = uri;

                if(link.download !== undefined) {
                    link.download = "clientes_suspendidos.csv";
                }

                if(document.createEvent) {
                    var e = document.createEvent('MouseEvents');
                    e.initEvent('click', true, true);
                    link.dispatchEvent(e);
                    return true;
                }

                
                window.open(uri, 'clientes_suspendidos.csv');
            },
            error: function(err){
                $(".preloader").fadeOut();
            }
        });
    });
});