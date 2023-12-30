var fv;

function save() {
    ban = 1;

    let rules = {
        title: {
            required: true
        },
        description: {
            required: true
        },
        service_type: {
            required: true
        },
        periodicity_id: {
            required: true
        },
        type: {
            required: true
        },
        price_pay: {
            required: true
        },
        "chanels[]": {
            valid_chanels_concs: true
        },
        "conc[]": {
            valid_chanels_concs: true
        }
    };

    let messages = {
        title: "Por Favor especifique el titulo",
        description: "Por Favor ingrese una descripción",
        price_pay: "Por favor especifique el costo",
        service_type: "Por favor seleccione un servicio",
        periodicity_id: "Por favor seleccione una periodicidad",
        type: "Por favor seleccione un tipo de servicio",
    }

    if ($('#service_type').val() != 'F') {
        rules.gb = {
            required: true
        };
        messages.gb = "Por favor ingrese la cantidad de GB del servicio";
    }

    if ($('#service_type').val() == 'T') {
        rules.sms = {
            required: true
        };
        rules.min = {
            required: true
        };
        messages.sms = "Por favor ingrese la cantidad de sms del servicio";
        messages.min = "Por favor ingrese la cantidad de minutos del servicio";
    }

    if ($('#service_type').val() == 'H' && ($('#type').val() == 'A' || $('#type').val() == 'P') || $('#type').val() == 'R') {
        rules.broadband = {
            required: true
        };
        messages.broadband = "Por favor seleccione un broadband";
        rules.codeAltan = {
            required: true
        };
        messages.codeAltan = "Por favor escriba el id de la oferta primaria";
        if ($('#type').val() == 'P' || $('#type').val() == 'R') {
            rules.codeAltanSuplementary = {
                required: true
            };
            messages.codeAltanSuplementary = "Por favor escriba el id de la oferta suplementaria";
        }
    }

    if ((($('#service_type').val() == 'T' || $('#service_type').val() == 'M' || $('#service_type').val() == 'MH') && $('#type').val() == 'A') || ($('#service_type').val() == 'T' && $('#type').val() == 'P')) {
        rules.codeAltan = {
            required: true
        };
        messages.codeAltan = "Por favor escriba el id de la oferta primaria";
    }
    if ((($('#service_type').val() == 'T' || $('#service_type').val() == 'M' || $('#service_type').val() == 'MH') && $('#type').val() == 'P') || ($('#service_type').val() == 'H' && $('#type').val() == 'R')) {
        rules.codeAltanSuplementary = {
            required: true
        };
        messages.codeAltanSuplementary = "Por favor escriba el id de la oferta suplementaria";
    }

    if($('#service_type').val() == 'F'){
        rules.fiber_zone = {
            select_zonFibra: true
        };
        rules.fiber_service = {
            select_servFibra: true
        };
        messages.select_zonFibra = "Por Favor agrege una relacion del Servicio con zona de fibra";
        messages.select_servFibra = "Por Favor agrege una relacion del Servicio con zona de fibra";


        if($('#serv-fiber-zone').val().length>0){
            $("#addServBtn").data('val',1);
        }
        else{
            valzone=$("#fiber_zone").valid();
            valserv=$("#fiber_service").valid();

            if(valzone && valserv){
                alert("Por Favor agrege al menos una relacion de servicio con zona de fibra, para ello seleccione una zona de fibra y su respectivo servicio asociado y haga click en el boton agregar");
                ban = 0;
            }
        }

    }

    if($('#service_type').val()!= 'T'){
        $('#is_band_twenty_eight').val('');
    }

    if (fv) {
        fv.destroy();
    }
    fv = $('#service_form').validate({
        ignore: ':hidden:not([class~=selectized]),:hidden > .selectized, .selectize-control .selectize-input input',
        errorPlacement: function(error, element) {
            if($(element).prop('id') == "chanels" || $(element).prop('id') == "conc"){
                elem=$('#'+$(element).prop('id')+"-selectized").parent().parent();
                error.insertAfter(elem);
            }
            else{
                error.insertAfter(element);
            }
        },
        rules: rules,
        messages: messages
    });
    if ($('#service_form').valid() && ban == 1) {
        sav('#service_form', function(res) {
            if (res.success) {
                getview('services');
                alert(res.msg);
                $("#addServBtn").data('val',0);
            } else {
                $(".preloader").fadeOut();
                alert(res.msg);
                //console.log('error', res.errorMsg);
                $("#addServBtn").data('val',0);
            }
        }, function(res) {
            alert(res.msg);
            //console.log('error', res.errorMsg);
            $("#addServBtn").data('val',0);
        });
    } else {
        $('#service_form').submit(function(e) {
            e.preventDefault();
            $("#addServBtn").data('val',0);
        });
    }
}

