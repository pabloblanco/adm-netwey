/*
Autor: Ing. LuisJ 
Contact: luis@gdalab.com
Junio 2021
 */

$(document).ready(function () {
    var format = {autoclose: true, format: 'dd-mm-yyyy'};
    $('#dateStar').datepicker(format);
    $('#dateEnd').datepicker(format);

    /*$('#dateStar').datepicker(format)
        .on('changeDate', function(selected){
            var dt = $('#dateEnd').val();
            if(dt == ''){
                $('#dateEnd').datepicker('setDate', sumDays($('#dateStar').datepicker('getDate'),90));
            }else{
                var diff = getDateDiff($('#dateStar').datepicker('getDate'), $('#dateEnd').datepicker('getDate'));
                if(diff > 90)
                    $('#dateEnd').datepicker('setDate', sumDays($('#dateStar').datepicker('getDate'), 90));
            }
        });

    $('#dateEnd').datepicker(format)
        .on('changeDate', function(selected){
            var dt = $('#dateStar').val();
            if(dt == ''){
                $('#dateStar').datepicker('update', sumDays($('#dateEnd').datepicker('getDate'), -90));
            }else{
                var diff = getDateDiff($('#dateStar').datepicker('getDate'), selected.date);
                if(diff > 90)
                    $('#dateStar').datepicker('update', sumDays($('#dateEnd').datepicker('getDate'), -90));
            }
    });*/
    /**
     * crear reporte
     */
    $('#search').on('click', function(e) {

        $('.preloader').show();
        if ($.fn.DataTable.isDataTable('#list-com')) {
            $('#list-com').DataTable().destroy();
        }

        $('#list-com').DataTable({
            searching: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "reports/get_dt_super_sim",
                data: function(d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.dateStar = $('#dateStar').val();
                    d.dateEnd = $('#dateEnd').val();
                    d.DN = $('#DN').val();
                },
                type: "POST"
            },
            initComplete: function(settings, json) {
                $(".preloader").fadeOut();
                $('#rep-sc').attr('hidden', null);
            },
            "order": [
                [8, "desc"], /*Fecha*/
                [0, "asc"],   /*DN*/
                [7, "asc"]    /*numero de recarga*/
            ],
            deferRender: true,
            ordering: true,
            columns: [
                {
                    data: 'msisdn',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'nameClient',
                    searchable: true,
                    orderable: false
                },
                {
                    data: 'mailClient',
                    searchable: true,
                    orderable: false
                },
                {
                    data: 'nameVendedor',
                    searchable: true,
                    orderable: false
                },
                {
                    data: 'mailvendedor',
                    searchable: true,
                    orderable: false
                },
                {
                    data: 'amount',
                    searchable: false,
                    orderable: true
                },
                {
                    data: 'servicio',
                    searchable: true,
                    orderable: false
                },
                {
                    data: 'rownum_sales',
                    searchable: false,
                    orderable: true
                },
                {
                    data: 'date_reg',
                    searchable: false,
                    orderable: true
                },
                {
                    data: 'id',
                    searchable: false,
                    orderable: true
                }
            ],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
            }
        });
    });

    $('#download').on('click', function() {
        $(".preloader").fadeIn();
        var data = $("#report_tb_form").serialize();
        $.ajax({
            type: "POST",
            url: 'reports/download_dt_super_sim',
            data: {
                data,
                _token: $('meta[name="csrf-token"]').attr('content'),
                dateStar: $('#dateStar').val(),
                dateEnd: $('#dateEnd').val(),
                DN: $('#DN').val()
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