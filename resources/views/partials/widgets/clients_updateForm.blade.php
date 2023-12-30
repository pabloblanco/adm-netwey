<link href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/16.0.0/css/intlTelInput.css" rel="stylesheet"/>
{{--
<button data-target="#editModal" data-toggle="modal" hidden="" id="open_edit_btn" type="button">
</button>
--}}
<!--modal de editar-->
<div class="modal" id="editModal" role="dialog">
    <div class="modal-dialog" id="modal01">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button class="close" data-dismiss="modal" type="button">
                    ×
                </button>
                <h4 class="modal-title">
                    Editar datos del DN:
                    <span id="modal_edit_dn">
                    </span>
                </h4>
            </div>
            <form action="api/client/update" id="edit_client_form" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">
                                    Nombre del cliente (*)
                                </label>
                                <input autocomplete="off" class="form-control" id="modal_edit_name" name="name" placeholder="Ingrese el nombre del cliente" type="text">
                                </input>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">
                                    Apellido del cliente (*)
                                </label>
                                <input autocomplete="off" class="form-control" id="modal_edit_last_name" name="last_name" placeholder="Ingrese el apellido del cliente" type="text">
                                </input>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group phone_group">
                                <label class="control-label w-100">
                                    Teléfono de contacto (*)
                                </label>
                                <input autocomplete="off" class="form-control phoneblock" id="modal_edit_phone" name="phone" placeholder="Ingrese el teléfono de contacto del cliente" type="text">
                                </input>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group phone_group">
                                <label class="control-label w-100">
                                    Otro teléfono de contacto
                                </label>
                                <input autocomplete="off" class="form-control phoneblock" id="modal_edit_phone_2" name="phone2" placeholder="Ingrese el teléfono del cliente" type="text">
                                </input>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">
                                    Correo electrónico (*)
                                </label>
                                <input autocomplete="off" class="form-control" id="modal_edit_email" name="email" placeholder="Ingrese el correo electrónico del cliente" type="email">
                                </input>
                                <span id="status_mail">
                                </span>
                            </div>
                        </div>
                        <div class="col-md-10">
                            <div class="form-group">
                                <label class="control-label">
                                    Dirección (*)
                                </label>
                                <input class="form-control" id="modal_edit_address" name="address" placeholder="Ingrese la dirección del cliente" type="text">
                                </input>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-warning btn-md button" id="btn_send" onclick="saveProfile();" title="" type="submit">
                                <i class="fa fa-check">
                                </i>
                                Guardar
                            </button>
                        </div>
                    </div>
                    <input id="temp_mailvalid" name="mailvalid" type="hidden">
                    </input>
                    <input id="origin_dni" name="dni" type="hidden">
                    </input>
                    <input id="origin_dn" name="msisdn" type="hidden">
                    </input>
                    <input id="origin_name" name="origin_name" type="hidden"/>
                    <input id="origin_name_last" name="origin_name_last" type="hidden"/>
                    <input id="origin_phone" name="origin_phone" type="hidden"/>
                    <input id="origin_phone2" name="origin_phone2" type="hidden"/>
                    <input id="origin_email" name="origin_email" type="hidden"/>
                    <input id="origin_address" name="origin_address" type="hidden"/>
                    <input id="origin_users" name="origin_users" type="hidden"/>
                </div>
            </form>
            <span class="text-right pr-5 pb-1">
                <strong>
                    * Elemento requerido
                </strong>
            </span>
        </div>
    </div>