function validateACS() {
    if ($('#type').val() == 'A') {
        $('#codeAltanSuplementaryContainer').show();
        $('#codeAltanSuplementary').val('');
        $('#codeAltanSuplementaryContainer').hide();
        return true;
    }
    if ($('#codeAltanSuplementary').val() == '') {
        return false;
    } else {
        return true;
    }
}

function update(object) {
    setModal(JSON.parse(object));
    $("#myModal").modal("show");
}

function deleteData(id, name) {
    if (confirm('¿desea eliminar el servicios: ' + name + '?')) {
        request('api/services/'.concat(id), 'DELETE', null, function(res) {
            if (res.success) {
                getview('services');
                alert(res.msg);
            } else {
                alert(res.msg);
                console.log('error', res.errorMsg);
            }
        }, function(res) {
            alert(res.msg);
            console.log('error', res.errorMsg);
        });
    }
}

function setModal(object) {
    var chanels = $('#chanels').selectize()[0].selectize,
        conc = $('#conc').selectize()[0].selectize,
        lists = $('#lists').selectize()[0].selectize;
    if (object != null) {
        $('#servi_id').val(object.id);
        $('h4.modal-title').text('Editar datos: '.concat(object.title));
        $('#id').val(object.id);
        $('#periodicity_id').val(object.periodicity_id);
        if (object.type != 'A' && object.sup && (((object.service_type == 'T' || object.service_type == 'M' || object.service_type == 'MH') && object.type == 'P') || (object.service_type == 'H' && object.type == 'P') || (object.service_type == 'H' && object.type == 'R'))) {
            if (object.type == 'P' && (object.service_type == 'H' || object.service_type == 'T')) {
                $('#codeAltanSuplementary').val(object.sup);
            } else {
                if ((object.type == 'R' && object.service_type == 'H') || (object.type == 'P' && (object.service_type == 'T' || object.service_type == 'M'  || object.service_type == 'MH'))) {
                    $('#codeAltanSuplementary').val(object.codeAltan);
                } else {
                    $('#codeAltanSuplementary').val('N/A');
                }
            }
        }
        if (object.service_type == 'H' || (object.service_type == 'H' && object.type == 'R')) {
            $('#type option[value="R"]').show();
        }
        if (object.type == 'A' || (object.type == 'P' && (object.service_type == 'H' || object.service_type == 'T'))) {
            $('#codeAltan').val(object.codeAltan);
        }

        if ((object.type == 'A' || object.type == 'P') && object.service_type == 'F'){
            $('#codeAltan-content').hide();
            $('#codeAltanSuplementaryContainer').hide();
        }
        if ((object.type == 'A' || object.type == 'P') && object.service_type != 'F'){
            $("#list_fiber_zones").addClass('d-none');
            $("#fiber_zone").val('');
        }

        if(object.service_type == 'F'){
            $('#gb-container').hide();

            $("#list_fiber_zones").removeClass('d-none');
            $('.preloader').show();
            params={
               service_id:object.id
            };
            request ('api/services/get-service-fiber-service', 'POST', params,
                function (res) {
                    if ( res.success ) {
                        $.each(res.data, function (i, item) {
                            // console.log(item);
                            $('#fiber_zone option[value="'+item.fiber_zone_id+'"]').prop('disabled',true);
                            addServiceCard(item.fiber_zone_id,item.fiber_zone_name,item.service_fz_pk,item.service_fz_name);
                        });
                    }
                    else{
                        alert(res.msg)
                        console.log(res.msg);
                    }
                    $('.preloader').hide();
                },
                function (res) {
                    alert('Hubo un error consultando servicios de fibra en zona');
                    console.log("error");
                    $('.preloader').hide();
                });
            $("#fiber_zone").val('');
        }
        else{
            $('#gb-container').show();
        }


        if(object.service_type == 'T' && !object.sup && object.supplementary=='Y'){
            $('#codeAltan').val('');
            $('#codeAltanSuplementary').val(object.codeAltan);
        }

        $('#title').val(object.title);
        $('#description').val(object.description);
        $('#price_pay').val(object.price_pay);
        $('#price_remaining').val(object.price_remaining);
        if (object.broadband) {
            $('#broadband').val(object.broadband);
        }
        $('#service_type').val(object.service_type);
        if (object.service_type == 'T') {
            $('#broadband-content').hide();
            $('#sms-content').show();
            $('#min-content').show();
            $('#nbte-content').show();
            $('#nbte-content').show();
            $('#is_band_twenty_eight').val(object.is_band_twenty_eight);
            if (object.min) {
                $('#min').val(object.min);
            }
            if (object.sms) {
                $('#sms').val(object.sms);
            }
        } else{
            if(object.service_type == 'M' || object.service_type == 'MH' || object.service_type == 'F'){
                $('#broadband-content').hide();
                $('#sms-content').hide();
                $('#min-content').hide();
                $('#nbte-content').hide();
                $('#is_band_twenty_eight').val('');
            }else {
                $('#broadband-content').show();
            }
        }
        $('#supplementary').val(object.supplementary);
        $('#type').val(object.type);
        $('#type').change();

        $('#status').val(object.status);
        if (object.concentrators) {
            object.concentrators.forEach(function(a) {
                conc.addItem(a.id);
            });
        }
        if (object.channels) {
            object.channels.forEach(function(a) {
                chanels.addItem(a.id);
            });
        }
        if (object.lists) {
            object.lists.forEach(function(a) {
                lists.addItem(a.id);
                $('#listsA').val(a.id);
            });
        }
        if (object.gb) {
            $('#gb').val(object.gb);
        } else {
            $('#gb').val(0);
        }
        if (object.type == 'A') {
            $('#channels-content').hide();
            $('#conc-content').hide();

            $('#listsa-content').show();

            if (object.service_type == 'T' || object.service_type == 'H' || object.service_type == 'M'  || object.service_type == 'MH') {
                $('#codeAltan-content').show();
                $('#codeAltanSuplementaryContainer').hide();
            } else {
                $('#codeAltan-content').hide();
            }
        } else {
            $('#plan_type').val(object.plan_type);
            if (object.plan_type == 'G') {
                $('#channels-content').show();
                $('#conc-content').show();
                $('#service-list').hide();
            } else {
                $('#channels-content').hide();
                $('#conc-content').hide();
                $('#service-list').show();
            }
            if (object.service_type == 'H') {
                if (object.type == 'R' || object.type == 'P') {
                    $('#codeAltanSuplementaryContainer').show();
                    if (object.type == 'P') {
                        $('#codeAltan-content').show();
                    } else {
                        $('#codeAltan-content').hide();
                    }
                }
            } else {
                if (object.service_type == 'T' || object.service_type == 'M' || object.service_type == 'MH') {
                    $('#content-especial-service').show();
                    if (object.type == 'P') {
                        $('#codeAltan-content').hide();
                        $('#codeAltanSuplementaryContainer').show();
                    } else {
                        if (object.type == 'A') {
                            $('#codeAltan-content').show();
                            $('#codeAltanSuplementaryContainer').hide();
                        }
                    }
                }
            }

            if (object.service_type == 'T'){
                if (object.type == 'P'){
                    $('#codeAltanSuplementaryContainer').show();
                    $('#codeAltan-content').show();
                }
                else{
                    $('#codeAltanSuplementaryContainer').hide();
                    $('#codeAltan-content').show();
                }
            }
        }

        if (object.type == 'R') {
            $('#content-especial-service').hide();
            $('#content-blim-service').hide();
        }
        else{
            $('#content-blim-service').show();
            $('#blim_service').val(object.blim_service);
        }

        $('#service_form').attr('action', 'api/services/'.concat(object.id));
        $('#service_form').attr('method', 'PUT');
    } else {
        $('#servi_id').val('0');
        $('h4.modal-title').text('Crear servicio');
        $('#id').val('');
        $('#periodicity_id').val('');
        $('#codeAltan').val('');
        $('#codeAltanSuplementary').val('');
        $('#title').val('');
        $('#description').val('');
        $('#price_pay').val('');
        $('#price_remaining').val('');
        $('#broadband').val('');
        $('#supplementary').val('N');
        $('#type').val('');
        $("#list_fiber_zones").addClass('d-none');
        $("#fiber_zone").val('');
        $("#fiber_services").val('');
        $('#serv-fiber-zone').val('');
        $('#serv-fiber-zone-container').html('');
        $('#fiber_zone option').prop('disabled',false);

        $('#service_type').val('');
        //$('#method_pay').val('');
        $('#status').val('A');
        $('#service_form').attr('action', 'api/services/store');
        $('#service_form').attr('method', 'POST');
        $('#gb').val(0);
        chanels.clear();
        $('#chanels-error').hide();
        conc.clear();
        $('#conc-error').hide();
        lists.clear();
        $('#listsA').val('');
        $('#content-especial-service').hide();
        $('#channels-content').hide();
        $('#conc-content').hide();
        $('#service-list').hide();
        $('#sms-content').hide();
        $('#min-content').hide();
        $('#nbte-content').hide();
        $('#gb-container').show();
        $('#is_band_twenty_eight').val('');
        $('#sms').val(0);
        $('#min').val(0);

        $('#listsa-content').hide();
    }
}

