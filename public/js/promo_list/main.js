var fv;

function save() {
    let rules = {
        name: {
            required: true
        },
        lifetime: {
            required: true,
            min: 1
        },
        status: {
            required: true
        }
    };

    let messages = {
        name: "Por Favor ingrese el Nombre.",
        status: " Por Favor especifique el Estado.",
        lifetime: " Por Favor ingrese una Duración valida.",
    }
    if (fv) {
        fv.destroy();
    }
    fv = $('#promo_list_form').validate({
        ignore: ':hidden:not([class~=selectized]),:hidden > .selectized, .selectize-control .selectize-input input',
        rules: rules,
        messages: messages
    });
    if ($('#promo_list_form').valid()) {
        sav('#promo_list_form', function(res) {
            if (res.success) {
                getview('promo_list');
                alert(res.msg);
            } else {
                $(".preloader").fadeOut();
                alert(res.msg);
            }
        }, function(res) {
            alert(res.msg);
        });
    } else {
        $('#promo_list_form').submit(function(e) {
            e.preventDefault();
        });
    }
}


function update(object) {
    setModal(JSON.parse(object));
    $("#myModal").modal("show");
}

function setModal(object) {
    $("label.error").hide();
    $('#status').val('');
    if (object != null) {
        $('h4.modal-title').text('Editar datos: '.concat(object.title));
        $('#id').val(object.id);
        $('#name').val(object.name);
        $('#lifetime').val(object.lifetime);
        $('#status').val(object.status);

        $('#promo_list_form').attr('action', 'api/promo_list/'.concat(object.id));
        $('#promo_list_form').attr('method', 'PUT');
    } else {
        $('h4.modal-title').text('Crear Lista de Descuentos');
        $('#name').val('');
        $('#lifetime').val('');
        $('#status').val('');
    }
}

$('#myModal').on('hidden.bs.modal', function() {
    setModal(null);
});

$(document).ready(function() {
    $(".preloader").fadeOut();
    if (!$.fn.DataTable.isDataTable('#myTable')) {
        $('#myTable').DataTable({
            language: {
                sProcessing: "Procesando...",
                sLengthMenu: "Mostrar _MENU_ registros",
                sZeroRecords: "No se encontraron resultados",
                sEmptyTable: "Ningún dato disponible en esta tabla",
                sInfo: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
                sInfoFiltered: "(filtrado de un total de _MAX_ registros)",
                sInfoPostFix: "",
                sSearch: "Buscar:",
                sUrl: "",
                sInfoThousands: ",",
                sLoadingRecords: "Cargando...",
                oPaginate: {
                    sFirst: "Primero",
                    sLast: "Último",
                    sNext: "Siguiente",
                    sPrevious: "Anterior"
                },
                oAria: {
                    sSortAscending: ": Activar para ordenar la columna de manera ascendente",
                    sSortDescending: ": Activar para ordenar la columna de manera descendente"
                }
            },
            order: false,
        });
    }
    $("#open_modal_btn").on('click',()=>{ $("#myModal").modal(); });
});