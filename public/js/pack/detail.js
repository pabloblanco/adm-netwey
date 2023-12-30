function setDefaultValues () {
    $('#method_pay').val('');
    $('#service_id').val('');
    $('#inv_article_id').val('');
    $('#price_pack').val(0);
    $('#price_serv').val(0);
    $('#total_price').val(0);
    $("#service_id_container").hide();
    $("#service_id option").show();
    $("#submit").hide();
}

function associateService (id) {
    if ($('#pack_service_form').valid()) {
        sav ('#pack_service_form', function (res) {
            alert(res.msg);
            getview('pack/detail/'.concat(id));
        },
        function (res) {
            alert(res.msg);
            console.log('error: '.concat(res.errorMsg));
        });
    } else {
        $('#pack_service_form').submit(function (e) {
            e.preventDefault();
        });
    }
}

function associateProduct (id) {
    if ($('#pack_product_form').valid()) {
        sav ('#pack_product_form', function (res) {
            alert(res.msg);
            getview('pack/detail/'.concat(id));
        },
        function (res) {
            alert(res.msg);
            console.log('error: '.concat(res.errorMsg));
        });
    } else {
        $('#pack_product_form').submit(function (e) {
            e.preventDefault();
        });
    }
}

function deassociateService (id, service) {
    if (confirm('¿desea eliminar el registro?')) {
        request ('api/pack/service/associated/'.concat(id).concat('/').concat(service), 'DELETE', null,
            function (res) {
                getview('pack/detail/'.concat(id));
                alert(res.msg);
                if (!res.success) {
                    console.log('succes(error)', res.errorMsg);
                }
            },
            function (res) {
                console.log('error: '.concat(res.errorMsg));
                alert(res.msg);
            });
    }
}

function deassociateProduct (id, product) {
    if (confirm('¿desea eliminar el registro?')) {
        request ('api/pack/product/associated/'.concat(id).concat('/').concat(product), 'DELETE', null,
            function (res) {
                getview('pack/detail/'.concat(id));
                alert(res.msg);
                if (!res.success) {
                    console.log('succes(error)', res.errorMsg);
                }
            },
            function (res) {
                console.log('error: '.concat(res.errorMsg));
                alert(res.msg);
            });
    }
}

$(document).ready(function () {


    function selectServicesInit(){
        if($('#pack_service').val() === ''){
            params={
                pack_id:$('#pack_id').val(),
                product_id: ($('#pack_product').val() !== '') ? $('#pack_product').val() :
$('#inv_article_id').val()
            };
            $(".preloader").fadeIn();
            request ('api/pack/get-services-fiber-zone-pack', 'POST', params,
            function (res) {
                if(res.success){
                    valact = $('#service_id').val();
                    $('#service_id').empty();
                    $('#service_id').append($('<option>', {value: '', text: 'Seleccione un servicio...'}));
                    res.services.forEach(function(service){
                        $('#service_id').append($('<option>', {price: service.price_pay,value: service.id, text: service.title}));
                    });

                    if($('#service_id option[value="'+valact+'"]').length){
                        $('#service_id').val(valact);
                    }

                    $(".preloader").fadeOut();
                }
                if (!res.success) {
                    console.log('succes(error)', res.errorMsg);
                    $(".preloader").fadeOut();
                }
            },
            function (res) {
                console.log('error: '.concat(res.errorMsg));
                alert(res.msg);
            });
        }

    }
    function selectProductsInit(){
        if($('#pack_product').val() === ''){
            params={
                pack_id:$('#pack_id').val(),
                services_id:($('#pack_service').val() !== '') ? $('#pack_service').val() :
$('#service_id').val()
            };
            $(".preloader").fadeIn();
            request ('api/pack/get-products-fiber-zone-pack', 'POST', params,
            function (res) {
                if(res.success){
                    valact = $('#inv_article_id').val();
                    $('#inv_article_id').empty();
                    $('#inv_article_id').append($('<option>', {value: '', text: 'Seleccione un servicio...'}));
                    res.products.forEach(function(product){
                        $('#inv_article_id').append($('<option>', {value: product.id, text: product.title}));
                    });

                    if($('#inv_article_id option[value="'+valact+'"]').length){
                        $('#inv_article_id').val(valact);
                    }

                    $(".preloader").fadeOut();
                }
                if (!res.success) {
                    $(".preloader").fadeOut();
                    console.log('succes(error)', res.errorMsg);
                }
            },
            function (res) {
                console.log('error: '.concat(res.errorMsg));
                alert(res.msg);
            });
        }
    }
    if($('#pack_type').val() == 'F'){
        selectServicesInit()
        selectProductsInit()
    }

    $(".preloader").fadeOut();
    $('#pack_service_form').validate({
        rules: {
            method_pay: {
                required: true
            },
            service_id: {
                required: true
            },
            price_pack: {
                required: true
            },
            price_serv: {
                required: true
            },
            total_price: {
                required: true
            }
        },
        messages: {
            method_pay: "Debe seleccionar un método de pago",
            service_id: "Debe seleccionar un servicio",
            price_pack: "Debe indicar el precio de la Cuota inicial",
            price_serv: "Debe indicar el precio del Servicio",
            total_price: "Debe indicar el Precio total del Paquete"
        }
    });
    $('#pack_product_form').validate({
        rules: {
            inv_article_id: {
                required: true
            }
        },
        messages: {
            inv_article_id: "Debe seleccionar un producto"
        }
    });

    setDefaultValues();

    $('#method_pay').on('change', function () {
        $("#service_id").val('');
        $('#price_pack').val(0);
        $('#price_serv').val(0);
        $('#total_price').val(0);
        $("#service_id_container").hide();
        if ($(this).val() != '') {
            $("#service_id_container").show();
            $("#service_id option").show();
            if($(this).val() == 'CO')
                $('#finan_detail_container').attr('hidden', true);
            else{
                $('#mof').text($(this).find(':selected').data('f'));
                $('#ta').text($(this).find(':selected').data('tam'));
                $('#wf').text($(this).find(':selected').data('wf'));
                $('#wq').text($(this).find(':selected').data('wq'));
                $('#mf').text($(this).find(':selected').data('mf'));
                $('#finan_detail_container').attr('hidden', null);
            }
        }
    });

    $('#service_id').on('change', function () {
        if ($(this).val() != '') {
            var price = $('#service_id option[value='.concat($(this).val()).concat(']')).attr("price");
            $('#price_serv').val(price);
            $("#submit").show();
        } else {
            $("#submit").hide();
        }

        if($('#pack_type').val() == 'F'){
            selectProductsInit()
        }
    });

    $('#inv_article_id').on('change', function () {
        if($('#pack_type').val() == 'F'){
            selectServicesInit()
        }
    });

    if ($('#method_pay option').length == 1) {
        $('#method_pay').val($('#method_pay option').val());
        $("#method_pay").change();
    }

    $('.price').on('blur', function(e){
        var pack = $('#price_pack').val(),
            service = $('#price_serv').val();

        if(pack && service && pack.trim() != '' && service.trim() != '' && !isNaN(parseInt(pack)) && !isNaN(parseInt(service))){
            $('#total_price').val(parseInt(pack) + parseInt(service))
        }
    });
});