$('#myModal').on('hidden.bs.modal', function() {
    setModal(null);
});

function closeServiceCard(idza){

    if($("#alert-"+idza).length > 0){
      $("#alert-"+idza).alert('close');
      let fiberzoneelems = $('#serv-fiber-zone').val().split(',').filter((item) => item !== idza);
      fiberzoneelems = fiberzoneelems.join();
      $('#serv-fiber-zone').val(fiberzoneelems);
      idzal = atob(idza).split('-');
      zoneid = idzal[0];
      $('#fiber_zone option[value="'+zoneid+'"]').prop('disabled',false);
    }
}

function addServiceCard(zoneid,zonename,serviceid,servicename){

  let idza=btoa(zoneid+'-'+serviceid).replaceAll('=','').trim();

  let html = '<div class="col-md-3 mb-2 p-0 alert alert-dismissible fade show" role="alert" id="alert-'+idza+'">';
  html += '<div class="mx-md-2 my-0 px-4 py-3 alert-personal" >';
  html += '<strong>Zona: </strong>'+zonename;
  html += '<br>';
  html += '<strong>Servicio: </strong>'+servicename;
  html += '<button type="button" class="close" onclick="closeServiceCard(\''+idza+'\')" aria-label="Close">';
  html += '<span aria-hidden="true">×</span>';
  html += '</button>';
  html += '</div>';
  html += '</div>';

  $('#serv-fiber-zone-container').append(html);

  fiberzoneelems=[];
  if($('#serv-fiber-zone').val().length > 0)
    fiberzoneelems = $('#serv-fiber-zone').val().split(',');

  fiberzoneelems.push(idza);

  fiberzoneelems = fiberzoneelems.join();

  $('#serv-fiber-zone').val(fiberzoneelems);

}

