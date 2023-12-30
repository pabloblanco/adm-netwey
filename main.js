
$(document).ready(function () {
    $(".preloader").fadeOut();
    $('#myTable').DataTable();
    $('#article').selectize({
        valueField: 'id',
        delimiter: ';',
        maxItems: null
    });
    // Order by the grouping
    $('#example tbody').on('click', 'tr.group', function() {
        var currentOrder = table.order()[0];
        if (currentOrder[0] === 2 && currentOrder[1] === 'asc') {
            table.order([2, 'desc']).draw();
        } else {
            table.order([2, 'asc']).draw();
        }
    });
    
});

function deletepack (pack_id, email) {
    del('api/seller_inventories/'+email+'/'+pack_id,
        pack_id,
        function (res) {
            console.log('success: '.concat(res));
            if (res) {
                alert('fue eliminado satisfactoriamente el registro: '.concat(pack_id));
                getview('sellerpack/'+email);
            } else {
                alert('error al eliminar el registro: '.concat(pack_id));
            }
        },
        function (res) {
            alert('error al eliminar el registro: '.concat(pack_id));
        });
}

function save () {
    var l = '';
    var i = 0;
    $('#article option:selected').each(function(){
        i = i + 1;
        if (i > 1) {
            l = l.concat(';');
        }
        l = l.concat($(this).val());
    });
    console.log(l);
    $('#article_list').val(l);
    sav ('#sellerinv_form', function (res) {
        getview('sellerpack/' + $('#user_email').val());
    },
    function (res) {
    });
}
function update (object) {
    setModal(JSON.parse(object));
    $('#open_modal_btn').click();
}

function setModal(object) {
    if (object != null) {
        $('h4.modal-title').text('Editar datos: '.concat(object.title));
        $('#pack_id').val(object.id);
        $('#status').val(object.status);
        $('#article_list').val('');
        if (object.products.length > 0) {
            var arr = [];
            for(var index = 0; index < object.products.length; index++) {
                var product = object.products[index];
                arr.push(product.detail_product.id);
                if (index == 0) {
                    $('#article_list').val(product.detail_product.id);
                } else {
                    $('#article_list').val($('#article_list').val().concat(';'.concat(product.detail_product.id)));
                }
            }
            console.log(arr);
            setSelect('article', arr);
        }
        //acomodar el back para guardar el update
        $('#sellerinv_form').attr('action', 'api/seller_inventories');
        $('#sellerinv_form').attr('method', 'PUT');
    } else {
        //acomodar para el crear
        $('h4.modal-title').text('Asignar paquete');
        $('#pack_id').val('');
        setSelect('article', null);
        $('#status').val('A');
        $('#sellerinv_form').attr('action', 'api/seller_inventories/store');
        $('#sellerinv_form').attr('method', 'POST');
    }
}