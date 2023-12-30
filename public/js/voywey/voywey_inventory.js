/*
Autor: Ing. LuisJ 
Marzo 2021
 */
function searchInventoryVoywey() {
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
            url: 'voywey/dt_voywey_inventory',
            data: function(d) {
                d._token = $('meta[name="csrf-token"]').attr('content');
            },
            type: "POST"
        },
        initComplete: function(settings, json) {
            $(".preloader").fadeOut();
            $('#rep-sc').attr('hidden', null);
        },
        deferRender: true,
        ordering: true,
        columns: [{
            data: 'detail',
            searchable: false,
            orderable: false,
            render: function(data, type, row, meta) {
                var html = '';
                if (row.asignados > 0 || row.en_camino > 0) {
                    html = '<button  type="button" class="btn btn-info btn-md button" onclick="viewDetail(\'' + row.id + '\',\'' + row.name + '\',\'' + row.detail + '\')">Ver equipos asignados</button>';
                } else {
                    html = '<button  type="button" class="btn btn-light btn-md button disable">Ver equipos asignados</button>';
                }
                html += '<br>';
                if (row.disp_bodega > 0) {
                    html += '<button  type="button" class="btn btn-danger btn-md button" onclick="viewDetailBodega(\'' + row.id + '\',\'' + row.name + '\',\'' + row.detail_warehouse + '\')">Ver detalles en bodega</button>';
                } else {
                    html += '<button  type="button" class="btn btn-light btn-md button disable">Ver detalles en bodega</button>';
                }
                return html;
            }
        }, {
            data: 'name',
            searchable: true,
            orderable: true
        }, {
            data: 'inv_total',
            searchable: false,
            orderable: true
        }, {
            data: 'disp_bodega',
            searchable: false,
            orderable: true
        }, {
            data: 'asignados',
            searchable: false,
            orderable: true
        }, {
            data: 'en_camino',
            searchable: false,
            orderable: true
        }],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        }
    });
}
$(document).ready(function() {
    searchInventoryVoywey();
    $('#download').on('click', function() {
        $(".preloader").fadeIn();
        var data = $("#report_tb_form").serialize();
        $.ajax({
            type: "POST",
            url: 'voywey/download_dt_voywey_inventory',
            data: {
                data,
                limit: '',
                _token: $('meta[name="csrf-token"]').attr('content')
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
    $('#modal_close_x2').on('click', function() {
        $("#myModal").modal('show'); //ocultamos el modal
    });
});

function viewDetail(id, name, inventory) {
    if ($.fn.DataTable.isDataTable('#myTableDetail')) {
        $('#myTableDetail').DataTable().destroy();
    }
    $('#inventory_detail').html('')
    inventory = JSON.parse(inventory);
    inventory.forEach(function(item, key) {
        var html = '';
        html = html.concat('<td>'.concat('<button  type="button" class="btn btn-info btn-md button" onclick="viewDetailRepartidor(\'' + id + '\',\'' + name + '\',\'' + item.sku + '\',\'' + item.deliveryEmail + '\')">Ver detalles</button>').concat('</td>'));
        html = html.concat('<td>'.concat(item.deliveryName).concat('</td>'));
        html = html.concat('<td>'.concat(item.deliveryLastName).concat('</td>'));
        html = html.concat('<td>'.concat(item.deliveryEmail).concat('</td>'));
        html = html.concat('<td>'.concat(item.sku).concat('</td>'));
        html = html.concat('<td>'.concat(item.nameProduct).concat('</td>'));
        html = html.concat('<td>'.concat(item.count).concat('</td>'));
        $('#inventory_detail').html($('#inventory_detail').html().concat('<tr>'.concat(html).concat('</tr>')));
    });
    $('#myTableDetail').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        }
    });
    // $('#open_modal_btn').click();
    $('#myModal').modal({
        backdrop: 'static',
        keyboard: false
    });
}

function viewDetailRepartidor(id, name, sku, email) {
    $(".preloader").show();
    // $.fn.dataTable.ext.errMode = 'throw';
    if ($.fn.DataTable.isDataTable('#myTableDetailRepartidor')) {
        $('#myTableDetailRepartidor').DataTable().destroy();
    }
    $('#myTableDetailRepartidor').DataTable({
        searching: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: 'voywey/get_detail_inventory',
            data: function(d) {
                d._token = $('meta[name="csrf-token"]').attr('content');
                d.warehouse = id;
                d.sku = sku;
                d.email = email;
            },
            type: "POST"
        },
        initComplete: function(settings, json) {
            $(".preloader").fadeOut();
            $('#rep-sc').attr('hidden', null);
        },
        deferRender: true,
        ordering: true,
        columns: [{
            data: 'dn',
            searchable: false,
            orderable: true
        }, {
            data: 'estatus',
            searchable: false,
            orderable: true
        }],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        }
    });
    $('#myModal2').modal({
        backdrop: 'static',
        keyboard: false
    });
    $("#myModal").modal('hide'); //ocultamos el modal
    $('#emailrepartidor').html(email);
    $('#skurepartidor').html(sku);
    $('#bodegarepartidor').html(name);
}

function viewDetailBodega(id, name, inventory) {
    if ($.fn.DataTable.isDataTable('#myTableDetailBodega')) {
        $('#myTableDetailBodega').DataTable().destroy();
    }
    $('#inventory_detail_bodega').html('')
    inventory = JSON.parse(inventory);
    inventory.forEach(function(item, key) {
        var html = '';
        html = html.concat('<td>'.concat(item.sku).concat('</td>'));
        html = html.concat('<td>'.concat(item.nameProduct).concat('</td>'));
        html = html.concat('<td>'.concat(item.dn).concat('</td>'));
        $('#inventory_detail_bodega').html($('#inventory_detail_bodega').html().concat('<tr>'.concat(html).concat('</tr>')));
    });
    $('#myTableDetailBodega').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        }
    });
    // $('#open_modal_btn').click();
    $('#myModal3').modal({
        backdrop: 'static',
        keyboard: false
    });
    $('#name_bodega').html(name);
}