</div>
<input id="editing_msisdn" type="hidden"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput-jquery.min.js">
</script>
<script>
    function formatPhone(elem,natmode=false){

        $(elem).intlTelInput("setCountry",'mx'); // se establece mx siempre en caso de que el usuario coloque el cod de otro pais
        if(natmode==true){
            var num = $(elem).intlTelInput("getNumber", intlTelInputUtils.numberFormat.NATIONAL); //formato nacional (999 999 9999)
        }
        if(natmode==false){
            var num = $(elem).intlTelInput("getNumber"); //formato internacional (+529999999999)
        }
        num=num.replace('+52', '');
        $(elem).val(num);
    }

    function deshabilitarButon(){
        $('#btn_send').prop('disabled', true);
        $('#btn_send').removeClass('btn-warning');
        $('#btn_send').addClass('btn-dark');
        $('#btn_send').attr('title', 'Debe seleccionar un correo valido para continuar');   
    }

    function habilitarButon(){
        $('#btn_send').prop('disabled', false);
        $('#btn_send').removeClass('btn-dark');
        $('#btn_send').addClass('btn-warning');
        $('#btn_send').attr('title', '');   
    }

    $(document).ready(function() {

        jQuery.extend(jQuery.validator.messages, {
            required: "Este campo es obligatorio.",
            remote: "Por favor, rellena este campo.",
            email: "Por favor, escribe una dirección de correo válida",
            url: "Por favor, escribe una URL válida.",
            date: "Por favor, escribe una fecha válida.",
            dateISO: "Por favor, escribe una fecha (ISO) válida.",
            number: "Por favor, escribe un número entero válido.",
            digits: "Por favor, escribe sólo dígitos.",
            creditcard: "Por favor, escribe un número de tarjeta válido.",
            equalTo: "Por favor, escribe el mismo valor de nuevo.",
            accept: "Por favor, escribe un valor con una extensión aceptada.",
            maxlength: jQuery.validator.format("Por favor, no escribas más de {0} caracteres."),
            minlength: jQuery.validator.format("Por favor, no escribas menos de {0} caracteres."),
            rangelength: jQuery.validator.format("Por favor, escribe un valor entre {0} y {1} caracteres."),
            range: jQuery.validator.format("Por favor, escribe un valor entre {0} y {1}."),
            max: jQuery.validator.format("Por favor, escribe un valor menor o igual a {0}."),
            min: jQuery.validator.format("Por favor, escribe un valor mayor o igual a {0}.")
        });

      jQuery.validator.addMethod("phoneValidate", function(value, element) {
          return this.optional(element) || ($(element).intlTelInput("isValidNumber") && /^[-.0-9\s]+$/g.test(value));
      }, 'Nro Teléfonico inválido.');


      jQuery.validator.addMethod("mailValidate", function(value, element) {
        return this.optional(element) || /^[a-zA-Z0-9_\-\.~]{2,}@[a-zA-Z0-9_\-\.~]{2,}\.[a-zA-Z]{2,4}$/.test(value);
      }, 'Email inválido.');


    $('#editModal').on('shown.bs.modal', function () {
        $(".phoneblock").intlTelInput({
            onlyCountries: ["mx"], //solo selecciona mexico
            initialCountry: "mx", //inicio la lista con mexico
            separateDialCode: true, // le doy separacion a los num
            // preferredCountries: ["mx"],//De toda la lista elijo mexico
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.0/js/utils.js"
        });
        $('.iti__selected-flag').css('height',$('#modal_edit_phone').css('height'));
        $('#edit_client_form .phone_group .iti').addClass('w-100');
        $('#status_mail').html('');
    });

    jQuery.validator.setDefaults({
      debug: true,
      success: "valid"
    });

    $('#edit_client_form').validate({
        rules: {
            name: {
                minlength: 3,
                required: true
            },
            last_name: {
                minlength: 3,
                required: true
            },
            phone: {
                required: true,
               // maxlength: 10,
               // minlength: 10,
               // digits: true,
                phoneValidate: true
            },
            phone2: {
              //  maxlength: 10,
              //  minlength: 10,
               // digits: true,
                phoneValidate: true
            },
            email: {
                //required: true,
                mailValidate: true
            },
            address: {
                //required: true
            }
        }/*,
        messages: {
            name: {
                minlength: "EL nombre es muy corto",
                required: "El nombre es requerido"
            },
            last_name: {
                 minlength: "El apellido es muy corto",
                required: "El apellido es requerido"
            },
            phone: {
                required: "El telefono es requerido"
               // maxlength: "El numero excede con el tamano permitido",
               // minlength: "El numero le faltan digitos para ser permitido",
               //digits: "No es premitido caracteres que no sea numero"
            }
        }*/
    });

    $("#modal_edit_email").blur(function(){
        //console.log('dni: ',$('#modal_edit_dni').val());
        //console.log('email: ',$('#modal_edit_email').val());
      //  console.log('msjmail ',$('#modal_edit_email-error').html());
      
        if($('#modal_edit_email-error').html()==="" && $('#modal_edit_email').val()!=""){
            $.ajax({
               //headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url: "{{route('check_email')}}",
                type: 'POST',
                dataType: 'json',
                data:{
                    '_token': "{{ csrf_token() }}",
                    'dni': $('#origin_dni').val(),
                    'mail': $('#modal_edit_email').val()
                },
                success: function(data){
                    
                        $('#status_mail').html(data.msj);
                        var stop;
                        if(data.code){
                            $('#temp_mailvalid').val('true');
                            stop = 10000;
                            habilitarButon();
                        }else{
                            $('#temp_mailvalid').val('false');
                            stop = 110000;
                            deshabilitarButon();
                        }
                       // console.log('code: ',$('#temp_mailvalid').val());
                       setTimeout(() => {
                            $('#status_mail').html('');
                        }, stop);
                },
                error: function(data){
                   console.log('error-> ',data);
                }
            });
        }else{
            $('#status_mail').html('');
            deshabilitarButon();
            if($('#modal_edit_email').val()===""){
                habilitarButon();
            }
            
        }
    });

    $('#modal_edit_email').on('change', function(e){
        var mail = $('#modal_edit_email').val();
        mail = mail.toLowerCase();
        $('#modal_edit_email').val(mail);
    });

    $('#modal_edit_phone,#modal_edit_phone_2').on('change', function(e){
        formatPhone($(this),true); //formateo de numero en formato nacional
    });

  });
</script>