$(document).ready(function() {
    $('#chanels').selectize(
    {
        onChange: function(value, isOnInitialize) {
            if(value !== null){
                $('#chanels').val(value);
                $('#chanels').valid();
                $('#conc').valid();
            }
            else{
                $('#chanels-error').hide();
            }
        }
    });

    $('#conc').selectize(
    {
        onChange: function(value, isOnInitialize) {
            if(value !== null){
                $('#conc').val(value);
                $('#chanels').valid();
                $('#conc').valid();
            }
            else{
                $('#conc-error').hide();
            }
        }
    });
    $('#lists').selectize();
    $('#content-especial-service').hide();
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

        jQuery.validator.addMethod("select_zonFibra", function(value, element) {
            if($('#addServBtn').data('val') === 0){
              if ($("#artic_type").val() === 'F') {
                if ($("#fiber_zone").val() === "") {
                  return false;
                }
              }
            }
            return true;
        }, "Por Favor agrege una relacion del Servicio con zona de fibra");

        jQuery.validator.addMethod("select_servFibra", function(value, element) {
            if($('#addServBtn').data('val') === 0){
              if ($("#artic_type").val() === 'F') {
                if ($("#fiber_service").val() === "") {
                  return false;
                }
              }
            }
            return true;
        }, "Por Favor agrege una relacion del Servicio con zona de fibra");

        jQuery.validator.addMethod("valid_chanels_concs", function(value, element) {
            if($('#type').val() === 'P' && $('#plan_type').val() === 'G' && $('#chanels').val() === null  && $('#conc').val() === null){
                return false;
            }
            else
                return true;
        }, "Debe incluir un valor en canal o en concentrador");
    }

    $('#codeAltanSuplementary').val('');
    $('#codeAltanSuplementaryContainer').hide();
    $('#sms-content').hide();
    $('#min-content').hide();
    $('#nbte-content').hide();
    $('#is_band_twenty_eight').val('');
    /*
     *   Oculto la opcion retencion cuando no sea la opcion internet hogar en el type_service cuando
     * se carga para editar
     */
    if ($('#service_type').val() != 'H') {
        $('#type option[value="R"]').hide();
    }
    /**/
    $('#service_type').on('change', function(e) {
        $('#content-especial-service').hide();
        $('#listsa-content').hide();
        switch ($(this).val())
        {
            case "H":
                if($('#broadband-content').css('display') == 'none'){
                    $('#broadband').val('');
                    $('#broadband-content').show();
                }

                if ($('#type').val() == 'A') {
                    $('#codeAltanSuplementaryContainer').hide();
                } else {
                    if($('#codeAltanSuplementaryContainer').css('display') == 'none'){
                        $('#codeAltanSuplementary').val('');
                        $('#codeAltanSuplementaryContainer').show();
                    }
                }

                if($('#codeAltan-content').css('display') == 'none'){
                    $('#codeAltan').val('');
                    $('#codeAltan-content').show();
                }
                $("#list_fiber_zones").addClass('d-none');
                $("#fiber_zone").val('');

                $('#sms-content').hide();
                $('#min-content').hide();
                $('#nbte-content').hide();
                $('#is_band_twenty_eight').val('');

                if($('#gb-container').css('display') == 'none'){
                    $('#gb').val('0');
                    $('#gb-container').show();
                }

                $("#type option[value='R']").show();
            break;

            case "M": //mifi
            case "MH":
                $('#broadband-content').hide();
                $('#broadband').val('');
                if ($('#type').val() == 'A') {
                    $('#codeAltanSuplementaryContainer').hide();
                    if($('#codeAltan-content').css('display') == 'none'){
                        $('#codeAltan').val('');
                        $('#codeAltan-content').show();
                    }
                } else {
                    if($('#codeAltanSuplementaryContainer').css('display') == 'none'){
                        $('#codeAltanSuplementary').val('');
                        $('#codeAltanSuplementaryContainer').show();
                    }
                    $('#codeAltan-content').hide();
                }

                $("#list_fiber_zones").addClass('d-none');
                $("#fiber_zone").val('');

                $('#sms-content').hide();
                $('#min-content').hide();
                $('#nbte-content').hide();
                $('#is_band_twenty_eight').val('');
                $('#type option[value="R"]').hide();
                $('#type').val('');
            break;

            case "T": //telefonia
                $('#broadband-content').hide();
                $('#broadband').val('');
                if ($('#type').val() == 'P') {
                    if($('#codeAltanSuplementaryContainer').css('display') == 'none'){
                        $('#codeAltanSuplementary').val('');
                        $('#codeAltanSuplementaryContainer').show();
                    }
                } else {
                    $('#codeAltanSuplementaryContainer').hide();
                }

                if($('#codeAltan-content').css('display') == 'none'){
                    $('#codeAltan').val('');
                    $('#codeAltan-content').show();
                }

                $("#list_fiber_zones").addClass('d-none');
                $("#fiber_zone").val('');

                if($('#sms-content').css('display') == 'none'){
                    $('#sms').val('0');
                    $('#sms-content').show();
                }

                if($('#min-content').css('display') == 'none'){
                    $('#min').val('0');
                    $('#min-content').show();
                }

                if($('#nbte-content').css('display') == 'none'){
                    $('#is_band_twenty_eight').val('Y');
                    $('#nbte-content').show();
                }

                $('#type option[value="R"]').hide();
                $('#type').val('');
            break;

            case "F":
                $("#list_fiber_zones").removeClass('d-none');
                $("#fiber_zone").val('');

                $('#codeAltan-content').hide();
                $('#codeAltanSuplementaryContainer').hide();
                $('#broadband-content').hide();
                $('#sms-content').hide();
                $('#min-content').hide();
                $('#nbte-content').hide();
                $('#is_band_twenty_eight').val('');
                $('#gb-container').hide();
                $('#type option[value="R"]').hide();
                $('#type').val('');
            break;

            default:
                //console.log('seleccione');

                if($('#broadband-content').css('display') == 'none'){
                    $('#broadband').val('');
                    $('#broadband-content').show();
                }

                if($('#codeAltan-content').css('display') == 'none'){
                    $('#codeAltan').val('');
                    $('#codeAltan-content').show();
                }

                if($('#gb-container').css('display') == 'none'){
                    $('#gb').val('0');
                    $('#gb-container').show();
                }

                $("#list_fiber_zones").addClass('d-none');
                $("#fiber_zone").val('');

                $('#codeAltanSuplementaryContainer').hide();
                $('#sms-content').hide();
                $('#min-content').hide();
                $('#nbte-content').hide();
                $('#is_band_twenty_eight').val('');
                $('#type option[value="R"]').hide();
                $('#type').val('');
        }

        // if ($(this).val() == 'T') {
        //     // $('#broadband-content').hide();
        //     // $('#broadband').val('');
        //     // if ($('#type').val() == 'P') {
        //     //     $('#codeAltanSuplementaryContainer').show();
        //     //     $('#codeAltan-content').show();
        //     // } else {
        //     //     $('#codeAltanSuplementaryContainer').hide();
        //     //     $('#codeAltan-content').show();
        //     // }
        //     // $('#sms-content').show();
        //     // $('#min-content').show();
        //     // $('#nbte-content').show();
        //     // $('#is_band_twenty_eight').val('Y');
        // } else{
        //     if ($(this).val() == 'M' || $(this).val() == 'MH') {
        //         // $('#broadband-content').hide();
        //         // $('#broadband').val('');
        //         // if ($('#type').val() == 'A') {
        //         //     $('#codeAltanSuplementaryContainer').hide();
        //         //     $('#codeAltan-content').show();
        //         // } else {
        //         //     $('#codeAltanSuplementaryContainer').show();
        //         //     $('#codeAltan-content').hide();
        //         // }
        //         // $('#sms-content').hide();
        //         // $('#min-content').hide();
        //         // $('#nbte-content').hide();
        //         // $('#is_band_twenty_eight').val('');
        //     } else {
        //         if ($(this).val() == 'F') {
        //             // $('#servEightFifteen-content').show();
        //             // $('#codeAltan-content').hide();
        //             // $('#codeAltanSuplementaryContainer').hide();
        //             // $('#broadband-content').hide();
        //             // $('#sms-content').hide();
        //             // $('#min-content').hide();
        //             // $('#nbte-content').hide();
        //             // $('#is_band_twenty_eight').val('');
        //             // $('#gb-container').hide();
        //         }
        //         else{
        //             // $('#broadband-content').show();
        //             // if ($('#type').val() == 'A') {
        //             //     $('#codeAltanSuplementaryContainer').hide();
        //             //     $('#codeAltan-content').show();
        //             // } else {
        //             //     $('#codeAltanSuplementaryContainer').show();
        //             //     $('#codeAltan-content').show();
        //             // }
        //             // $('#sms-content').hide();
        //             // $('#min-content').hide();
        //             // $('#nbte-content').hide();
        //             // $('#is_band_twenty_eight').val('');
        //             // $('#gb-container').show();
        //             /*
        //              * MUestro la opcion de retencion cuando se seleccion internet hogar
        //              */
        //             // if ($(this).val() == 'H') {
        //             //     $("#type option[value='R']").show();
        //             // }
        //             /**/
        //         }
        //     }
        // }
        /*
         *   Oculto el campo retencion cuando modifico el type service  a otro que nos sea internet hogar
         */
        // if ($(this).val() != 'H') {
        //     $('#type option[value="R"]').hide();
        //     $('#type').val('');
        // }
        /**/
    });

    $('#type').on('change', function() {
        $('#codeAltanSuplementaryContainer').hide();
        $('#channels-content').hide();
        $('#conc-content').hide();
        $('#content-especial-service').hide();
        $('#listsa-content').hide();
        if ($(this).val() != 'A') {
            $('#codeAltanSuplementaryContainer').show();
            $('#service-list').hide();
            $('#plan_type').val('G');
            $('#channels-content').show();
            $('#conc-content').show();
            $('#content-especial-service').show();
            if($(this).val() != ''){
                $('#listsa-content').hide();
            }
            if ($('#service_type').val() == 'T' || $('#service_type').val() == 'M' || $('#service_type').val() == 'MH') {
                $('#codeAltan-content').hide();
            } else {
                /*
                 * Oculto el campo altan code  y los servicios especiales y muestro el altan suplementario
                 */
                if ($(this).val() == 'R' && $('#service_type').val() == 'H') {
                    $('#codeAltan-content').hide();
                    $('#codeAltanSuplementaryContainer').show();
                    $('#content-especial-service').hide();
                    $('#content-blim-service').hide();
                } else {
                    $('#codeAltan-content').show();
                    $('#content-blim-service').show();
                }
                /**/
            }

            if ($('#service_type').val() == 'T'){

                if ($(this).val() == 'P') {

                    $('#codeAltanSuplementaryContainer').show();
                    $('#codeAltan-content').show();
                }
                else{

                    $('#codeAltanSuplementaryContainer').hide();
                    $('#codeAltan-content').show();
                }
            }
        } else {
            $('#content-especial-service').hide();
            $('#listsa-content').show();
            if ($(this).val() == 'R') {
                $('#content-blim-service').hide();
            } else {
                $('#content-blim-service').show();
            }

            if ($('#service_type').val() == 'T' || $('#service_type').val() == 'M' || $('#service_type').val() == 'MH') {
                $('#codeAltan-content').show();
            } else {
                $('#codeAltanSuplementaryContainer').hide();
            }
        }
        if($('#service_type').val() == 'F'){
            $('#codeAltan-content').hide();
            $('#codeAltanSuplementaryContainer').hide();
        }
    });
    $('#plan_type').on('change', function(e) {
        if ($(this).val() == 'G') {
            $('#channels-content').show();
            $('#conc-content').show();
            $('#service-list').hide();
        } else {
            $('#channels-content').hide();
            $('#conc-content').hide();
            $('#service-list').show();
        }
    });
    $("#open_modal_btn").on('click',()=>{ $("#myModal").modal(); });

    $("#fiber_zone").on("change", function() {

        if($('#fiber_zone').val() != "" && $('#fiber_zone').val() != "0"){
            $('.preloader').show();
            params={
                fiber_zone_id:$('#fiber_zone').val()
            };

            request ('api/services/get-fiber-services-list', 'POST', params,
                function (res) {

                    $("#fiber_service option[value!='']").remove();

                    if ( res.success ) {
                        $.each(res.data, function (i, item) {
                            $('#fiber_service').append($('<option>', {
                                value: item.id,
                                text : item.title
                            }));
                        });

                    }
                    else{
                        alert(res.msg)
                        console.log(res.msg);
                    }
                    $('.preloader').hide();
                },
                function (res) {
                    alert('Hubo un error consultando servivios de fibra en zona');
                    console.log("error");
                    $('.preloader').hide();
                });
        }
        else{
            $("#fiber_service option[value!='']").remove();
        }

    });

    $("#addServBtn").on("click",() => {

        $("#addServBtn").data('val',0);

        valzone=$("#fiber_zone").valid();
        valserv=$("#fiber_service").valid();

        if(valzone && valserv){
          let zoneid = $('#fiber_zone option:selected').val();
          let servid = $('#fiber_service option:selected').val();
          let zonename = $('#fiber_zone option:selected').text().replace(/\n/g,'').trim();
          let servicename = $('#fiber_service option:selected').text().replace(/\n/g,'').trim();

          addServiceCard(zoneid,zonename,servid,servicename);

          $("#fiber_service option[value!='']").remove();
          $('#fiber_zone option:selected').prop('disabled',true);
          $('#fiber_zone').val('');

        }

        $('select#fiber_zone').focus();
        $('select#fiber_zone').select();

    });
});