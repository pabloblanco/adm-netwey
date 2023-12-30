function swalalert(id_form){
    swal("Por favor ingrese su segunda contraseña", {
        content: {
            element: "input",
            attributes: {
              placeholder: "",
              type: "password",
            },
        },
        closeOnClickOutside: false,
        closeOnEsc: false,
        buttons: {
            cancel: true,
            confirm: true,
        }
    })
    .then((value) => {
        if(value && value != ''){
            $(".preloader").fadeIn();
            var params = new FormData();
            file = document.getElementById('image').files[0];
            params.append('_token', $('meta[name="csrf-token"]').attr('content'));
            params.append('image', file);
            params.append('nro_deposit', $('#nro_deposit').val());
            params.append('bank', $('#bank').val());
            params.append('date_deposit', $('#date_deposit').val());
            params.append('amount', $('#amount').val());
            params.append('second_pass', value);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                contentType: false,
                processData: false,
                cache: false,
                async: true,
                url: $(id_form).attr('action'),
                method: "POST",
                data: params,
                success: function (res) {
                    $('#myModal').hide();
                    $('.modal-backdrop').remove();
                    $( "body" ).removeClass( "modal-open" );
                    $(".preloader").fadeOut();
                    getview('concentrator/balance');
                    alert(res.msg);
                },
                error: function (res) {
                    $(".preloader").fadeOut();
                    console.log(res);
                }
            });
        }else{
            if(value != null){
                swal('Debe ingresar su segunda contraseña.')
                .then(() => {
                    swalalert(id_form);
                });

            }
        }
    });
}

function save(id){
    var id_form = '#concentrator_form';
    if ($(id_form).valid()) {
        $(id_form).submit(function (e) {
            e.preventDefault();
        });
        swalalert(id_form);
        // swal("Por favor ingrese su segunda contraseña", {
        //     content: {
        //         element: "input",
        //         attributes: {
        //           placeholder: "",
        //           type: "password",
        //         },
        //     }
        // })
        // .then((value) => {
        //     if(value && value != ''){
        //         $(".preloader").fadeIn();
        //         var params = new FormData();
        //         file = document.getElementById('image').files[0];
        //         params.append('_token', $('meta[name="csrf-token"]').attr('content'));
        //         params.append('image', file);
        //         params.append('nro_deposit', $('#nro_deposit').val());
        //         params.append('bank', $('#bank').val());
        //         params.append('date_deposit', $('#date_deposit').val());
        //         params.append('amount', $('#amount').val());
        //         params.append('second_pass', value);
        //         $.ajax({
        //             headers: {
        //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //             },
        //             contentType: false,
        //             processData: false,
        //             cache: false,
        //             async: true,
        //             url: $(id_form).attr('action'),
        //             method: "POST",
        //             data: params,
        //             success: function (res) {
        //                 $('#myModal').hide();
        //                 $('.modal-backdrop').remove();
        //                 $( "body" ).removeClass( "modal-open" );
        //                 $(".preloader").fadeOut();
        //                 getview('concentrator/balance');
        //                 alert(res.msg);
        //             },
        //             error: function (res) {
        //                 $(".preloader").fadeOut();
        //                 console.log(res);
        //             }
        //         });
        //     }else{
        //         swal('Debe ingresar su segunda contraseña.');
        //     }
        // });
    } else {
        $(id_form).submit(function (e) {
            e.preventDefault();
        })
    }
}

function setModal (concentrator) {
    concentrator = concentrator.replace(/\\\'/g, '"').replace(/\'/g, '"');
    var object = JSON.parse(concentrator);
    if (object != null) {
        $('#concentrator_form').attr('action', 'api/concentrator/balance/' + object.id);
        $('#modal_name').html(object.name);
        $('#modal_address').html(object.address);
        $('#modal_email').html(object.email);
        $('#modal_phone').html(object.phone);
        $('#modal_balance').html(object.balance);
        $('#commissions_amount').html(object.commissions*100);
        $('#myModal').modal();
    }
}
/*$('#myModal').on('hidden.bs.modal', function () {
    setModal(null);
});*/
$(document).ready(function () {
    var format = {autoclose: true, format: 'yyyy-mm-dd'};
    $('#date_deposit').datepicker(format);

    $('#concentrator_form').validate({
        rules: {
            nro_deposit: {
                number: true,
                required: true
            },
            date_deposit: {
                required: true
            },
            bank: {
                required: true
            },
            amount: {
                required: true,
                number: true,
                min: 0.1
            }
        },
        messages: {
            nro_deposit: {
                number: "Por favor especifique un número de depósito/transferencia válido",
                required: "Por favor especifique el número de depósito/transferencia"
            },
            date_deposit: {
                required: "Por favor especifique fecha del depósito/transferencia"
            },
            bank: {
                required: "Por favor seleccione un banco"
            },
            amount: {
                required: "Por favor especifique el monto de depósito/transferencia",
                number: "El monto de depósito/transferencia debe ser un número",
                min: "El monto de depósito/transferencia debe ser un valor mayor a 0"
            }
        }
    });
    $('#concentratorBalanceTable').DataTable({
        "columnDefs": [
            {
                "targets": 4,
                "orderable": false
            }
        ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        },
        processing: true,
        serverSide: true,
        ajax: 'api/concentrator/balance/datatable',
        columns: [
            {data: 'action', render:
                function(data,type,row,meta) {
                    var html = '<button type="button" class="btn btn-info btn-md button" onclick="setModal(\'' + JSON.stringify(row).replace(/"/g, '\\\'') + '\');">Ver</button>';
                    return html;
                }
            },
            {data: 'name'},
            {data: 'email'},
            {data: 'phone'},
            {data: 'balance_txt'}
        ]
    });
    $("#amount").on('keyup', function(){
        try{
        var value = ($(this).val())*1;
        assigned_amount = value/(1-($('#commissions_amount').html()/100));
        total_amount = assigned_amount+($('#modal_balance').html()*1);
            $('#assigned_amount').html(assigned_amount.toFixed(2));
            $('#total_amount').html(total_amount.toFixed(2));
        }catch(e){
            $('#assigned_amount').html(0);
            console.log(e);
        }
    });
});