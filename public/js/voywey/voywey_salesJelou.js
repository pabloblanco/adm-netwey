/*
Autor: Ing. LuisJ 
Marzo 2021
 */
function searchSalesJeluoVoywey() {
    $('.preloader').show();
    // $.fn.dataTable.ext.errMode = 'throw';
    if ($.fn.DataTable.isDataTable('#list-com')) {
        $('#list-com').DataTable().destroy();
    }
    $('#list-com').DataTable({
        searching: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: 'voywey/dt_voywey_SalesJeluo',
            data: function(d) {
                d._token = $('meta[name="csrf-token"]').attr('content');
                d.dateStar = $('#dateStar').val();
                d.dateEnd = $('#dateEnd').val();
                d.status = $('#status').val();
            },
            type: "POST"
        },
        initComplete: function(settings, json) {
            $(".preloader").fadeOut();
            $('#rep-sc').attr('hidden', null);
        },
        "order": [
            [3, "asc"]
        ],
        deferRender: true,
        ordering: true,
        columns: [{
            data: 'Orden',
            searchable: true,
            orderable: true
        }, {
            data: 'OrderVoy',
            searchable: true,
            orderable: false
        }, {
            data: 'status',
            searchable: false,
            orderable: false
        }, {
            data: 'Fecha',
            searchable: false,
            orderable: true
        }, {
            data: 'Dias_en_Activar',
            searchable: false,
            orderable: true
        }, {
            data: 'Fecha_Activacion',
            searchable: false,
            orderable: false
        }, {
            data: 'Monto',
            searchable: false,
            orderable: true
        }, {
            data: 'Codigo',
            searchable: true,
            orderable: true
        }, {
            data: 'FormaPago',
            searchable: true,
            orderable: true
        }, {
            data: 'UserMail',
            searchable: true,
            orderable: false
        }, {
            data: 'UserName',
            searchable: true,
            orderable: true
        }, {
            data: 'Userlastname',
            searchable: true,
            orderable: true
        }, {
            data: 'Userphone',
            searchable: true,
            orderable: false
        }, {
            data: 'Repartidorine',
            searchable: true,
            orderable: true
        }, {
            data: 'Repartidor_name',
            searchable: true,
            orderable: true
        }, {
            data: 'Repartidor_lastname',
            searchable: true,
            orderable: true
        }, {
            data: 'Repartidor_mail',
            searchable: true,
            orderable: true
        }, {
            data: 'Repartidor_phone',
            searchable: true,
            orderable: true
        }, {
            data: 'DNI',
            searchable: true,
            orderable: false
        }, {
            data: 'ClientName',
            searchable: true,
            orderable: true
        }, {
            data: 'ClientLastName',
            searchable: true,
            orderable: true
        }, {
            data: 'ClienteMail',
            searchable: true,
            orderable: true
        }, {
            data: 'MSISDN',
            searchable: true,
            orderable: true
        }, {
            data: 'Modelo',
            searchable: true,
            orderable: true
        }, {
            data: 'Full_plan',
            searchable: false,
            orderable: true
        }],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        }
    });
}
$(document).ready(function() {
    /**
     * filtrar reporte
     */
    $('#search').on('click', function(e) {
        searchSalesJeluoVoywey();
    });
    $('#download').on('click', function() {
        $(".preloader").fadeIn();
        var data = $("#report_tb_form").serialize();
        $.ajax({
            type: "POST",
            url: 'voywey/download_dt_voywey_SalesJeluo',
            data: {
                data,
                _token: $('meta[name="csrf-token"]').attr('content'),
                dateStar: $('#dateStar').val(),
                dateEnd: $('#dateEnd').val(),
                status: $('#status').val()
            },
            dataType: "json",
            success: function(response) {
                $(".preloader").fadeOut();
                swal('Generando reporte', 'El reporte estara disponible en unos minutos.', 'success');
            },
            error: function(err) {
                console.log("error al crear el reporte: ", err);
                $(".preloader").fadeOut();
            }
        });
    });
});